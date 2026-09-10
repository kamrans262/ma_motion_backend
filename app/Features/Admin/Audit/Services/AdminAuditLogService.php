<?php

namespace App\Features\Admin\Audit\Services;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class AdminAuditLogService
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->with('admin:id,name,email')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();
    }

    /** @param array<string, mixed> $filters @return Collection<int, AdminAuditLog> */
    public function export(array $filters): Collection
    {
        return $this->query($filters)
            ->with('admin:id,name,email')
            ->latest('id')
            ->limit(5000)
            ->get();
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        $now = CarbonImmutable::now(config('app.timezone'));

        return [
            'total_audit_events' => AdminAuditLog::query()->count(),
            'audit_events_today' => AdminAuditLog::query()->where('created_at', '>=', $now->startOfDay())->count(),
            'audit_events_7_days' => AdminAuditLog::query()->where('created_at', '>=', $now->subDays(6)->startOfDay())->count(),
            'admins' => User::query()
                ->where('role', UserRole::Admin->value)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ];
    }

    /** @param array<string, mixed> $filters @return Builder<AdminAuditLog> */
    private function query(array $filters): Builder
    {
        $query = AdminAuditLog::query();

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('event', 'like', $like)
                    ->orWhere('route_name', 'like', $like)
                    ->orWhere('path', 'like', $like)
                    ->orWhere('target_type', 'like', $like)
                    ->orWhereHas('admin', function (Builder $adminQuery) use ($like): void {
                        $adminQuery->where('name', 'like', $like)->orWhere('email', 'like', $like);
                    });
            });
        }

        if (! empty($filters['admin_id'])) {
            $query->where('admin_user_id', (int) $filters['admin_id']);
        }

        if (! empty($filters['method'])) {
            $query->where('method', (string) $filters['method']);
        }

        if (! empty($filters['status'])) {
            $query->where('response_status', (int) $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', CarbonImmutable::parse((string) $filters['from'], config('app.timezone'))->startOfDay());
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', CarbonImmutable::parse((string) $filters['to'], config('app.timezone'))->endOfDay());
        }

        return $query;
    }
}

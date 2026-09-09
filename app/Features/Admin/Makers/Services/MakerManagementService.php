<?php

namespace App\Features\Admin\Makers\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MakerManagementService
{
    /**
     * @param array{search?:string|null,status?:string|null} $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()
            ->where('role', UserRole::Maker->value)
            ->with('makerProfile')
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('makerProfile', static function ($profile) use ($search): void {
                        $profile
                            ->where('bio', 'like', '%'.$search.'%')
                            ->orWhere('location_text', 'like', '%'.$search.'%');
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(static fn (UserStatus $status): array => [$status->value => ucfirst($status->value)])
            ->all();
    }
}

<?php

namespace App\Features\Admin\Notifications\Services;

use App\Features\Notifications\Enums\NotificationType;
use App\Features\Notifications\Models\InAppNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class NotificationManagementService
{
    /**
     * @param array{search?:string|null,status?:string|null,type?:string|null} $filters
     * @return LengthAwarePaginator<int, InAppNotification>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = InAppNotification::query()
            ->with(['recipient:id,name,email,role,status', 'maker:id,name,email,status', 'show:id,maker_id,name,start_date,end_date,is_visible'])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%')
                    ->orWhereHas('recipient', static function ($user) use ($search): void {
                        $user->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('maker', static function ($maker) use ($search): void {
                        $maker->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('show', static fn ($show) => $show->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (($filters['status'] ?? null) === 'read') {
            $query->whereNotNull('read_at');
        } elseif (($filters['status'] ?? null) === 'unread') {
            $query->whereNull('read_at');
        }

        if (! empty($filters['type'])) {
            $query->where('type', (string) $filters['type']);
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @return array{total:int,unread:int,read:int,recipients:int} */
    public function summary(): array
    {
        return [
            'total' => InAppNotification::query()->count(),
            'unread' => InAppNotification::query()->whereNull('read_at')->count(),
            'read' => InAppNotification::query()->whereNotNull('read_at')->count(),
            'recipients' => InAppNotification::query()->distinct()->count('recipient_id'),
        ];
    }

    /** @return array<string, string> */
    public function types(): array
    {
        return NotificationType::labels();
    }
}

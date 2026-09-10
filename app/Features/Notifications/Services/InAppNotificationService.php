<?php

namespace App\Features\Notifications\Services;

use App\Features\Notifications\Models\InAppNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InAppNotificationService
{
    /**
     * @param array{search?:string|null,unread_only?:bool|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, InAppNotification>
     */
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = InAppNotification::query()
            ->where('recipient_id', $user->id)
            ->with(['maker:id,name,status', 'show:id,maker_id,name,start_date,end_date,is_visible'])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        if ((bool) ($filters['unread_only'] ?? false)) {
            $query->whereNull('read_at');
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }

    public function unreadCount(User $user): int
    {
        return InAppNotification::query()
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function findOwned(User $user, int $notificationId): InAppNotification
    {
        return InAppNotification::query()
            ->where('recipient_id', $user->id)
            ->with(['maker:id,name,status', 'show:id,maker_id,name,start_date,end_date,is_visible'])
            ->findOrFail($notificationId);
    }

    public function markRead(InAppNotification $notification): InAppNotification
    {
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $notification->fresh(['maker:id,name,status', 'show:id,maker_id,name,start_date,end_date,is_visible']);
    }

    public function markAllRead(User $user): int
    {
        return InAppNotification::query()
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }
}

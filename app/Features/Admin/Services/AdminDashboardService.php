<?php

namespace App\Features\Admin\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class AdminDashboardService
{
    /**
     * @return array{
     *   total_users:int,
     *   makers:int,
     *   appreciators:int,
     *   active_users:int,
     *   inactive_users:int,
     *   recent_users:Collection<int, User>
     * }
     */
    public function metrics(): array
    {
        return [
            'total_users' => User::query()->count(),
            'makers' => User::query()->where('role', UserRole::Maker->value)->count(),
            'appreciators' => User::query()->where('role', UserRole::Appreciator->value)->count(),
            'active_users' => User::query()->where('status', UserStatus::Active->value)->count(),
            'inactive_users' => User::query()->where('status', UserStatus::Inactive->value)->count(),
            'recent_users' => User::query()
                ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
                ->latest('id')
                ->limit(6)
                ->get(),
        ];
    }
}

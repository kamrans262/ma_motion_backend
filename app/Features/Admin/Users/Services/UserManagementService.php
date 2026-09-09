<?php

namespace App\Features\Admin\Users\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class UserManagementService
{
    /**
     * @param array{search?:string|null,role?:string|null,status?:string|null} $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'email_verified_at', 'last_login_at', 'created_at'])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @return array<string, string> */
    public function roles(): array
    {
        return collect(UserRole::cases())
            ->mapWithKeys(static fn (UserRole $role): array => [$role->value => ucfirst($role->value)])
            ->all();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(static fn (UserStatus $status): array => [$status->value => ucfirst($status->value)])
            ->all();
    }
}

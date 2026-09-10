<?php

namespace App\Features\Admin\Users\Actions;

use App\Features\Account\Services\AccountDeletionService;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class DeleteUserAction
{
    public function __construct(private readonly AccountDeletionService $deletion) {}

    public function execute(User $user): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw ValidationException::withMessages([
                'user' => 'Administrator accounts cannot be deleted from User Management.',
            ]);
        }

        $this->deletion->delete($user);
    }
}

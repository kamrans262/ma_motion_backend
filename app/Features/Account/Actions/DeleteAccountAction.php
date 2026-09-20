<?php

namespace App\Features\Account\Actions;

use App\Features\Account\Services\AccountDeletionService;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class DeleteAccountAction
{
    public function __construct(private readonly AccountDeletionService $deletion) {}

    /** @param array{current_password?:string|null,confirmation:string} $data */
    public function execute(User $user, array $data): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw ValidationException::withMessages(['account' => 'Administrator accounts cannot be deleted from the mobile account endpoint.']);
        }

        if ($user->password !== null && ! Hash::check((string) ($data['current_password'] ?? ''), $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }

        $this->deletion->delete($user);
    }
}

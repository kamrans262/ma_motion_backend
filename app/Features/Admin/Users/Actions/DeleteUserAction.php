<?php

namespace App\Features\Admin\Users\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteUserAction
{
    public function execute(User $user): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw ValidationException::withMessages([
                'user' => 'Administrator accounts cannot be deleted from User Management.',
            ]);
        }

        DB::transaction(static function () use ($user): void {
            $user->tokens()->delete();
            $user->delete();
        });
    }
}

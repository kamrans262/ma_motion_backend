<?php

namespace App\Features\Admin\Users\Actions;

use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateUserAction
{
    /** @param array{name:string,email:string,status:string} $data */
    public function execute(User $actor, User $user, array $data): User
    {
        if ($actor->is($user) && $data['status'] === UserStatus::Inactive->value) {
            throw ValidationException::withMessages([
                'status' => 'You cannot deactivate the administrator account you are currently using.',
            ]);
        }

        return DB::transaction(function () use ($user, $data): User {
            $wasActive = $user->isActive();

            $user->update([
                'name' => trim($data['name']),
                'email' => Str::lower(trim($data['email'])),
                'status' => UserStatus::from($data['status']),
            ]);

            if ($wasActive && ! $user->isActive()) {
                $user->tokens()->delete();
            }

            return $user->refresh();
        });
    }
}

<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SwitchUserExperienceAction
{
    public function execute(User $user, UserRole $experience): User
    {
        if ($user->role !== $experience) {
            throw ValidationException::withMessages([
                'experience' => ['Account roles cannot be switched after creation.'],
            ]);
        }

        return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
    }
}

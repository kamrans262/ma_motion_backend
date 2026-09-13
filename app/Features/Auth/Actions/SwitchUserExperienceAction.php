<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SwitchUserExperienceAction
{
    public function execute(User $user, UserRole $experience): User
    {
        return DB::transaction(function () use ($user, $experience): User {
            $completed = match ($experience) {
                UserRole::Maker => $user->makerProfile()
                    ->whereNotNull('onboarding_completed_at')
                    ->exists(),
                UserRole::Appreciator => $user->appreciatorProfile()
                    ->whereNotNull('onboarding_completed_at')
                    ->exists(),
                UserRole::Admin => false,
            };

            if (! $completed) {
                throw ValidationException::withMessages([
                    'experience' => ['Complete this profile before switching to it.'],
                ]);
            }

            if ($user->role !== $experience) {
                $user->update(['role' => $experience]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

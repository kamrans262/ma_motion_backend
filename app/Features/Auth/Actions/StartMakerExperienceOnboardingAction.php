<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StartMakerExperienceOnboardingAction
{
    public function execute(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            $user->makerProfile()->firstOrCreate(['user_id' => $user->id]);

            if ($user->role !== UserRole::Maker) {
                $user->update(['role' => UserRole::Maker]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

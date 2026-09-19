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
            $makerProfile = $user->makerProfile()->firstOrCreate(['user_id' => $user->id]);
            $appreciator = $user->appreciatorProfile()->first();
            if (blank($makerProfile->location_text) && filled($appreciator?->location_text)) {
                $makerProfile->update([
                    'location_text' => $appreciator->location_text,
                    'location_id' => $makerProfile->location_id ?? $appreciator->location_id,
                ]);
            }

            if ($user->role !== UserRole::Maker) {
                $user->update(['role' => UserRole::Maker]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

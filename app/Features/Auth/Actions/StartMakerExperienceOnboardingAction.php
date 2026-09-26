<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StartMakerExperienceOnboardingAction
{
    public function execute(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            if ($user->role === UserRole::Admin) {
                throw ValidationException::withMessages([
                    'role' => ['Admin accounts cannot be converted to Maker.'],
                ]);
            }

            $appreciator = $user->appreciatorProfile()->first();
            $makerProfile = $user->makerProfile()->firstOrCreate(['user_id' => $user->id]);

            if (blank($makerProfile->location_text) && filled($appreciator?->location_text)) {
                $makerProfile->update([
                    'location_text' => $appreciator->location_text,
                    'location_id' => $makerProfile->location_id ?? $appreciator->location_id,
                ]);
            }

            // Appreciator -> Maker is a one-way account conversion. A user
            // must never retain both profile types.
            $user->appreciatorProfile()->delete();

            if ($user->role !== UserRole::Maker) {
                $user->update(['role' => UserRole::Maker]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CompleteAppreciatorExperienceOnboardingAction
{
    /**
     * @param  array{name:string,email:string,location_text:string,location_id?:int|null}  $data
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->appreciatorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location_text' => trim($data['location_text']),
                    'location_id' => $data['location_id'] ?? null,
                    'onboarding_completed_at' => now(),
                ],
            );

            if ($user->role !== UserRole::Appreciator) {
                $user->update(['role' => UserRole::Appreciator]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

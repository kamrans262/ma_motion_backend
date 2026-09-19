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
            // Appreciator onboarding belongs to the same authenticated user.
            // Persist edited shared fields, without issuing another account/token.
            $user->update([
                'name' => trim($data['name']),
                'email' => mb_strtolower(trim($data['email'])),
            ]);

            $existing = $user->appreciatorProfile()->first();
            $locationText = trim($data['location_text']);

            $user->appreciatorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location_text' => $locationText,
                    'location_id' => $data['location_id'] ?? (
                        $existing?->location_text === $locationText
                            ? $existing->location_id
                            : null
                    ),
                    'onboarding_completed_at' => $existing?->onboarding_completed_at ?? now(),
                ],
            );

            if ($user->role !== UserRole::Appreciator) {
                $user->update(['role' => UserRole::Appreciator]);
            }

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

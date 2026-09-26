<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CompleteAppreciatorExperienceOnboardingAction
{
    /**
     * @param  array{name:string,email:string,location_text:string,location_id?:int|null}  $data
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            if ($user->role !== UserRole::Appreciator) {
                throw ValidationException::withMessages([
                    'role' => ['Only an Appreciator account can update Appreciator onboarding.'],
                ]);
            }

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

            return $user->refresh()->loadMissing(['makerProfile', 'appreciatorProfile']);
        });
    }
}

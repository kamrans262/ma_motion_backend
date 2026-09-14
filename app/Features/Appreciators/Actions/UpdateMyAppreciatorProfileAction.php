<?php

namespace App\Features\Appreciators\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateMyAppreciatorProfileAction
{
    /**
     * @param  array{name:string,email:string,location_text:string,location_id?:int|null}  $data
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $normalizedEmail = mb_strtolower(trim($data['email']));

            $user->update([
                'name' => trim($data['name']),
                'email' => $normalizedEmail,
            ]);

            $user->appreciatorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location_text' => trim($data['location_text']),
                    'location_id' => $data['location_id'] ?? null,
                    'onboarding_completed_at' => $user->appreciatorProfile?->onboarding_completed_at ?? now(),
                ],
            );

            return $user->refresh()->loadMissing(['appreciatorProfile.location']);
        });
    }
}

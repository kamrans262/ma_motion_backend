<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterAppreciatorOnboardingAction
{
    /**
     * @param  array{name:string,email:string,location_text:string,location_id?:int|null,device_name?:string|null}  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => null,
                'role' => UserRole::Appreciator,
                'status' => UserStatus::Active,
            ]);

            $user->appreciatorProfile()->create([
                'location_text' => trim($data['location_text']),
                'location_id' => $data['location_id'] ?? null,
                'onboarding_completed_at' => now(),
            ]);

            $deviceName = trim((string) ($data['device_name'] ?? ''));

            $token = $user->createToken(
                $deviceName !== '' ? $deviceName : 'MA Motion mobile',
                ['mobile'],
            )->plainTextToken;

            return [
                'user' => $user->refresh(),
                'token' => $token,
            ];
        });
    }
}

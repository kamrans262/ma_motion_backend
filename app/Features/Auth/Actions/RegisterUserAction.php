<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterUserAction
{
    /**
     * @param  array{name:string,email:string,password:string,role:string,device_name?:string|null}  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::from($data['role']),
                'status' => UserStatus::Active,
            ]);

            $deviceName = trim((string) ($data['device_name'] ?? ''));

            $token = $user->createToken(
                $deviceName !== '' ? $deviceName : 'MA Motion mobile',
                ['mobile'],
            )->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}

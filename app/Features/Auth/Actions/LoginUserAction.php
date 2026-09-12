<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Exceptions\InactiveAccountException;
use App\Features\Auth\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class LoginUserAction
{
    /**
     * @return array{user: User, token: string}
     */
    public function execute(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! is_string($user->password) || $user->password === '' || ! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if (! $user->isActive()) {
            throw new InactiveAccountException();
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken(
            $deviceName ?: 'MA Motion mobile',
            ['mobile'],
        )->plainTextToken;

        return [
            'user' => $user->refresh(),
            'token' => $token,
        ];
    }
}

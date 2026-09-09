<?php

namespace App\Features\Admin\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Admin\Http\Requests\AdminLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticateAdminAction
{
    /**
     * Authenticate an active administrator using Laravel's session guard.
     *
     * @throws ValidationException
     */
    public function execute(AdminLoginRequest $request): User
    {
        $credentials = [
            'email' => strtolower(trim((string) $request->string('email'))),
            'password' => (string) $request->input('password'),
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match an active administrator account.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }
}

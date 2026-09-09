<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Password;

final class RequestPasswordResetAction
{
    public function execute(string $email): void
    {
        Password::broker()->sendResetLink(['email' => $email]);
    }
}

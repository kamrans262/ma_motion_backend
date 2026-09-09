<?php

namespace App\Features\Auth\Actions;

use App\Models\User;

final class LogoutUserAction
{
    public function execute(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}

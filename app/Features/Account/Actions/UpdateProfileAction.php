<?php

namespace App\Features\Account\Actions;

use App\Models\User;

final class UpdateProfileAction
{
    public function execute(User $user, string $name): User
    {
        $user->update(['name' => trim($name)]);

        return $user->refresh()->loadMissing('makerProfile');
    }
}

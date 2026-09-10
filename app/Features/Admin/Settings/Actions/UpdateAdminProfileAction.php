<?php

namespace App\Features\Admin\Settings\Actions;

use App\Models\User;

final class UpdateAdminProfileAction
{
    /** @param array{name:string,email:string} $data */
    public function execute(User $admin, array $data): User
    {
        $admin->update([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
        ]);

        return $admin->refresh();
    }
}

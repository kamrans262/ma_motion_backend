<?php

namespace App\Features\Admin\Settings\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ChangeAdminPasswordAction
{
    /** @param array{current_password:string,password:string} $data */
    public function execute(User $admin, array $data): void
    {
        if (! Hash::check($data['current_password'], $admin->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }

        DB::transaction(function () use ($admin, $data): void {
            $admin->update(['password' => $data['password']]);
            $admin->tokens()->delete();
        });
    }
}

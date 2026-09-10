<?php

namespace App\Features\Account\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ChangePasswordAction
{
    /** @param array{current_password:string,password:string} $data */
    public function execute(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }

        $currentTokenId = $user->currentAccessToken()?->getKey();

        DB::transaction(function () use ($user, $data, $currentTokenId): void {
            $user->update(['password' => $data['password']]);

            $tokens = $user->tokens();
            if ($currentTokenId !== null) {
                $tokens->whereKeyNot($currentTokenId);
            }
            $tokens->delete();
        });
    }
}

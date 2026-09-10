<?php

namespace App\Features\Saves\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UnsaveMakerAction
{
    public function execute(User $appreciator, User $maker): void
    {
        if (! $appreciator->isActive() || ! $appreciator->hasRole(UserRole::Appreciator)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        // An Appreciator must still be able to remove a previously saved Maker
        // after that Maker is deactivated, so deletion only requires the Maker role.
        if (! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        MakerSave::query()
            ->where('appreciator_id', $appreciator->id)
            ->where('maker_id', $maker->id)
            ->delete();
    }
}

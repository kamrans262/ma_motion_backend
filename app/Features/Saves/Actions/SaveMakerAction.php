<?php

namespace App\Features\Saves\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class SaveMakerAction
{
    public function execute(User $appreciator, User $maker): MakerSave
    {
        if (! $appreciator->isActive() || ! $appreciator->hasRole(UserRole::Appreciator)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        if (! $maker->isActive() || ! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        $now = now();

        MakerSave::query()->insertOrIgnore([
            'appreciator_id' => $appreciator->id,
            'maker_id' => $maker->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return MakerSave::query()
            ->where('appreciator_id', $appreciator->id)
            ->where('maker_id', $maker->id)
            ->firstOrFail();
    }
}

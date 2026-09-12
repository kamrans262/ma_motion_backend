<?php

namespace App\Features\Saves\Actions;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class UnsaveArtworkAction
{
    public function execute(User $user, Artwork $artwork): void
    {
        if (! $user->isActive() || ! $user->hasRole(UserRole::Maker, UserRole::Appreciator)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        ArtworkSave::query()
            ->where('user_id', $user->id)
            ->where('artwork_id', $artwork->id)
            ->delete();
    }
}

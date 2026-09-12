<?php

namespace App\Features\Saves\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class SaveArtworkAction
{
    public function execute(User $user, Artwork $artwork): ArtworkSave
    {
        if (! $user->isActive() || ! $user->hasRole(UserRole::Maker, UserRole::Appreciator)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        $isPublicArtwork = Artwork::query()
            ->whereKey($artwork->getKey())
            ->where('moderation_status', ArtworkModerationStatus::Approved->value)
            ->where('is_visible', true)
            ->whereHas('maker', static function ($maker): void {
                $maker
                    ->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value);
            })
            ->exists();

        if (! $isPublicArtwork) {
            throw (new ModelNotFoundException())->setModel(Artwork::class, [$artwork->getKey()]);
        }

        $now = now();

        ArtworkSave::query()->insertOrIgnore([
            'user_id' => $user->id,
            'artwork_id' => $artwork->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return ArtworkSave::query()
            ->where('user_id', $user->id)
            ->where('artwork_id', $artwork->id)
            ->firstOrFail();
    }
}

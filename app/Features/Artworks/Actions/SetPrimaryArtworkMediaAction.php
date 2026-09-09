<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use Illuminate\Support\Facades\DB;

final class SetPrimaryArtworkMediaAction
{
    public function execute(Artwork $artwork, ArtworkMedia $media): Artwork
    {
        abort_unless($media->artwork_id === $artwork->id, 404);

        DB::transaction(function () use ($artwork, $media): void {
            $artwork->media()->update(['is_primary' => false]);
            $media->forceFill(['is_primary' => true])->save();
        });

        return $artwork->fresh(['maker', 'type', 'style', 'location', 'media']);
    }
}

<?php

namespace App\Features\Admin\Artworks\Actions;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Support\ArtworkData;

final class UpdateArtworkAction
{
    /** @param array<string, mixed> $data */
    public function execute(Artwork $artwork, array $data): Artwork
    {
        $artwork->update(ArtworkData::normalize($data));
        return $artwork->fresh(['maker.makerProfile', 'type', 'style', 'location', 'media']);
    }
}

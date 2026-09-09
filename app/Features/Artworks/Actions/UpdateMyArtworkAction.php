<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Support\ArtworkData;

final class UpdateMyArtworkAction
{
    /** @param array<string, mixed> $data */
    public function execute(Artwork $artwork, array $data): Artwork
    {
        $artwork->fill(ArtworkData::normalize($data));

        if ($artwork->isDirty([
            'title',
            'description',
            'artwork_type_id',
            'artwork_style_id',
            'location_id',
            'location_text',
        ])) {
            $artwork->moderation_status = ArtworkModerationStatus::Pending;
            $artwork->rejection_reason = null;
        }

        $artwork->save();

        return $artwork->load(['maker', 'type', 'style', 'location', 'media']);
    }
}

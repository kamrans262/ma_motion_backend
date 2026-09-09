<?php

namespace App\Features\Admin\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;

final class ModerateArtworkAction
{
    /** @param array{moderation_status:string,rejection_reason?:string|null} $data */
    public function execute(Artwork $artwork, array $data): Artwork
    {
        $status = ArtworkModerationStatus::from($data['moderation_status']);
        $reason = $status === ArtworkModerationStatus::Rejected
            ? trim((string) ($data['rejection_reason'] ?? ''))
            : null;

        $artwork->forceFill([
            'moderation_status' => $status,
            'rejection_reason' => $reason === '' ? null : $reason,
        ])->save();

        return $artwork->fresh(['maker.makerProfile', 'type', 'style', 'location', 'media']);
    }
}

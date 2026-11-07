<?php

namespace App\Features\Artworks\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Models\User;

final class MakerArtworkApprovalService
{
    public function statusForNewArtwork(User $maker): ArtworkModerationStatus
    {
        if ($maker->makerProfile?->artwork_approval_granted_at !== null) {
            return ArtworkModerationStatus::Approved;
        }

        // Preserve approval for legacy Makers whose first artwork was
        // approved before the persistent approval column was introduced.
        $previouslyApproved = $maker->artworks()
            ->withTrashed()
            ->where('moderation_status', ArtworkModerationStatus::Approved->value)
            ->exists();

        return $previouslyApproved
            ? ArtworkModerationStatus::Approved
            : ArtworkModerationStatus::Pending;
    }

    public function statusForEditedArtwork(Artwork $artwork): ArtworkModerationStatus
    {
        // Individual admin rejections cannot be bypassed by editing media.
        // Editing a rejected artwork resubmits that specific item for review.
        if ($artwork->moderation_status === ArtworkModerationStatus::Rejected) {
            return ArtworkModerationStatus::Pending;
        }

        return $this->statusForNewArtwork($artwork->maker);
    }
}

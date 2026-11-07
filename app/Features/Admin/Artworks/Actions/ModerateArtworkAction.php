<?php

namespace App\Features\Admin\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use Illuminate\Support\Facades\DB;

final class ModerateArtworkAction
{
    /** @param array{moderation_status:string,rejection_reason?:string|null} $data */
    public function execute(Artwork $artwork, array $data): Artwork
    {
        $status = ArtworkModerationStatus::from($data['moderation_status']);
        $reason = $status === ArtworkModerationStatus::Rejected
            ? trim((string) ($data['rejection_reason'] ?? ''))
            : null;

        return DB::transaction(function () use ($artwork, $status, $reason): Artwork {
            $artwork->forceFill([
                'moderation_status' => $status,
                'rejection_reason' => $reason === '' ? null : $reason,
            ])->save();

            if ($status === ArtworkModerationStatus::Approved) {
                // Approval is granted to the Maker once, and survives removal
                // or subsequent moderation of their first approved artwork.
                $profile = $artwork->maker->makerProfile()->firstOrCreate([
                    'user_id' => $artwork->maker_id,
                ]);
                if ($profile->artwork_approval_granted_at === null) {
                    $profile->forceFill(['artwork_approval_granted_at' => now()])->save();
                }
            }

            return $artwork->fresh(['maker.makerProfile', 'type', 'style', 'location', 'media']);
        });
    }
}

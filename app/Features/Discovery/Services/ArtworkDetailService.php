<?php

namespace App\Features\Discovery\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;

final class ArtworkDetailService
{
    public function findVisible(int $id): Artwork
    {
        return Artwork::query()
            ->where('moderation_status', ArtworkModerationStatus::Approved->value)
            ->where('is_visible', true)
            ->whereHas('maker', static function (Builder $makerQuery): void {
                $makerQuery
                    ->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value);
            })
            ->with([
                'maker' => static function ($makerQuery): void {
                    $makerQuery
                        ->select(['id', 'name', 'role', 'status', 'created_at'])
                        ->with([
                            'makerProfile.location',
                            'makerProfile.artworkSlots.artwork.primaryMedia',
                        ])
                        ->withCount(['savedByAppreciators as saves_count']);
                },
                'type:id,name,slug,is_active',
                'style:id,name,slug,is_active',
                'location',
                'media',
                'primaryMedia',
            ])
            ->findOrFail($id);
    }
}

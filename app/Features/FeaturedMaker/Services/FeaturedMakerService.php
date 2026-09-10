<?php

namespace App\Features\FeaturedMaker\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\FeaturedMaker\Models\FeaturedMakerSetting;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;

final class FeaturedMakerService
{
    public function current(): ?FeaturedMakerSetting
    {
        $setting = FeaturedMakerSetting::query()
            ->where('slot', FeaturedMakerSetting::POST_LOGIN_SLOT)
            ->where('is_active', true)
            ->whereNotNull('maker_id')
            ->whereHas('maker', static function ($query): void {
                $query->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value);
            })
            ->first();

        if (! $setting) {
            return null;
        }

        $makerId = (int) $setting->maker_id;

        $setting->load([
            'maker' => static function ($query): void {
                $query->select(['id', 'name', 'created_at'])
                    ->with('makerProfile.location')
                    ->withCount(['savedByAppreciators as saves_count']);
            },
            'featuredArtwork' => static function ($query) use ($makerId): void {
                $query->where('maker_id', $makerId)
                    ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true)
                    ->with(['maker:id,name', 'type', 'style', 'location', 'media']);
            },
        ]);

        $setting->maker?->load([
            'artworks' => static function ($query): void {
                $query->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true)
                    ->with(['maker:id,name', 'type', 'style', 'location', 'media'])
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->limit(8);
            },
        ]);

        return $setting;
    }
}

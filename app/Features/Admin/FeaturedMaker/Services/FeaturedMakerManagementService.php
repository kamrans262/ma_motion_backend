<?php

namespace App\Features\Admin\FeaturedMaker\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\FeaturedMaker\Models\FeaturedMakerSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class FeaturedMakerManagementService
{
    public function setting(): FeaturedMakerSetting
    {
        $setting = FeaturedMakerSetting::query()
            ->where('slot', FeaturedMakerSetting::POST_LOGIN_SLOT)
            ->with([
                'maker.makerProfile.location',
                'featuredArtwork.primaryMedia',
                'updatedBy:id,name',
            ])
            ->first();

        if ($setting) {
            if ($setting->maker) {
                $setting->maker->loadCount(['savedByAppreciators as saves_count']);
            }

            return $setting;
        }

        return new FeaturedMakerSetting([
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'eyebrow' => 'Featured Maker',
            'is_active' => false,
        ]);
    }

    /** @return Collection<int, User> */
    public function makers(): Collection
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->with('makerProfile.location')
            ->withCount([
                'savedByAppreciators as saves_count',
                'artworks as public_artworks_count' => static fn ($query) => $query
                    ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);
    }

    public function selectableMaker(?int $makerId): ?User
    {
        if (! $makerId) {
            return null;
        }

        return User::query()
            ->whereKey($makerId)
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->with('makerProfile.location')
            ->withCount([
                'savedByAppreciators as saves_count',
                'artworks as public_artworks_count' => static fn ($query) => $query
                    ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true),
            ])
            ->first();
    }

    /** @return Collection<int, Artwork> */
    public function eligibleArtworks(?User $maker): Collection
    {
        if (! $maker) {
            return collect();
        }

        return Artwork::query()
            ->where('maker_id', $maker->id)
            ->where('moderation_status', ArtworkModerationStatus::Approved->value)
            ->where('is_visible', true)
            ->with('primaryMedia')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(100)
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function update(array $data, User $admin): FeaturedMakerSetting
    {
        $makerId = (int) $data['maker_id'];
        $artworkId = isset($data['featured_artwork_id']) && $data['featured_artwork_id'] !== ''
            ? (int) $data['featured_artwork_id']
            : null;

        if ($artworkId !== null) {
            $validArtwork = Artwork::query()
                ->whereKey($artworkId)
                ->where('maker_id', $makerId)
                ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                ->where('is_visible', true)
                ->exists();

            if (! $validArtwork) {
                throw ValidationException::withMessages([
                    'featured_artwork_id' => 'The spotlight artwork must be an approved, visible artwork owned by the selected Maker.',
                ]);
            }
        }

        return DB::transaction(static function () use ($data, $admin, $makerId, $artworkId): FeaturedMakerSetting {
            return FeaturedMakerSetting::query()->updateOrCreate(
                ['slot' => FeaturedMakerSetting::POST_LOGIN_SLOT],
                [
                    'maker_id' => $makerId,
                    'featured_artwork_id' => $artworkId,
                    'eyebrow' => self::nullableTrimmed($data['eyebrow'] ?? null),
                    'headline' => self::nullableTrimmed($data['headline'] ?? null),
                    'description' => self::nullableTrimmed($data['description'] ?? null),
                    'is_active' => (bool) ($data['is_active'] ?? false),
                    'updated_by' => $admin->id,
                ],
            );
        });
    }

    public function remove(): void
    {
        FeaturedMakerSetting::query()
            ->where('slot', FeaturedMakerSetting::POST_LOGIN_SLOT)
            ->delete();
    }

    private static function nullableTrimmed(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}

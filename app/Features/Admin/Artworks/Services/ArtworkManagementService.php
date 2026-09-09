<?php

namespace App\Features\Admin\Artworks\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use App\Features\Auth\Enums\UserRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ArtworkManagementService
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Artwork>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Artwork::query()
            ->with(['maker:id,name,email', 'type:id,name', 'style:id,name', 'location', 'primaryMedia'])
            ->withCount('media')
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('location_text', 'like', '%'.$search.'%')
                    ->orWhereHas('maker', static function ($maker) use ($search): void {
                        $maker->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        foreach (['maker_id', 'artwork_type_id', 'artwork_style_id', 'location_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['moderation_status'])) {
            $query->where('moderation_status', $filters['moderation_status']);
        }

        if (($filters['visibility'] ?? null) === 'visible') {
            $query->where('is_visible', true);
        } elseif (($filters['visibility'] ?? null) === 'hidden') {
            $query->where('is_visible', false);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function find(int $artworkId): Artwork
    {
        return Artwork::query()
            ->with(['maker.makerProfile', 'type', 'style', 'location', 'media'])
            ->findOrFail($artworkId);
    }

    public function findMedia(Artwork $artwork, int $mediaId): ArtworkMedia
    {
        return ArtworkMedia::query()->where('artwork_id', $artwork->id)->findOrFail($mediaId);
    }

    /** @return Collection<int, User> */
    public function makers(): Collection
    {
        return User::query()->where('role', UserRole::Maker->value)->orderBy('name')->get(['id', 'name', 'email']);
    }

    /** @return Collection<int, ArtworkType> */
    public function types(): Collection
    {
        return ArtworkType::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    /** @return Collection<int, ArtworkStyle> */
    public function styles(): Collection
    {
        return ArtworkStyle::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    /** @return Collection<int, Location> */
    public function locations(): Collection
    {
        return Location::query()->orderBy('sort_order')->orderBy('city')->orderBy('region')->get();
    }

    /** @return array<string, string> */
    public function moderationStatuses(): array
    {
        return ArtworkModerationStatus::labels();
    }
}

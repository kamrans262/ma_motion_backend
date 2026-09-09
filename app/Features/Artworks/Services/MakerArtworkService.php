<?php

namespace App\Features\Artworks\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MakerArtworkService
{
    /**
     * @param array{search?:string|null,moderation_status?:string|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, Artwork>
     */
    public function paginate(User $maker, array $filters): LengthAwarePaginator
    {
        $query = Artwork::query()
            ->where('maker_id', $maker->id)
            ->with(['type', 'style', 'location', 'media'])
            ->orderBy('sort_order')
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('location_text', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['moderation_status'])) {
            $query->where('moderation_status', $filters['moderation_status']);
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findOwned(User $maker, int $artworkId): Artwork
    {
        return Artwork::query()
            ->where('maker_id', $maker->id)
            ->with(['maker', 'type', 'style', 'location', 'media'])
            ->findOrFail($artworkId);
    }

    public function findOwnedMedia(Artwork $artwork, int $mediaId): ArtworkMedia
    {
        return ArtworkMedia::query()
            ->where('artwork_id', $artwork->id)
            ->findOrFail($mediaId);
    }

    /** @return array<string, string> */
    public function moderationStatuses(): array
    {
        return ArtworkModerationStatus::labels();
    }
}

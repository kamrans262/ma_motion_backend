<?php

namespace App\Features\Discovery\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Discovery\Data\LocationMatch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ArtworkDiscoveryService
{
    public function __construct(
        private readonly LocationMatchService $locationMatches,
        private readonly ShowStatusFilterService $showStatuses,
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Artwork>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $locationMatch = $this->locationMatches->match($filters);
        $query = $this->publicArtworkQuery();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        if (! empty($filters['type_ids'])) {
            $query->whereIn('artwork_type_id', array_map('intval', (array) $filters['type_ids']));
        }
        if (! empty($filters['style_ids'])) {
            $query->whereIn('artwork_style_id', array_map('intval', (array) $filters['style_ids']));
        }

        $statuses = array_values((array) ($filters['show_statuses'] ?? []));
        if ($statuses !== []) {
            $showStatuses = $this->showStatuses;
            $query->whereHas('shows', static function (Builder $showQuery) use ($statuses, $showStatuses): void {
                $showQuery->where('is_visible', true);
                $showStatuses->apply($showQuery, $statuses);
            });
        }

        $this->applyLocationMatch($query, $locationMatch);

        $perPage = min(max((int) ($filters['per_page'] ?? 24), 1), 50);
        $paginator = $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $this->attachDistances($paginator->getCollection(), $locationMatch);

        return $paginator;
    }

    /** @return Builder<Artwork> */
    private function publicArtworkQuery(): Builder
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
                        ->with(['makerProfile.location'])
                        ->withCount(['savedByAppreciators as saves_count']);
                },
                'type:id,name,slug,is_active',
                'style:id,name,slug,is_active',
                'location',
                'primaryMedia',
            ]);
    }

    /** @param Builder<Artwork> $query */
    private function applySearch(Builder $query, string $search): void
    {
        $like = '%'.$search.'%';

        $query->where(function (Builder $searchQuery) use ($like): void {
            $searchQuery
                ->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('location_text', 'like', $like)
                ->orWhereHas('maker', static function (Builder $makerQuery) use ($like): void {
                    $makerQuery->where('name', 'like', $like)
                        ->orWhereHas('makerProfile', static function (Builder $profileQuery) use ($like): void {
                            $profileQuery
                                ->where('bio', 'like', $like)
                                ->orWhere('location_text', 'like', $like);
                        });
                })
                ->orWhereHas('shows', static function (Builder $showQuery) use ($like): void {
                    $showQuery->where('is_visible', true)
                        ->where(function (Builder $showText) use ($like): void {
                            $showText
                                ->where('name', 'like', $like)
                                ->orWhere('description', 'like', $like)
                                ->orWhere('location_text', 'like', $like);
                        });
                });
        });
    }

    /** @param Builder<Artwork> $query */
    private function applyLocationMatch(Builder $query, ?LocationMatch $match): void
    {
        if ($match === null) {
            return;
        }

        if ($match->ids === []) {
            $query->whereRaw('1 = 0');
            return;
        }

        $ids = $match->ids;
        $query->where(function (Builder $locationQuery) use ($ids): void {
            $locationQuery
                ->whereIn('location_id', $ids)
                ->orWhere(function (Builder $makerFallback) use ($ids): void {
                    $makerFallback->whereNull('location_id')
                        ->whereHas('maker.makerProfile', static function (Builder $profileQuery) use ($ids): void {
                            $profileQuery->whereIn('location_id', $ids);
                        });
                });
        });
    }

    private function attachDistances($artworks, ?LocationMatch $match): void
    {
        if ($match === null || $match->distancesById === []) {
            return;
        }

        foreach ($artworks as $artwork) {
            $locationId = $artwork->location_id
                ? (int) $artwork->location_id
                : ($artwork->maker?->makerProfile?->location_id ? (int) $artwork->maker->makerProfile->location_id : null);
            $artwork->setAttribute('distance_km', $match->distanceFor($locationId));
        }
    }
}

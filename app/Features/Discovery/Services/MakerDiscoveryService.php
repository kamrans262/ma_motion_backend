<?php

namespace App\Features\Discovery\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Discovery\Data\LocationMatch;
use App\Features\Shows\Enums\ShowStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class MakerDiscoveryService
{
    public function __construct(
        private readonly LocationMatchService $locationMatches,
        private readonly ShowStatusFilterService $showStatuses,
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $locationMatch = $this->locationMatches->match($filters);
        $query = $this->publicMakerQuery();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $this->applySearch($query, $search);
        }

        $typeIds = array_map('intval', array_values((array) ($filters['type_ids'] ?? [])));
        $styleIds = array_map('intval', array_values((array) ($filters['style_ids'] ?? [])));
        if ($typeIds !== [] || $styleIds !== []) {
            $query->whereHas('artworks', static function (Builder $artworkQuery) use ($typeIds, $styleIds): void {
                $artworkQuery
                    ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true);
                if ($typeIds !== []) {
                    $artworkQuery->whereIn('artwork_type_id', $typeIds);
                }
                if ($styleIds !== []) {
                    $artworkQuery->whereIn('artwork_style_id', $styleIds);
                }
            });
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

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);
        $paginator = $query
            ->orderByDesc('saves_count')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $this->attachDistances($paginator->getCollection(), $locationMatch);

        return $paginator;
    }

    /** @return Builder<User> */
    private function publicMakerQuery(): Builder
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->select(['id', 'name', 'role', 'status', 'created_at'])
            ->with(['makerProfile.location'])
            ->withCount([
                'savedByAppreciators as saves_count',
                'artworks as public_artworks_count' => static function (Builder $artworkQuery): void {
                    $artworkQuery
                        ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                        ->where('is_visible', true);
                },
                'shows as current_shows_count' => static function (Builder $showQuery): void {
                    $showQuery->where('is_visible', true)->withStatus(ShowStatus::Current->value);
                },
                'shows as upcoming_shows_count' => static function (Builder $showQuery): void {
                    $showQuery->where('is_visible', true)->withStatus(ShowStatus::Upcoming->value);
                },
            ]);
    }

    /** @param Builder<User> $query */
    private function applySearch(Builder $query, string $search): void
    {
        $like = '%'.$search.'%';

        $query->where(function (Builder $searchQuery) use ($like): void {
            $searchQuery
                ->where('name', 'like', $like)
                ->orWhereHas('makerProfile', static function (Builder $profileQuery) use ($like): void {
                    $profileQuery
                        ->where('bio', 'like', $like)
                        ->orWhere('location_text', 'like', $like);
                })
                ->orWhereHas('artworks', static function (Builder $artworkQuery) use ($like): void {
                    $artworkQuery
                        ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                        ->where('is_visible', true)
                        ->where(function (Builder $artworkText) use ($like): void {
                            $artworkText
                                ->where('title', 'like', $like)
                                ->orWhere('description', 'like', $like)
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

    /** @param Builder<User> $query */
    private function applyLocationMatch(Builder $query, ?LocationMatch $match): void
    {
        if ($match === null) {
            return;
        }

        if ($match->ids === []) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereHas('makerProfile', static function (Builder $profileQuery) use ($match): void {
            $profileQuery->whereIn('location_id', $match->ids);
        });
    }

    private function attachDistances($makers, ?LocationMatch $match): void
    {
        if ($match === null || $match->distancesById === []) {
            return;
        }

        foreach ($makers as $maker) {
            $locationId = $maker->makerProfile?->location_id ? (int) $maker->makerProfile->location_id : null;
            $maker->setAttribute('distance_km', $match->distanceFor($locationId));
        }
    }
}

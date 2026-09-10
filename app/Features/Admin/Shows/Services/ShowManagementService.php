<?php

namespace App\Features\Admin\Shows\Services;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Enums\ShowStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ShowManagementService
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Show>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Show::query()
            ->with(['maker:id,name,email,status', 'location'])
            ->withCount('artworks')
            ->orderByDesc('start_date')
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('location_text', 'like', '%'.$search.'%')
                    ->orWhereHas('maker', static function ($maker) use ($search): void {
                        $maker->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        foreach (['maker_id', 'location_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['status'])) {
            $query->withStatus((string) $filters['status']);
        }

        if (($filters['visibility'] ?? null) === 'visible') {
            $query->where('is_visible', true);
        } elseif (($filters['visibility'] ?? null) === 'hidden') {
            $query->where('is_visible', false);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function find(int $showId): Show
    {
        return Show::query()
            ->with(['maker.makerProfile', 'location', 'artworks.type', 'artworks.style', 'artworks.primaryMedia'])
            ->findOrFail($showId);
    }

    public function findMaker(int $makerId): User
    {
        return User::query()->where('role', UserRole::Maker->value)->findOrFail($makerId);
    }

    /** @return Collection<int, User> */
    public function makers(): Collection
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'status']);
    }

    /** @return Collection<int, Location> */
    public function locations(): Collection
    {
        return Location::query()->orderBy('sort_order')->orderBy('city')->orderBy('region')->get();
    }

    /** @return Collection<int, Artwork> */
    public function makerArtworks(User $maker): Collection
    {
        return Artwork::query()
            ->where('maker_id', $maker->id)
            ->with('primaryMedia')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return ShowStatus::labels();
    }
}

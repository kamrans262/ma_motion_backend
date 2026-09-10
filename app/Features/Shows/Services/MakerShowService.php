<?php

namespace App\Features\Shows\Services;

use App\Features\Shows\Enums\ShowStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MakerShowService
{
    /**
     * @param array{search?:string|null,status?:string|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, Show>
     */
    public function paginate(User $maker, array $filters): LengthAwarePaginator
    {
        $query = Show::query()
            ->where('maker_id', $maker->id)
            ->with(['maker:id,name', 'location', 'artworks.type', 'artworks.style', 'artworks.primaryMedia'])
            ->orderByDesc('start_date')
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('location_text', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->withStatus((string) $filters['status']);
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }

    public function findOwned(User $maker, int $showId): Show
    {
        return Show::query()
            ->where('maker_id', $maker->id)
            ->with(['maker:id,name', 'location', 'artworks.type', 'artworks.style', 'artworks.primaryMedia'])
            ->findOrFail($showId);
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return ShowStatus::labels();
    }
}

<?php

namespace App\Features\Discovery\Services;

use App\Features\Locations\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DiscoveryLocationService
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Location>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Location::query()->where('is_active', true);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($searchQuery) use ($like): void {
                $searchQuery
                    ->where('city', 'like', $like)
                    ->orWhere('region', 'like', $like)
                    ->orWhere('postal_code', 'like', $like)
                    ->orWhere('country_code', 'like', $like);
            });
        }

        if (! empty($filters['country_code'])) {
            $query->where('country_code', strtoupper((string) $filters['country_code']));
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query
            ->orderBy('sort_order')
            ->orderBy('city')
            ->orderBy('region')
            ->orderBy('postal_code')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

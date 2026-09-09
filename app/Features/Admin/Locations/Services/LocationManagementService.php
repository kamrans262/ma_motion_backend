<?php

namespace App\Features\Admin\Locations\Services;

use App\Features\Locations\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LocationManagementService
{
    /** @param array{search?:string|null,status?:string|null,country_code?:string|null} $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Location::query()->orderBy('sort_order')->orderBy('city')->orderBy('region');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('city', 'like', '%'.$search.'%')
                    ->orWhere('region', 'like', '%'.$search.'%')
                    ->orWhere('postal_code', 'like', '%'.$search.'%');
            });
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($filters['country_code'])) {
            $query->where('country_code', strtoupper($filters['country_code']));
        }

        return $query->paginate(20)->withQueryString();
    }
}

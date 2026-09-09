<?php

namespace App\Features\Admin\Taxonomy\Types\Services;

use App\Features\Taxonomy\Models\ArtworkType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TypeManagementService
{
    /** @param array{search?:string|null,status?:string|null} $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ArtworkType::query()->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate(20)->withQueryString();
    }
}

<?php

namespace App\Features\Admin\Content\Services;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ContentManagementService
{
    /** @param array{search?:string|null,status?:string|null} $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = AppContentPage::query()->orderBy('sort_order')->orderBy('title');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        if (($filters['status'] ?? null) === 'published') {
            $query->where('is_published', true);
        } elseif (($filters['status'] ?? null) === 'draft') {
            $query->where('is_published', false);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function summary(): array
    {
        return [
            'total' => AppContentPage::query()->count(),
            'published' => AppContentPage::query()->where('is_published', true)->count(),
            'drafts' => AppContentPage::query()->where('is_published', false)->count(),
            'system' => AppContentPage::query()->where('is_system', true)->count(),
        ];
    }
}

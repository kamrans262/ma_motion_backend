<?php

namespace App\Features\Content\Services;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PublicContentService
{
    /** @return LengthAwarePaginator<int, AppContentPage> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return AppContentPage::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?AppContentPage
    {
        return AppContentPage::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->first();
    }
}

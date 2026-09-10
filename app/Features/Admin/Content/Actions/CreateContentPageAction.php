<?php

namespace App\Features\Admin\Content\Actions;

use App\Features\Content\Models\AppContentPage;

final class CreateContentPageAction
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): AppContentPage
    {
        $published = (bool) ($data['is_published'] ?? false);

        return AppContentPage::query()->create([
            'title' => trim((string) $data['title']),
            'slug' => (string) $data['slug'],
            'body' => trim((string) $data['body']),
            'is_published' => $published,
            'is_system' => false,
            'sort_order' => (int) $data['sort_order'],
            'published_at' => $published ? now() : null,
        ]);
    }
}

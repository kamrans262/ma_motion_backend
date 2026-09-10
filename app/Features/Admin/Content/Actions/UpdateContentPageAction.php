<?php

namespace App\Features\Admin\Content\Actions;

use App\Features\Content\Models\AppContentPage;

final class UpdateContentPageAction
{
    /** @param array<string, mixed> $data */
    public function execute(AppContentPage $page, array $data): AppContentPage
    {
        $page->update([
            'title' => trim((string) $data['title']),
            'slug' => $page->is_system ? $page->slug : (string) $data['slug'],
            'body' => trim((string) $data['body']),
            'sort_order' => (int) $data['sort_order'],
        ]);

        return $page->refresh();
    }
}

<?php

namespace App\Features\Admin\Content\Actions;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Validation\ValidationException;

final class ToggleContentPagePublishAction
{
    public function execute(AppContentPage $page): AppContentPage
    {
        if (! $page->is_published && trim((string) $page->body) === '') {
            throw ValidationException::withMessages(['body' => 'Add page content before publishing.']);
        }

        $published = ! $page->is_published;
        $page->update([
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);

        return $page->refresh();
    }
}

<?php

namespace Database\Seeders;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Database\Seeder;

final class AppContentPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['title' => 'Terms & Conditions', 'slug' => 'terms-and-conditions', 'sort_order' => 10],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'sort_order' => 20],
        ] as $definition) {
            $page = AppContentPage::withTrashed()->firstOrNew(['slug' => $definition['slug']]);

            if (! $page->exists) {
                $page->title = $definition['title'];
                $page->body = null;
                $page->is_published = false;
                $page->sort_order = $definition['sort_order'];
                $page->published_at = null;
            } else {
                // Preserve administrator-authored legal text and publish state on reruns.
                if (blank($page->title)) {
                    $page->title = $definition['title'];
                }

                if ($page->sort_order === null) {
                    $page->sort_order = $definition['sort_order'];
                }
            }

            // These slugs are permanent system records even if an older/partial install
            // created them incorrectly or they were soft-deleted.
            $page->is_system = true;
            $page->save();

            if ($page->trashed()) {
                $page->restore();
            }
        }
    }
}

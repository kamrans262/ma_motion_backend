<?php

namespace Tests\Feature\Content;

use App\Features\Content\Models\AppContentPage;
use Database\Seeders\AppContentPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppContentPageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_legal_pages_are_seeded_as_protected_drafts_idempotently(): void
    {
        $this->seed(AppContentPageSeeder::class);
        $this->seed(AppContentPageSeeder::class);

        $this->assertDatabaseCount('app_content_pages', 2);
        foreach (['terms-and-conditions', 'privacy-policy'] as $slug) {
            $page = AppContentPage::query()->where('slug', $slug)->firstOrFail();
            $this->assertTrue($page->is_system);
            $this->assertFalse($page->is_published);
        }
    }

    public function test_seeder_repairs_existing_or_soft_deleted_required_pages_without_overwriting_legal_content(): void
    {
        $terms = AppContentPage::query()->create([
            'title' => 'Approved Terms Title',
            'slug' => 'terms-and-conditions',
            'body' => 'Approved legal copy',
            'is_published' => true,
            'is_system' => false,
            'sort_order' => 10,
            'published_at' => now(),
        ]);

        $privacy = AppContentPage::query()->create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'body' => 'Approved privacy copy',
            'is_published' => false,
            'is_system' => false,
            'sort_order' => 20,
        ]);
        $privacy->delete();

        $this->seed(AppContentPageSeeder::class);

        $terms->refresh();
        $privacy = AppContentPage::query()->where('slug', 'privacy-policy')->firstOrFail();

        $this->assertTrue($terms->is_system);
        $this->assertSame('Approved Terms Title', $terms->title);
        $this->assertSame('Approved legal copy', $terms->body);
        $this->assertTrue($terms->is_published);

        $this->assertTrue($privacy->is_system);
        $this->assertNull($privacy->deleted_at);
        $this->assertSame('Approved privacy copy', $privacy->body);
    }
}

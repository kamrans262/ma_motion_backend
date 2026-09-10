<?php

namespace Tests\Feature\Api\V1\Content;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_api_returns_only_published_pages_and_full_page_body(): void
    {
        $published = AppContentPage::query()->create(['title'=>'Privacy Policy','slug'=>'privacy-policy','body'=>'Privacy body','is_published'=>true,'is_system'=>true,'sort_order'=>20,'published_at'=>now()]);
        AppContentPage::query()->create(['title'=>'Draft','slug'=>'draft-page','body'=>'Draft body','is_published'=>false,'is_system'=>false,'sort_order'=>30]);

        $this->getJson('/api/v1/content')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.slug', $published->slug)->assertJsonMissing(['slug'=>'draft-page']);
        $this->getJson('/api/v1/content/privacy-policy')->assertOk()->assertJsonPath('data.body', 'Privacy body');
    }

    public function test_unpublished_or_unknown_content_is_not_publicly_exposed(): void
    {
        AppContentPage::query()->create(['title'=>'Terms & Conditions','slug'=>'terms-and-conditions','body'=>'Draft terms','is_published'=>false,'is_system'=>true,'sort_order'=>10]);

        $this->getJson('/api/v1/content/terms-and-conditions')->assertNotFound();
        $this->getJson('/api/v1/content/does-not-exist')->assertNotFound();
    }
}

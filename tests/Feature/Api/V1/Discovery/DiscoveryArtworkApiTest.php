<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Models\Show;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscoveryArtworkApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_visual_discovery_search_filters_only_public_artwork_and_supports_singular_filter_aliases(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');
        $type = ArtworkType::query()->create(['name'=>'Painting','slug'=>'painting','is_active'=>true,'sort_order'=>0]);
        $style = ArtworkStyle::query()->create(['name'=>'Geometric','slug'=>'geometric','is_active'=>true,'sort_order'=>0]);
        $location = Location::query()->create(['city'=>'New York','region'=>'NY','postal_code'=>'10001','country_code'=>'US','latitude'=>40.7500,'longitude'=>-73.9970,'is_active'=>true,'sort_order'=>0]);
        $maker = User::factory()->create(['name'=>'Maya Linwood','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['bio'=>'Geometric painter','location_id'=>$location->id,'location_text'=>'New York, NY']);

        $public = Artwork::query()->create(['maker_id'=>$maker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Purple Geometry','description'=>'A precise city study','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        Artwork::query()->create(['maker_id'=>$maker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Hidden Geometry','moderation_status'=>'approved','is_visible'=>false,'sort_order'=>0]);
        Artwork::query()->create(['maker_id'=>$maker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Pending Geometry','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);

        $inactiveMaker = User::factory()->create(['name'=>'Maya Inactive','role'=>UserRole::Maker,'status'=>UserStatus::Inactive]);
        Artwork::query()->create(['maker_id'=>$inactiveMaker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'location_id'=>$location->id,'title'=>'Inactive Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $show = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Current Geometry','start_date'=>'2026-09-01','end_date'=>'2026-09-20','is_visible'=>true,'sort_order'=>0]);
        $show->artworks()->sync([$public->id=>['sort_order'=>0]]);

        $query = http_build_query(['search'=>'Maya','type_id'=>$type->id,'style_id'=>$style->id,'show_status'=>'current','city'=>'New York']);
        $response = $this->getJson('/api/v1/discovery?'.$query);

        $response->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $public->id)->assertJsonPath('data.0.maker.name', 'Maya Linwood')->assertJsonPath('data.0.location_source', 'maker');
        $response->assertJsonMissing(['title'=>'Hidden Geometry'])->assertJsonMissing(['title'=>'Pending Geometry'])->assertJsonMissing(['title'=>'Inactive Work']);
        $this->assertArrayNotHasKey('email', $response->json('data.0.maker'));
        $this->assertArrayNotHasKey('moderation_status', $response->json('data.0'));
        $this->assertArrayNotHasKey('rejection_reason', $response->json('data.0'));
    }

    public function test_discovery_artworks_alias_returns_same_public_grid_contract(): void
    {
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $artwork = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Public Grid Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $this->getJson('/api/v1/discovery/artworks')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $artwork->id)
            ->assertJsonPath('data.0.title', 'Public Grid Work');
    }
}

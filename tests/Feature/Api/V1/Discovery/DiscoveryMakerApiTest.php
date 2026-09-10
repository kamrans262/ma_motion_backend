<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Models\Show;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscoveryMakerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_maker_discovery_combines_taxonomy_show_location_and_statistics_filters(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');
        $type = ArtworkType::query()->create(['name'=>'Sculpture','slug'=>'sculpture','is_active'=>true,'sort_order'=>0]);
        $style = ArtworkStyle::query()->create(['name'=>'Contemporary','slug'=>'contemporary','is_active'=>true,'sort_order'=>0]);
        $location = Location::query()->create(['city'=>'New York','region'=>'NY','postal_code'=>'10001','country_code'=>'US','latitude'=>40.7500,'longitude'=>-73.9970,'is_active'=>true,'sort_order'=>0]);

        $maker = User::factory()->create(['name'=>'Sculptor One','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['bio'=>'Contemporary sculptor','location_id'=>$location->id,'location_text'=>'NYC']);
        Artwork::query()->create(['maker_id'=>$maker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Bronze Form','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        Show::query()->create(['maker_id'=>$maker->id,'name'=>'October Forms','start_date'=>'2026-10-01','end_date'=>'2026-10-20','is_visible'=>true,'sort_order'=>0]);

        foreach (User::factory()->count(2)->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active]) as $appreciator) {
            MakerSave::query()->create(['appreciator_id'=>$appreciator->id,'maker_id'=>$maker->id]);
        }

        $pendingOnly = User::factory()->create(['name'=>'Sculptor Pending','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $pendingOnly->makerProfile()->create(['location_id'=>$location->id]);
        Artwork::query()->create(['maker_id'=>$pendingOnly->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Private Bronze','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        Show::query()->create(['maker_id'=>$pendingOnly->id,'name'=>'Upcoming Private','start_date'=>'2026-10-01','end_date'=>'2026-10-20','is_visible'=>true,'sort_order'=>0]);

        $inactive = User::factory()->create(['name'=>'Sculptor Inactive','role'=>UserRole::Maker,'status'=>UserStatus::Inactive]);
        $inactive->makerProfile()->create(['location_id'=>$location->id]);
        Artwork::query()->create(['maker_id'=>$inactive->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'title'=>'Inactive Bronze','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $query = http_build_query(['search'=>'Sculptor','type_ids'=>[$type->id],'style_ids'=>[$style->id],'show_statuses'=>['upcoming'],'postal_code'=>'10001']);
        $response = $this->getJson('/api/v1/discovery/makers?'.$query);

        $response->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $maker->id)->assertJsonPath('data.0.statistics.saved_count', 2)->assertJsonPath('data.0.statistics.artwork_count', 1)->assertJsonPath('data.0.statistics.upcoming_show_count', 1);
        $this->assertArrayNotHasKey('email', $response->json('data.0'));
    }

    public function test_maker_search_matches_public_artwork_and_visible_show_content(): void
    {
        $maker = User::factory()->create(['name'=>'Maker Without Search Term','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['bio'=>'Neutral biography']);
        Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Copper Moon','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        Show::query()->create(['maker_id'=>$maker->id,'name'=>'Autumn Orbit','start_date'=>'2026-10-01','end_date'=>'2026-10-10','is_visible'=>true,'sort_order'=>0]);

        $this->getJson('/api/v1/discovery/makers?search=Copper%20Moon')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $maker->id);
        $this->getJson('/api/v1/discovery/makers?search=Autumn%20Orbit')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $maker->id);
    }
}

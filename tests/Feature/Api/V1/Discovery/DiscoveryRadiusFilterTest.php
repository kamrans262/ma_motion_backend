<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscoveryRadiusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_radius_filter_uses_artwork_location_first_and_maker_location_as_fallback(): void
    {
        $near = Location::query()->create(['city'=>'Brooklyn','region'=>'NY','postal_code'=>'11201','country_code'=>'US','latitude'=>40.7306,'longitude'=>-73.9352,'is_active'=>true,'sort_order'=>0]);
        $far = Location::query()->create(['city'=>'Los Angeles','region'=>'CA','postal_code'=>'90001','country_code'=>'US','latitude'=>34.0522,'longitude'=>-118.2437,'is_active'=>true,'sort_order'=>0]);

        $explicitMaker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $explicitMaker->makerProfile()->create(['location_id'=>$far->id]);
        $explicit = Artwork::query()->create(['maker_id'=>$explicitMaker->id,'location_id'=>$near->id,'title'=>'Explicit Near','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $fallbackMaker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $fallbackMaker->makerProfile()->create(['location_id'=>$near->id]);
        $fallback = Artwork::query()->create(['maker_id'=>$fallbackMaker->id,'title'=>'Fallback Near','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $farMaker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $farMaker->makerProfile()->create(['location_id'=>$far->id]);
        $farArtwork = Artwork::query()->create(['maker_id'=>$farMaker->id,'title'=>'Far Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);

        $query = http_build_query(['latitude'=>40.7128,'longitude'=>-74.0060,'radius_km'=>20]);
        $response = $this->getJson('/api/v1/discovery?'.$query)->assertOk()->assertJsonPath('meta.total', 2);

        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertTrue($rows->has($explicit->id));
        $this->assertTrue($rows->has($fallback->id));
        $this->assertFalse($rows->has($farArtwork->id));
        $this->assertSame('artwork', $rows[$explicit->id]['location_source']);
        $this->assertSame('maker', $rows[$fallback->id]['location_source']);
        $this->assertLessThanOrEqual(20.0, (float) $rows[$explicit->id]['distance_km']);
        $this->assertLessThanOrEqual(20.0, (float) $rows[$fallback->id]['distance_km']);
    }

    public function test_radius_coordinates_must_be_supplied_as_a_complete_triplet(): void
    {
        $this->getJson('/api/v1/discovery?latitude=40.7128')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['longitude', 'radius_km']);
    }
}

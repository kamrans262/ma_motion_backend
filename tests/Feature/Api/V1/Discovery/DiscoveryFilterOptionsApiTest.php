<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscoveryFilterOptionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_options_expose_only_enabled_taxonomy_and_location_capabilities(): void
    {
        $activeType = ArtworkType::query()->create(['name'=>'Painting','slug'=>'painting','is_active'=>true,'sort_order'=>0]);
        ArtworkType::query()->create(['name'=>'Disabled Type','slug'=>'disabled-type','is_active'=>false,'sort_order'=>1]);
        $activeStyle = ArtworkStyle::query()->create(['name'=>'Minimal','slug'=>'minimal','is_active'=>true,'sort_order'=>0]);
        ArtworkStyle::query()->create(['name'=>'Disabled Style','slug'=>'disabled-style','is_active'=>false,'sort_order'=>1]);

        $response = $this->getJson('/api/v1/discovery/filters');
        $response->assertOk()->assertJsonPath('data.types.0.id', $activeType->id)->assertJsonPath('data.styles.0.id', $activeStyle->id)->assertJsonPath('data.location.radius_km.max', 500)->assertJsonFragment(['value'=>'current'])->assertJsonFragment(['value'=>'upcoming'])->assertJsonFragment(['value'=>'past']);
        $response->assertJsonMissing(['name'=>'Disabled Type'])->assertJsonMissing(['name'=>'Disabled Style']);
    }

    public function test_location_autocomplete_is_active_paginated_and_searchable_by_city_or_postal_code(): void
    {
        $ny = Location::query()->create(['city'=>'New York','region'=>'NY','postal_code'=>'10001','country_code'=>'US','is_active'=>true,'sort_order'=>0]);
        Location::query()->create(['city'=>'New York','region'=>'NY','postal_code'=>'10002','country_code'=>'US','is_active'=>false,'sort_order'=>1]);
        Location::query()->create(['city'=>'Toronto','region'=>'ON','postal_code'=>'M5V','country_code'=>'CA','is_active'=>true,'sort_order'=>0]);

        $this->getJson('/api/v1/discovery/locations?search=10001&country_code=us')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $ny->id)
            ->assertJsonPath('data.0.city', 'New York');
    }

    public function test_inactive_taxonomy_and_invalid_radius_are_rejected_before_querying(): void
    {
        $inactive = ArtworkType::query()->create(['name'=>'Inactive','slug'=>'inactive','is_active'=>false,'sort_order'=>0]);

        $this->getJson('/api/v1/discovery?type_id='.$inactive->id)->assertUnprocessable()->assertJsonValidationErrors('type_ids.0');
        $this->getJson('/api/v1/discovery?latitude=40&longitude=-74&radius_km=501')->assertUnprocessable()->assertJsonValidationErrors('radius_km');
    }
}

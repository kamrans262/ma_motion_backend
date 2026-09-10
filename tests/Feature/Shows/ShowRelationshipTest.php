<?php

namespace Tests\Feature\Shows;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShowRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_relationships_connect_maker_location_and_owned_artwork(): void
    {
        $maker = User::factory()->create(['role'=>UserRole::Maker]);
        $location = Location::query()->create(['city'=>'Chicago','region'=>'IL','postal_code'=>'60601','country_code'=>'US','is_active'=>true,'sort_order'=>0]);
        $artwork = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Installed Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        $show = Show::query()->create(['maker_id'=>$maker->id,'location_id'=>$location->id,'name'=>'Chicago Exhibition','start_date'=>'2026-10-01','end_date'=>'2026-10-15','is_visible'=>true,'sort_order'=>0]);
        $show->artworks()->attach($artwork->id,['sort_order'=>0]);

        $this->assertTrue($maker->shows->contains($show));
        $this->assertTrue($location->shows->contains($show));
        $this->assertTrue($artwork->shows->contains($show));
        $this->assertTrue($show->artworks->contains($artwork));
    }
}

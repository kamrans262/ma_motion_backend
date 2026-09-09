<?php

namespace Tests\Feature\Artworks;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Features\Auth\Enums\UserRole;
use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_artwork_relationships_connect_maker_taxonomy_location_and_media(): void
    {
        $maker=User::factory()->create(['role'=>UserRole::Maker]);
        $type=ArtworkType::query()->create(['name'=>'Sculpture','slug'=>'sculpture','is_active'=>true,'sort_order'=>0]);
        $style=ArtworkStyle::query()->create(['name'=>'Organic','slug'=>'organic','is_active'=>true,'sort_order'=>0]);
        $location=Location::query()->create(['city'=>'Chicago','region'=>'IL','postal_code'=>'60601','country_code'=>'US','is_active'=>true,'sort_order'=>0]);
        $artwork=Artwork::query()->create(['maker_id'=>$maker->id,'artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'location_id'=>$location->id,'title'=>'Form','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        $media=ArtworkMedia::query()->create(['artwork_id'=>$artwork->id,'kind'=>'image','disk'=>'public','path'=>'artworks/form.jpg','mime_type'=>'image/jpeg','size_bytes'=>100,'width'=>100,'height'=>100,'sort_order'=>0,'is_primary'=>true]);
        $this->assertTrue($maker->artworks->contains($artwork));
        $this->assertTrue($type->artworks->contains($artwork));
        $this->assertTrue($style->artworks->contains($artwork));
        $this->assertTrue($location->artworks->contains($artwork));
        $this->assertTrue($artwork->media->contains($media));
    }
}

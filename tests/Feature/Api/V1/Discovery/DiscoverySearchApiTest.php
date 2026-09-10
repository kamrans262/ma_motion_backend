<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscoverySearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unified_search_returns_artwork_and_maker_sections_using_same_filters(): void
    {
        $maker = User::factory()->create(['name'=>'Orbit Artist','role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create(['bio'=>'Installation work']);
        $artwork = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Orbit Light','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        $show = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Orbit Exhibition','start_date'=>'2026-10-01','end_date'=>'2026-10-10','is_visible'=>true,'sort_order'=>0]);
        $show->artworks()->sync([$artwork->id=>['sort_order'=>0]]);

        $response = $this->getJson('/api/v1/discovery/search?search=Orbit&per_page=10');
        $response->assertOk()->assertJsonPath('data.artworks.0.id', $artwork->id)->assertJsonPath('data.makers.0.id', $maker->id)->assertJsonPath('meta.artworks.total', 1)->assertJsonPath('meta.makers.total', 1);
    }
}

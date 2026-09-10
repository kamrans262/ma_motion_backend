<?php

namespace Tests\Feature\Api\V1\Shows;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicMakerShowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_public_maker_show_api_exposes_only_visible_current_or_upcoming_shows_and_approved_visible_artwork(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $approved = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Approved Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        $pending = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Pending Work','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);

        $current = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Current Show','start_date'=>'2026-09-01','end_date'=>'2026-09-20','is_visible'=>true,'sort_order'=>0]);
        $current->artworks()->sync([$approved->id=>['sort_order'=>0],$pending->id=>['sort_order'=>1]]);
        Show::query()->create(['maker_id'=>$maker->id,'name'=>'Upcoming Show','start_date'=>'2026-10-01','end_date'=>'2026-10-20','is_visible'=>true,'sort_order'=>0]);
        Show::query()->create(['maker_id'=>$maker->id,'name'=>'Past Show','start_date'=>'2026-08-01','end_date'=>'2026-08-20','is_visible'=>true,'sort_order'=>0]);
        Show::query()->create(['maker_id'=>$maker->id,'name'=>'Hidden Show','start_date'=>'2026-10-01','end_date'=>'2026-10-20','is_visible'=>false,'sort_order'=>0]);

        $response = $this->getJson('/api/v1/makers/'.$maker->id.'/shows');
        $response->assertOk()->assertJsonPath('meta.total',2)->assertJsonMissing(['name'=>'Past Show'])->assertJsonMissing(['name'=>'Hidden Show']);
        $this->getJson('/api/v1/makers/'.$maker->id.'/shows/'.$current->id)
            ->assertOk()->assertJsonPath('data.artworks.0.id',$approved->id)->assertJsonMissing(['title'=>'Pending Work']);
    }

    public function test_inactive_maker_has_no_public_show_feed(): void
    {
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Inactive]);
        $this->getJson('/api/v1/makers/'.$maker->id.'/shows')->assertNotFound();
    }
}

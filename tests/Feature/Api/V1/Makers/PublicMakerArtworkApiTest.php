<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicMakerArtworkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_maker_gallery_only_exposes_approved_visible_work(): void
    {
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $public = Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Public Work','moderation_status'=>'approved','is_visible'=>true,'sort_order'=>0]);
        Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Hidden Work','moderation_status'=>'approved','is_visible'=>false,'sort_order'=>0]);
        Artwork::query()->create(['maker_id'=>$maker->id,'title'=>'Pending Work','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);

        $this->getJson('/api/v1/makers/'.$maker->id.'/artworks')
            ->assertOk()->assertJsonPath('meta.total',1)->assertJsonPath('data.0.id',$public->id)
            ->assertJsonMissing(['title'=>'Hidden Work'])->assertJsonMissing(['title'=>'Pending Work']);
    }

    public function test_inactive_or_non_maker_has_no_public_gallery(): void
    {
        $inactive = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Inactive]);
        $appreciator = User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $this->getJson('/api/v1/makers/'.$inactive->id.'/artworks')->assertNotFound();
        $this->getJson('/api/v1/makers/'.$appreciator->id.'/artworks')->assertNotFound();
    }
}

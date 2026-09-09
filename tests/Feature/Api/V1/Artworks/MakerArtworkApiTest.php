<?php

namespace Tests\Feature\Api\V1\Artworks;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MakerArtworkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_upload_list_update_and_soft_delete_owned_artwork(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role'=>UserRole::Maker, 'status'=>UserStatus::Active]);
        $type = ArtworkType::query()->create(['name'=>'Painting','slug'=>'painting','is_active'=>true,'sort_order'=>0]);
        $style = ArtworkStyle::query()->create(['name'=>'Modernist','slug'=>'modernist','is_active'=>true,'sort_order'=>0]);
        $location = Location::query()->create(['city'=>'New York','region'=>'NY','postal_code'=>'10001','country_code'=>'US','is_active'=>true,'sort_order'=>0]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $response = $this->withToken($token)->post('/api/v1/me/artworks', [
            'title'=>'First Work','description'=>'Initial description','artwork_type_id'=>$type->id,'artwork_style_id'=>$style->id,'location_id'=>$location->id,'location_text'=>'New York, NY','media'=>[UploadedFile::fake()->image('first.jpg',1200,800)->size(500)],
        ]);
        $response->assertCreated()->assertJsonPath('data.title','First Work')->assertJsonPath('data.moderation_status','pending')->assertJsonCount(1,'data.media')->assertJsonPath('data.media.0.is_primary',true);
        $id = (int) $response->json('data.id');

        $this->withToken($token)->getJson('/api/v1/me/artworks')->assertOk()->assertJsonPath('meta.total',1)->assertJsonPath('data.0.id',$id);

        Artwork::query()->whereKey($id)->update(['moderation_status'=>ArtworkModerationStatus::Approved->value]);
        $this->withToken($token)->patchJson('/api/v1/me/artworks/'.$id, ['title'=>'Updated Work'])->assertOk()->assertJsonPath('data.title','Updated Work')->assertJsonPath('data.moderation_status','pending');

        $this->withToken($token)->deleteJson('/api/v1/me/artworks/'.$id)->assertOk();
        $this->assertSoftDeleted('artworks', ['id'=>$id]);
    }

    public function test_maker_cannot_read_or_update_another_makers_artwork(): void
    {
        $owner = User::factory()->create(['role'=>UserRole::Maker, 'status'=>UserStatus::Active]);
        $other = User::factory()->create(['role'=>UserRole::Maker, 'status'=>UserStatus::Active]);
        $artwork = Artwork::query()->create(['maker_id'=>$owner->id,'title'=>'Private Draft','moderation_status'=>'pending','is_visible'=>true,'sort_order'=>0]);
        $token = $other->createToken('mobile',['mobile'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/me/artworks/'.$artwork->id)->assertNotFound();
        $this->withToken($token)->patchJson('/api/v1/me/artworks/'.$artwork->id,['title'=>'Hijack'])->assertNotFound();
    }

    public function test_artwork_management_requires_active_maker_authentication(): void
    {
        $this->getJson('/api/v1/me/artworks')->assertUnauthorized();
        $appreciator=User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $token=$appreciator->createToken('mobile',['mobile'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/me/artworks')->assertForbidden();
    }
}

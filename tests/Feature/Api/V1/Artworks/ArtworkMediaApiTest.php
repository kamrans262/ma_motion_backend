<?php

namespace Tests\Feature\Api\V1\Artworks;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkMediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_add_change_primary_and_remove_artwork_images(): void
    {
        Storage::fake('public');
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $token=$maker->createToken('mobile',['mobile'])->plainTextToken;
        $created=$this->withToken($token)->post('/api/v1/me/artworks',['title'=>'Media Test','media'=>[UploadedFile::fake()->image('one.jpg',800,600)]])->assertCreated();
        $id=(int)$created->json('data.id');
        Artwork::query()->whereKey($id)->update(['moderation_status'=>ArtworkModerationStatus::Approved->value]);

        $added=$this->withToken($token)->post('/api/v1/me/artworks/'.$id.'/media',['media'=>[UploadedFile::fake()->image('two.png',900,700)]])->assertCreated()->assertJsonPath('data.moderation_status','pending')->assertJsonCount(2,'data.media');
        $secondId=(int)$added->json('data.media.1.id');
        $this->withToken($token)->patchJson('/api/v1/me/artworks/'.$id.'/media/'.$secondId.'/primary')->assertOk()->assertJsonPath('data.media.1.is_primary',true);
        $firstId=(int)$added->json('data.media.0.id');
        $this->withToken($token)->deleteJson('/api/v1/me/artworks/'.$id.'/media/'.$firstId)->assertOk()->assertJsonCount(1,'data.media');
    }

    public function test_last_artwork_image_cannot_be_removed(): void
    {
        Storage::fake('public');
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $token=$maker->createToken('mobile',['mobile'])->plainTextToken;
        $created=$this->withToken($token)->post('/api/v1/me/artworks',['title'=>'Keep One','media'=>[UploadedFile::fake()->image('one.jpg',800,600)]])->assertCreated();
        $this->withToken($token)->deleteJson('/api/v1/me/artworks/'.$created->json('data.id').'/media/'.$created->json('data.media.0.id'))->assertUnprocessable()->assertJsonValidationErrors('media');
    }
}

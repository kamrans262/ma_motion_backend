<?php

namespace Tests\Feature\Api\V1\Artworks;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_svg_and_non_image_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $token=$maker->createToken('mobile',['mobile'])->plainTextToken;
        $this->withToken($token)->withHeaders(['Accept'=>'application/json'])->post('/api/v1/me/artworks',['title'=>'Unsafe','media'=>[UploadedFile::fake()->createWithContent('vector.svg','<svg><script>alert(1)</script></svg>')]])->assertUnprocessable()->assertJsonValidationErrors('media.0');
        $this->assertDatabaseCount('artworks',0);
        $this->assertDatabaseCount('artwork_media',0);
    }

    public function test_inactive_taxonomy_cannot_be_assigned_by_maker(): void
    {
        Storage::fake('public');
        $maker=User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $token=$maker->createToken('mobile',['mobile'])->plainTextToken;
        $type=\App\Features\Taxonomy\Models\ArtworkType::query()->create(['name'=>'Hidden Type','slug'=>'hidden-type','is_active'=>false,'sort_order'=>0]);
        $this->withToken($token)->withHeaders(['Accept'=>'application/json'])->post('/api/v1/me/artworks',['title'=>'Invalid Type','artwork_type_id'=>$type->id,'media'=>[UploadedFile::fake()->image('ok.jpg',600,600)]])->assertUnprocessable()->assertJsonValidationErrors('artwork_type_id');
    }
}

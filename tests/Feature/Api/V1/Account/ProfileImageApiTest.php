<?php

namespace Tests\Feature\Api\V1\Account;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProfileImageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_upload_replace_and_remove_profile_image(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create();
        $token = $maker->createToken('mobile',['mobile'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/me/profile-image', ['image'=>UploadedFile::fake()->image('first.jpg', 500, 500)->size(100)])->assertOk();
        $firstPath = $maker->fresh()->makerProfile->profile_image_path;
        Storage::disk('public')->assertExists($firstPath);

        $this->withToken($token)->postJson('/api/v1/me/profile-image', ['image'=>UploadedFile::fake()->image('second.png', 600, 600)->size(120)])->assertOk();
        $secondPath = $maker->fresh()->makerProfile->profile_image_path;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->withToken($token)->deleteJson('/api/v1/me/profile-image')->assertOk()->assertJsonPath('data.maker_profile.profile_image_url', null);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_profile_image_is_maker_only_and_rejects_unsupported_uploads(): void
    {
        Storage::fake('public');
        $appreciator = User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $token = $appreciator->createToken('mobile',['mobile'])->plainTextToken;
        $this->withToken($token)->postJson('/api/v1/me/profile-image', ['image'=>UploadedFile::fake()->image('avatar.jpg')])->assertForbidden();
    }

    public function test_maker_profile_image_rejects_svg(): void
    {
        Storage::fake('public');
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $maker->makerProfile()->create();
        $token = $maker->createToken('mobile',['mobile'])->plainTextToken;
        $svg = UploadedFile::fake()->createWithContent('avatar.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        $this->withToken($token)->postJson('/api/v1/me/profile-image', ['image'=>$svg])->assertUnprocessable()->assertJsonValidationErrors('image');
    }
}

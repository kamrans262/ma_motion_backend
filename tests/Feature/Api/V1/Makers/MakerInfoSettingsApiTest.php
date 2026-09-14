<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Makers\Models\MakerProfileContent;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MakerInfoSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_visibility_settings_persist_and_private_profile_returns_content(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/maker-profile', [
                'show_website_on_info_page' => false,
                'show_email_on_info_page' => true,
                'show_shows_on_info_page' => false,
                'website_url' => 'https://artist.example',
                'contact_email' => 'hello@artist.example',
            ])
            ->assertOk()
            ->assertJsonPath('data.show_website_on_info_page', false)
            ->assertJsonPath('data.show_email_on_info_page', true)
            ->assertJsonPath('data.show_shows_on_info_page', false);

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'Salon content',
                'media' => UploadedFile::fake()->image('salon.jpg', 1200, 900),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.slot', 1)
            ->assertJsonPath('data.kind', 'image')
            ->assertJsonPath('data.caption', 'Salon content');

        $this->withToken($token)
            ->getJson('/api/v1/me/maker-profile')
            ->assertOk()
            ->assertJsonPath('data.carousel_content.0.slot', 1)
            ->assertJsonPath('data.carousel_content.0.caption', 'Salon content');

        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => $maker->id,
            'show_website_on_info_page' => 0,
            'show_email_on_info_page' => 1,
            'show_shows_on_info_page' => 0,
        ]);
    }

    public function test_content_slot_can_be_replaced_caption_updated_and_deleted_with_storage_cleanup(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'First caption',
                'media' => UploadedFile::fake()->image('first.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $content = MakerProfileContent::query()->firstOrFail();
        $firstPath = $content->path;
        Storage::disk('public')->assertExists($firstPath);

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'Replacement caption',
                'media' => UploadedFile::fake()->image('replacement.png'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.caption', 'Replacement caption');

        $content->refresh();
        $this->assertNotSame($firstPath, $content->path);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($content->path);

        $replacementPath = $content->path;

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'Caption only update',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.caption', 'Caption only update');

        $this->assertSame($replacementPath, $content->refresh()->path);

        $this->withToken($token)
            ->deleteJson('/api/v1/me/maker-profile/carousel/1')
            ->assertOk();

        $this->assertDatabaseMissing('maker_profile_contents', ['id' => $content->id]);
        Storage::disk('public')->assertMissing($replacementPath);
    }

    public function test_content_slot_requires_media_when_empty(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'Missing media',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('media');
    }

    public function test_appreciator_cannot_manage_maker_profile_content(): void
    {
        Storage::fake('public');

        $appreciator = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => UploadedFile::fake()->image('not-allowed.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to perform this action.');
    }

    public function test_artwork_detail_enforces_website_email_and_show_privacy(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $profile = $maker->makerProfile()->create([
            'website_url' => 'https://artist.example',
            'contact_email' => 'private@artist.example',
            'show_website_on_info_page' => false,
            'show_email_on_info_page' => false,
            'show_shows_on_info_page' => false,
        ]);

        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Public work',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        Show::query()->create([
            'maker_id' => $maker->id,
            'name' => 'Private current show',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->getJson('/api/v1/discovery/artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.maker.website_url', null)
            ->assertJsonPath('data.maker.contact_email', null)
            ->assertJsonPath('data.maker.show_shows_on_info_page', false);

        $this->getJson('/api/v1/makers/'.$maker->id.'/shows')
            ->assertNotFound();

        $profile->update([
            'show_website_on_info_page' => true,
            'show_email_on_info_page' => true,
            'show_shows_on_info_page' => true,
        ]);

        $this->getJson('/api/v1/discovery/artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.maker.website_url', 'https://artist.example')
            ->assertJsonPath('data.maker.contact_email', 'private@artist.example')
            ->assertJsonPath('data.maker.show_shows_on_info_page', true);

        $this->getJson('/api/v1/makers/'.$maker->id.'/shows')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }
}

<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MakerInfoSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_manage_info_page_visibility_preferences(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $response = $this->actingAs($maker, 'sanctum')
            ->patchJson('/api/v1/me/maker-profile', [
                'name' => 'Artist Ken',
                'bio' => 'A contemporary artist exploring form and color.',
                'location_text' => 'Chicago, IL',
                'website_url' => 'artist.example',
                'contact_email' => 'contact@artist.example',
                'show_website_on_info_page' => true,
                'show_email_on_info_page' => true,
                'show_shows_on_info_page' => false,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Artist Ken')
            ->assertJsonPath('data.website_url', 'https://artist.example')
            ->assertJsonPath('data.contact_email', 'contact@artist.example')
            ->assertJsonPath('data.show_website_on_info_page', true)
            ->assertJsonPath('data.show_email_on_info_page', true)
            ->assertJsonPath('data.show_shows_on_info_page', false);

        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => $maker->id,
            'show_website_on_info_page' => true,
            'show_email_on_info_page' => true,
            'show_shows_on_info_page' => false,
        ]);
    }

    public function test_maker_can_create_replace_caption_and_delete_three_slot_carousel_content(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $first = UploadedFile::fake()->image('salon.jpg', 900, 900);

        $this->actingAs($maker, 'sanctum')
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => $first,
                'caption' => 'Salon photo',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.slot', 1)
            ->assertJsonPath('data.kind', 'image')
            ->assertJsonPath('data.caption', 'Salon photo');

        $record = $maker->refresh()->makerProfile->carouselMedia()->where('slot', 1)->firstOrFail();
        $firstPath = $record->path;
        Storage::disk('public')->assertExists($firstPath);

        $replacement = UploadedFile::fake()->image('salon-replacement.jpg', 1000, 1000);

        $this->actingAs($maker, 'sanctum')
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => $replacement,
                'caption' => 'Replacement salon photo',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.slot', 1)
            ->assertJsonPath('data.caption', 'Replacement salon photo');

        $record = $maker->refresh()->makerProfile->carouselMedia()->where('slot', 1)->firstOrFail();
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($record->path);

        $this->actingAs($maker, 'sanctum')
            ->postJson('/api/v1/me/maker-profile/carousel/1', [
                'caption' => 'Updated salon caption',
            ])
            ->assertOk()
            ->assertJsonPath('data.caption', 'Updated salon caption');

        $this->actingAs($maker, 'sanctum')
            ->deleteJson('/api/v1/me/maker-profile/carousel/1')
            ->assertOk();

        $this->assertDatabaseMissing('maker_profile_carousel_media', [
            'maker_profile_id' => $maker->makerProfile->id,
            'slot' => 1,
        ]);
        Storage::disk('public')->assertMissing($record->path);
    }

    public function test_carousel_is_limited_to_slots_one_to_three_and_supported_media_types(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($maker, 'sanctum')
            ->post('/api/v1/me/maker-profile/carousel/4', [
                'media' => UploadedFile::fake()->image('invalid.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertNotFound();

        $this->actingAs($maker, 'sanctum')
            ->post('/api/v1/me/maker-profile/carousel/1', [
                'media' => UploadedFile::fake()->create(
                    'malware.svg',
                    4,
                    'image/svg+xml',
                ),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('media');
    }

    public function test_public_artwork_detail_respects_maker_info_page_visibility_opt_in(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
            'name' => 'Visible Maker',
        ]);

        $maker->makerProfile()->create([
            'bio' => 'Public bio',
            'website_url' => 'https://artist.example',
            'contact_email' => 'contact@artist.example',
            'show_website_on_info_page' => false,
            'show_email_on_info_page' => true,
            'show_shows_on_info_page' => false,
        ]);

        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Public Work',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->getJson('/api/v1/discovery/artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.maker.website_url', null)
            ->assertJsonPath('data.maker.contact_email', 'contact@artist.example')
            ->assertJsonPath('data.maker.show_shows_on_info_page', false);
    }
}

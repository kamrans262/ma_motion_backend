<?php

namespace Tests\Feature\Api\V1\Discovery;

use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DiscoveryArtworkDetailApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_artwork_detail_returns_all_media_and_public_maker_profile(): void
    {
        Storage::fake('public');

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
            'name' => 'Mara Vellan',
        ]);

        $profile = $maker->makerProfile()->updateOrCreate(
            ['user_id' => $maker->id],
            [
                'bio' => 'A contemporary artist exploring material and color.',
                'location_text' => 'Chicago, IL',
                'website_url' => 'https://artist.example',
                'contact_email' => 'private@example.com',
            ],
        );

        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Tide Register No. 4',
            'description' => 'Built in slow layers over eleven months.',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $second = ArtworkMedia::query()->create([
            'artwork_id' => $artwork->id,
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'artworks/second.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 200,
            'width' => 800,
            'height' => 1000,
            'alt_text' => 'Second artwork view',
            'sort_order' => 2,
            'is_primary' => false,
        ]);

        $first = ArtworkMedia::query()->create([
            'artwork_id' => $artwork->id,
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'artworks/first.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 100,
            'width' => 800,
            'height' => 1000,
            'alt_text' => 'Primary artwork view',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $slotTwo = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Maker Info Content 2',
            'description' => 'Second configured art display item.',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        $slotTwoMedia = ArtworkMedia::query()->create([
            'artwork_id' => $slotTwo->id,
            'kind' => 'image',
            'disk' => 'public',
            'path' => 'artworks/content-two.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 300,
            'width' => 900,
            'height' => 1100,
            'alt_text' => 'Maker Info Content 2',
            'sort_order' => 1,
            'is_primary' => true,
        ]);
        $slotThreePending = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Pending Maker Info Content 3',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 2,
        ]);
        $slotFourHidden = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Hidden Maker Info Content 4',
            'moderation_status' => 'approved',
            'is_visible' => false,
            'sort_order' => 3,
        ]);

        $profile->artworkSlots()->create(['slot' => 2, 'artwork_id' => $slotTwo->id]);
        $profile->artworkSlots()->create(['slot' => 3, 'artwork_id' => $slotThreePending->id]);
        $profile->artworkSlots()->create(['slot' => 4, 'artwork_id' => $slotFourHidden->id]);

        $this->getJson('/api/v1/discovery/artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.id', $artwork->id)
            ->assertJsonPath('data.title', 'Tide Register No. 4')
            ->assertJsonPath('data.description', 'Built in slow layers over eleven months.')
            ->assertJsonPath('data.media.0.id', $first->id)
            ->assertJsonPath('data.media.1.id', $second->id)
            ->assertJsonPath('data.primary_media.id', $first->id)
            ->assertJsonCount(1, 'data.art_display_artworks')
            ->assertJsonPath('data.art_display_artworks.0.slot', 2)
            ->assertJsonPath('data.art_display_artworks.0.artwork.id', $slotTwo->id)
            ->assertJsonPath('data.art_display_artworks.0.artwork.title', 'Maker Info Content 2')
            ->assertJsonPath('data.art_display_artworks.0.artwork.primary_media.id', $slotTwoMedia->id)
            ->assertJsonPath('data.maker.id', $maker->id)
            ->assertJsonPath('data.maker.name', 'Mara Vellan')
            ->assertJsonPath('data.maker.website_url', 'https://artist.example')
            ->assertJsonPath('data.maker.saved_count', 0)
            ->assertJsonMissing(['contact_email' => 'private@example.com']);
    }

    public function test_hidden_pending_or_inactive_maker_artwork_is_not_publicly_exposed(): void
    {
        $activeMaker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $inactiveMaker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Inactive,
        ]);

        $hidden = Artwork::query()->create([
            'maker_id' => $activeMaker->id,
            'title' => 'Hidden',
            'moderation_status' => 'approved',
            'is_visible' => false,
            'sort_order' => 0,
        ]);

        $pending = Artwork::query()->create([
            'maker_id' => $activeMaker->id,
            'title' => 'Pending',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $inactiveMakerArtwork = Artwork::query()->create([
            'maker_id' => $inactiveMaker->id,
            'title' => 'Inactive Maker Artwork',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->getJson('/api/v1/discovery/artworks/'.$hidden->id)->assertNotFound();
        $this->getJson('/api/v1/discovery/artworks/'.$pending->id)->assertNotFound();
        $this->getJson('/api/v1/discovery/artworks/'.$inactiveMakerArtwork->id)->assertNotFound();
    }
}

<?php

namespace Tests\Feature\Api\V1\FeaturedMaker;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\FeaturedMaker\Models\FeaturedMakerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedMakerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_registered_user_receives_published_featured_maker_and_public_artwork_only(): void
    {
        $maker = User::factory()->create(['name' => 'Featured Artist', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $publicArtwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Visible Piece',
            'moderation_status' => ArtworkModerationStatus::Approved,
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Hidden Piece',
            'moderation_status' => ArtworkModerationStatus::Approved,
            'is_visible' => false,
            'sort_order' => 1,
        ]);
        Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Rejected Piece',
            'moderation_status' => ArtworkModerationStatus::Rejected,
            'is_visible' => true,
            'sort_order' => 2,
        ]);
        FeaturedMakerSetting::query()->create([
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'maker_id' => $maker->id,
            'featured_artwork_id' => $publicArtwork->id,
            'eyebrow' => 'Maker Spotlight',
            'headline' => 'Meet Featured Artist',
            'description' => 'A curated introduction.',
            'is_active' => true,
        ]);

        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/featured-maker')
            ->assertOk()
            ->assertJsonPath('data.maker.id', $maker->id)
            ->assertJsonPath('data.content.eyebrow', 'Maker Spotlight')
            ->assertJsonPath('data.content.headline', 'Meet Featured Artist')
            ->assertJsonPath('data.spotlight_artwork.id', $publicArtwork->id)
            ->assertJsonCount(1, 'data.artworks')
            ->assertJsonPath('data.artworks.0.id', $publicArtwork->id);

        $this->assertStringNotContainsString($maker->email, $response->getContent());
        $this->assertStringNotContainsString('Hidden Piece', $response->getContent());
        $this->assertStringNotContainsString('Rejected Piece', $response->getContent());
    }

    public function test_unpublished_or_inactive_maker_returns_no_featured_maker(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Inactive]);
        FeaturedMakerSetting::query()->create([
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'maker_id' => $maker->id,
            'is_active' => true,
        ]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/featured-maker')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_unpublished_setting_returns_no_featured_maker(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        FeaturedMakerSetting::query()->create([
            'slot' => FeaturedMakerSetting::POST_LOGIN_SLOT,
            'maker_id' => $maker->id,
            'is_active' => false,
        ]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/featured-maker')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_featured_maker_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/featured-maker')->assertUnauthorized();
    }

    public function test_featured_maker_endpoint_rejects_inactive_account(): void
    {
        $user = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Inactive]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/featured-maker')->assertForbidden();
    }
}

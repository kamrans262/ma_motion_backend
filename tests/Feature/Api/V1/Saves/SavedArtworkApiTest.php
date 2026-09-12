<?php

namespace Tests\Feature\Api\V1\Saves;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SavedArtworkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_save_list_check_and_unsave_public_artwork(): void
    {
        $viewer = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $artwork = $this->publicArtwork();
        $token = $viewer->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.artwork_id', $artwork->id)
            ->assertJsonPath('data.saved', true);

        $this->withToken($token)
            ->getJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.saved', true);

        $this->withToken($token)
            ->getJson('/api/v1/me/saved-artworks?per_page=6&page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $artwork->id)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 6)
            ->assertJsonPath('meta.total', 1);

        $this->assertDatabaseHas('artwork_saves', [
            'user_id' => $viewer->id,
            'artwork_id' => $artwork->id,
        ]);

        $this->withToken($token)
            ->deleteJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.saved', false);

        $this->assertDatabaseMissing('artwork_saves', [
            'user_id' => $viewer->id,
            'artwork_id' => $artwork->id,
        ]);
    }

    public function test_appreciator_can_save_artwork_without_changing_saved_maker_rules(): void
    {
        $appreciator = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $artwork = $this->publicArtwork('Appreciator Favorite');
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertOk()
            ->assertJsonPath('data.saved', true);

        $this->assertSame(1, ArtworkSave::query()->count());
    }

    public function test_saved_artwork_routes_require_auth_and_supported_mobile_role(): void
    {
        $artwork = $this->publicArtwork();

        $this->postJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertUnauthorized();

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);
        $token = $admin->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-artworks/'.$artwork->id)
            ->assertForbidden();
    }

    public function test_hidden_or_unapproved_artwork_cannot_be_newly_saved(): void
    {
        $viewer = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $artist = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $hidden = Artwork::query()->create([
            'maker_id' => $artist->id,
            'title' => 'Hidden',
            'moderation_status' => 'approved',
            'is_visible' => false,
            'sort_order' => 0,
        ]);
        $pending = Artwork::query()->create([
            'maker_id' => $artist->id,
            'title' => 'Pending',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $token = $viewer->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-artworks/'.$hidden->id)
            ->assertNotFound();

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-artworks/'.$pending->id)
            ->assertNotFound();
    }

    private function publicArtwork(string $title = 'Saved Work'): Artwork
    {
        $artist = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        return Artwork::query()->create([
            'maker_id' => $artist->id,
            'title' => $title,
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
    }
}

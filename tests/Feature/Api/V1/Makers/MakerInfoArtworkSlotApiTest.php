<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MakerInfoArtworkSlotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_assign_replace_and_detach_owned_artworks_without_deleting_them(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $profile = $maker->makerProfile()->create();
        $first = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'First artwork',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $second = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Second artwork',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/2', ['artwork_id' => $first->id])
            ->assertOk()
            ->assertJsonPath('data.slot', 2)
            ->assertJsonPath('data.artwork.id', $first->id);

        $this->withToken($token)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/2', ['artwork_id' => $second->id])
            ->assertOk()
            ->assertJsonPath('data.artwork.id', $second->id);

        $this->assertDatabaseHas('maker_profile_artwork_slots', [
            'maker_profile_id' => $profile->id,
            'slot' => 2,
            'artwork_id' => $second->id,
        ]);

        $this->withToken($token)
            ->deleteJson('/api/v1/me/maker-profile/artwork-slots/2')
            ->assertOk();

        $this->assertDatabaseMissing('maker_profile_artwork_slots', [
            'maker_profile_id' => $profile->id,
            'slot' => 2,
        ]);
        $this->assertDatabaseHas('artworks', ['id' => $second->id]);
    }

    public function test_artwork_cannot_be_used_in_two_slots_or_assigned_by_another_maker(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $other = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Owned artwork',
            'moderation_status' => 'pending',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;
        $otherToken = $other->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/2', ['artwork_id' => $artwork->id])
            ->assertOk();

        $this->withToken($token)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/3', ['artwork_id' => $artwork->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('artwork_id');

        $this->withToken($otherToken)
            ->putJson('/api/v1/me/maker-profile/artwork-slots/2', ['artwork_id' => $artwork->id])
            ->assertNotFound();
    }

    public function test_private_profile_returns_artwork_slot_contract(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $profile = $maker->makerProfile()->create();
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Grid artwork',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $profile->artworkSlots()->create(['slot' => 4, 'artwork_id' => $artwork->id]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/maker-profile')
            ->assertOk()
            ->assertJsonPath('data.artwork_slots.0.slot', 4)
            ->assertJsonPath('data.artwork_slots.0.artwork.id', $artwork->id)
            ->assertJsonPath('data.artwork_slots.0.artwork.title', 'Grid artwork');
    }
}

<?php

namespace Tests\Feature\Api\V1\Shows;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MakerShowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_create_list_update_and_soft_delete_owned_show_with_related_artwork(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $location = Location::query()->create([
            'city' => 'New York',
            'region' => 'NY',
            'postal_code' => '10001',
            'country_code' => 'US',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Related Work',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/me/shows', [
            'name' => 'Autumn Studio Show',
            'description' => 'New works',
            'location_id' => $location->id,
            'location_text' => 'MA Gallery',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'artwork_ids' => [$artwork->id],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Autumn Studio Show')
            ->assertJsonPath('data.artworks.0.id', $artwork->id);

        $showId = (int) $response->json('data.id');

        $this->withToken($token)
            ->getJson('/api/v1/me/shows')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $showId);

        $this->withToken($token)
            ->patchJson('/api/v1/me/shows/'.$showId, [
                'name' => 'Autumn Studio Exhibition',
                'end_date' => '2026-10-25',
                'artwork_ids' => [],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Autumn Studio Exhibition')
            ->assertJsonCount(0, 'data.artworks');

        $this->withToken($token)
            ->deleteJson('/api/v1/me/shows/'.$showId)
            ->assertOk();

        $this->assertSoftDeleted('shows', ['id' => $showId]);
    }

    public function test_maker_cannot_manage_another_makers_show_or_attach_another_makers_artwork(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $other = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $show = Show::query()->create([
            'maker_id' => $owner->id,
            'name' => 'Owner Show',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-10',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $foreignArtwork = Artwork::query()->create([
            'maker_id' => $owner->id,
            'title' => 'Owner Work',
            'moderation_status' => 'approved',
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $token = $other->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/shows/'.$show->id)
            ->assertNotFound();

        $this->withToken($token)
            ->postJson('/api/v1/me/shows', [
                'name' => 'Invalid Link',
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-10',
                'artwork_ids' => [$foreignArtwork->id],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('artwork_ids');
    }

    public function test_show_dates_are_validated_for_authenticated_maker(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $makerToken = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($makerToken)
            ->postJson('/api/v1/me/shows', [
                'name' => 'Bad Dates',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-01',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    }

    public function test_show_management_requires_maker_authentication(): void
    {
        $this->get('/api/v1/me/shows')->assertUnauthorized();

        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/shows')
            ->assertForbidden();
    }
}

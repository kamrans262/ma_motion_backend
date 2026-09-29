<?php

namespace Tests\Feature\Api\V1\Makers;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_update_own_profile(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/maker-profile', [
                'name' => 'My Maker Name',
                'bio' => 'My maker biography',
                'location_text' => 'Chicago, IL',
                'current_upcoming_shows' => [
                    [
                        'description' => 'Fall group exhibition',
                        'location' => 'Chicago Arts Center',
                    ],
                    [
                        'description' => 'Winter solo presentation',
                        'location' => 'New York, NY',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'My Maker Name')
            ->assertJsonPath('data.location', 'Chicago, IL')
            ->assertJsonPath('data.current_upcoming_shows.0.description', 'Fall group exhibition')
            ->assertJsonPath('data.current_upcoming_shows.0.location', 'Chicago Arts Center')
            ->assertJsonCount(2, 'data.current_upcoming_shows');

        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => $maker->id,
            'location_text' => 'Chicago, IL',
        ]);

        $profile = $maker->refresh()->makerProfile;
        $this->assertSame([
            [
                'description' => 'Fall group exhibition',
                'location' => 'Chicago Arts Center',
            ],
            [
                'description' => 'Winter solo presentation',
                'location' => 'New York, NY',
            ],
        ], $profile?->current_upcoming_shows);
    }

    public function test_appreciator_cannot_update_maker_profile(): void
    {
        $user = User::factory()->create(['role' => UserRole::Appreciator]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/maker-profile', ['bio' => 'Not allowed'])
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to perform this action.');
    }

    public function test_maker_profile_update_requires_authentication(): void
    {
        $this->patchJson('/api/v1/me/maker-profile', ['bio' => 'No token'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}

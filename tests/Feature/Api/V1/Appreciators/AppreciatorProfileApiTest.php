<?php

namespace Tests\Feature\Api\V1\Appreciators;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppreciatorProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_appreciator_can_load_and_update_settings(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $profile = $user->appreciatorProfile()->create([
            'location_text' => 'Old Town',
            'onboarding_completed_at' => now(),
        ]);
        $location = Location::query()->create([
            'name' => 'New York',
            'city' => 'New York',
            'region' => 'NY',
            'postal_code' => '10001',
            'country_code' => 'US',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_active' => true,
        ]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/appreciator-profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Original Name')
            ->assertJsonPath('data.email', 'original@example.com')
            ->assertJsonPath('data.location_text', 'Old Town');

        $this->withToken($token)
            ->patchJson('/api/v1/me/appreciator-profile', [
                'name' => '  Updated Name  ',
                'email' => '  UPDATED@EXAMPLE.COM  ',
                'location_text' => '  New York, NY  ',
                'location_id' => $location->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.location_text', 'New York, NY')
            ->assertJsonPath('data.location_id', $location->id)
            ->assertJsonPath('data.onboarding_completed', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
        $this->assertDatabaseHas('appreciator_profiles', [
            'id' => $profile->id,
            'user_id' => $user->id,
            'location_text' => 'New York, NY',
            'location_id' => $location->id,
        ]);
    }

    public function test_appreciator_settings_enforce_unique_email_and_active_location(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $user = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago',
            'onboarding_completed_at' => now(),
        ]);
        $inactiveLocation = Location::query()->create([
            'name' => 'Inactive',
            'city' => 'Inactive',
            'country_code' => 'US',
            'latitude' => 0,
            'longitude' => 0,
            'is_active' => false,
        ]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/appreciator-profile', [
                'name' => 'Appreciator',
                'email' => 'taken@example.com',
                'location_text' => 'Inactive',
                'location_id' => $inactiveLocation->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'location_id']);
    }

    public function test_non_appreciator_cannot_use_appreciator_settings_endpoint(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me/appreciator-profile')
            ->assertForbidden();
    }
}

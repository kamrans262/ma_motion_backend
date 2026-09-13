<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Appreciators\Models\AppreciatorProfile;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppreciatorOnboardingRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_appreciator_onboarding_creates_passwordless_completed_profile_and_token(): void
    {
        $location = Location::query()->create([
            'city' => 'Chicago',
            'region' => 'IL',
            'postal_code' => '60601',
            'country_code' => 'US',
            'latitude' => 41.8864,
            'longitude' => -87.6186,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $response = $this->postJson('/api/v1/auth/appreciator-onboarding', [
            'name' => 'Art Lover',
            'location_text' => 'Chicago 60601',
            'location_id' => $location->id,
            'email' => 'LOVER@example.com',
            'device_name' => 'Infinix Android',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Art Lover')
            ->assertJsonPath('data.user.email', 'lover@example.com')
            ->assertJsonPath('data.user.role', UserRole::Appreciator->value)
            ->assertJsonPath('data.user.status', UserStatus::Active->value)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('users', [
            'name' => 'Art Lover',
            'email' => 'lover@example.com',
            'password' => null,
            'role' => UserRole::Appreciator->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->assertDatabaseHas('appreciator_profiles', [
            'user_id' => 1,
            'location_text' => 'Chicago 60601',
            'location_id' => $location->id,
        ]);

        $this->assertNotNull(
            AppreciatorProfile::query()
                ->where('user_id', 1)
                ->value('onboarding_completed_at'),
        );

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Infinix Android',
        ]);
    }

    public function test_appreciator_onboarding_token_can_restore_authenticated_role(): void
    {
        $register = $this->postJson('/api/v1/auth/appreciator-onboarding', [
            'name' => 'Art Lover',
            'location_text' => 'Chicago',
            'email' => 'lover@example.com',
        ])->assertCreated();

        $token = (string) $register->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.role', UserRole::Appreciator->value)
            ->assertJsonPath('data.name', 'Art Lover');
    }

    public function test_appreciator_onboarding_rejects_inactive_managed_location(): void
    {
        $location = Location::query()->create([
            'city' => 'Inactive City',
            'country_code' => 'US',
            'is_active' => false,
            'sort_order' => 10,
        ]);

        $this->postJson('/api/v1/auth/appreciator-onboarding', [
            'name' => 'Art Lover',
            'location_text' => 'Inactive City',
            'location_id' => $location->id,
            'email' => 'lover@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['location_id']);
    }

    public function test_appreciator_onboarding_rejects_duplicate_email(): void
    {
        $payload = [
            'name' => 'Art Lover',
            'location_text' => 'Chicago',
            'email' => 'lover@example.com',
        ];

        $this->postJson('/api/v1/auth/appreciator-onboarding', $payload)
            ->assertCreated();

        $this->postJson('/api/v1/auth/appreciator-onboarding', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}

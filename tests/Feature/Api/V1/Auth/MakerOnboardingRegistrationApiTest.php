<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerOnboardingRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_onboarding_can_create_passwordless_account_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/maker-onboarding', [
            'name' => 'Artist Ken',
            'email' => 'ARTIST@example.com',
            'device_name' => 'Infinix Android',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Artist Ken')
            ->assertJsonPath('data.user.email', 'artist@example.com')
            ->assertJsonPath('data.user.role', UserRole::Maker->value)
            ->assertJsonPath('data.user.status', UserStatus::Active->value)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('users', [
            'name' => 'Artist Ken',
            'email' => 'artist@example.com',
            'password' => null,
            'role' => UserRole::Maker->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => 1,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Infinix Android',
        ]);
    }

    public function test_passwordless_onboarding_token_can_access_private_maker_profile(): void
    {
        $register = $this->postJson('/api/v1/auth/maker-onboarding', [
            'name' => 'Artist Ken',
            'email' => 'artist@example.com',
        ])->assertCreated();

        $token = (string) $register->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/me/maker-profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Artist Ken')
            ->assertJsonPath('data.onboarding_completed', false);
    }

    public function test_passwordless_maker_cannot_use_password_login_endpoint(): void
    {
        $this->postJson('/api/v1/auth/maker-onboarding', [
            'name' => 'Artist Ken',
            'email' => 'artist@example.com',
        ])->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'artist@example.com',
            'password' => 'Anything123',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_maker_onboarding_rejects_duplicate_email(): void
    {
        $payload = [
            'name' => 'Artist Ken',
            'email' => 'artist@example.com',
        ];

        $this->postJson('/api/v1/auth/maker-onboarding', $payload)
            ->assertCreated();

        $this->postJson('/api/v1/auth/maker-onboarding', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}

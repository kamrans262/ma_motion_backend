<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Maker One',
            'email' => 'MAKER@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Maker->value,
            'device_name' => 'Test Phone',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'maker@example.com')
            ->assertJsonPath('data.user.role', UserRole::Maker->value)
            ->assertJsonPath('data.user.status', UserStatus::Active->value)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('users', [
            'email' => 'maker@example.com',
            'role' => UserRole::Maker->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Test Phone',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_appreciator_can_register_without_device_name(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Appreciator One',
            'email' => 'appreciator@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Appreciator->value,
        ])->assertCreated()
            ->assertJsonPath('data.user.role', UserRole::Appreciator->value);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'MA Motion mobile',
        ]);
    }

    public function test_blank_device_name_uses_default_token_name(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Blank Device',
            'email' => 'blank-device@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Maker->value,
            'device_name' => '   ',
        ])->assertCreated();

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'MA Motion mobile',
        ]);
    }

    public function test_admin_role_cannot_be_selected_during_public_registration(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Admin Attempt',
            'email' => 'admin-attempt@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Admin->value,
        ])->assertUnprocessable()->assertJsonPath('success', false);

        $this->assertDatabaseMissing('users', ['email' => 'admin-attempt@example.com']);
    }

    public function test_duplicate_email_and_weak_password_are_rejected(): void
    {
        $payload = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
            'role' => UserRole::Appreciator->value,
        ];

        $this->postJson('/api/v1/auth/register', $payload)->assertCreated();

        $this->postJson('/api/v1/auth/register', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Weak Password',
            'email' => 'weak@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Maker->value,
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}

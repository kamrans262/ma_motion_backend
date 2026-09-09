<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('Secure123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'LOGIN@example.com',
            'password' => 'Secure123',
            'device_name' => 'Test Device',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_invalid_credentials_return_standard_unauthorized_response(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('Secure123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'Wrong123',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('Secure123'),
            'status' => UserStatus::Inactive,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'Secure123',
        ])->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}

<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedAccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_active_user_can_read_me(): void
    {
        $user = User::factory()->create(['role' => UserRole::Maker]);
        $token = $user->createToken('test', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', UserRole::Maker->value);
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current', ['mobile'])->plainTextToken;
        $user->createToken('other', ['mobile']);

        $this->withToken($currentToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_inactive_account_cannot_use_existing_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test', ['mobile'])->plainTextToken;

        $user->update(['status' => UserStatus::Inactive]);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertForbidden()
            ->assertJsonPath('message', 'This account is inactive.');
    }
}

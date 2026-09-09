<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth:sanctum', 'account.active', 'role:maker'])
            ->get('/api/v1/test-maker-only', static fn () => response()->json(['ok' => true]));
    }

    public function test_required_role_allows_matching_user(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker]);
        $token = $maker->createToken('test', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/test-maker-only')
            ->assertOk();
    }

    public function test_required_role_rejects_non_matching_user_with_standard_contract(): void
    {
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator]);
        $token = $appreciator->createToken('test', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/test-maker-only')
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }
}

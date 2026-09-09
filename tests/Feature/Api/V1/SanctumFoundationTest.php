<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SanctumFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_can_issue_a_sanctum_api_token(): void
    {
        $user = User::factory()->create();

        $plainTextToken = $user->createToken('test-device')->plainTextToken;

        $this->assertStringContainsString('|', $plainTextToken);
        $this->assertSame(1, $user->tokens()->count());
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}

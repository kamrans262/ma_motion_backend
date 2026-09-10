<?php

namespace Tests\Feature\Api\V1\Account;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_update_own_basic_profile_name(): void
    {
        $user = User::factory()->create(['role'=>UserRole::Appreciator,'status'=>UserStatus::Active]);
        $token = $user->createToken('mobile',['mobile'])->plainTextToken;

        $this->withToken($token)->patchJson('/api/v1/me/profile', ['name'=>'Updated Appreciator'])
            ->assertOk()->assertJsonPath('data.name', 'Updated Appreciator');

        $this->assertSame('Updated Appreciator', $user->fresh()->name);
    }

    public function test_profile_update_requires_authentication(): void
    {
        $this->patchJson('/api/v1/me/profile', ['name'=>'No Session'])->assertUnauthorized();
    }
}

<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceSwitchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_exposes_only_the_users_current_profile_type(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'appreciator')
            ->assertJsonPath('data.maker_registered', false)
            ->assertJsonPath('data.maker_onboarding_completed', false)
            ->assertJsonPath('data.appreciator_registered', true)
            ->assertJsonPath('data.appreciator_onboarding_completed', true)
            ->assertJsonPath('data.maker_profile', null)
            ->assertJsonPath('data.appreciator_profile.location_text', 'Chicago');
    }

    public function test_appreciator_to_maker_is_one_way_and_starts_maker_onboarding(): void
    {
        $user = User::factory()->create([
            'name' => 'Ari Viewer',
            'email' => 'ari@example.com',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago 60601',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/experience/maker/onboarding')
            ->assertOk()
            ->assertJsonPath('data.role', 'maker')
            ->assertJsonPath('data.maker_registered', true)
            ->assertJsonPath('data.maker_onboarding_completed', false)
            ->assertJsonPath('data.appreciator_registered', false)
            ->assertJsonPath('data.appreciator_onboarding_completed', false)
            ->assertJsonPath('data.maker_profile.location_text', 'Chicago 60601')
            ->assertJsonPath('data.appreciator_profile', null);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => $user->id,
            'location_text' => 'Chicago 60601',
        ]);
        $this->assertDatabaseMissing('appreciator_profiles', [
            'user_id' => $user->id,
        ]);
        $this->assertSame(UserRole::Maker, $user->fresh()->role);

        // Completing Maker onboarding can call the start endpoint again.
        // It stays idempotent and never recreates an Appreciator profile.
        $this->withToken($token)
            ->postJson('/api/v1/me/experience/maker/onboarding')
            ->assertOk()
            ->assertJsonPath('data.role', 'maker')
            ->assertJsonPath('data.appreciator_registered', false);

        $this->assertDatabaseCount('maker_profiles', 1);
        $this->assertDatabaseCount('appreciator_profiles', 0);
    }

    public function test_maker_cannot_switch_or_onboard_as_appreciator(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $user->makerProfile()->create([
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/experience', ['experience' => 'appreciator'])
            ->assertNotFound();

        $this->withToken($token)
            ->postJson('/api/v1/me/experience/appreciator/onboarding', [
                'name' => 'Maker',
                'email' => $user->email,
                'location_text' => 'Chicago',
            ])
            ->assertForbidden();

        $this->assertSame(UserRole::Maker, $user->fresh()->role);
        $this->assertDatabaseCount('maker_profiles', 1);
        $this->assertDatabaseCount('appreciator_profiles', 0);
    }
}

<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Appreciators\Models\AppreciatorProfile;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceSwitchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_exposes_both_profile_completion_flags_and_active_experience(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $user->makerProfile()->create(['onboarding_completed_at' => now()]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.active_experience', UserRole::Maker->value)
            ->assertJsonPath('data.maker_registered', true)
            ->assertJsonPath('data.maker_onboarding_completed', true)
            ->assertJsonPath('data.appreciator_registered', true)
            ->assertJsonPath('data.appreciator_onboarding_completed', true)
            ->assertJsonPath('data.maker_profile.location_text', null)
            ->assertJsonPath('data.appreciator_profile.location_text', 'Chicago');
    }

    public function test_me_supplies_both_profile_locations_independent_of_active_role(): void
    {
        $user = User::factory()->create([
            'name' => 'Dual Profile',
            'email' => 'dual@example.com',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $user->makerProfile()->create([
            'bio' => 'Keep the original statement',
            'location_text' => 'Brooklyn 11201',
            'onboarding_completed_at' => now(),
        ]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago 60601',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.name', 'Dual Profile')
            ->assertJsonPath('data.email', 'dual@example.com')
            ->assertJsonPath('data.maker_profile.location_text', 'Brooklyn 11201')
            ->assertJsonPath('data.maker_profile.bio', 'Keep the original statement')
            ->assertJsonPath('data.appreciator_profile.location_text', 'Chicago 60601');

        $this->withToken($token)
            ->patchJson('/api/v1/me/experience', ['experience' => 'maker'])
            ->assertOk()
            ->assertJsonPath('data.maker_profile.location_text', 'Brooklyn 11201')
            ->assertJsonPath('data.appreciator_profile.location_text', 'Chicago 60601');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_completed_profiles_switch_on_same_user_and_keep_token_and_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Dual User',
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $makerProfile = $user->makerProfile()->create([
            'bio' => 'Original maker bio',
            'onboarding_completed_at' => now(),
        ]);
        $user->appreciatorProfile()->create([
            'location_text' => 'Chicago',
            'onboarding_completed_at' => now(),
        ]);

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $artwork = Artwork::query()->create([
            'maker_id' => $maker->id,
            'title' => 'Saved Work',
        ]);
        ArtworkSave::query()->create([
            'user_id' => $user->id,
            'artwork_id' => $artwork->id,
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/experience', ['experience' => 'appreciator'])
            ->assertOk()
            ->assertJsonPath('data.active_experience', 'appreciator')
            ->assertJsonPath('data.maker_onboarding_completed', true)
            ->assertJsonPath('data.appreciator_onboarding_completed', true);

        $this->assertSame(UserRole::Appreciator, $user->fresh()->role);
        $this->assertDatabaseHas('maker_profiles', [
            'id' => $makerProfile->id,
            'bio' => 'Original maker bio',
        ]);
        $this->assertDatabaseHas('artwork_saves', [
            'user_id' => $user->id,
            'artwork_id' => $artwork->id,
        ]);
        $this->assertDatabaseCount('users', 2);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'appreciator');

        $this->withToken($token)
            ->patchJson('/api/v1/me/experience', ['experience' => 'maker'])
            ->assertOk()
            ->assertJsonPath('data.active_experience', 'maker');

        $this->assertSame(UserRole::Maker, $user->fresh()->role);
    }

    public function test_switch_rejects_incomplete_target_profile_without_changing_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $user->makerProfile()->create(['onboarding_completed_at' => now()]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/me/experience', ['experience' => 'appreciator'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['experience']);

        $this->assertSame(UserRole::Maker, $user->fresh()->role);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_maker_can_complete_appreciator_profile_on_same_account_without_new_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing Maker',
            'email' => 'maker@example.com',
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $user->makerProfile()->create([
            'bio' => 'Keep this maker profile',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/experience/appreciator/onboarding', [
                'name' => 'Edited Name',
                'email' => 'edited@example.com',
                'location_text' => 'Chicago',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Edited Name')
            ->assertJsonPath('data.email', 'edited@example.com')
            ->assertJsonPath('data.role', 'appreciator')
            ->assertJsonPath('data.appreciator_onboarding_completed', true)
            ->assertJsonPath('data.maker_onboarding_completed', true);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Edited Name',
            'email' => 'edited@example.com',
        ]);
        $this->assertDatabaseHas('maker_profiles', [
            'user_id' => $user->id,
            'bio' => 'Keep this maker profile',
        ]);
        $this->assertDatabaseHas('appreciator_profiles', [
            'user_id' => $user->id,
            'location_text' => 'Chicago',
        ]);

        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
    }

    public function test_appreciator_can_start_maker_onboarding_on_same_account_without_duplicate_user(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        AppreciatorProfile::query()->create([
            'user_id' => $user->id,
            'location_text' => 'Chicago',
            'onboarding_completed_at' => now(),
        ]);

        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/experience/maker/onboarding')
            ->assertOk()
            ->assertJsonPath('data.role', 'maker')
            ->assertJsonPath('data.maker_registered', true)
            ->assertJsonPath('data.maker_onboarding_completed', false)
            ->assertJsonPath('data.appreciator_onboarding_completed', true);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('maker_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('appreciator_profiles', [
            'user_id' => $user->id,
            'location_text' => 'Chicago',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/me/maker-profile')
            ->assertOk()
            ->assertJsonPath('data.onboarding_completed', false);
    }
}

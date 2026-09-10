<?php

namespace Tests\Feature\Api\V1\Saves;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedMakerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_appreciator_can_save_list_and_unsave_maker_idempotently(): void
    {
        $maker = User::factory()->create(['name' => 'Saved Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->makerProfile()->create(['bio' => 'A saved Maker', 'location_text' => 'London']);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/me/saved-makers/'.$maker->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.saved', true)
            ->assertJsonPath('data.maker.id', $maker->id)
            ->assertJsonPath('data.maker.saved_count', 1);

        $this->withToken($token)->postJson('/api/v1/me/saved-makers/'.$maker->id)
            ->assertOk()
            ->assertJsonPath('data.saved', true);

        $this->assertSame(1, MakerSave::query()->count());

        $this->withToken($token)->getJson('/api/v1/me/saved-makers')
            ->assertOk()
            ->assertJsonPath('data.0.id', $maker->id)
            ->assertJsonPath('data.0.saved_count', 1)
            ->assertJsonPath('meta.total', 1);

        $this->withToken($token)->deleteJson('/api/v1/me/saved-makers/'.$maker->id)
            ->assertOk()
            ->assertJsonPath('data.maker_id', $maker->id)
            ->assertJsonPath('data.saved', false);

        $this->assertDatabaseMissing('maker_saves', ['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        $this->withToken($token)->getJson('/api/v1/me/saved-makers')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_save_management_requires_authentication_and_appreciator_role(): void
    {
        $activeMaker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);

        $this->postJson('/api/v1/me/saved-makers/'.$activeMaker->id)
            ->assertUnauthorized();

        $makerUser = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $makerToken = $makerUser->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($makerToken)
            ->postJson('/api/v1/me/saved-makers/'.$activeMaker->id)
            ->assertForbidden();
    }

    public function test_only_active_makers_can_be_newly_saved(): void
    {
        $inactiveMaker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Inactive]);
        $nonMaker = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-makers/'.$inactiveMaker->id)
            ->assertNotFound();

        $this->withToken($token)
            ->postJson('/api/v1/me/saved-makers/'.$nonMaker->id)
            ->assertNotFound();
    }

    public function test_inactive_saved_maker_is_hidden_but_can_still_be_unsaved(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        $maker->update(['status' => UserStatus::Inactive]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/saved-makers')->assertOk()->assertJsonPath('meta.total', 0);
        $this->assertDatabaseHas('maker_saves', ['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);

        $this->withToken($token)->deleteJson('/api/v1/me/saved-makers/'.$maker->id)->assertOk()->assertJsonPath('data.saved', false);
        $this->assertDatabaseMissing('maker_saves', ['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
    }
}

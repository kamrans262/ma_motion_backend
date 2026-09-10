<?php

namespace Tests\Feature\Api\V1\Saves;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_maker_receives_only_aggregate_profile_saved_count(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $first = User::factory()->create(['name' => 'Private Saver One', 'email' => 'saver1@example.com', 'role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $second = User::factory()->create(['name' => 'Private Saver Two', 'email' => 'saver2@example.com', 'role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $first->id, 'maker_id' => $maker->id]);
        MakerSave::query()->create(['appreciator_id' => $second->id, 'maker_id' => $maker->id]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me/maker-statistics')
            ->assertOk()
            ->assertJsonPath('data.maker_id', $maker->id)
            ->assertJsonPath('data.profile_saved_count', 2);

        $this->assertStringNotContainsString('Private Saver One', $response->getContent());
        $this->assertStringNotContainsString('saver1@example.com', $response->getContent());
        $this->assertStringNotContainsString('Private Saver Two', $response->getContent());
    }

    public function test_maker_statistics_requires_maker_authentication(): void
    {
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;

        $this->getJson('/api/v1/me/maker-statistics')->assertUnauthorized();
        $this->withToken($token)->getJson('/api/v1/me/maker-statistics')->assertForbidden();
    }
}

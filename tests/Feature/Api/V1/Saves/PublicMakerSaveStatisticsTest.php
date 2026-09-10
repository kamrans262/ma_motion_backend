<?php

namespace Tests\Feature\Api\V1\Saves;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMakerSaveStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_maker_directory_and_detail_expose_aggregate_saved_count(): void
    {
        $maker = User::factory()->create(['name' => 'Popular Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->makerProfile()->create(['bio' => 'Popular']);
        $appreciators = User::factory()->count(2)->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        foreach ($appreciators as $appreciator) {
            MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        }

        $this->getJson('/api/v1/makers?search=Popular Maker')
            ->assertOk()
            ->assertJsonPath('data.0.id', $maker->id)
            ->assertJsonPath('data.0.saved_count', 2);

        $this->getJson('/api/v1/makers/'.$maker->id)
            ->assertOk()
            ->assertJsonPath('data.id', $maker->id)
            ->assertJsonPath('data.saved_count', 2);
    }
}

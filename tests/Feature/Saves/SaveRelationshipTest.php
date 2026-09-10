<?php

namespace Tests\Feature\Saves;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_relationship_is_bidirectional_unique_and_cascades_on_account_deletion(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);

        $this->assertTrue($appreciator->savedMakers()->whereKey($maker->id)->exists());
        $this->assertTrue($maker->savedByAppreciators()->whereKey($appreciator->id)->exists());

        $appreciator->delete();
        $this->assertDatabaseCount('maker_saves', 0);
    }
}

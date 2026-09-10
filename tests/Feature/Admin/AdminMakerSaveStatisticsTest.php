<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMakerSaveStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_maker_directory_and_detail_show_save_count(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['name' => 'Statistics Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $maker->makerProfile()->create(['bio' => 'Stats']);
        $appreciators = User::factory()->count(2)->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        foreach ($appreciators as $appreciator) {
            MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        }

        $this->actingAs($admin)->get('/admin/makers?search=Statistics Maker')
            ->assertOk()
            ->assertSee('Hearts / Saves')
            ->assertSee('>2</a>', false);

        $this->actingAs($admin)->get('/admin/makers/'.$maker->id)
            ->assertOk()
            ->assertSee('Profile & Statistics', false)
            ->assertSee('Hearts / Saves')
            ->assertSee('View save details');
    }
}

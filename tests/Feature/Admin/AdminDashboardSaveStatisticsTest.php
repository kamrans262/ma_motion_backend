<?php

namespace Tests\Feature\Admin;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardSaveStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_surfaces_artwork_show_save_and_top_maker_statistics(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['name' => 'Dashboard Popular Maker', 'role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $first = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $second = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $first->id, 'maker_id' => $maker->id]);
        MakerSave::query()->create(['appreciator_id' => $second->id, 'maker_id' => $maker->id]);
        Artwork::query()->create(['maker_id' => $maker->id, 'title' => 'Dashboard Artwork']);
        Show::query()->create(['maker_id' => $maker->id, 'name' => 'Dashboard Show', 'start_date' => '2026-10-01', 'end_date' => '2026-10-10']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Total Artwork')
            ->assertSee('Total Shows')
            ->assertSee('Maker Saves')
            ->assertSee('Most Hearted Makers')
            ->assertSee('Dashboard Popular Maker')
            ->assertSee('Recently added artwork')
            ->assertSee('Dashboard Artwork');
    }
}

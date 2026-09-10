<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSavesUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_saves_admin_reuses_ma_motion_design_system_and_responsive_contract(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);

        $this->actingAs($admin)->get('/admin/saves')
            ->assertOk()
            ->assertSee('ma-save-stat-grid', false)
            ->assertSee('ma-save-filter-grid', false)
            ->assertSee('ma-ranked-list', false)
            ->assertSee('Hearts & Saves', false);

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));

        $this->assertStringContainsString("admin.saves.index", $sidebar);
        $this->assertStringNotContainsString('<span>Hearts & Saves</span><span class="ma-nav__soon">Soon</span>', $sidebar);
        $this->assertStringContainsString('.ma-save-filter-grid', $css);
        $this->assertStringContainsString('.ma-ranked-list', $css);
        $this->assertStringContainsString('.ma-dashboard-section', $css);
        $this->assertStringContainsString('@media (max-width: 720px)', $css);
        $this->assertStringContainsString('margin-bottom: var(--ma-space-6);', $css);
    }
}

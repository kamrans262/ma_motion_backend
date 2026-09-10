<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminShowUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_admin_pages_reuse_ma_motion_design_system_and_sidebar_navigation(): void
    {
        $admin = User::factory()->create(['role'=>UserRole::Admin,'status'=>UserStatus::Active]);
        $maker = User::factory()->create(['role'=>UserRole::Maker,'status'=>UserStatus::Active]);
        $show = Show::query()->create(['maker_id'=>$maker->id,'name'=>'Responsive Show','start_date'=>'2026-10-01','end_date'=>'2026-10-10','is_visible'=>true,'sort_order'=>0]);

        $this->actingAs($admin)->get('/admin/shows')->assertOk()->assertSee('Shows &amp; Exhibitions', false)->assertSee('ma-show-filter-grid', false)->assertSee('Create show');
        $this->actingAs($admin)->get('/admin/shows/'.$show->id)->assertOk()->assertSee('ma-show-artwork-picker', false)->assertSee('Public profile rule');

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));
        $this->assertStringContainsString("admin.shows.index", $sidebar);
        $this->assertStringNotContainsString('<span>Shows</span><span class="ma-nav__soon">Soon</span>', $sidebar);
        foreach (['.ma-show-filter-grid','.ma-show-artwork-picker','@media (max-width: 720px)','margin-bottom: var(--ma-space-6);'] as $needle) {
            $this->assertStringContainsString($needle, $css);
        }
    }
}

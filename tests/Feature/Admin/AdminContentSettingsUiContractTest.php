<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Content\Models\AppContentPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminContentSettingsUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_and_settings_reuse_ma_motion_design_system_and_sidebar_scrollbar(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        AppContentPage::query()->create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'body' => 'Policy', 'is_system' => true, 'is_published' => false, 'sort_order' => 20]);

        $this->actingAs($admin)->get('/admin/content')->assertOk()->assertSee('ma-content-stat-grid', false)->assertSee('ma-content-grid', false)->assertSee('ma-content-filter-grid', false)->assertSee('ma-content-title-slug-grid', false);
        $this->actingAs($admin)->get('/admin/settings')->assertOk()->assertSee('ma-settings-grid', false)->assertSee('Administrator details')->assertSee('Change password');

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));
        $this->assertStringContainsString('admin.content.index', $sidebar);
        $this->assertStringContainsString('admin.settings.index', $sidebar);
        $this->assertStringContainsString('.ma-content-grid', $css);
        $this->assertStringContainsString('.ma-content-title-slug-grid { align-items: start; }', $css);
        $this->assertStringContainsString('.ma-content-title-slug-grid .ma-field > input { height: 50px; min-height: 50px; }', $css);
        $this->assertStringContainsString('.ma-settings-grid', $css);
        $this->assertStringContainsString('scrollbar-gutter: stable', $css);
        $this->assertStringContainsString('.ma-nav::-webkit-scrollbar-thumb', $css);
    }
}

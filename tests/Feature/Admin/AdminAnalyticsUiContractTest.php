<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAnalyticsUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_and_audit_pages_reuse_ma_motion_design_and_sidebar_scrollbar(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);

        $this->actingAs($admin)->get('/admin/analytics')->assertOk()->assertSee('ma-analytics-grid', false)->assertSee('ma-analytics-period-stats', false);
        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk()->assertSee('ma-audit-filter-grid', false)->assertSee('ma-audit-directory', false);

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));
        $this->assertStringContainsString("admin.analytics.index", $sidebar);
        $this->assertStringContainsString("admin.audit-logs.index", $sidebar);
        $this->assertStringContainsString('.ma-analytics-breakdown-grid', $css);
        $this->assertStringContainsString('.ma-audit-filter-grid', $css);
        $this->assertStringContainsString('scrollbar-gutter: stable', $css);
        $this->assertStringContainsString('.ma-nav::-webkit-scrollbar-thumb', $css);
    }
}

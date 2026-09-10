<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminNotificationsUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_admin_reuses_ma_motion_design_and_responsive_contract(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);

        $this->actingAs($admin)->get('/admin/notifications')
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('ma-notification-stat-grid', false)
            ->assertSee('ma-notification-filter-grid', false)
            ->assertSee('ma-notification-policy', false)
            ->assertSee('Current Delivery Policy');

        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $css = file_get_contents(public_path('assets/admin/css/admin.css'));

        $this->assertStringContainsString('admin.notifications.index', $sidebar);
        $this->assertStringNotContainsString('<span>Notifications</span><span class="ma-nav__soon">Soon</span>', $sidebar);
        $this->assertStringContainsString('.ma-notification-filter-grid', $css);
        $this->assertStringContainsString('@media (max-width: 720px)', $css);
        $this->assertStringContainsString('.ma-nav::-webkit-scrollbar-thumb', $css);
    }
}

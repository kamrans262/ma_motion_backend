<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_links_to_analytics_and_surfaces_audit_activity(): void
    {
        $admin = User::factory()->create(['name' => 'Dashboard Admin', 'role' => UserRole::Admin, 'status' => UserStatus::Active]);
        AdminAuditLog::query()->create(['admin_user_id' => $admin->id, 'event' => 'admin.settings.profile.update', 'route_name' => 'admin.settings.profile.update', 'method' => 'PUT', 'path' => 'admin/settings/profile', 'response_status' => 302]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Open Analytics')
            ->assertSee('Audit Events')
            ->assertSee('Recent admin activity')
            ->assertSee('admin.settings.profile.update');
    }
}

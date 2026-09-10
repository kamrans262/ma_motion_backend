<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_export_audit_log(): void
    {
        $admin = User::factory()->create(['name' => 'Audit Administrator', 'role' => UserRole::Admin, 'status' => UserStatus::Active]);
        AdminAuditLog::query()->create(['admin_user_id' => $admin->id, 'event' => 'admin.settings.profile.update', 'route_name' => 'admin.settings.profile.update', 'method' => 'PUT', 'path' => 'admin/settings/profile', 'request_data' => ['fields' => ['name']], 'response_status' => 302, 'ip_address' => '127.0.0.1']);
        AdminAuditLog::query()->create(['admin_user_id' => $admin->id, 'event' => 'admin.content.store', 'route_name' => 'admin.content.store', 'method' => 'POST', 'path' => 'admin/content', 'request_data' => ['fields' => ['title']], 'response_status' => 302, 'ip_address' => '127.0.0.1']);

        $this->actingAs($admin)->get('/admin/audit-logs?method=PUT&search=settings')
            ->assertOk()
            ->assertSee('Admin Audit Logs')
            ->assertSee('admin.settings.profile.update')
            ->assertDontSee('admin.content.store')
            ->assertSee('ma-audit-directory', false);

        $response = $this->actingAs($admin)->get('/admin/audit-logs/export?method=PUT');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}

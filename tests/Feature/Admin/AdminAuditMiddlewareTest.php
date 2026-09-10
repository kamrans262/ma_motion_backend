<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAuditMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_write_operation_is_audited_with_target_and_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);

        $this->actingAs($admin)->put('/admin/makers/'.$maker->id, [
            'name' => 'Audited Maker',
            'email' => $maker->email,
            'status' => UserStatus::Active->value,
            'bio' => 'Private profile text should not be copied into the audit payload.',
            'location_text' => null,
            'location_id' => null,
        ])->assertRedirect();

        $log = AdminAuditLog::query()->where('route_name', 'admin.makers.update')->firstOrFail();
        $this->assertSame($admin->id, $log->admin_user_id);
        $this->assertSame('PUT', $log->method);
        $this->assertSame('maker', $log->target_type);
        $this->assertSame((string) $maker->id, $log->target_id);
        $this->assertSame(302, $log->response_status);
        $encoded = json_encode($log->request_data);
        $this->assertStringNotContainsString('Private profile text', $encoded);
        $this->assertStringNotContainsString($maker->email, $encoded);
    }

    public function test_password_values_are_never_persisted_in_audit_request_data(): void
    {
        $admin = User::factory()->create(['password' => 'OldAdmin12', 'role' => UserRole::Admin, 'status' => UserStatus::Active]);

        $this->actingAs($admin)->put('/admin/settings/password', [
            'current_password' => 'OldAdmin12',
            'password' => 'NewAdmin34',
            'password_confirmation' => 'NewAdmin34',
        ])->assertRedirect();

        $log = AdminAuditLog::query()->where('route_name', 'admin.settings.password.update')->firstOrFail();
        $encoded = json_encode($log->request_data);
        $this->assertStringNotContainsString('OldAdmin12', $encoded);
        $this->assertStringNotContainsString('NewAdmin34', $encoded);
        $this->assertContains('password', $log->request_data['fields']);
    }
}

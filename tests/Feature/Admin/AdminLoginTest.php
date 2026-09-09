<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('MA Motion')
            ->assertSee('Welcome back');
    }

    public function test_active_admin_can_login_with_session_authentication(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Secure123',
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        $this->post('/admin/login', [
            'email' => 'ADMIN@example.com',
            'password' => 'Secure123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_non_admin_cannot_login_to_admin_panel(): void
    {
        User::factory()->create([
            'email' => 'maker@example.com',
            'password' => 'Secure123',
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'maker@example.com',
            'password' => 'Secure123',
        ])->assertRedirect('/admin/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive-admin@example.com',
            'password' => 'Secure123',
            'role' => UserRole::Admin,
            'status' => UserStatus::Inactive,
        ]);

        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'inactive-admin@example.com',
            'password' => 'Secure123',
        ])->assertRedirect('/admin/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}

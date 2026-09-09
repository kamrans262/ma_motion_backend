<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_active_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('MA Motion control center');
    }

    public function test_authenticated_non_admin_is_signed_out_and_denied_admin_access(): void
    {
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($maker)
            ->get('/admin')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_admin_is_signed_out_and_denied_admin_access(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => UserStatus::Inactive,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}

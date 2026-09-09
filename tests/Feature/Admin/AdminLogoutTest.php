<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_logout_and_session_authentication_is_cleared(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($admin)
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}

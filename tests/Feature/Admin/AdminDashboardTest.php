<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_live_account_metrics_and_recent_users(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        User::factory()->count(2)->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);

        User::factory()->create([
            'name' => 'Recent Appreciator',
            'email' => 'recent@example.com',
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Inactive,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Makers')
            ->assertSee('Appreciators')
            ->assertSee('Recent Appreciator')
            ->assertSee('recent@example.com');

        $response->assertDontSee('password');
    }
}

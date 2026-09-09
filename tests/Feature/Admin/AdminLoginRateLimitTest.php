<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'rate-admin@example.com',
            'password' => 'Secure123',
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/admin/login', [
                'email' => 'rate-admin@example.com',
                'password' => 'WrongPassword123',
            ])->assertRedirect();
        }

        $this->post('/admin/login', [
            'email' => 'rate-admin@example.com',
            'password' => 'WrongPassword123',
        ])->assertTooManyRequests();
    }
}

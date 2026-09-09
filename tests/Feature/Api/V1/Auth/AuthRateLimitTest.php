<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'limit@example.com',
            'password' => Hash::make('Secure123'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'limit@example.com',
                'password' => 'Wrong123',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'limit@example.com',
            'password' => 'Wrong123',
        ])->assertTooManyRequests()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Too many requests. Please try again later.');
    }
}

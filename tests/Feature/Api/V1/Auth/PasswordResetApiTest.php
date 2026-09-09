<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_uses_generic_response_and_notifies_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $existing = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertOk()->json();

        $missing = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertOk()->json();

        $this->assertSame($existing['message'], $missing['message']);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_valid_reset_token_changes_password_and_revokes_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('OldPass123'),
        ]);
        $user->createToken('old-device', ['mobile']);
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email']);
    }
}

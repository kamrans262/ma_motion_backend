<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\PushDeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PushDeviceTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_token_registration_requires_authentication(): void
    {
        $this->postJson('/api/v1/me/push-tokens', [
            'token' => str_repeat('token', 18), 'platform' => 'android',
        ])->assertUnauthorized();
    }

    public function test_users_can_register_rotate_and_remove_their_own_push_device(): void
    {
        $firstUser = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $secondUser = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = str_repeat('fcmRegistration', 8);

        Sanctum::actingAs($firstUser);
        $this->postJson('/api/v1/me/push-tokens', [
            'token' => $token, 'platform' => 'android',
        ])->assertOk()->assertJsonPath('data.registered', true);

        $this->assertDatabaseHas('push_device_tokens', [
            'user_id' => $firstUser->id,
            'token_hash' => hash('sha256', $token),
        ]);

        Sanctum::actingAs($secondUser);
        // FCM rotates ownership if a user signs into the same device.
        $this->postJson('/api/v1/me/push-tokens', [
            'token' => $token, 'platform' => 'android',
        ])->assertOk();
        $this->assertSame(1, PushDeviceToken::query()->count());
        $this->assertDatabaseHas('push_device_tokens', [
            'user_id' => $secondUser->id,
            'token_hash' => hash('sha256', $token),
        ]);

        Sanctum::actingAs($firstUser);
        $this->deleteJson('/api/v1/me/push-tokens', [
            'token' => $token,
        ])->assertOk();
        $this->assertSame(1, PushDeviceToken::query()->count());

        Sanctum::actingAs($secondUser);
        $this->deleteJson('/api/v1/me/push-tokens', [
            'token' => $token,
        ])->assertOk()->assertJsonPath('data.unregistered', true);
        $this->assertDatabaseCount('push_device_tokens', 0);
    }

    public function test_invalid_platform_and_too_short_token_are_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/push-tokens', [
            'token' => 'short', 'platform' => 'web',
        ])->assertUnprocessable()->assertJsonValidationErrors(['token', 'platform']);
    }

    public function test_deleting_an_account_cascades_push_devices(): void
    {
        $user = User::factory()->create();
        $token = str_repeat('firebaseToken', 9);
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/me/push-tokens', [
            'token' => $token, 'platform' => 'ios',
        ])->assertOk();

        // User currently does not use SoftDeletes; deleting must clean up tokens.
        $user->delete();
        $this->assertDatabaseCount('push_device_tokens', 0);
    }
}

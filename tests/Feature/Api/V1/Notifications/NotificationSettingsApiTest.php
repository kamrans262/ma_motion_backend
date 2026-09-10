<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_read_default_and_update_notification_settings(): void
    {
        $user = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/notification-settings')
            ->assertOk()
            ->assertJsonPath('data.saved_maker_show_alerts', true);

        $this->withToken($token)->patchJson('/api/v1/me/notification-settings', ['saved_maker_show_alerts' => false])
            ->assertOk()
            ->assertJsonPath('data.saved_maker_show_alerts', false);

        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id, 'saved_maker_show_alerts' => false]);
    }

    public function test_notification_settings_require_authentication(): void
    {
        $this->getJson('/api/v1/me/notification-settings')->assertUnauthorized();
    }
}

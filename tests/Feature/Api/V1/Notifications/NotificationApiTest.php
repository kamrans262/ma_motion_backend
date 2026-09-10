<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_list_and_mark_owned_notifications_read(): void
    {
        $user = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $notification = InAppNotification::query()->create([
            'recipient_id' => $user->id,
            'type' => 'saved_maker_show',
            'title' => 'New show',
            'body' => 'A saved Maker announced a show.',
            'dedupe_key' => 'test-owned-1',
        ]);
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonPath('data.0.id', $notification->id)
            ->assertJsonPath('data.0.is_read', false);

        $this->withToken($token)->patchJson('/api/v1/me/notifications/'.$notification->id.'/read')
            ->assertOk()
            ->assertJsonPath('data.is_read', true);
    }

    public function test_active_user_can_mark_all_owned_notifications_read(): void
    {
        $user = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        foreach ([1, 2] as $index) {
            InAppNotification::query()->create([
                'recipient_id' => $user->id,
                'type' => 'saved_maker_show',
                'title' => 'Alert '.$index,
                'body' => 'Notification body',
                'dedupe_key' => 'test-read-all-'.$index,
            ]);
        }
        $token = $user->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->patchJson('/api/v1/me/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.marked_read', 2)
            ->assertJsonPath('data.unread_count', 0);

        $this->assertSame(0, InAppNotification::query()->whereNull('read_at')->count());
    }

    public function test_user_cannot_mark_another_accounts_notification_read(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $other = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        $notification = InAppNotification::query()->create([
            'recipient_id' => $owner->id,
            'type' => 'saved_maker_show',
            'title' => 'Private alert',
            'body' => 'Private body',
            'dedupe_key' => 'test-private-1',
        ]);
        $token = $other->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->patchJson('/api/v1/me/notifications/'.$notification->id.'/read')->assertNotFound();
    }

    public function test_notification_management_requires_authentication(): void
    {
        $this->getJson('/api/v1/me/notifications')->assertUnauthorized();
    }
}

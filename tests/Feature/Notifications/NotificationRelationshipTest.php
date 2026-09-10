<?php

namespace Tests\Feature\Notifications;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Features\Notifications\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipient_deletion_cascades_notification_and_preference_records(): void
    {
        $user = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        NotificationPreference::query()->create(['user_id' => $user->id, 'saved_maker_show_alerts' => true]);
        InAppNotification::query()->create([
            'recipient_id' => $user->id,
            'type' => 'saved_maker_show',
            'title' => 'Lifecycle test',
            'body' => 'Lifecycle body',
            'dedupe_key' => 'lifecycle-1',
        ]);

        $user->delete();

        $this->assertDatabaseCount('notification_preferences', 0);
        $this->assertDatabaseCount('in_app_notifications', 0);
    }
}

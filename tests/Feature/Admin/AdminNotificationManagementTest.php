<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminNotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_inspect_and_filter_in_app_notification_log(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => UserStatus::Active]);
        $recipient = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active, 'name' => 'Notification Recipient']);
        InAppNotification::query()->create([
            'recipient_id' => $recipient->id,
            'type' => 'saved_maker_show',
            'title' => 'Studio show announced',
            'body' => 'A saved Maker announced an upcoming show.',
            'dedupe_key' => 'admin-notification-1',
        ]);

        $this->actingAs($admin)->get('/admin/notifications')
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Notification Recipient')
            ->assertSee('Studio show announced');

        $this->actingAs($admin)->get('/admin/notifications?status=unread&type=saved_maker_show&search=Studio')
            ->assertOk()
            ->assertSee('Studio show announced');
    }
}

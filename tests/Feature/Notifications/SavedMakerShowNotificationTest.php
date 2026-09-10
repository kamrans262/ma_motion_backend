<?php

namespace Tests\Feature\Notifications;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Features\Notifications\Models\NotificationPreference;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Actions\CreateShowAction;
use App\Features\Shows\Actions\UpdateShowAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SavedMakerShowNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_appreciator_receives_one_alert_when_maker_announces_upcoming_show(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active, 'name' => 'Avery Maker']);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);

        $show = app(CreateShowAction::class)->execute($maker, [
            'name' => 'Future Motion',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
        ]);

        $this->assertDatabaseHas('in_app_notifications', [
            'recipient_id' => $appreciator->id,
            'maker_id' => $maker->id,
            'show_id' => $show->id,
            'type' => 'saved_maker_show',
        ]);
        $this->assertSame(1, InAppNotification::query()->where('recipient_id', $appreciator->id)->count());

        app(UpdateShowAction::class)->execute($show, ['description' => 'Updated details']);
        $this->assertSame(1, InAppNotification::query()->where('recipient_id', $appreciator->id)->count());
    }

    public function test_disabled_preference_suppresses_future_saved_maker_show_alerts(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        $appreciator = User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);
        MakerSave::query()->create(['appreciator_id' => $appreciator->id, 'maker_id' => $maker->id]);
        NotificationPreference::query()->create(['user_id' => $appreciator->id, 'saved_maker_show_alerts' => false]);

        app(CreateShowAction::class)->execute($maker, [
            'name' => 'Quiet Show',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->assertDatabaseCount('in_app_notifications', 0);
    }

    public function test_unsaved_appreciator_and_past_show_do_not_generate_alerts(): void
    {
        $maker = User::factory()->create(['role' => UserRole::Maker, 'status' => UserStatus::Active]);
        User::factory()->create(['role' => UserRole::Appreciator, 'status' => UserStatus::Active]);

        app(CreateShowAction::class)->execute($maker, [
            'name' => 'Past Show',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertDatabaseCount('in_app_notifications', 0);
    }
}

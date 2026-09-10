<?php

namespace App\Features\Notifications\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Enums\NotificationType;
use App\Features\Notifications\Models\InAppNotification;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Enums\ShowStatus;
use App\Features\Shows\Models\Show;
final class SavedMakerShowNotificationService
{
    /**
     * Create one in-app notification per eligible saved-Maker relationship.
     * The dedupe key makes repeated show edits/visibility toggles idempotent.
     */
    public function announceIfEligible(Show $show): int
    {
        $show->loadMissing('maker:id,name,role,status');
        $maker = $show->maker;

        if (! $show->is_visible || $show->status() === ShowStatus::Past || ! $maker || ! $maker->isActive()) {
            return 0;
        }

        $recipientIds = MakerSave::query()
            ->select('maker_saves.appreciator_id')
            ->join('users', 'users.id', '=', 'maker_saves.appreciator_id')
            ->leftJoin('notification_preferences', 'notification_preferences.user_id', '=', 'maker_saves.appreciator_id')
            ->where('maker_saves.maker_id', $show->maker_id)
            ->where('users.role', UserRole::Appreciator->value)
            ->where('users.status', UserStatus::Active->value)
            ->where(function ($query): void {
                $query->whereNull('notification_preferences.saved_maker_show_alerts')
                    ->orWhere('notification_preferences.saved_maker_show_alerts', true);
            })
            ->pluck('maker_saves.appreciator_id')
            ->unique()
            ->values();

        $created = 0;
        $status = $show->status();
        foreach ($recipientIds as $recipientId) {
            $notification = InAppNotification::query()->firstOrCreate(
                ['dedupe_key' => 'saved-maker-show:'.$recipientId.':'.$show->id],
                [
                    'recipient_id' => (int) $recipientId,
                    'maker_id' => $show->maker_id,
                    'show_id' => $show->id,
                    'type' => NotificationType::SavedMakerShow->value,
                    'title' => $maker->name.' announced a show',
                    'body' => $show->name.' is '.$status->value.'.',
                    'data' => [
                        'maker_id' => $show->maker_id,
                        'maker_name' => $maker->name,
                        'show_id' => $show->id,
                        'show_name' => $show->name,
                        'show_status' => $status->value,
                        'start_date' => $show->start_date->toDateString(),
                        'end_date' => $show->end_date->toDateString(),
                        'location_text' => $show->location_text,
                    ],
                ],
            );

            if ($notification->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}

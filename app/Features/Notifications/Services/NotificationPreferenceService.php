<?php

namespace App\Features\Notifications\Services;

use App\Features\Notifications\Models\NotificationPreference;
use App\Models\User;

final class NotificationPreferenceService
{
    public function forUser(User $user): NotificationPreference
    {
        return NotificationPreference::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['saved_maker_show_alerts' => true],
        );
    }

    /** @param array{saved_maker_show_alerts:bool} $data */
    public function update(User $user, array $data): NotificationPreference
    {
        $preference = $this->forUser($user);
        $preference->update($data);

        return $preference->fresh();
    }
}

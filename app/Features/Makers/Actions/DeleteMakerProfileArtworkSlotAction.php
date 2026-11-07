<?php

namespace App\Features\Makers\Actions;

use App\Models\User;

final class DeleteMakerProfileArtworkSlotAction
{
    public function execute(User $maker, int $slot): void
    {
        if ($slot < 2 || $slot > 4) {
            return;
        }

        $maker->makerProfile?->artworkSlots()->where('slot', $slot)->delete();
    }
}

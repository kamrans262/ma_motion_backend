<?php

namespace App\Features\Saves\Services;

use App\Features\Saves\Models\MakerSave;
use App\Models\User;

final class MakerStatisticsService
{
    /** @return array{maker_id:int,profile_saved_count:int} */
    public function forMaker(User $maker): array
    {
        return [
            'maker_id' => $maker->id,
            'profile_saved_count' => MakerSave::query()->where('maker_id', $maker->id)->count(),
        ];
    }
}

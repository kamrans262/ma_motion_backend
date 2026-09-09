<?php

namespace App\Features\Admin\Locations\Actions;

use App\Features\Locations\Models\Location;

final class ToggleStatusLocationAction
{
    public function execute(Location $location): Location
    {
        $location->update(['is_active' => ! $location->is_active]);
        return $location->refresh();
    }
}

<?php

namespace App\Features\Admin\Locations\Actions;

use App\Features\Locations\Models\Location;

final class DeleteLocationAction
{
    public function execute(Location $location): void
    {
        $location->delete();
    }
}

<?php

namespace App\Features\Admin\Locations\Actions;

use App\Features\Locations\Models\Location;

final class UpdateLocationAction
{
    public function execute(Location $location, array $data, NormalizeLocationData $normalizer): Location
    {
        $location->update($normalizer->execute($data));
        return $location->refresh();
    }
}

<?php

namespace App\Features\Admin\Locations\Actions;

use App\Features\Locations\Models\Location;

final class CreateLocationAction
{
    public function execute(array $data, NormalizeLocationData $normalizer): Location
    {
        $location = Location::create($normalizer->execute($data));
        return $location;
    }
}

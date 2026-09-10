<?php

namespace App\Features\Discovery\Data;

final readonly class LocationMatch
{
    /**
     * @param array<int, int> $ids
     * @param array<int, float> $distancesById
     */
    public function __construct(
        public array $ids,
        public array $distancesById = [],
    ) {}

    public function distanceFor(?int $locationId): ?float
    {
        if ($locationId === null || ! array_key_exists($locationId, $this->distancesById)) {
            return null;
        }

        return $this->distancesById[$locationId];
    }
}

<?php

namespace App\Features\Discovery\Services;

use App\Features\Discovery\Data\LocationMatch;
use App\Features\Locations\Models\Location;

final class LocationMatchService
{
    private const EARTH_RADIUS_KM = 6371.0088;

    /** @param array<string, mixed> $filters */
    public function match(array $filters): ?LocationMatch
    {
        if (! $this->hasLocationFilter($filters)) {
            return null;
        }

        $query = Location::query()->where('is_active', true);

        if (! empty($filters['location_id'])) {
            $query->whereKey((int) $filters['location_id']);
        }
        if (! empty($filters['city'])) {
            $query->where('city', 'like', (string) $filters['city']);
        }
        if (! empty($filters['postal_code'])) {
            $query->where('postal_code', 'like', (string) $filters['postal_code']);
        }
        if (! empty($filters['country_code'])) {
            $query->where('country_code', strtoupper((string) $filters['country_code']));
        }

        $hasRadius = isset($filters['latitude'], $filters['longitude'], $filters['radius_km']);
        if (! $hasRadius) {
            return new LocationMatch(
                ids: $query->pluck('id')->map(static fn ($id): int => (int) $id)->all(),
            );
        }

        $latitude = (float) $filters['latitude'];
        $longitude = (float) $filters['longitude'];
        $radiusKm = (float) $filters['radius_km'];

        $this->applyBoundingBox($query, $latitude, $longitude, $radiusKm);

        $ids = [];
        $distances = [];
        foreach ($query->get(['id', 'latitude', 'longitude']) as $location) {
            if ($location->latitude === null || $location->longitude === null) {
                continue;
            }

            $distance = $this->distanceKm(
                $latitude,
                $longitude,
                (float) $location->latitude,
                (float) $location->longitude,
            );

            if ($distance <= $radiusKm + 0.000001) {
                $id = (int) $location->id;
                $ids[] = $id;
                $distances[$id] = round($distance, 2);
            }
        }

        return new LocationMatch(ids: $ids, distancesById: $distances);
    }

    /** @param array<string, mixed> $filters */
    private function hasLocationFilter(array $filters): bool
    {
        foreach (['location_id', 'city', 'postal_code', 'country_code', 'radius_km'] as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                return true;
            }
        }

        return false;
    }

    private function applyBoundingBox($query, float $latitude, float $longitude, float $radiusKm): void
    {
        $latitudeDelta = $radiusKm / 111.045;
        $minLatitude = max(-90.0, $latitude - $latitudeDelta);
        $maxLatitude = min(90.0, $latitude + $latitudeDelta);
        $query->whereBetween('latitude', [$minLatitude, $maxLatitude]);

        $cosLatitude = abs(cos(deg2rad($latitude)));
        $longitudeDelta = $cosLatitude < 0.000001
            ? 180.0
            : min(180.0, $radiusKm / (111.045 * $cosLatitude));

        if ($longitudeDelta >= 180.0) {
            return;
        }

        $west = $longitude - $longitudeDelta;
        $east = $longitude + $longitudeDelta;

        if ($west < -180.0) {
            $wrappedWest = $west + 360.0;
            $query->where(function ($longitudeQuery) use ($wrappedWest, $east): void {
                $longitudeQuery->whereBetween('longitude', [$wrappedWest, 180.0])
                    ->orWhereBetween('longitude', [-180.0, $east]);
            });
            return;
        }

        if ($east > 180.0) {
            $wrappedEast = $east - 360.0;
            $query->where(function ($longitudeQuery) use ($west, $wrappedEast): void {
                $longitudeQuery->whereBetween('longitude', [$west, 180.0])
                    ->orWhereBetween('longitude', [-180.0, $wrappedEast]);
            });
            return;
        }

        $query->whereBetween('longitude', [$west, $east]);
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $a = sin($latDelta / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($lonDelta / 2) ** 2;
        $a = min(1.0, max(0.0, $a));

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

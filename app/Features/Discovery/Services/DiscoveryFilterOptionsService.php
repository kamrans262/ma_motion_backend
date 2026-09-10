<?php

namespace App\Features\Discovery\Services;

use App\Features\Shows\Enums\ShowStatus;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;

final class DiscoveryFilterOptionsService
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        $types = ArtworkType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(static fn (ArtworkType $type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])->values()->all();

        $styles = ArtworkStyle::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(static fn (ArtworkStyle $style): array => [
                'id' => $style->id,
                'name' => $style->name,
                'slug' => $style->slug,
            ])->values()->all();

        $statuses = collect(ShowStatus::cases())
            ->map(static fn (ShowStatus $status): array => [
                'value' => $status->value,
                'label' => ShowStatus::labels()[$status->value],
            ])->values()->all();

        return [
            'types' => $types,
            'styles' => $styles,
            'show_statuses' => $statuses,
            'location' => [
                'autocomplete_path' => '/api/v1/discovery/locations',
                'supports' => ['location_id', 'city', 'postal_code', 'country_code', 'latitude', 'longitude', 'radius_km'],
                'radius_km' => ['min' => 1, 'max' => 500],
                'radius_requires' => ['latitude', 'longitude', 'radius_km'],
            ],
            'aliases' => [
                'type_id' => 'type_ids',
                'style_id' => 'style_ids',
                'show_status' => 'show_statuses',
            ],
        ];
    }
}

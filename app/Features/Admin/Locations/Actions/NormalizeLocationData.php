<?php

namespace App\Features\Admin\Locations\Actions;

final class NormalizeLocationData
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): array
    {
        return [
            'city' => trim($data['city']),
            'region' => $this->nullableTrim($data['region'] ?? null),
            'postal_code' => $this->nullableTrim($data['postal_code'] ?? null),
            'country_code' => strtoupper(trim($data['country_code'])),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_active' => (bool) $data['is_active'],
            'sort_order' => (int) $data['sort_order'],
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}

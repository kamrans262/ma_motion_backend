<?php

namespace App\Features\Shows\Support;

final class ShowData
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        foreach (['name', 'description', 'location_text'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null) {
                continue;
            }

            $value = trim((string) $data[$field]);
            $data[$field] = $value === '' && $field !== 'name' ? null : $value;
        }

        if (array_key_exists('location_id', $data) && ($data['location_id'] === '' || $data['location_id'] === null)) {
            $data['location_id'] = null;
        }

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        return $data;
    }
}

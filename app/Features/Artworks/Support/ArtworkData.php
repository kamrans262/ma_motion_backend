<?php

namespace App\Features\Artworks\Support;

final class ArtworkData
{
    /**
     * Normalize the validated Maker/Admin payload without inventing fields.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        foreach (['title', 'description', 'location_text'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($data[$field] === null) {
                continue;
            }

            $value = trim((string) $data[$field]);
            $data[$field] = $value === '' && $field !== 'title' ? null : $value;
        }

        foreach (['artwork_type_id', 'artwork_style_id', 'location_id'] as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        return $data;
    }
}

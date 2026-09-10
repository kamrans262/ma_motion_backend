<?php

namespace App\Features\Admin\Audit\Services;

use Illuminate\Http\Request;

final class AuditRequestSummaryService
{
    /**
     * Deliberately records field names and a tiny allow-list of operational values.
     * Names, emails, passwords, legal copy, bios, descriptions, tokens and uploads
     * are not persisted in the audit payload.
     *
     * @return array<string, mixed>
     */
    public function summarize(Request $request): array
    {
        $input = $request->except(['_token', '_method']);
        $fields = array_keys($input);
        sort($fields);

        $safeKeys = [
            'status',
            'role',
            'is_visible',
            'is_published',
            'moderation_status',
            'maker_id',
            'location_id',
            'artwork_type_id',
            'artwork_style_id',
            'sort_order',
        ];

        $safeValues = [];
        foreach ($safeKeys as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];
            if (is_scalar($value) || $value === null) {
                $safeValues[$key] = $value;
            }
        }

        $fileFields = array_keys($request->allFiles());
        sort($fileFields);

        return array_filter([
            'fields' => $fields,
            'safe_values' => $safeValues,
            'file_fields' => $fileFields,
        ], static fn (array $value): bool => $value !== []);
    }
}

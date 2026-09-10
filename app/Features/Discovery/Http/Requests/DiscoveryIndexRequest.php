<?php

namespace App\Features\Discovery\Http\Requests;

use App\Features\Shows\Enums\ShowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DiscoveryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach ([
            ['type_ids', 'type_id'],
            ['style_ids', 'style_id'],
            ['show_statuses', 'show_status'],
        ] as [$plural, $singular]) {
            $value = $this->input($plural, $this->input($singular));
            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value) && str_contains($value, ',')) {
                $value = array_values(array_filter(
                    array_map('trim', explode(',', $value)),
                    static fn (string $item): bool => $item !== '',
                ));
            } elseif (! is_array($value)) {
                $value = [$value];
            }

            $merge[$plural] = array_values($value);
        }

        foreach (['search', 'city', 'postal_code'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $value = trim((string) $this->input($key));
                $merge[$key] = $value === '' ? null : $value;
            }
        }

        if ($this->has('country_code') && is_string($this->input('country_code'))) {
            $value = strtoupper(trim((string) $this->input('country_code')));
            $merge['country_code'] = $value === '' ? null : $value;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $activeType = Rule::exists('artwork_types', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );
        $activeStyle = Rule::exists('artwork_styles', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );
        $activeLocation = Rule::exists('locations', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );

        return [
            'search' => ['nullable', 'string', 'max:120'],
            'type_ids' => ['nullable', 'array', 'max:20'],
            'type_ids.*' => ['integer', 'distinct', $activeType],
            'style_ids' => ['nullable', 'array', 'max:20'],
            'style_ids.*' => ['integer', 'distinct', $activeStyle],
            'show_statuses' => ['nullable', 'array', 'max:3'],
            'show_statuses.*' => ['string', 'distinct', Rule::in(ShowStatus::values())],
            'location_id' => ['nullable', 'integer', $activeLocation],
            'city' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude,radius_km'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude,radius_km'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:500', 'required_with:latitude,longitude'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

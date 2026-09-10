<?php

namespace App\Features\Discovery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LocationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('search') && is_string($this->input('search'))) {
            $value = trim((string) $this->input('search'));
            $merge['search'] = $value === '' ? null : $value;
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
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

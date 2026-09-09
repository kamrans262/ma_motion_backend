<?php

namespace App\Features\Admin\Locations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LocationIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->filled('country_code')) {
            $this->merge(['country_code' => strtoupper(trim((string) $this->input('country_code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'country_code' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

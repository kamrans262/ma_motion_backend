<?php

namespace App\Features\Admin\Locations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('country_code')) {
            $this->merge(['country_code' => strtoupper(trim((string) $this->input('country_code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'city' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

}

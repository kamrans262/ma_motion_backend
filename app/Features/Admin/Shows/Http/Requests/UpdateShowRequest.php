<?php

namespace App\Features\Admin\Shows\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateShowRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('artwork_ids')) {
            $this->merge(['artwork_ids' => []]);
        }
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'location_text' => ['nullable', 'string', 'max:180'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'artwork_ids' => ['array', 'max:100'],
            'artwork_ids.*' => ['integer', 'distinct', Rule::exists('artworks', 'id')->whereNull('deleted_at')],
        ];
    }
}

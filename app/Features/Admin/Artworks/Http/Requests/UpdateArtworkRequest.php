<?php

namespace App\Features\Admin\Artworks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateArtworkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'artwork_type_id' => ['nullable', 'integer', Rule::exists('artwork_types', 'id')->whereNull('deleted_at')],
            'artwork_style_id' => ['nullable', 'integer', Rule::exists('artwork_styles', 'id')->whereNull('deleted_at')],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'location_text' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}

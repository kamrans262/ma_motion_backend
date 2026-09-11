<?php

namespace App\Features\Makers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMyMakerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'location_text' => ['sometimes', 'nullable', 'string', 'max:180'],
        ];
    }
}

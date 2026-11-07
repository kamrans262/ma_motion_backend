<?php

namespace App\Features\Makers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignMakerProfileArtworkSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'artwork_id' => ['required', 'integer', 'exists:artworks,id'],
        ];
    }
}

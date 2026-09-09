<?php

namespace App\Features\Artworks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddArtworkMediaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'media' => ['required', 'array', 'min:1', 'max:10'],
            'media.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:10240',
                'dimensions:max_width=12000,max_height=12000',
            ],
        ];
    }
}

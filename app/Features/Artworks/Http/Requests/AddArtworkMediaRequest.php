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
                'mimes:jpg,jpeg,png,webp,mp4,mov,m4v,webm',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-m4v,video/webm',
                'max:25600',
            ],
        ];
    }
}

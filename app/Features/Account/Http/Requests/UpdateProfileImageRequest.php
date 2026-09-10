<?php

namespace App\Features\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'dimensions:max_width=8000,max_height=8000',
            ],
        ];
    }
}

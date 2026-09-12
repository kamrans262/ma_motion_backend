<?php

namespace App\Features\Makers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SaveMakerProfileContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'caption' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'media' => [
                'sometimes',
                'file',
                'max:25600',
                'mimes:jpg,jpeg,png,webp,mp4,mov,m4v,webm',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-m4v,video/webm',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('caption') && is_string($this->input('caption'))) {
            $caption = trim((string) $this->input('caption'));
            $this->merge(['caption' => $caption === '' ? null : $caption]);
        }
    }
}

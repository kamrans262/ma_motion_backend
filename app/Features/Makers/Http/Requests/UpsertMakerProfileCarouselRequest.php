<?php

namespace App\Features\Makers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpsertMakerProfileCarouselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'media' => [
                'sometimes',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime',
                'max:25600',
            ],
            'caption' => ['sometimes', 'nullable', 'string', 'max:280'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('caption') && is_string($this->input('caption'))) {
            $caption = trim((string) $this->input('caption'));
            $this->merge([
                'caption' => $caption === '' ? null : $caption,
            ]);
        }
    }
}

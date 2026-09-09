<?php

namespace App\Features\Admin\Artworks\Http\Requests;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ModerateArtworkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'moderation_status' => ['required', Rule::in(ArtworkModerationStatus::values())],
            'rejection_reason' => [
                Rule::requiredIf($this->input('moderation_status') === ArtworkModerationStatus::Rejected->value),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}

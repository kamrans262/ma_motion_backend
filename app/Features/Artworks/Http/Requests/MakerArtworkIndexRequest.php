<?php

namespace App\Features\Artworks\Http\Requests;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MakerArtworkIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:200'],
            'moderation_status' => ['nullable', Rule::in(ArtworkModerationStatus::values())],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}

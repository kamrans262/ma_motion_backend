<?php

namespace App\Features\Admin\Artworks\Http\Requests;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ArtworkIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:200'],
            'maker_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'artwork_type_id' => ['nullable', 'integer', Rule::exists('artwork_types', 'id')->whereNull('deleted_at')],
            'artwork_style_id' => ['nullable', 'integer', Rule::exists('artwork_styles', 'id')->whereNull('deleted_at')],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'moderation_status' => ['nullable', Rule::in(ArtworkModerationStatus::values())],
            'visibility' => ['nullable', Rule::in(['visible', 'hidden'])],
        ];
    }
}

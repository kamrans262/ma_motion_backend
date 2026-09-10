<?php

namespace App\Features\Admin\FeaturedMaker\Http\Requests;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFeaturedMakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'maker_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(static fn ($query) => $query
                    ->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value)),
            ],
            'featured_artwork_id' => ['nullable', 'integer', 'exists:artworks,id'],
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'headline' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

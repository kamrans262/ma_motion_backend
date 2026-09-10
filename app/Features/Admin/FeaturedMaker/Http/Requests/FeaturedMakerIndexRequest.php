<?php

namespace App\Features\Admin\FeaturedMaker\Http\Requests;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FeaturedMakerIndexRequest extends FormRequest
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
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(static fn ($query) => $query
                    ->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value)),
            ],
        ];
    }
}

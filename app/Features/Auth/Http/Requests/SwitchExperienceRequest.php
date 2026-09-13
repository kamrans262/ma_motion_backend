<?php

namespace App\Features\Auth\Http\Requests;

use App\Features\Auth\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SwitchExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'experience' => [
                'required',
                'string',
                Rule::in([UserRole::Maker->value, UserRole::Appreciator->value]),
            ],
        ];
    }
}

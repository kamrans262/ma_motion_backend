<?php

namespace App\Features\Auth\Http\Requests;

use App\Features\Auth\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string', 'max:12000'],
            'role' => ['nullable', 'string', Rule::in([UserRole::Maker->value, UserRole::Appreciator->value])],
            'name' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}

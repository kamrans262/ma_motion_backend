<?php

namespace App\Features\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppreciatorOnboardingRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'location_text' => trim((string) $this->input('location_text')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $activeLocation = Rule::exists('locations', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'location_text' => ['required', 'string', 'max:180'],
            'location_id' => ['nullable', 'integer', $activeLocation],
            'otp_challenge_id' => ['nullable', 'uuid'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}

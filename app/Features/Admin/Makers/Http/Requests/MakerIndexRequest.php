<?php

namespace App\Features\Admin\Makers\Http\Requests;

use App\Features\Auth\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MakerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

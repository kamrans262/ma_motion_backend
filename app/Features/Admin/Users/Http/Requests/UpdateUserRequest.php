<?php

namespace App\Features\Admin\Users\Http\Requests;

use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($user instanceof User ? $user->id : $user),
            ],
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }
}

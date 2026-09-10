<?php

namespace App\Features\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:255'],
            'confirmation' => ['required', 'string', Rule::in(['DELETE'])],
        ];
    }
}

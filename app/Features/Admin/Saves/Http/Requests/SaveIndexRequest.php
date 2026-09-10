<?php

namespace App\Features\Admin\Saves\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:180'],
            'maker_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'appreciator_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

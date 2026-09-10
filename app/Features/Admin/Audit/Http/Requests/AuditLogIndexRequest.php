<?php

namespace App\Features\Admin\Audit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AuditLogIndexRequest extends FormRequest
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
            'admin_id' => ['nullable', 'integer', 'exists:users,id'],
            'method' => ['nullable', Rule::in(['POST', 'PUT', 'PATCH', 'DELETE'])],
            'status' => ['nullable', 'integer', 'min:100', 'max:599'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Features\Admin\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AnalyticsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(['7', '30', '90'])],
        ];
    }

    public function days(): int
    {
        return (int) ($this->validated('period') ?? 30);
    }
}

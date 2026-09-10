<?php

namespace App\Features\Admin\Shows\Http\Requests;

use App\Features\Shows\Enums\ShowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ShowIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:200'],
            'maker_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'status' => ['nullable', Rule::in(ShowStatus::values())],
            'visibility' => ['nullable', Rule::in(['visible', 'hidden'])],
        ];
    }
}

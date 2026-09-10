<?php

namespace App\Features\Shows\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

final class UpdateMyShowRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'location_id' => ['sometimes', 'nullable', 'integer', $this->activeLocationExists()],
            'location_text' => ['sometimes', 'nullable', 'string', 'max:180'],
            'start_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'artwork_ids' => ['sometimes', 'array', 'max:100'],
            'artwork_ids.*' => ['integer', 'distinct', Rule::exists('artworks', 'id')->whereNull('deleted_at')],
        ];
    }

    private function activeLocationExists(): Exists
    {
        return Rule::exists('locations', 'id')->where(
            static fn (Builder $query): Builder => $query->whereNull('deleted_at')->where('is_active', true),
        );
    }
}

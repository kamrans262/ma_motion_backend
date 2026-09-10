<?php

namespace App\Features\Admin\Shows\Http\Requests;

use App\Features\Auth\Enums\UserRole;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreShowRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'maker_id' => ['required', 'integer', Rule::exists('users', 'id')->where(
                static fn (Builder $query): Builder => $query->where('role', UserRole::Maker->value),
            )],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'location_text' => ['nullable', 'string', 'max:180'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}

<?php

namespace App\Features\Artworks\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

final class UpdateMyArtworkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'artwork_type_id' => ['sometimes', 'nullable', 'integer', $this->activeExists('artwork_types')],
            'artwork_style_id' => ['sometimes', 'nullable', 'integer', $this->activeExists('artwork_styles')],
            'location_id' => ['sometimes', 'nullable', 'integer', $this->activeExists('locations')],
            'location_text' => ['sometimes', 'nullable', 'string', 'max:180'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    private function activeExists(string $table): Exists
    {
        return Rule::exists($table, 'id')->where(
            static fn (Builder $query): Builder => $query->whereNull('deleted_at')->where('is_active', true),
        );
    }
}

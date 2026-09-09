<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Requests;

use App\Features\Admin\Taxonomy\Support\TaxonomyData;
use App\Features\Taxonomy\Models\ArtworkType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $item = $this->route('type');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('artwork_types', 'name')->ignore($item instanceof ArtworkType ? $item->id : $item)],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $item = $this->route('type');
            $name = trim((string) $this->input('name'));
            $ignoreId = $item instanceof ArtworkType ? $item->id : (is_numeric($item) ? (int) $item : null);
            if ($name !== '' && TaxonomyData::slugExists(ArtworkType::class, $name, $ignoreId)) {
                $validator->errors()->add('name', 'A type with the same URL-friendly name already exists.');
            }
        }];
    }
}

<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Requests;

use App\Features\Admin\Taxonomy\Support\TaxonomyData;
use App\Features\Taxonomy\Models\ArtworkStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateStyleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $item = $this->route('style');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('artwork_styles', 'name')->ignore($item instanceof ArtworkStyle ? $item->id : $item)],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $item = $this->route('style');
            $name = trim((string) $this->input('name'));
            $ignoreId = $item instanceof ArtworkStyle ? $item->id : (is_numeric($item) ? (int) $item : null);
            if ($name !== '' && TaxonomyData::slugExists(ArtworkStyle::class, $name, $ignoreId)) {
                $validator->errors()->add('name', 'A style with the same URL-friendly name already exists.');
            }
        }];
    }
}

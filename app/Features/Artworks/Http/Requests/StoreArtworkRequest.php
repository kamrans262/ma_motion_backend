<?php

namespace App\Features\Artworks\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

final class StoreArtworkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'artwork_type_id' => ['nullable', 'integer', $this->activeExists('artwork_types')],
            'artwork_style_id' => ['nullable', 'integer', $this->activeExists('artwork_styles')],
            'location_id' => ['nullable', 'integer', $this->activeExists('locations')],
            'location_text' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'media' => ['required', 'array', 'min:1', 'max:10'],
            'media.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,m4v,webm',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-m4v,video/webm',
                'max:25600',
            ],
        ];
    }

    private function activeExists(string $table): Exists
    {
        return Rule::exists($table, 'id')->where(
            static fn (Builder $query): Builder => $query->whereNull('deleted_at')->where('is_active', true),
        );
    }
}

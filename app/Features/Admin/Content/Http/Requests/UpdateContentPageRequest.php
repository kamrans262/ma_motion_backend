<?php

namespace App\Features\Admin\Content\Http\Requests;

use App\Features\Content\Models\AppContentPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class UpdateContentPageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    public function rules(): array
    {
        /** @var AppContentPage|null $page */
        $page = $this->route('contentPage');

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('app_content_pages', 'slug')->ignore($page?->id)],
            'body' => ['required', 'string', 'max:100000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}

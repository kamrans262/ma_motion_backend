<?php

namespace App\Features\Makers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMyMakerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('website_url') && is_string($this->input('website_url'))) {
            $website = trim((string) $this->input('website_url'));

            if ($website !== '' && ! preg_match('#^https?://#i', $website)) {
                $website = 'https://'.$website;
            }

            $merge['website_url'] = $website === '' ? null : $website;
        }

        foreach (['name', 'bio', 'location_text', 'contact_email'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $value = trim((string) $this->input($key));
                $merge[$key] = $value === '' ? null : $value;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $activeType = Rule::exists('artwork_types', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );
        $activeStyle = Rule::exists('artwork_styles', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );
        $activeLocation = Rule::exists('locations', 'id')->where(
            static fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'),
        );

        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'location_text' => ['sometimes', 'nullable', 'string', 'max:180'],
            'location_id' => ['sometimes', 'nullable', 'integer', $activeLocation],
            'website_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'contact_email' => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
            'show_website_on_info_page' => ['sometimes', 'boolean'],
            'show_email_on_info_page' => ['sometimes', 'boolean'],
            'show_shows_on_info_page' => ['sometimes', 'boolean'],
            'type_ids' => ['sometimes', 'array', 'min:1', 'max:20'],
            'type_ids.*' => ['integer', 'distinct', $activeType],
            'style_ids' => ['sometimes', 'array', 'min:1', 'max:20'],
            'style_ids.*' => ['integer', 'distinct', $activeStyle],
            'complete_onboarding' => ['sometimes', 'boolean'],
        ];
    }
}

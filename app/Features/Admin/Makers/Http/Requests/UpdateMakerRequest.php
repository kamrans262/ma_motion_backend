<?php

namespace App\Features\Admin\Makers\Http\Requests;

use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maker = $this->route('maker');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($maker instanceof User ? $maker->id : $maker)],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'bio' => ['nullable', 'string', 'max:5000'],
            'location_text' => ['nullable', 'string', 'max:180'],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'website_url' => ['nullable', 'url:http,https', 'max:2048'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'show_website_on_info_page' => ['sometimes', 'boolean'],
            'show_email_on_info_page' => ['sometimes', 'boolean'],
            'show_shows_on_info_page' => ['sometimes', 'boolean'],
        ];
    }
}

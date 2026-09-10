<?php

namespace App\Features\Notifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class NotificationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('unread_only')) {
            return;
        }

        $value = $this->input('unread_only');
        if (in_array($value, [true, 1, '1', 'true'], true)) {
            $this->merge(['unread_only' => true]);
        } elseif (in_array($value, [false, 0, '0', 'false'], true)) {
            $this->merge(['unread_only' => false]);
        }
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:180'],
            'unread_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

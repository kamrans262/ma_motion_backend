<?php

namespace App\Features\Admin\Notifications\Http\Requests;

use App\Features\Notifications\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class NotificationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', Rule::in(['read', 'unread'])],
            'type' => ['nullable', Rule::enum(NotificationType::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

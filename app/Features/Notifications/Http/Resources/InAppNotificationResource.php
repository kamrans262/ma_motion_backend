<?php

namespace App\Features\Notifications\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InAppNotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data ?? [],
            'is_read' => $this->isRead(),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'context' => [
                'maker' => $this->maker ? [
                    'id' => $this->maker->id,
                    'name' => $this->maker->name,
                ] : null,
                'show' => $this->show ? [
                    'id' => $this->show->id,
                    'name' => $this->show->name,
                    'status' => $this->show->status()->value,
                    'start_date' => $this->show->start_date->toDateString(),
                    'end_date' => $this->show->end_date->toDateString(),
                ] : null,
            ],
        ];
    }
}

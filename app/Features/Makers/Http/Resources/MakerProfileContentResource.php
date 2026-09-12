<?php

namespace App\Features\Makers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class MakerProfileContentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot' => (int) $this->slot,
            'kind' => $this->kind,
            'url' => Storage::disk('public')->url($this->path),
            'mime_type' => $this->mime_type,
            'caption' => $this->caption,
        ];
    }
}

<?php

namespace App\Features\Shows\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShowArtworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'slug' => $this->type->slug,
            ] : null,
            'style' => $this->style ? [
                'id' => $this->style->id,
                'name' => $this->style->name,
                'slug' => $this->style->slug,
            ] : null,
            'primary_image_url' => $this->primaryMedia?->url(),
        ];
    }
}

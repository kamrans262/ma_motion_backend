<?php

namespace App\Features\Artworks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ArtworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'maker' => [
                'id' => $this->maker?->id,
                'name' => $this->maker?->name,
            ],
            'title' => $this->title,
            'description' => $this->description,
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
            'location' => $this->location ? [
                'id' => $this->location->id,
                'label' => $this->location->displayLabel(),
                'city' => $this->location->city,
                'region' => $this->location->region,
                'postal_code' => $this->location->postal_code,
                'country_code' => $this->location->country_code,
                'latitude' => $this->location->latitude,
                'longitude' => $this->location->longitude,
            ] : null,
            'location_text' => $this->location_text,
            'moderation_status' => $this->moderation_status->value,
            'is_visible' => $this->is_visible,
            'rejection_reason' => $this->rejection_reason,
            'sort_order' => $this->sort_order,
            'media' => ArtworkMediaResource::collection($this->media)->resolve($request),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

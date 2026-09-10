<?php

namespace App\Features\Shows\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'maker' => [
                'id' => $this->maker?->id,
                'name' => $this->maker?->name,
            ],
            'name' => $this->name,
            'description' => $this->description,
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
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->resource->status()->value,
            'is_visible' => $this->is_visible,
            'sort_order' => $this->sort_order,
            'artworks' => $this->relationLoaded('artworks')
                ? ShowArtworkResource::collection($this->artworks)->resolve($request)
                : [],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

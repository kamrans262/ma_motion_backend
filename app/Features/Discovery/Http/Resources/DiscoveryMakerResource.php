<?php

namespace App\Features\Discovery\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class DiscoveryMakerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $profile = $this->makerProfile;
        $location = $profile?->location;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $profile?->bio,
            'profile_image_url' => $profile?->profile_image_path
                ? Storage::disk('public')->url($profile->profile_image_path)
                : null,
            'location' => $location ? [
                'id' => $location->id,
                'label' => $location->displayLabel(),
                'city' => $location->city,
                'region' => $location->region,
                'postal_code' => $location->postal_code,
                'country_code' => $location->country_code,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ] : null,
            'location_text' => $profile?->location_text,
            'distance_km' => $this->getAttribute('distance_km'),
            'statistics' => [
                'saved_count' => (int) ($this->saves_count ?? 0),
                'artwork_count' => (int) ($this->public_artworks_count ?? 0),
                'current_show_count' => (int) ($this->current_shows_count ?? 0),
                'upcoming_show_count' => (int) ($this->upcoming_shows_count ?? 0),
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

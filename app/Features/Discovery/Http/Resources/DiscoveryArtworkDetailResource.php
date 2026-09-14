<?php

namespace App\Features\Discovery\Http\Resources;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Http\Resources\ArtworkMediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class DiscoveryArtworkDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $makerProfile = $this->maker?->makerProfile;
        $effectiveLocation = $this->location ?: $makerProfile?->location;
        $primaryMedia = $this->primaryMedia;
        $artDisplayArtworks = collect($makerProfile?->artworkSlots ?? [])
            ->filter(function ($placement): bool {
                $artwork = $placement->artwork;

                return $placement->slot >= 2
                    && $placement->slot <= 4
                    && $artwork !== null
                    && (int) $artwork->maker_id === (int) $this->maker_id
                    && $artwork->moderation_status === ArtworkModerationStatus::Approved
                    && $artwork->is_visible;
            })
            ->map(function ($placement) use ($request): array {
                $artwork = $placement->artwork;
                $primaryMedia = $artwork->primaryMedia;

                return [
                    'slot' => (int) $placement->slot,
                    'artwork' => [
                        'id' => $artwork->id,
                        'title' => $artwork->title,
                        'description' => $artwork->description,
                        'primary_media' => $primaryMedia
                            ? ArtworkMediaResource::make($primaryMedia)->resolve($request)
                            : null,
                        'created_at' => $artwork->created_at?->toISOString(),
                    ],
                ];
            })
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'media' => ArtworkMediaResource::collection($this->media)->resolve($request),
            'primary_media' => $primaryMedia
                ? ArtworkMediaResource::make($primaryMedia)->resolve($request)
                : null,
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
            'location' => $effectiveLocation ? [
                'id' => $effectiveLocation->id,
                'label' => $effectiveLocation->displayLabel(),
                'city' => $effectiveLocation->city,
                'region' => $effectiveLocation->region,
                'postal_code' => $effectiveLocation->postal_code,
                'country_code' => $effectiveLocation->country_code,
                'latitude' => $effectiveLocation->latitude,
                'longitude' => $effectiveLocation->longitude,
            ] : null,
            'location_text' => $this->location_text ?: $makerProfile?->location_text,
            'art_display_artworks' => $artDisplayArtworks,
            'maker' => $this->maker ? [
                'id' => $this->maker->id,
                'name' => $this->maker->name,
                'bio' => $makerProfile?->bio,
                'location' => $makerProfile?->location_text,
                'profile_image_url' => $makerProfile?->profile_image_path
                    ? Storage::disk('public')->url($makerProfile->profile_image_path)
                    : null,
                'website_url' => ($makerProfile?->show_website_on_info_page ?? true)
                    ? $makerProfile?->website_url
                    : null,
                'contact_email' => ($makerProfile?->show_email_on_info_page ?? false)
                    ? $makerProfile?->contact_email
                    : null,
                'show_shows_on_info_page' => $makerProfile?->show_shows_on_info_page ?? true,
                'saved_count' => (int) ($this->maker->saves_count ?? 0),
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

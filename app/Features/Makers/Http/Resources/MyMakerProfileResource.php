<?php

namespace App\Features\Makers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class MyMakerProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $profile = $this->makerProfile;
        $location = $profile?->location;
        $types = $profile?->types ?? collect();
        $styles = $profile?->styles ?? collect();
        $carousel = $profile?->carouselMedia ?? collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $profile?->bio,
            'location' => $profile?->location_text,
            'location_text' => $profile?->location_text,
            'managed_location' => $location ? [
                'id' => $location->id,
                'label' => $location->displayLabel(),
                'city' => $location->city,
                'region' => $location->region,
                'postal_code' => $location->postal_code,
                'country_code' => $location->country_code,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ] : null,
            'website_url' => $profile?->website_url,
            'contact_email' => $profile?->contact_email,
            'profile_image_url' => $profile?->profile_image_path
                ? Storage::disk('public')->url($profile->profile_image_path)
                : null,
            'show_website_on_info_page' => $profile?->show_website_on_info_page ?? true,
            'show_email_on_info_page' => $profile?->show_email_on_info_page ?? false,
            'show_shows_on_info_page' => $profile?->show_shows_on_info_page ?? true,
            'types' => $types->map(static fn ($type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])->values()->all(),
            'styles' => $styles->map(static fn ($style): array => [
                'id' => $style->id,
                'name' => $style->name,
                'slug' => $style->slug,
            ])->values()->all(),
            'carousel_content' => MakerProfileCarouselMediaResource::collection($carousel)->resolve($request),
            'onboarding_completed_at' => $profile?->onboarding_completed_at?->toISOString(),
            'onboarding_completed' => $profile?->onboarding_completed_at !== null,
        ];
    }
}

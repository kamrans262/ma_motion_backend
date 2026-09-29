<?php

namespace App\Features\Makers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class MakerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $profile = $this->makerProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $profile?->bio,
            'location' => $profile?->location_text,
            'current_upcoming_shows' => ($profile?->show_shows_on_info_page ?? true)
                ? ($profile?->current_upcoming_shows ?? [])
                : [],
            'profile_image_url' => $profile?->profile_image_path
                ? Storage::disk('public')->url($profile->profile_image_path)
                : null,
            'saved_count' => (int) ($this->saves_count ?? 0),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

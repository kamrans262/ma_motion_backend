<?php

namespace App\Features\Appreciators\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MyAppreciatorProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $profile = $this->appreciatorProfile;

        return [
            'name' => $this->name,
            'email' => $this->email,
            'location_text' => $profile?->location_text,
            'location_id' => $profile?->location_id,
            'location' => $profile?->location ? [
                'id' => $profile->location->id,
                'label' => $profile->location->displayLabel(),
            ] : null,
            'onboarding_completed' => $profile?->onboarding_completed_at !== null,
        ];
    }
}

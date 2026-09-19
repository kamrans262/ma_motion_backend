<?php

namespace App\Features\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['makerProfile', 'appreciatorProfile']);

        $makerProfile = $this->makerProfile;
        $appreciatorProfile = $this->appreciatorProfile;
        // /me is the authenticated account's source of truth for both experiences.
        // Expose only this user's shared profile fields, even while the other
        // experience is active, so onboarding can prefill without role changes.


        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'active_experience' => $this->role?->value,
            'maker_registered' => $makerProfile !== null,
            'maker_onboarding_completed' => $makerProfile?->onboarding_completed_at !== null,
            'appreciator_registered' => $appreciatorProfile !== null,
            'appreciator_onboarding_completed' => $appreciatorProfile?->onboarding_completed_at !== null,
            'status' => $this->status?->value,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'maker_profile' => $makerProfile === null ? null : [
                'bio' => $makerProfile->bio,
                'location' => $makerProfile->location_text,
                'location_text' => $makerProfile->location_text,
                'profile_image_url' => $makerProfile->profile_image_path
                    ? Storage::disk('public')->url($makerProfile->profile_image_path)
                    : null,
            ],
            'appreciator_profile' => $appreciatorProfile === null ? null : [
                'location_text' => $appreciatorProfile->location_text,
                'location_id' => $appreciatorProfile->location_id,
            ],
        ];
    }
}

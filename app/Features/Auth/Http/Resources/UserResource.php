<?php

namespace App\Features\Auth\Http\Resources;

use App\Features\Auth\Enums\UserRole;
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
        $activeMakerProfile = $this->role === UserRole::Maker ? $makerProfile : null;

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
            'maker_profile' => $activeMakerProfile === null ? null : [
                'bio' => $activeMakerProfile->bio,
                'location' => $activeMakerProfile->location_text,
                'profile_image_url' => $activeMakerProfile->profile_image_path
                    ? Storage::disk('public')->url($activeMakerProfile->profile_image_path)
                    : null,
            ],
        ];
    }
}

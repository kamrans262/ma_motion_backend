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
        $makerProfile = $this->role === UserRole::Maker ? $this->makerProfile : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'status' => $this->status?->value,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'maker_profile' => $makerProfile === null ? null : [
                'bio' => $makerProfile->bio,
                'location' => $makerProfile->location_text,
                'profile_image_url' => $makerProfile->profile_image_path
                    ? Storage::disk('public')->url($makerProfile->profile_image_path)
                    : null,
            ],
        ];
    }
}

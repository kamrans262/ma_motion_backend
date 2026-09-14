<?php

namespace App\Features\Makers\Http\Resources;

use App\Features\Artworks\Http\Resources\ArtworkResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MakerProfileArtworkSlotResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'slot' => (int) $this->slot,
            'artwork' => ArtworkResource::make($this->artwork)->resolve($request),
        ];
    }
}

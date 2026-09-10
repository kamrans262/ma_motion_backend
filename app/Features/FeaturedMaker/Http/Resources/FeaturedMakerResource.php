<?php

namespace App\Features\FeaturedMaker\Http\Resources;

use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Makers\Http\Resources\MakerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeaturedMakerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'maker' => MakerResource::make($this->maker)->resolve($request),
            'content' => [
                'eyebrow' => $this->eyebrow ?: 'Featured Maker',
                'headline' => $this->headline ?: $this->maker?->name,
                'description' => $this->description,
            ],
            'spotlight_artwork' => $this->featuredArtwork
                ? ArtworkResource::make($this->featuredArtwork)->resolve($request)
                : null,
            'artworks' => ArtworkResource::collection($this->maker?->artworks ?? collect())->resolve($request),
        ];
    }
}

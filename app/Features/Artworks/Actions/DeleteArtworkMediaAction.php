<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Models\ArtworkMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class DeleteArtworkMediaAction
{
    public function execute(Artwork $artwork, ArtworkMedia $media, bool $resetModeration = true): Artwork
    {
        abort_unless($media->artwork_id === $artwork->id, 404);

        if ($artwork->media()->count() <= 1) {
            throw ValidationException::withMessages([
                'media' => 'An artwork must keep at least one image.',
            ]);
        }

        $disk = $media->disk;
        $path = $media->path;
        $wasPrimary = $media->is_primary;

        DB::transaction(function () use ($artwork, $media, $wasPrimary, $resetModeration): void {
            $media->delete();

            if ($wasPrimary) {
                $next = $artwork->media()->first();
                $next?->forceFill(['is_primary' => true])->save();
            }

            if ($resetModeration) {
                $artwork->forceFill([
                    'moderation_status' => ArtworkModerationStatus::Pending,
                    'rejection_reason' => null,
                ])->save();
            }
        });

        Storage::disk($disk)->delete($path);

        return $artwork->fresh(['maker', 'type', 'style', 'location', 'media']);
    }
}

<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Services\ArtworkMediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class AddArtworkMediaAction
{
    public function __construct(private readonly ArtworkMediaStorageService $mediaStorage) {}

    /** @param array<int, UploadedFile> $files */
    public function execute(Artwork $artwork, array $files): Artwork
    {
        DB::transaction(function () use ($artwork, $files): void {
            $this->mediaStorage->addImages($artwork, $files);
            $artwork->forceFill([
                'moderation_status' => ArtworkModerationStatus::Pending,
                'rejection_reason' => null,
            ])->save();
        });

        return $artwork->fresh(['maker', 'type', 'style', 'location', 'media']);
    }
}

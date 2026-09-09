<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Artworks\Services\ArtworkMediaStorageService;
use App\Features\Artworks\Support\ArtworkData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class CreateArtworkAction
{
    public function __construct(private readonly ArtworkMediaStorageService $mediaStorage) {}

    /**
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $files
     */
    public function execute(User $maker, array $data, array $files): Artwork
    {
        return DB::transaction(function () use ($maker, $data, $files): Artwork {
            $artwork = $maker->artworks()->create(ArtworkData::normalize($data) + [
                'moderation_status' => ArtworkModerationStatus::Pending->value,
                'is_visible' => true,
            ]);

            $this->mediaStorage->addImages($artwork, $files);

            return $artwork->load(['maker', 'type', 'style', 'location', 'media']);
        });
    }
}

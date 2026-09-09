<?php

namespace App\Features\Artworks\Services;

use App\Features\Artworks\Models\Artwork;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class ArtworkMediaStorageService
{
    public const MAX_IMAGES_PER_ARTWORK = 10;

    /**
     * @param array<int, UploadedFile> $files
     */
    public function addImages(Artwork $artwork, array $files): void
    {
        if ($files === []) {
            return;
        }

        $existingCount = $artwork->media()->count();
        if ($existingCount + count($files) > self::MAX_IMAGES_PER_ARTWORK) {
            throw ValidationException::withMessages([
                'media' => 'An artwork can contain a maximum of '.self::MAX_IMAGES_PER_ARTWORK.' images.',
            ]);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($artwork, $files, &$storedPaths, $existingCount): void {
                $nextSortOrder = (int) ($artwork->media()->max('sort_order') ?? -1) + 1;
                $hasPrimary = $artwork->media()->where('is_primary', true)->exists();

                foreach ($files as $file) {
                    $mime = (string) $file->getMimeType();
                    $extension = $this->extensionForMime($mime);
                    $dimensions = @getimagesize($file->getRealPath());

                    if ($dimensions === false) {
                        throw ValidationException::withMessages([
                            'media' => 'One of the uploaded files could not be verified as a valid image.',
                        ]);
                    }

                    $directory = 'artworks/'.$artwork->maker_id.'/'.$artwork->id;
                    $filename = (string) Str::uuid().'.'.$extension;
                    $path = $file->storeAs($directory, $filename, 'public');

                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException('The artwork image could not be stored.');
                    }

                    $storedPaths[] = $path;
                    $isPrimary = ! $hasPrimary;

                    $artwork->media()->create([
                        'kind' => 'image',
                        'disk' => 'public',
                        'path' => $path,
                        'mime_type' => $mime,
                        'size_bytes' => (int) $file->getSize(),
                        'width' => (int) $dimensions[0],
                        'height' => (int) $dimensions[1],
                        'alt_text' => null,
                        'sort_order' => $nextSortOrder++,
                        'is_primary' => $isPrimary,
                    ]);

                    if ($isPrimary) {
                        $hasPrimary = true;
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }
    }

    private function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'media' => 'Only JPG, PNG and WebP artwork images are supported.',
            ]),
        };
    }
}

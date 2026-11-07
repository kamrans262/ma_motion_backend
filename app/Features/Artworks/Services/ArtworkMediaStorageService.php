<?php

namespace App\Features\Artworks\Services;

use App\Features\Artworks\Models\Artwork;
use App\Support\Media\FiveSecondVideoGuard;
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

    public function __construct(private readonly FiveSecondVideoGuard $videoGuard) {}

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
                'media' => 'An artwork can contain a maximum of '.self::MAX_IMAGES_PER_ARTWORK.' media files.',
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
                    $isVideo = str_starts_with($mime, 'video/');
                    $dimensions = null;

                    if ($isVideo) {
                        $this->videoGuard->validate($file, 'media');
                    } else {
                        if ($file->getSize() > 10 * 1024 * 1024) {
                            throw ValidationException::withMessages([
                                'media' => 'Artwork images must be 10 MB or smaller.',
                            ]);
                        }

                        $dimensions = @getimagesize($file->getRealPath());
                        if ($dimensions === false || $dimensions[0] > 12000 || $dimensions[1] > 12000) {
                            throw ValidationException::withMessages([
                                'media' => 'Artwork image must be valid and at most 12000 × 12000 pixels.',
                            ]);
                        }
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
                        'kind' => $isVideo ? 'video' : 'image',
                        'disk' => 'public',
                        'path' => $path,
                        'mime_type' => $mime,
                        'size_bytes' => (int) $file->getSize(),
                        'width' => $dimensions === null ? null : (int) $dimensions[0],
                        'height' => $dimensions === null ? null : (int) $dimensions[1],
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
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-m4v' => 'm4v',
            'video/webm' => 'webm',
            default => throw ValidationException::withMessages([
                'media' => 'Only JPG, PNG, WebP images or MP4, MOV, M4V, WebM videos are supported.',
            ]),
        };
    }
}

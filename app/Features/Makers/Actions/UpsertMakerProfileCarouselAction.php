<?php

namespace App\Features\Makers\Actions;

use App\Features\Makers\Models\MakerProfileCarouselMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

final class UpsertMakerProfileCarouselAction
{
    /**
     * @param array{caption?:string|null} $data
     */
    public function execute(
        User $maker,
        int $slot,
        array $data,
        ?UploadedFile $media,
    ): MakerProfileCarouselMedia {
        $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);
        $existing = $profile->carouselMedia()->where('slot', $slot)->first();

        if ($existing === null && $media === null) {
            throw ValidationException::withMessages([
                'media' => ['Please choose an image or video for this carousel slot.'],
            ]);
        }

        $newPath = null;

        if ($media !== null) {
            $newPath = $media->store('maker-profile-carousel/'.$maker->id, 'public');

            if (! is_string($newPath) || $newPath === '') {
                throw ValidationException::withMessages([
                    'media' => ['The carousel media could not be stored.'],
                ]);
            }
        }

        $oldDisk = $existing?->disk;
        $oldPath = $existing?->path;

        try {
            $record = DB::transaction(function () use (
                $profile,
                $existing,
                $slot,
                $data,
                $media,
                $newPath,
            ): MakerProfileCarouselMedia {
                $record = $existing ?? new MakerProfileCarouselMedia([
                    'maker_profile_id' => $profile->id,
                    'slot' => $slot,
                ]);

                if ($media !== null && $newPath !== null) {
                    $mime = $media->getMimeType();

                    $record->fill([
                        'kind' => is_string($mime) && str_starts_with($mime, 'video/')
                            ? 'video'
                            : 'image',
                        'disk' => 'public',
                        'path' => $newPath,
                        'mime_type' => $mime,
                        'size_bytes' => $media->getSize(),
                    ]);
                }

                if (array_key_exists('caption', $data)) {
                    $record->caption = $data['caption'];
                }

                $record->save();

                return $record->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if (
            $newPath !== null &&
            $oldDisk !== null &&
            $oldPath !== null &&
            $oldPath !== $newPath
        ) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return $record;
    }
}

<?php

namespace App\Features\Makers\Actions;

use App\Features\Makers\Models\MakerProfileContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class SaveMakerProfileContentAction
{
    public function execute(User $maker, int $slot, ?UploadedFile $media, ?string $caption): MakerProfileContent
    {
        if ($slot !== 1) {
            throw ValidationException::withMessages([
                'slot' => ['Only Content 1 is Maker profile media. Content 2 through 4 are artworks.'],
            ]);
        }

        $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);
        $existing = $profile->contents()->where('slot', $slot)->first();

        if (! $existing && ! $media) {
            throw ValidationException::withMessages([
                'media' => ['Media is required when creating a content slot.'],
            ]);
        }

        $newPath = null;
        $newMimeType = null;
        $newKind = null;

        if ($media) {
            $newPath = $media->store('maker-profiles/'.$maker->id.'/content', 'public');

            if (! is_string($newPath) || $newPath === '') {
                throw new RuntimeException('The Maker content could not be stored.');
            }

            $newMimeType = $media->getMimeType();
            $newKind = is_string($newMimeType) && str_starts_with($newMimeType, 'video/')
                ? 'video'
                : 'image';
        }

        $oldPath = $existing?->path;

        try {
            $content = DB::transaction(function () use (
                $profile,
                $slot,
                $existing,
                $newPath,
                $newMimeType,
                $newKind,
                $caption,
            ): MakerProfileContent {
                $values = [
                    'caption' => $caption,
                ];

                if ($newPath !== null) {
                    $values['path'] = $newPath;
                    $values['mime_type'] = $newMimeType;
                    $values['kind'] = $newKind;
                }

                if ($existing) {
                    $existing->update($values);

                    return $existing->refresh();
                }

                return $profile->contents()->create([
                    'slot' => $slot,
                    'kind' => $newKind ?? 'image',
                    'path' => $newPath,
                    'mime_type' => $newMimeType,
                    'caption' => $caption,
                ]);
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $content;
    }
}

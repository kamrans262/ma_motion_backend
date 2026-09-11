<?php

namespace App\Features\Account\Services;

use App\Features\Artworks\Models\Artwork;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class AccountDeletionService
{
    public function delete(User $user): void
    {
        $profileImagePath = $user->makerProfile?->profile_image_path;

        $profileCarouselFiles = $user->makerProfile
            ? $user->makerProfile->carouselMedia()
                ->get(['disk', 'path'])
                ->map(static fn ($media): array => [
                    'disk' => $media->disk,
                    'path' => $media->path,
                ])
                ->all()
            : [];

        $mediaFiles = Artwork::query()
            ->withTrashed()
            ->where('maker_id', $user->id)
            ->with('media:id,artwork_id,disk,path')
            ->get()
            ->flatMap(static fn (Artwork $artwork) => $artwork->media)
            ->map(static fn ($media): array => ['disk' => $media->disk, 'path' => $media->path])
            ->all();

        DB::transaction(static function () use ($user): void {
            $user->tokens()->delete();
            $user->delete();
        });

        foreach ([...$mediaFiles, ...$profileCarouselFiles] as $mediaFile) {
            Storage::disk($mediaFile['disk'])->delete($mediaFile['path']);
        }

        if ($profileImagePath) {
            Storage::disk('public')->delete($profileImagePath);
        }
    }
}

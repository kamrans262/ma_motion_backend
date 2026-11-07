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
        $contentPaths = $user->makerProfile?->contents()
            ->pluck('path')
            ->filter()
            ->all() ?? [];

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

        foreach ($mediaFiles as $mediaFile) {
            Storage::disk($mediaFile['disk'])->delete($mediaFile['path']);
        }

        if ($profileImagePath) {
            Storage::disk('public')->delete($profileImagePath);
        }

        foreach ($contentPaths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}

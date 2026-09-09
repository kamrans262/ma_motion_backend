<?php

namespace App\Features\Admin\Users\Actions;

use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class DeleteUserAction
{
    public function execute(User $user): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw ValidationException::withMessages([
                'user' => 'Administrator accounts cannot be deleted from User Management.',
            ]);
        }

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
    }
}

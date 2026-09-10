<?php

namespace App\Features\Account\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class UpdateMakerProfileImageAction
{
    public function execute(User $maker, UploadedFile $image): User
    {
        $oldPath = $maker->makerProfile?->profile_image_path;
        $newPath = $image->store('maker-profiles/'.$maker->id, 'public');

        if (! is_string($newPath) || $newPath === '') {
            throw new RuntimeException('The profile image could not be stored.');
        }

        try {
            DB::transaction(function () use ($maker, $newPath): void {
                $maker->makerProfile()->updateOrCreate(
                    ['user_id' => $maker->id],
                    ['profile_image_path' => $newPath],
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $maker->refresh()->load('makerProfile');
    }
}

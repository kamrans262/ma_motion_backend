<?php

namespace App\Features\Makers\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteMakerProfileCarouselAction
{
    public function execute(User $maker, int $slot): void
    {
        $profile = $maker->makerProfile;
        $media = $profile?->carouselMedia()->where('slot', $slot)->first();

        if ($media === null) {
            return;
        }

        $disk = $media->disk;
        $path = $media->path;

        DB::transaction(static function () use ($media): void {
            $media->delete();
        });

        Storage::disk($disk)->delete($path);
    }
}

<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;
use Throwable;

final class PublicMediaUrl
{
    /**
     * Do not return stale public URLs from legacy media imports. The viewer
     * uses profile_image_url to decide whether a Salon Image slide exists.
     */
    public static function ifExists(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        try {
            $disk = Storage::disk('public');

            return $disk->exists($path) ? $disk->url($path) : null;
        } catch (Throwable) {
            return null;
        }
    }
}

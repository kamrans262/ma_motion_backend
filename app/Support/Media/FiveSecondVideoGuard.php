<?php

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Verify the duration of actual uploaded video bytes, never client-supplied
 * duration/extension. Fail closed when the configured ffprobe is unavailable.
 */
class FiveSecondVideoGuard
{
    public const MAX_SECONDS = 5.0;

    public function validate(UploadedFile $file, string $field = 'media'): void
    {
        $mime = (string) $file->getMimeType();

        if (! str_starts_with($mime, 'video/')) {
            return;
        }

        $binary = (string) config('media.ffprobe_binary', 'ffprobe');

        try {
            $probe = new Process([
                $binary,
                '-v', 'error',
                '-show_entries', 'format=duration:stream=codec_type',
                '-of', 'json',
                $file->getRealPath(),
            ]);
            $probe->setTimeout(20);
            $probe->run();

            if (! $probe->isSuccessful()) {
                throw new \RuntimeException('Could not inspect uploaded video.');
            }

            $data = json_decode($probe->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            $duration = $data['format']['duration'] ?? null;
            $hasVideo = collect($data['streams'] ?? [])
                ->contains(fn ($stream): bool => ($stream['codec_type'] ?? null) === 'video');

            if (! $hasVideo || ! is_numeric($duration) ||
                ! is_finite((float) $duration) || (float) $duration <= 0) {
                throw new \RuntimeException('Uploaded video has no valid video stream or duration.');
            }
        } catch (Throwable) {
            throw ValidationException::withMessages([
                $field => 'Video duration could not be verified. Please upload a valid video (5 seconds or less).',
            ]);
        }

        if ((float) $duration > self::MAX_SECONDS) {
            throw ValidationException::withMessages([
                $field => 'Video must be 5 seconds or less.',
            ]);
        }
    }
}

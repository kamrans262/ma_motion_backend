<?php

namespace App\Features\Makers\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class DeleteMakerProfileContentAction
{
    public function execute(User $maker, int $slot): void
    {
        if ($slot !== 1) {
            throw ValidationException::withMessages([
                'slot' => ['Only Content 1 is Maker profile media. Content 2 through 4 are artworks.'],
            ]);
        }

        $content = $maker->makerProfile?->contents()->where('slot', $slot)->first();

        if (! $content) {
            return;
        }

        $path = $content->path;

        DB::transaction(static fn () => $content->delete());

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}

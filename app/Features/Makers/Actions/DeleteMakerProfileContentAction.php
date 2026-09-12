<?php

namespace App\Features\Makers\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteMakerProfileContentAction
{
    public function execute(User $maker, int $slot): void
    {
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

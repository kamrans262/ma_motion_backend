<?php

namespace App\Features\Account\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteMakerProfileImageAction
{
    public function execute(User $maker): User
    {
        $profile = $maker->makerProfile;
        $oldPath = $profile?->profile_image_path;

        if ($profile && $oldPath) {
            DB::transaction(static fn () => $profile->update(['profile_image_path' => null]));
            Storage::disk('public')->delete($oldPath);
        }

        return $maker->refresh()->load('makerProfile');
    }
}

<?php

namespace App\Features\Makers\Actions;

use App\Features\Artworks\Models\Artwork;
use App\Features\Makers\Models\MakerProfileArtworkSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignMakerProfileArtworkSlotAction
{
    public function execute(User $maker, int $slot, int $artworkId): MakerProfileArtworkSlot
    {
        if ($slot < 2 || $slot > 4) {
            throw ValidationException::withMessages([
                'slot' => ['Artwork slot must be between 2 and 4.'],
            ]);
        }

        $artwork = Artwork::query()
            ->where('maker_id', $maker->id)
            ->find($artworkId);

        if ($artwork === null) {
            throw (new ModelNotFoundException())->setModel(Artwork::class, [$artworkId]);
        }

        $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);

        return DB::transaction(function () use ($profile, $slot, $artwork): MakerProfileArtworkSlot {
            $duplicateSlot = $profile->artworkSlots()
                ->where('artwork_id', $artwork->id)
                ->where('slot', '!=', $slot)
                ->exists();

            if ($duplicateSlot) {
                throw ValidationException::withMessages([
                    'artwork_id' => ['This artwork is already used in another Maker Info slot.'],
                ]);
            }

            $assignment = $profile->artworkSlots()->updateOrCreate(
                ['slot' => $slot],
                ['artwork_id' => $artwork->id],
            );

            return $assignment->load([
                'artwork.maker',
                'artwork.type',
                'artwork.style',
                'artwork.location',
                'artwork.media',
            ]);
        });
    }
}

<?php

namespace App\Features\Shows\Services;

use App\Features\Artworks\Models\Artwork;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class ShowArtworkSyncService
{
    /** @param array<int, int|string> $artworkIds */
    public function sync(Show $show, User $maker, array $artworkIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $artworkIds)));
        $ids = array_values(array_filter($ids, static fn (int $id): bool => $id > 0));

        if ($ids !== []) {
            $ownedIds = Artwork::query()
                ->where('maker_id', $maker->id)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            $foreignIds = array_values(array_diff($ids, $ownedIds));
            if ($foreignIds !== []) {
                throw ValidationException::withMessages([
                    'artwork_ids' => ['Every related artwork must belong to the show Maker.'],
                ]);
            }
        }

        $syncData = [];
        foreach ($ids as $position => $id) {
            $syncData[$id] = ['sort_order' => $position];
        }

        $show->artworks()->sync($syncData);
    }
}

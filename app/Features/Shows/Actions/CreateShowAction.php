<?php

namespace App\Features\Shows\Actions;

use App\Features\Shows\Models\Show;
use App\Features\Shows\Services\ShowArtworkSyncService;
use App\Features\Shows\Support\ShowData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateShowAction
{
    public function __construct(private readonly ShowArtworkSyncService $artworkSync) {}

    /** @param array<string, mixed> $data */
    public function execute(User $maker, array $data): Show
    {
        return DB::transaction(function () use ($maker, $data): Show {
            $hasArtworkSelection = array_key_exists('artwork_ids', $data);
            $artworkIds = $hasArtworkSelection ? (array) $data['artwork_ids'] : [];
            unset($data['artwork_ids']);

            $show = $maker->shows()->create(ShowData::normalize($data) + [
                'is_visible' => true,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            if ($hasArtworkSelection) {
                $this->artworkSync->sync($show, $maker, $artworkIds);
            }

            return $show->load(['maker', 'location', 'artworks.type', 'artworks.style', 'artworks.primaryMedia']);
        });
    }
}

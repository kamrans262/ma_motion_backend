<?php

namespace App\Features\Shows\Actions;

use App\Features\Shows\Models\Show;
use App\Features\Shows\Services\ShowArtworkSyncService;
use App\Features\Shows\Support\ShowData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateShowAction
{
    public function __construct(private readonly ShowArtworkSyncService $artworkSync) {}

    /** @param array<string, mixed> $data */
    public function execute(Show $show, array $data): Show
    {
        return DB::transaction(function () use ($show, $data): Show {
            $hasArtworkSelection = array_key_exists('artwork_ids', $data);
            $artworkIds = $hasArtworkSelection ? (array) $data['artwork_ids'] : [];
            unset($data['artwork_ids']);

            $normalized = ShowData::normalize($data);
            $startDate = CarbonImmutable::parse(
                (string) ($normalized['start_date'] ?? $show->start_date->toDateString()),
            );
            $endDate = CarbonImmutable::parse(
                (string) ($normalized['end_date'] ?? $show->end_date->toDateString()),
            );

            if ($endDate->lt($startDate)) {
                throw ValidationException::withMessages([
                    'end_date' => ['The end date must be on or after the start date.'],
                ]);
            }

            $show->update($normalized);

            if ($hasArtworkSelection) {
                $this->artworkSync->sync($show, $show->maker, $artworkIds);
            }

            return $show->fresh(['maker', 'location', 'artworks.type', 'artworks.style', 'artworks.primaryMedia']);
        });
    }
}

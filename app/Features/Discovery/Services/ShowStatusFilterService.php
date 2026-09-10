<?php

namespace App\Features\Discovery\Services;

use App\Features\Shows\Enums\ShowStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class ShowStatusFilterService
{
    /**
     * @param Builder<\App\Features\Shows\Models\Show> $query
     * @param array<int, string> $statuses
     */
    public function apply(Builder $query, array $statuses): Builder
    {
        $statuses = array_values(array_intersect(array_unique($statuses), ShowStatus::values()));
        if ($statuses === []) {
            return $query;
        }

        $today = CarbonImmutable::today(config('app.timezone'))->toDateString();

        return $query->where(function (Builder $group) use ($statuses, $today): void {
            foreach ($statuses as $status) {
                $group->orWhere(function (Builder $statusQuery) use ($status, $today): void {
                    match ($status) {
                        ShowStatus::Current->value => $statusQuery
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today),
                        ShowStatus::Upcoming->value => $statusQuery->whereDate('start_date', '>', $today),
                        ShowStatus::Past->value => $statusQuery->whereDate('end_date', '<', $today),
                        default => $statusQuery->whereRaw('1 = 0'),
                    };
                });
            }
        });
    }
}

<?php

namespace App\Features\Shows\Models;

use App\Features\Artworks\Models\Artwork;
use App\Features\Locations\Models\Location;
use App\Features\Shows\Enums\ShowStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'maker_id',
    'location_id',
    'name',
    'description',
    'location_text',
    'start_date',
    'end_date',
    'is_visible',
    'sort_order',
])]
final class Show extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return BelongsToMany<Artwork, $this> */
    public function artworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'show_artwork')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('artworks.id');
    }

    public function status(?CarbonInterface $today = null): ShowStatus
    {
        $date = $today
            ? CarbonImmutable::parse($today->toDateString(), config('app.timezone'))
            : CarbonImmutable::today(config('app.timezone'));

        if ($this->end_date->lt($date)) {
            return ShowStatus::Past;
        }

        if ($this->start_date->gt($date)) {
            return ShowStatus::Upcoming;
        }

        return ShowStatus::Current;
    }

    /** @param Builder<Show> $query */
    public function scopeWithStatus(Builder $query, ShowStatus|string $status): Builder
    {
        $value = $status instanceof ShowStatus ? $status->value : $status;
        $today = CarbonImmutable::today(config('app.timezone'))->toDateString();

        return match ($value) {
            ShowStatus::Current->value => $query
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today),
            ShowStatus::Upcoming->value => $query->whereDate('start_date', '>', $today),
            ShowStatus::Past->value => $query->whereDate('end_date', '<', $today),
            default => $query,
        };
    }

    /** @param Builder<Show> $query */
    public function scopeCurrentOrUpcoming(Builder $query): Builder
    {
        return $query->whereDate(
            'end_date',
            '>=',
            CarbonImmutable::today(config('app.timezone'))->toDateString(),
        );
    }
}

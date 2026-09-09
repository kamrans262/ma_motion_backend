<?php

namespace App\Features\Artworks\Models;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'maker_id',
    'artwork_type_id',
    'artwork_style_id',
    'location_id',
    'title',
    'description',
    'location_text',
    'moderation_status',
    'is_visible',
    'rejection_reason',
    'sort_order',
])]
final class Artwork extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'moderation_status' => ArtworkModerationStatus::class,
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /** @return BelongsTo<ArtworkType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ArtworkType::class, 'artwork_type_id');
    }

    /** @return BelongsTo<ArtworkStyle, $this> */
    public function style(): BelongsTo
    {
        return $this->belongsTo(ArtworkStyle::class, 'artwork_style_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return HasMany<ArtworkMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ArtworkMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasOne<ArtworkMedia, $this> */
    public function primaryMedia(): HasOne
    {
        return $this->hasOne(ArtworkMedia::class)->where('is_primary', true);
    }
}

<?php

namespace App\Features\Makers\Models;

use App\Features\Locations\Models\Location;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'bio',
    'location_text',
    'location_id',
    'profile_image_path',
    'website_url',
    'contact_email',
    'show_website_on_info_page',
    'show_email_on_info_page',
    'show_shows_on_info_page',
    'onboarding_completed_at',
])]
final class MakerProfile extends Model
{
    protected function casts(): array
    {
        return [
            'show_website_on_info_page' => 'boolean',
            'show_email_on_info_page' => 'boolean',
            'show_shows_on_info_page' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return BelongsToMany<ArtworkType, $this> */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(
            ArtworkType::class,
            'maker_profile_type',
            'maker_profile_id',
            'artwork_type_id',
        )->withTimestamps();
    }

    /** @return BelongsToMany<ArtworkStyle, $this> */
    public function styles(): BelongsToMany
    {
        return $this->belongsToMany(
            ArtworkStyle::class,
            'maker_profile_style',
            'maker_profile_id',
            'artwork_style_id',
        )->withTimestamps();
    }

    /** @return HasMany<MakerProfileCarouselMedia, $this> */
    public function carouselMedia(): HasMany
    {
        return $this->hasMany(MakerProfileCarouselMedia::class)
            ->orderBy('slot');
    }
}

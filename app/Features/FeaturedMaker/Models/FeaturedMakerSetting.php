<?php

namespace App\Features\FeaturedMaker\Models;

use App\Features\Artworks\Models\Artwork;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'slot',
    'maker_id',
    'featured_artwork_id',
    'eyebrow',
    'headline',
    'description',
    'is_active',
    'updated_by',
])]
final class FeaturedMakerSetting extends Model
{
    public const POST_LOGIN_SLOT = 'post_login';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    /** @return BelongsTo<Artwork, $this> */
    public function featuredArtwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class, 'featured_artwork_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

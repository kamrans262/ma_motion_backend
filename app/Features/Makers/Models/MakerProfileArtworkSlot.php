<?php

namespace App\Features\Makers\Models;

use App\Features\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'maker_profile_id',
    'slot',
    'artwork_id',
])]
final class MakerProfileArtworkSlot extends Model
{
    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'artwork_id' => 'integer',
        ];
    }

    /** @return BelongsTo<MakerProfile, $this> */
    public function makerProfile(): BelongsTo
    {
        return $this->belongsTo(MakerProfile::class);
    }

    /** @return BelongsTo<Artwork, $this> */
    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}

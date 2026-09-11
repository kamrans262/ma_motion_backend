<?php

namespace App\Features\Makers\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'maker_profile_id',
    'slot',
    'kind',
    'disk',
    'path',
    'mime_type',
    'size_bytes',
    'caption',
])]
final class MakerProfileCarouselMedia extends Model
{
    protected $table = 'maker_profile_carousel_media';

    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    /** @return BelongsTo<MakerProfile, $this> */
    public function makerProfile(): BelongsTo
    {
        return $this->belongsTo(MakerProfile::class);
    }
}

<?php

namespace App\Features\Makers\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'maker_profile_id',
    'slot',
    'kind',
    'path',
    'mime_type',
    'caption',
])]
final class MakerProfileContent extends Model
{
    /** @return BelongsTo<MakerProfile, $this> */
    public function makerProfile(): BelongsTo
    {
        return $this->belongsTo(MakerProfile::class);
    }
}

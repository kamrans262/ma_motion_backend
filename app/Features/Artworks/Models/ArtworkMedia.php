<?php

namespace App\Features\Artworks\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'artwork_id',
    'kind',
    'disk',
    'path',
    'mime_type',
    'size_bytes',
    'width',
    'height',
    'alt_text',
    'sort_order',
    'is_primary',
])]
final class ArtworkMedia extends Model
{
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    /** @return BelongsTo<Artwork, $this> */
    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}

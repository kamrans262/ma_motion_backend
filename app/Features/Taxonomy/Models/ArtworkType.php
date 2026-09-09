<?php

namespace App\Features\Taxonomy\Models;

use App\Features\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'is_active', 'sort_order'])]
final class ArtworkType extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return HasMany<Artwork, $this> */
    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'artwork_type_id');
    }
}

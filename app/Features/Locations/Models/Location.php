<?php

namespace App\Features\Locations\Models;

use App\Features\Makers\Models\MakerProfile;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['city', 'region', 'postal_code', 'country_code', 'latitude', 'longitude', 'is_active', 'sort_order'])]
final class Location extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function displayLabel(): string
    {
        return collect([$this->city, $this->region, $this->postal_code, $this->country_code])
            ->filter(static fn (?string $value): bool => filled($value))
            ->implode(', ');
    }

    /** @return HasMany<MakerProfile, $this> */
    public function makerProfiles(): HasMany
    {
        return $this->hasMany(MakerProfile::class);
    }
}

<?php

namespace App\Models;

use App\Features\Appreciators\Models\AppreciatorProfile;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Auth\Models\SocialAccount;
use App\Features\Makers\Models\MakerProfile;
use App\Features\Shows\Models\Show;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        $allowed = array_map(
            static fn (UserRole|string $role): string => $role instanceof UserRole ? $role->value : $role,
            $roles,
        );

        return in_array($this->role?->value, $allowed, true);
    }

    /** @return HasMany<SocialAccount, $this> */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return HasOne<MakerProfile, $this> */
    public function makerProfile(): HasOne
    {
        return $this->hasOne(MakerProfile::class);
    }

    /** @return HasOne<AppreciatorProfile, $this> */
    public function appreciatorProfile(): HasOne
    {
        return $this->hasOne(AppreciatorProfile::class);
    }

    /** @return HasMany<Artwork, $this> */
    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'maker_id');
    }

    /** @return HasMany<Show, $this> */
    public function shows(): HasMany
    {
        return $this->hasMany(Show::class, 'maker_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function savedMakers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'maker_saves', 'appreciator_id', 'maker_id')
            ->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function savedByAppreciators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'maker_saves', 'maker_id', 'appreciator_id')
            ->withTimestamps();
    }
}

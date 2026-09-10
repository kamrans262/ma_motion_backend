<?php

namespace App\Features\Makers\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class PublicMakerArtworkService
{
    /** @return LengthAwarePaginator<int,Artwork> */
    public function paginate(User $maker, int $perPage = 24): LengthAwarePaginator
    {
        if (! $maker->hasRole(UserRole::Maker) || $maker->status !== UserStatus::Active) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->id]);
        }

        return Artwork::query()
            ->where('maker_id', $maker->id)
            ->where('moderation_status', ArtworkModerationStatus::Approved->value)
            ->where('is_visible', true)
            ->with([
                'maker' => static fn ($query) => $query->with(['makerProfile.location'])->withCount(['savedByAppreciators as saves_count']),
                'type:id,name,slug,is_active',
                'style:id,name,slug,is_active',
                'location',
                'primaryMedia',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(min(max($perPage, 1), 50))
            ->withQueryString();
    }
}

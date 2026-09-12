<?php

namespace App\Features\Saves\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\ArtworkSave;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class SavedArtworkService
{
    /**
     * @param array{per_page?:int|null,page?:int|null} $filters
     * @return LengthAwarePaginator<int, Artwork>
     */
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = ArtworkSave::query()
            ->where('user_id', $user->id)
            ->whereHas('artwork', static function (Builder $artwork): void {
                $artwork
                    ->where('moderation_status', ArtworkModerationStatus::Approved->value)
                    ->where('is_visible', true)
                    ->whereHas('maker', static function (Builder $maker): void {
                        $maker
                            ->where('role', UserRole::Maker->value)
                            ->where('status', UserStatus::Active->value);
                    });
            })
            ->with([
                'artwork.maker' => static function ($maker): void {
                    $maker
                        ->select(['id', 'name', 'role', 'status', 'created_at'])
                        ->with(['makerProfile.location'])
                        ->withCount(['savedByAppreciators as saves_count']);
                },
                'artwork.type:id,name,slug,is_active',
                'artwork.style:id,name,slug,is_active',
                'artwork.location',
                'artwork.primaryMedia',
            ])
            ->latest('id');

        $perPage = min(max((int) ($filters['per_page'] ?? 24), 1), 50);
        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(static fn (ArtworkSave $save): ?Artwork => $save->artwork)
                ->filter()
                ->values(),
        );

        return $paginator;
    }
}

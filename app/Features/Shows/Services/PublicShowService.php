<?php

namespace App\Features\Shows\Services;

use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PublicShowService
{
    /**
     * @param array{status?:string|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, Show>
     */
    public function paginateForMaker(int $makerId, array $filters): LengthAwarePaginator
    {
        $maker = $this->findVisibleMaker($makerId);
        $query = $this->publicQuery($maker);

        if (! empty($filters['status'])) {
            $query->withStatus((string) $filters['status']);
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query
            ->orderBy('start_date')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForMaker(int $makerId, int $showId): Show
    {
        $maker = $this->findVisibleMaker($makerId);

        return $this->publicQuery($maker)->findOrFail($showId);
    }

    private function findVisibleMaker(int $makerId): User
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->where(static function ($query): void {
                $query
                    ->whereDoesntHave('makerProfile')
                    ->orWhereHas('makerProfile', static function ($profileQuery): void {
                        $profileQuery->where('show_shows_on_info_page', true);
                    });
            })
            ->findOrFail($makerId);
    }

    private function publicQuery(User $maker)
    {
        return Show::query()
            ->where('maker_id', $maker->id)
            ->where('is_visible', true)
            ->currentOrUpcoming()
            ->with([
                'maker:id,name',
                'location',
                'artworks' => static function ($query): void {
                    $query->where('moderation_status', ArtworkModerationStatus::Approved->value)
                        ->where('is_visible', true)
                        ->with(['type', 'style', 'primaryMedia']);
                },
            ]);
    }
}

<?php

namespace App\Features\Saves\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SavedMakerService
{
    /**
     * @param array{search?:string|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(User $appreciator, array $filters): LengthAwarePaginator
    {
        $query = MakerSave::query()
            ->where('appreciator_id', $appreciator->id)
            ->whereHas('maker', static function ($maker): void {
                $maker->where('role', UserRole::Maker->value)
                    ->where('status', UserStatus::Active->value);
            })
            ->with(['maker' => static function ($maker): void {
                $maker->select(['id', 'name', 'role', 'status', 'created_at'])
                    ->with('makerProfile')
                    ->withCount(['savedByAppreciators as saves_count']);
            }])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->whereHas('maker', static function ($maker) use ($search): void {
                $maker->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('makerProfile', static function ($profile) use ($search): void {
                            $profile->where('bio', 'like', '%'.$search.'%')
                                ->orWhere('location_text', 'like', '%'.$search.'%');
                        });
                });
            });
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);
        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(static fn (MakerSave $save): User => $save->maker)
                ->values(),
        );

        return $paginator;
    }
}

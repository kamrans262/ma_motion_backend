<?php

namespace App\Features\Admin\Saves\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Saves\Models\MakerSave;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class SaveManagementService
{
    /**
     * @param array{search?:string|null,maker_id?:int|null,appreciator_id?:int|null} $filters
     * @return LengthAwarePaginator<int, MakerSave>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = MakerSave::query()
            ->with([
                'appreciator:id,name,email,role,status',
                'maker:id,name,email,role,status',
            ])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('appreciator', static function ($user) use ($search): void {
                    $user->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })->orWhereHas('maker', static function ($user) use ($search): void {
                    $user->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            });
        }

        if (! empty($filters['maker_id'])) {
            $query->where('maker_id', (int) $filters['maker_id']);
        }

        if (! empty($filters['appreciator_id'])) {
            $query->where('appreciator_id', (int) $filters['appreciator_id']);
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @return array{total_saves:int,saving_appreciators:int,saved_makers:int} */
    public function summary(): array
    {
        return [
            'total_saves' => MakerSave::query()->count(),
            'saving_appreciators' => MakerSave::query()->distinct()->count('appreciator_id'),
            'saved_makers' => MakerSave::query()->distinct()->count('maker_id'),
        ];
    }

    /** @return Collection<int, User> */
    public function topMakers(): Collection
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->whereHas('savedByAppreciators')
            ->select(['id', 'name', 'role', 'status'])
            ->withCount(['savedByAppreciators as saves_count'])
            ->orderByDesc('saves_count')
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    public function filteredMaker(?int $makerId): ?User
    {
        if (! $makerId) {
            return null;
        }

        return User::query()
            ->where('role', UserRole::Maker->value)
            ->select(['id', 'name', 'role', 'status'])
            ->find($makerId);
    }
}

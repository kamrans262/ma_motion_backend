<?php

namespace App\Features\Makers\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MakerDirectoryService
{
    /**
     * @param array{search?:string|null,per_page?:int|null} $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->visibleMakerQuery();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('makerProfile', static function ($profile) use ($search): void {
                        $profile
                            ->where('bio', 'like', '%'.$search.'%')
                            ->orWhere('location_text', 'like', '%'.$search.'%');
                    });
            });
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 50);

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function findVisible(int $id): User
    {
        return $this->visibleMakerQuery()->findOrFail($id);
    }

    private function visibleMakerQuery()
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->where('status', UserStatus::Active->value)
            ->with('makerProfile')
            ->select(['id', 'name', 'role', 'status', 'created_at']);
    }
}

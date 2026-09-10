<?php

namespace App\Features\Admin\Makers\Services;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Locations\Models\Location;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class MakerManagementService
{
    /** @param array{search?:string|null,status?:string|null} $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()
            ->where('role', UserRole::Maker->value)
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->with('makerProfile.location')
            ->withCount(['savedByAppreciators as saves_count'])
            ->latest('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('makerProfile', static function ($profile) use ($search): void {
                        $profile->where('bio', 'like', '%'.$search.'%')
                            ->orWhere('location_text', 'like', '%'.$search.'%')
                            ->orWhereHas('location', static function ($location) use ($search): void {
                                $location->where('city', 'like', '%'.$search.'%')
                                    ->orWhere('region', 'like', '%'.$search.'%')
                                    ->orWhere('postal_code', 'like', '%'.$search.'%');
                            });
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(20)->withQueryString();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(static fn (UserStatus $status): array => [$status->value => ucfirst($status->value)])
            ->all();
    }

    /** @return Collection<int, Location> */
    public function locations(): Collection
    {
        return Location::query()->orderByDesc('is_active')->orderBy('sort_order')->orderBy('city')->get();
    }
}

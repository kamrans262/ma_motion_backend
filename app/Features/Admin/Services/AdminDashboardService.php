<?php

namespace App\Features\Admin\Services;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class AdminDashboardService
{
    /** @return array<string, mixed> */
    public function metrics(): array
    {
        return [
            'total_users' => User::query()->count(),
            'makers' => User::query()->where('role', UserRole::Maker->value)->count(),
            'appreciators' => User::query()->where('role', UserRole::Appreciator->value)->count(),
            'active_users' => User::query()->where('status', UserStatus::Active->value)->count(),
            'inactive_users' => User::query()->where('status', UserStatus::Inactive->value)->count(),
            'total_artworks' => Artwork::query()->count(),
            'total_shows' => Show::query()->count(),
            'total_maker_saves' => MakerSave::query()->count(),
            'total_notifications' => InAppNotification::query()->count(),
            'total_audit_events' => AdminAuditLog::query()->count(),
            'audit_events_today' => AdminAuditLog::query()->whereDate('created_at', now()->toDateString())->count(),
            'recent_users' => $this->recentUsers(),
            'recent_artworks' => $this->recentArtworks(),
            'top_makers' => $this->topMakers(),
            'recent_audit' => AdminAuditLog::query()->with('admin:id,name')->latest('id')->limit(5)->get(),
        ];
    }

    /** @return Collection<int, User> */
    private function recentUsers(): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->latest('id')
            ->limit(6)
            ->get();
    }

    /** @return Collection<int, Artwork> */
    private function recentArtworks(): Collection
    {
        return Artwork::query()
            ->select(['id', 'maker_id', 'title', 'moderation_status', 'is_visible', 'created_at'])
            ->with('maker:id,name')
            ->latest('id')
            ->limit(6)
            ->get();
    }

    /** @return Collection<int, User> */
    private function topMakers(): Collection
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
}

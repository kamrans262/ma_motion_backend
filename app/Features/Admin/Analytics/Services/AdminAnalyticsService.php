<?php

namespace App\Features\Admin\Analytics\Services;

use App\Features\Admin\Audit\Models\AdminAuditLog;
use App\Features\Artworks\Enums\ArtworkModerationStatus;
use App\Features\Artworks\Models\Artwork;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Notifications\Models\InAppNotification;
use App\Features\Saves\Models\MakerSave;
use App\Features\Shows\Enums\ShowStatus;
use App\Features\Shows\Models\Show;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class AdminAnalyticsService
{
    /** @return array<string, mixed> */
    public function report(int $days = 30): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $today = CarbonImmutable::today(config('app.timezone'));
        $start = $today->subDays($days - 1)->startOfDay();
        $end = $today->endOfDay();

        return [
            'days' => $days,
            'period_start' => $start,
            'period_end' => $end,
            'totals' => [
                'users' => User::query()->count(),
                'makers' => User::query()->where('role', UserRole::Maker->value)->count(),
                'appreciators' => User::query()->where('role', UserRole::Appreciator->value)->count(),
                'artworks' => Artwork::query()->count(),
                'shows' => Show::query()->count(),
                'saves' => MakerSave::query()->count(),
                'notifications' => InAppNotification::query()->count(),
                'admin_actions' => AdminAuditLog::query()->count(),
            ],
            'period' => [
                'users' => User::query()->whereBetween('created_at', [$start, $end])->count(),
                'artworks' => Artwork::query()->whereBetween('created_at', [$start, $end])->count(),
                'shows' => Show::query()->whereBetween('created_at', [$start, $end])->count(),
                'saves' => MakerSave::query()->whereBetween('created_at', [$start, $end])->count(),
                'notifications' => InAppNotification::query()->whereBetween('created_at', [$start, $end])->count(),
                'admin_actions' => AdminAuditLog::query()->whereBetween('created_at', [$start, $end])->count(),
            ],
            'user_status' => [
                'active' => User::query()->where('status', UserStatus::Active->value)->count(),
                'inactive' => User::query()->where('status', UserStatus::Inactive->value)->count(),
            ],
            'artwork_moderation' => [
                'pending' => Artwork::query()->where('moderation_status', ArtworkModerationStatus::Pending->value)->count(),
                'approved' => Artwork::query()->where('moderation_status', ArtworkModerationStatus::Approved->value)->count(),
                'rejected' => Artwork::query()->where('moderation_status', ArtworkModerationStatus::Rejected->value)->count(),
                'visible' => Artwork::query()->where('is_visible', true)->count(),
                'hidden' => Artwork::query()->where('is_visible', false)->count(),
            ],
            'show_status' => [
                'current' => Show::query()->withStatus(ShowStatus::Current)->count(),
                'upcoming' => Show::query()->withStatus(ShowStatus::Upcoming)->count(),
                'past' => Show::query()->withStatus(ShowStatus::Past)->count(),
            ],
            'top_makers' => $this->topMakers(),
            'daily_activity' => $this->dailyActivity($start, $end),
            'recent_audit' => AdminAuditLog::query()->with('admin:id,name')->latest('id')->limit(8)->get(),
            'top_admin_actions' => AdminAuditLog::query()
                ->selectRaw('route_name, COUNT(*) as aggregate')
                ->whereNotNull('route_name')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('route_name')
                ->orderByDesc('aggregate')
                ->limit(6)
                ->get(),
        ];
    }

    /** @return Collection<int, User> */
    private function topMakers(): Collection
    {
        return User::query()
            ->where('role', UserRole::Maker->value)
            ->select(['id', 'name', 'role', 'status'])
            ->withCount(['savedByAppreciators as saves_count'])
            ->orderByDesc('saves_count')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    /** @return array<int, array<string, int|string>> */
    private function dailyActivity(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $userCounts = $this->countsByDate(User::query(), $start, $end);
        $artworkCounts = $this->countsByDate(Artwork::query(), $start, $end);
        $saveCounts = $this->countsByDate(MakerSave::query(), $start, $end);
        $auditCounts = $this->countsByDate(AdminAuditLog::query(), $start, $end);

        $rows = [];
        for ($date = $start->startOfDay(); $date->lte($end); $date = $date->addDay()) {
            $key = $date->toDateString();
            $rows[] = [
                'date' => $key,
                'label' => $date->format('M j'),
                'users' => $userCounts[$key] ?? 0,
                'artworks' => $artworkCounts[$key] ?? 0,
                'saves' => $saveCounts[$key] ?? 0,
                'admin_actions' => $auditCounts[$key] ?? 0,
            ];
        }

        return $rows;
    }

    /** @param Builder<*> $query @return array<string, int> */
    private function countsByDate(Builder $query, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return $query
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as aggregate')
            ->groupByRaw('DATE(created_at)')
            ->pluck('aggregate', 'activity_date')
            ->map(static fn ($count): int => (int) $count)
            ->all();
    }
}

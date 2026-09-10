@extends('admin.layouts.app')

@section('title', 'Analytics & Reporting')
@section('page_heading', 'Analytics & Reporting')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Platform intelligence</p>
        <h2>Analytics & Reporting</h2>
        <p>Real database metrics for users, Makers, artwork, shows, saves, notifications, and Admin operations. No placeholder statistics are used.</p>
    </div>
    <div class="ma-page-heading__actions">
        <a class="ma-button ma-button--outline" href="{{ route('admin.analytics.export', ['period' => $days]) }}">Export CSV</a>
        <a class="ma-button ma-button--outline" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
    </div>
</section>

<section class="ma-panel ma-panel--filters ma-analytics-filter-panel">
    <form class="ma-analytics-period-form" method="GET" action="{{ route('admin.analytics.index') }}">
        <div class="ma-field">
            <label for="period">Reporting period</label>
            <select id="period" name="period">
                @foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                    <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="ma-filter-actions"><button class="ma-button ma-button--primary" type="submit">Apply period</button></div>
    </form>
</section>

<section class="ma-stat-grid ma-analytics-grid" aria-label="Platform totals">
    @include('admin.partials.stat-card', ['label' => 'Total Users', 'value' => $totals['users'], 'hint' => 'All registered accounts'])
    @include('admin.partials.stat-card', ['label' => 'Makers', 'value' => $totals['makers'], 'hint' => 'Creator accounts'])
    @include('admin.partials.stat-card', ['label' => 'Appreciators', 'value' => $totals['appreciators'], 'hint' => 'Discovery accounts'])
    @include('admin.partials.stat-card', ['label' => 'Artwork', 'value' => $totals['artworks'], 'hint' => 'Non-deleted artwork'])
    @include('admin.partials.stat-card', ['label' => 'Shows', 'value' => $totals['shows'], 'hint' => 'Non-deleted exhibitions'])
    @include('admin.partials.stat-card', ['label' => 'Maker Saves', 'value' => $totals['saves'], 'hint' => 'Current Heart relationships'])
    @include('admin.partials.stat-card', ['label' => 'Notifications', 'value' => $totals['notifications'], 'hint' => 'In-app notification records'])
    @include('admin.partials.stat-card', ['label' => 'Admin Actions', 'value' => $totals['admin_actions'], 'hint' => 'Audited write operations'])
</section>

<section class="ma-panel ma-analytics-period-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Activity</p><h3>Last {{ $days }} days</h3></div><span class="ma-panel__meta">{{ $period_start->format('M j') }} – {{ $period_end->format('M j, Y') }}</span></div>
    <div class="ma-analytics-period-stats">
        <div><span>New users</span><strong>{{ number_format($period['users']) }}</strong></div>
        <div><span>Artwork added</span><strong>{{ number_format($period['artworks']) }}</strong></div>
        <div><span>Shows added</span><strong>{{ number_format($period['shows']) }}</strong></div>
        <div><span>Saves created</span><strong>{{ number_format($period['saves']) }}</strong></div>
        <div><span>Notifications sent</span><strong>{{ number_format($period['notifications']) }}</strong></div>
        <div><span>Admin actions</span><strong>{{ number_format($period['admin_actions']) }}</strong></div>
    </div>
</section>

<section class="ma-analytics-breakdown-grid">
    <article class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Accounts</p><h3>User access</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Active users</dt><dd>{{ number_format($user_status['active']) }}</dd></div>
            <div><dt>Inactive users</dt><dd>{{ number_format($user_status['inactive']) }}</dd></div>
        </dl>
    </article>

    <article class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Moderation</p><h3>Artwork health</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Pending</dt><dd>{{ number_format($artwork_moderation['pending']) }}</dd></div>
            <div><dt>Approved</dt><dd>{{ number_format($artwork_moderation['approved']) }}</dd></div>
            <div><dt>Rejected</dt><dd>{{ number_format($artwork_moderation['rejected']) }}</dd></div>
            <div><dt>Visible</dt><dd>{{ number_format($artwork_moderation['visible']) }}</dd></div>
            <div><dt>Hidden</dt><dd>{{ number_format($artwork_moderation['hidden']) }}</dd></div>
        </dl>
    </article>

    <article class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Exhibitions</p><h3>Show timeline</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Current Shows</dt><dd>{{ number_format($show_status['current']) }}</dd></div>
            <div><dt>Upcoming Shows</dt><dd>{{ number_format($show_status['upcoming']) }}</dd></div>
            <div><dt>Past Shows</dt><dd>{{ number_format($show_status['past']) }}</dd></div>
        </dl>
    </article>
</section>

<section class="ma-dashboard-grid ma-analytics-main-grid">
    <article class="ma-panel ma-panel--wide">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Trend</p><h3>Daily platform activity</h3></div><span class="ma-panel__meta">{{ $days }} days</span></div>
        <div class="ma-table-wrap ma-analytics-table-wrap">
            <table class="ma-table ma-analytics-activity-table">
                <thead><tr><th>Date</th><th>Users</th><th>Artwork</th><th>Saves</th><th>Admin actions</th></tr></thead>
                <tbody>
                    @foreach ($daily_activity as $row)
                        <tr><td data-label="Date">{{ $row['label'] }}</td><td data-label="Users">{{ $row['users'] }}</td><td data-label="Artwork">{{ $row['artworks'] }}</td><td data-label="Saves">{{ $row['saves'] }}</td><td data-label="Admin actions">{{ $row['admin_actions'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Community interest</p><h3>Most Hearted Makers</h3></div></div>
        @if ($top_makers->isEmpty())
            <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No Maker saves yet</h4><p>Ranking appears when Appreciators save Makers.</p></div>
        @else
            <ol class="ma-ranked-list ma-ranked-list--compact">
                @foreach ($top_makers as $maker)
                    <li><span class="ma-ranked-list__rank">{{ $loop->iteration }}</span><div class="ma-ranked-list__content"><strong><a class="ma-text-link" href="{{ route('admin.makers.show', $maker) }}">{{ $maker->name }}</a></strong><small>{{ ucfirst($maker->status->value) }}</small></div><span class="ma-ranked-list__count">{{ number_format($maker->saves_count) }}</span></li>
                @endforeach
            </ol>
        @endif
    </aside>
</section>

<section class="ma-dashboard-grid ma-analytics-audit-grid">
    <article class="ma-panel ma-panel--wide">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Governance</p><h3>Recent Admin activity</h3></div><a class="ma-text-link" href="{{ route('admin.audit-logs.index') }}">View all</a></div>
        @if ($recent_audit->isEmpty())
            <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No audited actions yet</h4><p>Future Admin write operations will appear here.</p></div>
        @else
            <div class="ma-table-wrap"><table class="ma-table"><thead><tr><th>Admin</th><th>Action</th><th>Status</th><th>When</th></tr></thead><tbody>
                @foreach ($recent_audit as $entry)
                    <tr><td data-label="Admin">{{ $entry->admin?->name ?? 'Deleted admin' }}</td><td data-label="Action"><code>{{ $entry->route_name ?? $entry->event }}</code></td><td data-label="Status">{{ $entry->response_status }}</td><td data-label="When">{{ $entry->created_at?->format('M j, H:i') }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </article>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Operations</p><h3>Top Admin actions</h3></div></div>
        @if ($top_admin_actions->isEmpty())
            <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No action pattern yet</h4><p>Action frequencies are calculated from real audit records.</p></div>
        @else
            <ol class="ma-ranked-list ma-ranked-list--compact">
                @foreach ($top_admin_actions as $entry)
                    <li><span class="ma-ranked-list__rank">{{ $loop->iteration }}</span><div class="ma-ranked-list__content"><strong>{{ $entry->route_name }}</strong></div><span class="ma-ranked-list__count">{{ number_format($entry->aggregate) }}</span></li>
                @endforeach
            </ol>
        @endif
    </aside>
</section>
@endsection

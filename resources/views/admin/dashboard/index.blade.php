@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_heading', 'Dashboard')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Platform overview</p>
        <h2>MA Motion control center</h2>
        <p>Live account, artwork, show, and Maker-save statistics give Admin users an operational view of the platform without direct database access.</p>
    </div>
    <div class="ma-page-heading__actions"><a class="ma-button ma-button--outline" href="{{ route('admin.analytics.index') }}">Open Analytics</a><a class="ma-button ma-button--outline" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a></div>
</section>

<section class="ma-stat-grid" aria-label="Platform statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Users', 'value' => $total_users, 'hint' => 'All registered accounts'])
    @include('admin.partials.stat-card', ['label' => 'Makers', 'value' => $makers, 'hint' => 'Creator accounts'])
    @include('admin.partials.stat-card', ['label' => 'Appreciators', 'value' => $appreciators, 'hint' => 'Discovery accounts'])
    @include('admin.partials.stat-card', ['label' => 'Total Artwork', 'value' => $total_artworks, 'hint' => 'Non-deleted artwork'])
    @include('admin.partials.stat-card', ['label' => 'Total Shows', 'value' => $total_shows, 'hint' => 'Non-deleted exhibitions'])
    @include('admin.partials.stat-card', ['label' => 'Maker Saves', 'value' => $total_maker_saves, 'hint' => 'Hearts / saved Makers'])
    @include('admin.partials.stat-card', ['label' => 'Active Users', 'value' => $active_users, 'hint' => 'Accounts with access'])
    @include('admin.partials.stat-card', ['label' => 'Inactive Users', 'value' => $inactive_users, 'hint' => 'Access currently disabled'])
    @include('admin.partials.stat-card', ['label' => 'Notifications', 'value' => $total_notifications, 'hint' => 'In-app notification records'])
    @include('admin.partials.stat-card', ['label' => 'Audit Events', 'value' => $total_audit_events, 'hint' => $audit_events_today.' Admin actions today'])
</section>

<section class="ma-dashboard-grid">
    <article class="ma-panel ma-panel--wide">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Accounts</p><h3>Recently registered</h3></div><span class="ma-panel__meta">Latest 6</span></div>
        @if ($recent_users->isEmpty())
            <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No users yet</h4><p>Newly registered Makers and Appreciators will appear here.</p></div>
        @else
            <div class="ma-table-wrap">
                <table class="ma-table"><thead><tr><th scope="col">User</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Joined</th></tr></thead><tbody>
                    @foreach ($recent_users as $user)
                        <tr><td data-label="User"><strong><a class="ma-text-link" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></strong><small>{{ $user->email }}</small></td><td data-label="Role">{{ ucfirst($user->role->value) }}</td><td data-label="Status"><span class="ma-badge {{ $user->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ ucfirst($user->status->value) }}</span></td><td data-label="Joined">{{ $user->created_at?->format('M j, Y') }}</td></tr>
                    @endforeach
                </tbody></table>
            </div>
        @endif
    </article>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Community interest</p><h3>Most Hearted Makers</h3></div><a class="ma-text-link" href="{{ route('admin.saves.index') }}">All saves</a></div>
        @if ($top_makers->isEmpty())
            <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No saves yet</h4><p>Maker interest will appear here as Appreciators use the Heart action.</p></div>
        @else
            <ol class="ma-ranked-list ma-ranked-list--compact">
                @foreach ($top_makers as $maker)
                    <li><span class="ma-ranked-list__rank">{{ $loop->iteration }}</span><div class="ma-ranked-list__content"><strong><a class="ma-text-link" href="{{ route('admin.makers.show', $maker) }}">{{ $maker->name }}</a></strong><small>{{ ucfirst($maker->status->value) }}</small></div><a class="ma-ranked-list__count" href="{{ route('admin.saves.index', ['maker_id' => $maker->id]) }}">{{ number_format($maker->saves_count) }}</a></li>
                @endforeach
            </ol>
        @endif
        <div class="ma-callout"><strong>Maker privacy</strong><p>Makers see only their aggregate saved count. Admin retains the operational relationship detail required by the scope.</p></div>
    </aside>
</section>

<section class="ma-panel ma-dashboard-section">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Artwork</p><h3>Recently added artwork</h3></div><span class="ma-panel__meta">Latest 6</span></div>
    @if ($recent_artworks->isEmpty())
        <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No artwork yet</h4><p>Recently added Maker artwork will appear here.</p></div>
    @else
        <div class="ma-table-wrap"><table class="ma-table"><thead><tr><th scope="col">Artwork</th><th scope="col">Maker</th><th scope="col">Moderation</th><th scope="col">Visibility</th><th scope="col">Added</th></tr></thead><tbody>
            @foreach ($recent_artworks as $artwork)
                <tr><td data-label="Artwork"><strong><a class="ma-text-link" href="{{ route('admin.artworks.show', $artwork) }}">{{ $artwork->title }}</a></strong></td><td data-label="Maker">{{ $artwork->maker?->name ?? 'Unknown Maker' }}</td><td data-label="Moderation"><span class="ma-badge ma-badge--purple">{{ ucfirst($artwork->moderation_status->value) }}</span></td><td data-label="Visibility">{{ $artwork->is_visible ? 'Visible' : 'Hidden' }}</td><td data-label="Added">{{ $artwork->created_at?->format('M j, Y') }}</td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</section>
<section class="ma-panel ma-dashboard-section ma-dashboard-audit-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Governance</p><h3>Recent admin activity</h3></div><a class="ma-text-link" href="{{ route('admin.audit-logs.index') }}">View audit trail</a></div>
    @if ($recent_audit->isEmpty())
        <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No audited actions yet</h4><p>Authenticated Admin write operations will appear here.</p></div>
    @else
        <div class="ma-table-wrap"><table class="ma-table"><thead><tr><th>Admin</th><th>Action</th><th>Status</th><th>When</th></tr></thead><tbody>
            @foreach ($recent_audit as $entry)
                <tr><td data-label="Admin">{{ $entry->admin?->name ?? 'Deleted admin' }}</td><td data-label="Action">{{ $entry->route_name ?? $entry->event }}</td><td data-label="Status">{{ $entry->response_status }}</td><td data-label="When">{{ $entry->created_at?->format('M j, H:i') }}</td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</section>
@endsection

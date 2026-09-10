@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page_heading', 'Notifications')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">In-App Activity</p>
        <h2>Notifications</h2>
        <p>Monitor system-generated in-app alerts while keeping delivery preferences under each user's control.</p>
    </div>
    <span class="ma-pill">{{ number_format($summary['unread']) }} unread</span>
</section>

<section class="ma-stat-grid ma-notification-stat-grid" aria-label="Notification statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Notifications', 'value' => $summary['total'], 'hint' => 'System-generated in-app records'])
    @include('admin.partials.stat-card', ['label' => 'Unread', 'value' => $summary['unread'], 'hint' => 'Waiting to be opened'])
    @include('admin.partials.stat-card', ['label' => 'Read', 'value' => $summary['read'], 'hint' => 'Already acknowledged'])
    @include('admin.partials.stat-card', ['label' => 'Recipients', 'value' => $summary['recipients'], 'hint' => 'Accounts that received an alert'])
</section>

<section class="ma-panel ma-notification-policy">
    <div class="ma-notification-policy__copy">
        <p class="ma-eyebrow">Current Delivery Policy</p>
        <h3>Saved Maker show alerts</h3>
        <p>When a visible current or upcoming show is announced, active Appreciators who have saved that Maker can receive one deduplicated in-app alert. Users may disable future Saved Maker show alerts from Notification Settings.</p>
    </div>
    <span class="ma-notification-policy__badge">In-app enabled</span>
</section>

<section class="ma-panel ma-panel--filters">
    <form class="ma-notification-filter-grid" method="GET" action="{{ route('admin.notifications.index') }}">
        <div class="ma-field">
            <label for="search">Search notifications</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Recipient, Maker, show or message">
        </div>
        <div class="ma-field">
            <label for="status">Read status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option>
                <option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option>
            </select>
        </div>
        <div class="ma-field">
            <label for="type">Notification type</label>
            <select id="type" name="type">
                <option value="">All types</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="ma-filter-actions">
            <button class="ma-button ma-button--primary" type="submit">Apply filters</button>
            <a class="ma-button ma-button--outline" href="{{ route('admin.notifications.index') }}">Reset</a>
        </div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header">
        <div><p class="ma-eyebrow">Delivery Log</p><h3>In-app notification activity</h3></div>
        <span class="ma-panel__meta">20 per page</span>
    </div>

    @if ($notifications->isEmpty())
        <div class="ma-empty-state">
            <span class="ma-empty-state__icon" aria-hidden="true">◇</span>
            <h4>No matching notifications</h4>
            <p>Alerts will appear here when saved Makers announce eligible shows.</p>
        </div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-notification-table">
                <thead><tr><th scope="col">Recipient</th><th scope="col">Message</th><th scope="col">Context</th><th scope="col">Status</th><th scope="col">Created</th></tr></thead>
                <tbody>
                    @foreach ($notifications as $notification)
                        <tr>
                            <td data-label="Recipient"><strong>{{ $notification->recipient?->name ?? 'Deleted account' }}</strong><small>{{ $notification->recipient?->email ?? 'Unavailable' }}</small></td>
                            <td data-label="Message"><div class="ma-notification-message"><strong>{{ $notification->title }}</strong><small>{{ $notification->body }}</small><span class="ma-badge ma-badge--purple">{{ $notification->type->label() }}</span></div></td>
                            <td data-label="Context"><div class="ma-notification-context">@if($notification->maker)<a class="ma-text-link" href="{{ route('admin.makers.show', $notification->maker) }}">{{ $notification->maker->name }}</a>@endif @if($notification->show)<a class="ma-text-link" href="{{ route('admin.shows.show', $notification->show) }}">{{ $notification->show->name }}</a>@endif @if(!$notification->maker && !$notification->show)<span class="ma-muted-copy">Unavailable</span>@endif</div></td>
                            <td data-label="Status"><span class="ma-notification-state {{ $notification->read_at ? '' : 'ma-notification-state--unread' }}">{{ $notification->read_at ? 'Read' : 'Unread' }}</span></td>
                            <td data-label="Created">{{ $notification->created_at?->format('M j, Y g:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $notifications])
    @endif
</section>
@endsection

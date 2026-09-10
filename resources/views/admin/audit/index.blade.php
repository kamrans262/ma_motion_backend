@extends('admin.layouts.app')

@section('title', 'Audit Logs')
@section('page_heading', 'Audit Logs')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Operational governance</p>
        <h2>Admin Audit Logs</h2>
        <p>Append-only visibility into authenticated Admin write operations. Sensitive field values are deliberately excluded from stored request summaries.</p>
    </div>
    <div class="ma-page-heading__actions">
        <a class="ma-button ma-button--outline" href="{{ route('admin.audit-logs.export', request()->query()) }}">Export CSV</a>
        <a class="ma-button ma-button--outline" href="{{ route('admin.analytics.index') }}">Analytics</a>
    </div>
</section>

<section class="ma-stat-grid ma-audit-stat-grid" aria-label="Audit statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Audit Events', 'value' => $total_audit_events, 'hint' => 'Recorded Admin write operations'])
    @include('admin.partials.stat-card', ['label' => 'Today', 'value' => $audit_events_today, 'hint' => 'Events since start of day'])
    @include('admin.partials.stat-card', ['label' => 'Last 7 Days', 'value' => $audit_events_7_days, 'hint' => 'Recent operational activity'])
</section>

<section class="ma-panel ma-panel--filters">
    <form class="ma-audit-filter-grid" method="GET" action="{{ route('admin.audit-logs.index') }}">
        <div class="ma-field"><label for="search">Search</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Action, route, path or Admin"></div>
        <div class="ma-field"><label for="admin_id">Admin</label><select id="admin_id" name="admin_id"><option value="">All Admins</option>@foreach ($admins as $admin)<option value="{{ $admin->id }}" @selected((string)($filters['admin_id'] ?? '') === (string)$admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="method">Method</label><select id="method" name="method"><option value="">All methods</option>@foreach (['POST','PUT','PATCH','DELETE'] as $method)<option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="status">HTTP status</label><input id="status" name="status" type="number" min="100" max="599" value="{{ $filters['status'] ?? '' }}" placeholder="e.g. 302"></div>
        <div class="ma-field"><label for="from">From</label><input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="ma-field"><label for="to">To</label><input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="ma-filter-actions"><button class="ma-button ma-button--primary" type="submit">Apply filters</button><a class="ma-button ma-button--outline" href="{{ route('admin.audit-logs.index') }}">Reset</a></div>
    </form>
</section>

<section class="ma-panel ma-audit-directory">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Audit trail</p><h3>Admin operations</h3></div><span class="ma-panel__meta">25 per page</span></div>

    @if ($auditLogs->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No audit events match</h4><p>Adjust the filters or perform an Admin write operation.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-audit-table">
                <thead><tr><th>Admin</th><th>Action</th><th>Target</th><th>HTTP</th><th>IP</th><th>When</th></tr></thead>
                <tbody>
                    @foreach ($auditLogs as $entry)
                        <tr>
                            <td data-label="Admin"><strong>{{ $entry->admin?->name ?? 'Deleted admin' }}</strong><small>{{ $entry->admin?->email }}</small></td>
                            <td data-label="Action"><strong>{{ $entry->route_name ?? $entry->event }}</strong><small>{{ $entry->path }}</small></td>
                            <td data-label="Target">{{ $entry->target_type ? $entry->target_type.' #'.$entry->target_id : '—' }}</td>
                            <td data-label="HTTP"><span class="ma-badge ma-badge--purple">{{ $entry->method }}</span> {{ $entry->response_status }}</td>
                            <td data-label="IP">{{ $entry->ip_address ?? '—' }}</td>
                            <td data-label="When">{{ $entry->created_at?->format('M j, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ma-pagination">{{ $auditLogs->links() }}</div>
    @endif
</section>
@endsection

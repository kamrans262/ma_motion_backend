@extends('admin.layouts.app')

@section('title', 'Users')
@section('page_heading', 'Users')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Account Management</p>
        <h2>Users</h2>
        <p>Search, filter, review, activate, deactivate and safely manage registered accounts from one consistent workspace.</p>
    </div>
    <span class="ma-pill">{{ number_format($users->total()) }} matching accounts</span>
</section>

<section class="ma-panel ma-panel--filters">
    <form class="ma-filter-grid" method="GET" action="{{ route('admin.users.index') }}">
        <div class="ma-field">
            <label for="search">Search</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
        </div>
        <div class="ma-field">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="">All roles</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="ma-field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="ma-filter-actions">
            <button class="ma-button ma-button--primary" type="submit">Apply filters</button>
            <a class="ma-button ma-button--outline" href="{{ route('admin.users.index') }}">Reset</a>
        </div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header">
        <div>
            <p class="ma-eyebrow">Directory</p>
            <h3>Registered accounts</h3>
        </div>
        <span class="ma-panel__meta">20 per page</span>
    </div>

    @if ($users->isEmpty())
        <div class="ma-empty-state">
            <span class="ma-empty-state__icon" aria-hidden="true">◇</span>
            <h4>No matching users</h4>
            <p>Try clearing one or more filters.</p>
        </div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td data-label="User">
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }}</small>
                            </td>
                            <td data-label="Role"><span class="ma-badge ma-badge--purple">{{ ucfirst($user->role->value) }}</span></td>
                            <td data-label="Status">
                                <span class="ma-badge {{ $user->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ ucfirst($user->status->value) }}</span>
                            </td>
                            <td data-label="Joined">{{ $user->created_at?->format('M j, Y') }}</td>
                            <td data-label="Action"><a class="ma-text-link" href="{{ route('admin.users.show', $user) }}">Manage</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $users])
    @endif
</section>
@endsection

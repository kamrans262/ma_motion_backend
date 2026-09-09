@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_heading', 'Dashboard')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Platform overview</p>
        <h2>MA Motion control center</h2>
        <p>Core account metrics are live. Additional cards will activate as Makers, artwork, shows, saves and analytics modules are implemented.</p>
    </div>
    <span class="ma-pill"><span class="ma-status-dot" aria-hidden="true"></span> System online</span>
</section>

<section class="ma-stat-grid" aria-label="Account statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Users', 'value' => $total_users, 'hint' => 'All registered accounts'])
    @include('admin.partials.stat-card', ['label' => 'Makers', 'value' => $makers, 'hint' => 'Creator accounts'])
    @include('admin.partials.stat-card', ['label' => 'Appreciators', 'value' => $appreciators, 'hint' => 'Discovery accounts'])
    @include('admin.partials.stat-card', ['label' => 'Active', 'value' => $active_users, 'hint' => 'Accounts with access'])
    @include('admin.partials.stat-card', ['label' => 'Inactive', 'value' => $inactive_users, 'hint' => 'Access currently disabled'])
</section>

<section class="ma-dashboard-grid">
    <article class="ma-panel ma-panel--wide">
        <div class="ma-panel__header">
            <div>
                <p class="ma-eyebrow">Accounts</p>
                <h3>Recently registered</h3>
            </div>
            <span class="ma-panel__meta">Latest 6</span>
        </div>

        @if ($recent_users->isEmpty())
            <div class="ma-empty-state">
                <span class="ma-empty-state__icon" aria-hidden="true">◇</span>
                <h4>No users yet</h4>
                <p>Newly registered Makers and Appreciators will appear here.</p>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent_users as $user)
                            <tr>
                                <td data-label="User">
                                    <strong><a class="ma-text-link" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></strong>
                                    <small>{{ $user->email }}</small>
                                </td>
                                <td data-label="Role">{{ ucfirst($user->role->value) }}</td>
                                <td data-label="Status">
                                    <span class="ma-badge {{ $user->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }}">
                                        {{ ucfirst($user->status->value) }}
                                    </span>
                                </td>
                                <td data-label="Joined">{{ $user->created_at?->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>

    <aside class="ma-panel">
        <div class="ma-panel__header">
            <div>
                <p class="ma-eyebrow">Roadmap</p>
                <h3>Admin foundation</h3>
            </div>
        </div>
        <ul class="ma-check-list">
            <li><span>✓</span> Session-based admin authentication</li>
            <li><span>✓</span> Active admin role enforcement</li>
            <li><span>✓</span> Responsive MA Motion design system</li>
            <li><span>✓</span> Reusable navigation, cards and tables</li>
            <li><span>✓</span> Account dashboard metrics</li>
        </ul>
        <div class="ma-callout">
            <strong>Users & Makers enabled</strong>
            <p>User and Maker management now use this shared responsive design system. Types, Styles and structured Location management are next.</p>
        </div>
    </aside>
</section>
@endsection

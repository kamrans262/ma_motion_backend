@extends('admin.layouts.app')

@section('title', 'Makers')
@section('page_heading', 'Makers')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Creator Management</p>
        <h2>Makers</h2>
        <p>Review Maker accounts, profile information, structured locations, and the number of Appreciators who have saved each Maker.</p>
    </div>
    <span class="ma-pill">{{ number_format($makers->total()) }} matching Makers</span>
</section>

<section class="ma-panel ma-panel--filters">
    <form class="ma-filter-grid ma-filter-grid--compact" method="GET" action="{{ route('admin.makers.index') }}">
        <div class="ma-field">
            <label for="search">Search</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, bio or location">
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
            <a class="ma-button ma-button--outline" href="{{ route('admin.makers.index') }}">Reset</a>
        </div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Directory</p><h3>Maker accounts</h3></div><span class="ma-panel__meta">20 per page</span></div>

    @if ($makers->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching Makers</h4><p>Try clearing one or more filters.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table">
                <thead><tr><th scope="col">Maker</th><th scope="col">Location</th><th scope="col">Hearts / Saves</th><th scope="col">Status</th><th scope="col">Profile</th><th scope="col">Action</th></tr></thead>
                <tbody>
                    @foreach ($makers as $maker)
                        <tr>
                            <td data-label="Maker"><strong>{{ $maker->name }}</strong><small>{{ $maker->email }}</small></td>
                            <td data-label="Location">{{ $maker->makerProfile?->location?->displayLabel() ?? $maker->makerProfile?->location_text ?? 'Not set' }}</td>
                            <td data-label="Hearts / Saves"><a class="ma-text-link" href="{{ route('admin.saves.index', ['maker_id' => $maker->id]) }}">{{ number_format($maker->saves_count) }}</a></td>
                            <td data-label="Status"><span class="ma-badge {{ $maker->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ ucfirst($maker->status->value) }}</span></td>
                            <td data-label="Profile">{{ filled($maker->makerProfile?->bio) ? 'Bio added' : 'Needs bio' }}</td>
                            <td data-label="Action"><a class="ma-text-link" href="{{ route('admin.makers.show', $maker) }}">Manage</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $makers])
    @endif
</section>
@endsection

@extends('admin.layouts.app')

@section('title', 'Shows')
@section('page_heading', 'Shows')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Shows &amp; Exhibitions</p>
        <h2>Shows</h2>
        <p>Create and manage Maker exhibitions, dates, venues, visibility and related artwork. Current, upcoming and past status is derived automatically from the show dates.</p>
    </div>
    <span class="ma-pill">{{ number_format($shows->total()) }} matching shows</span>
</section>

@if($errors->any())
<div class="ma-alert ma-alert--error" role="alert"><div><strong>Please correct the following.</strong><ul class="ma-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<div class="ma-detail-grid ma-show-create-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Create</p><h3>Add a show</h3></div><span class="ma-panel__meta">Maker-owned exhibition</span></div>
        <form class="ma-form" method="POST" action="{{ route('admin.shows.store') }}">@csrf
            <div class="ma-form-grid">
                <div class="ma-field"><label for="maker_id">Maker</label><select id="maker_id" name="maker_id" required><option value="">Select Maker</option>@foreach($makers as $maker)<option value="{{ $maker->id }}" @selected((string)old('maker_id') === (string)$maker->id)>{{ $maker->name }} · {{ $maker->email }}</option>@endforeach</select></div>
                <div class="ma-field"><label for="name">Show name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="180" required placeholder="Exhibition or show name"></div>
            </div>
            <div class="ma-field"><label for="description">Description</label><textarea id="description" name="description" rows="5" maxlength="10000" placeholder="About this show">{{ old('description') }}</textarea></div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="location_id">Managed location</label><select id="location_id" name="location_id"><option value="">No structured location</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((string)old('location_id') === (string)$location->id)>{{ $location->displayLabel() }}{{ $location->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
                <div class="ma-field"><label for="location_text">Public venue / location label</label><input id="location_text" name="location_text" value="{{ old('location_text') }}" maxlength="180" placeholder="Gallery name, venue or address"></div>
            </div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required></div>
                <div class="ma-field"><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required></div>
            </div>
            <div class="ma-field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order', 0) }}"><span class="ma-field__hint">Related artwork can be attached from the Manage Show screen after creation.</span></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Create show</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Status Logic</p><h3>Date-driven status</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Current</dt><dd>Today falls between the start and end dates.</dd></div>
            <div><dt>Upcoming</dt><dd>The start date is after today.</dd></div>
            <div><dt>Past</dt><dd>The end date is before today.</dd></div>
        </dl>
        <div class="ma-callout"><strong>No stale status field</strong><p>MA Motion derives show status from dates so current/upcoming information stays accurate automatically.</p></div>
    </aside>
</div>

<section class="ma-panel ma-panel--filters">
    <form class="ma-show-filter-grid" method="GET" action="{{ route('admin.shows.index') }}">
        <div class="ma-field ma-show-filter-grid__search"><label for="search">Search</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Show, Maker, description or venue"></div>
        <div class="ma-field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option>@foreach($statuses as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="visibility">Visibility</label><select id="visibility" name="visibility"><option value="">All visibility</option><option value="visible" @selected(($filters['visibility'] ?? '') === 'visible')>Visible</option><option value="hidden" @selected(($filters['visibility'] ?? '') === 'hidden')>Hidden</option></select></div>
        <div class="ma-field"><label for="filter_maker_id">Maker</label><select id="filter_maker_id" name="maker_id"><option value="">All Makers</option>@foreach($makers as $maker)<option value="{{ $maker->id }}" @selected((string)($filters['maker_id'] ?? '') === (string)$maker->id)>{{ $maker->name }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="filter_location_id">Location</label><select id="filter_location_id" name="location_id"><option value="">All locations</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((string)($filters['location_id'] ?? '') === (string)$location->id)>{{ $location->displayLabel() }}</option>@endforeach</select></div>
        <div class="ma-filter-actions"><button class="ma-button ma-button--primary" type="submit">Apply filters</button><a class="ma-button ma-button--outline" href="{{ route('admin.shows.index') }}">Reset</a></div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Directory</p><h3>Shows &amp; exhibitions</h3></div><span class="ma-panel__meta">20 per page</span></div>
    @if($shows->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching shows</h4><p>Create a show above or clear one or more filters.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-show-table">
                <thead><tr><th scope="col">Show</th><th scope="col">Maker</th><th scope="col">Dates</th><th scope="col">Status</th><th scope="col">Location</th><th scope="col">Artwork</th><th scope="col">Visibility</th><th scope="col">Action</th></tr></thead>
                <tbody>
                @foreach($shows as $show)
                    @php($showStatus = $show->status()->value)
                    <tr>
                        <td data-label="Show"><strong>{{ $show->name }}</strong><small>#{{ $show->id }} · added {{ $show->created_at?->format('M j, Y') }}</small></td>
                        <td data-label="Maker"><strong>{{ $show->maker?->name ?? 'Unknown' }}</strong><small>{{ $show->maker?->email }}</small></td>
                        <td data-label="Dates"><strong>{{ $show->start_date?->format('M j, Y') }}</strong><small>to {{ $show->end_date?->format('M j, Y') }}</small></td>
                        <td data-label="Status"><span class="ma-badge {{ $showStatus === 'current' ? 'ma-badge--success' : ($showStatus === 'upcoming' ? 'ma-badge--purple' : 'ma-badge--muted') }}">{{ ucfirst($showStatus) }}</span></td>
                        <td data-label="Location">{{ $show->location?->displayLabel() ?? $show->location_text ?? '—' }}</td>
                        <td data-label="Artwork">{{ $show->artworks_count }} work{{ $show->artworks_count === 1 ? '' : 's' }}</td>
                        <td data-label="Visibility"><span class="ma-badge {{ $show->is_visible ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ $show->is_visible ? 'Visible' : 'Hidden' }}</span></td>
                        <td data-label="Action"><a class="ma-text-link" href="{{ route('admin.shows.show', $show) }}">Manage</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator'=>$shows])
    @endif
</section>
@endsection

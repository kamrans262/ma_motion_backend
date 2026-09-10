@extends('admin.layouts.app')

@section('title', 'Manage Show')
@section('page_heading', 'Manage Show')

@section('content')
@php($showStatus = $show->status()->value)
<section class="ma-page-heading">
    <div><p class="ma-eyebrow">Show #{{ $show->id }}</p><h2>{{ $show->name }}</h2><p>Edit exhibition details, dates, venue, related artwork and public visibility.</p></div>
    <a class="ma-button ma-button--outline" href="{{ route('admin.shows.index') }}">Back to Shows</a>
</section>

@if($errors->any())
<div class="ma-alert ma-alert--error" role="alert"><div><strong>Please correct the following.</strong><ul class="ma-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<div class="ma-detail-grid ma-show-detail-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Details</p><h3>Edit show</h3></div><span class="ma-badge {{ $showStatus === 'current' ? 'ma-badge--success' : ($showStatus === 'upcoming' ? 'ma-badge--purple' : 'ma-badge--muted') }}">{{ ucfirst($showStatus) }}</span></div>
        <form class="ma-form" method="POST" action="{{ route('admin.shows.update', $show) }}">@csrf @method('PUT')
            <div class="ma-field"><label for="name">Show name</label><input id="name" name="name" value="{{ old('name', $show->name) }}" maxlength="180" required></div>
            <div class="ma-field"><label for="description">Description</label><textarea id="description" name="description" rows="7" maxlength="10000">{{ old('description', $show->description) }}</textarea></div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="location_id">Managed location</label><select id="location_id" name="location_id"><option value="">No structured location</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((string)old('location_id',$show->location_id)===(string)$location->id)>{{ $location->displayLabel() }}{{ $location->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
                <div class="ma-field"><label for="location_text">Public venue / location label</label><input id="location_text" name="location_text" value="{{ old('location_text',$show->location_text) }}" maxlength="180"></div>
            </div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" value="{{ old('start_date',$show->start_date?->toDateString()) }}" required></div>
                <div class="ma-field"><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" value="{{ old('end_date',$show->end_date?->toDateString()) }}" required></div>
            </div>
            <div class="ma-field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order',$show->sort_order) }}" required></div>

            <fieldset class="ma-show-artwork-fieldset">
                <legend>Related artwork</legend>
                <p class="ma-field__hint">Only artwork owned by {{ $show->maker?->name ?? 'this Maker' }} can be related to this show. The order below is preserved in the show relationship.</p>
                @php($selectedArtworkIds = array_map('intval', old('artwork_ids', $show->artworks->pluck('id')->all())))
                <div class="ma-show-artwork-picker">
                    @if($makerArtworks->isEmpty())
                        <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No Maker artwork yet</h4><p>This show can be saved without related artwork.</p></div>
                    @else
                        @foreach($makerArtworks as $artwork)
                            <label class="ma-show-artwork-option">
                                <input type="checkbox" name="artwork_ids[]" value="{{ $artwork->id }}" @checked(in_array((int)$artwork->id, $selectedArtworkIds, true))>
                                @if($artwork->primaryMedia)<img src="{{ $artwork->primaryMedia->url() }}" alt="">@else<span class="ma-show-artwork-option__placeholder" aria-hidden="true">◇</span>@endif
                                <span><strong>{{ $artwork->title }}</strong><small>#{{ $artwork->id }} · {{ ucfirst($artwork->moderation_status->value) }} · {{ $artwork->is_visible ? 'Visible' : 'Hidden' }}</small></span>
                            </label>
                        @endforeach
                    @endif
                </div>
            </fieldset>

            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save show</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Publishing</p><h3>Show status</h3></div><span class="ma-badge {{ $show->is_visible ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ $show->is_visible ? 'Visible' : 'Hidden' }}</span></div>
        <dl class="ma-definition-list">
            <div><dt>Maker</dt><dd>{{ $show->maker?->name ?? 'Unknown' }}</dd></div>
            <div><dt>Status</dt><dd>{{ ucfirst($showStatus) }} · calculated from dates</dd></div>
            <div><dt>Dates</dt><dd>{{ $show->start_date?->format('M j, Y') }} – {{ $show->end_date?->format('M j, Y') }}</dd></div>
            <div><dt>Location</dt><dd>{{ $show->location?->displayLabel() ?? $show->location_text ?? 'Not set' }}</dd></div>
            <div><dt>Related artwork</dt><dd>{{ $show->artworks->count() }}</dd></div>
            <div><dt>Created</dt><dd>{{ $show->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
        <div class="ma-stack-actions">
            <form method="POST" action="{{ route('admin.shows.toggle-visibility',$show) }}">@csrf @method('PATCH')<button class="ma-button ma-button--outline ma-button--full" type="submit">{{ $show->is_visible ? 'Hide show' : 'Unhide show' }}</button></form>
        </div>
        <div class="ma-callout"><strong>Public profile rule</strong><p>Only visible current or upcoming shows are exposed through the public Maker show API. Past shows remain available to the Maker and Admin for history.</p></div>
    </aside>
</div>

<section class="ma-panel ma-danger-zone-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Danger Zone</p><h3>Delete show</h3></div></div>
    <p class="ma-muted-copy">Deletion is soft so the show record is preserved safely. Related artwork itself is never deleted.</p>
    <form method="POST" action="{{ route('admin.shows.destroy',$show) }}" onsubmit="return confirm('Delete this show?')">@csrf @method('DELETE')<button class="ma-button ma-button--danger" type="submit">Delete show</button></form>
</section>
@endsection

@extends('admin.layouts.app')

@section('title', 'Artwork')
@section('page_heading', 'Artwork')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Artwork Management</p>
        <h2>Artwork</h2>
        <p>Review Maker uploads, search and filter the catalog, moderate submissions, control visibility and inspect artwork media.</p>
    </div>
    <span class="ma-pill">{{ number_format($artworks->total()) }} matching artwork</span>
</section>

<section class="ma-panel ma-panel--filters">
    <form class="ma-artwork-filter-grid" method="GET" action="{{ route('admin.artworks.index') }}">
        <div class="ma-field ma-artwork-filter-grid__search"><label for="search">Search</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title, description, Maker or location"></div>
        <div class="ma-field"><label for="moderation_status">Moderation</label><select id="moderation_status" name="moderation_status"><option value="">All statuses</option>@foreach($moderationStatuses as $value=>$label)<option value="{{ $value }}" @selected(($filters['moderation_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="visibility">Visibility</label><select id="visibility" name="visibility"><option value="">All visibility</option><option value="visible" @selected(($filters['visibility'] ?? '') === 'visible')>Visible</option><option value="hidden" @selected(($filters['visibility'] ?? '') === 'hidden')>Hidden</option></select></div>
        <div class="ma-field"><label for="maker_id">Maker</label><select id="maker_id" name="maker_id"><option value="">All Makers</option>@foreach($makers as $maker)<option value="{{ $maker->id }}" @selected((string)($filters['maker_id'] ?? '') === (string)$maker->id)>{{ $maker->name }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="artwork_type_id">Type</label><select id="artwork_type_id" name="artwork_type_id"><option value="">All types</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string)($filters['artwork_type_id'] ?? '') === (string)$type->id)>{{ $type->name }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="artwork_style_id">Style</label><select id="artwork_style_id" name="artwork_style_id"><option value="">All styles</option>@foreach($styles as $style)<option value="{{ $style->id }}" @selected((string)($filters['artwork_style_id'] ?? '') === (string)$style->id)>{{ $style->name }}</option>@endforeach</select></div>
        <div class="ma-field"><label for="location_id">Location</label><select id="location_id" name="location_id"><option value="">All locations</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((string)($filters['location_id'] ?? '') === (string)$location->id)>{{ $location->displayLabel() }}</option>@endforeach</select></div>
        <div class="ma-filter-actions"><button class="ma-button ma-button--primary" type="submit">Apply filters</button><a class="ma-button ma-button--outline" href="{{ route('admin.artworks.index') }}">Reset</a></div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Catalog</p><h3>Artwork directory</h3></div><span class="ma-panel__meta">20 per page</span></div>
    @if($artworks->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching artwork</h4><p>Try clearing one or more filters.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-artwork-table">
                <thead><tr><th scope="col">Artwork</th><th scope="col">Maker</th><th scope="col">Type / Style</th><th scope="col">Moderation</th><th scope="col">Visibility</th><th scope="col">Media</th><th scope="col">Action</th></tr></thead>
                <tbody>
                @foreach($artworks as $artwork)
                    <tr>
                        <td data-label="Artwork"><div class="ma-artwork-cell">@if($artwork->primaryMedia)<img class="ma-artwork-thumb" src="{{ $artwork->primaryMedia->url() }}" alt="">@else<span class="ma-artwork-thumb ma-artwork-thumb--placeholder" aria-hidden="true">◇</span>@endif<div><strong>{{ $artwork->title }}</strong><small>#{{ $artwork->id }} · {{ $artwork->created_at?->format('M j, Y') }}</small>@if($artwork->makerInfoSlots->isNotEmpty())<small>Maker Info · @foreach($artwork->makerInfoSlots as $placement)Content {{ $placement->slot }}{{ $loop->last ? '' : ', ' }}@endforeach</small>@endif</div></div></td>
                        <td data-label="Maker"><strong>{{ $artwork->maker?->name ?? 'Unknown' }}</strong><small>{{ $artwork->maker?->email }}</small></td>
                        <td data-label="Type / Style">{{ $artwork->type?->name ?? '—' }}<small>{{ $artwork->style?->name ?? 'No style' }}</small></td>
                        <td data-label="Moderation"><span class="ma-badge {{ $artwork->moderation_status->value === 'approved' ? 'ma-badge--success' : ($artwork->moderation_status->value === 'rejected' ? 'ma-badge--danger' : 'ma-badge--purple') }}">{{ ucfirst($artwork->moderation_status->value) }}</span></td>
                        <td data-label="Visibility"><span class="ma-badge {{ $artwork->is_visible ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ $artwork->is_visible ? 'Visible' : 'Hidden' }}</span></td>
                        <td data-label="Media">{{ $artwork->media_count }} image{{ $artwork->media_count === 1 ? '' : 's' }}</td>
                        <td data-label="Action"><a class="ma-text-link" href="{{ route('admin.artworks.show', $artwork) }}">Manage</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator'=>$artworks])
    @endif
</section>
@endsection

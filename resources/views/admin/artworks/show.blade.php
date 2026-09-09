@extends('admin.layouts.app')

@section('title', 'Manage Artwork')
@section('page_heading', 'Manage Artwork')

@section('content')
<section class="ma-page-heading">
    <div><p class="ma-eyebrow">Artwork #{{ $artwork->id }}</p><h2>{{ $artwork->title }}</h2><p>Manage artwork metadata, moderation, visibility and its uploaded image set.</p></div>
    <a class="ma-button ma-button--outline" href="{{ route('admin.artworks.index') }}">Back to Artwork</a>
</section>

@if($errors->any())
<div class="ma-alert ma-alert--error" role="alert"><div><strong>Please correct the following.</strong><ul class="ma-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<div class="ma-detail-grid ma-artwork-detail-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Metadata</p><h3>Edit artwork</h3></div><span class="ma-badge {{ $artwork->is_visible ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ $artwork->is_visible ? 'Visible' : 'Hidden' }}</span></div>
        <form class="ma-form" method="POST" action="{{ route('admin.artworks.update', $artwork) }}">@csrf @method('PUT')
            <div class="ma-field"><label for="title">Title</label><input id="title" name="title" value="{{ old('title', $artwork->title) }}" required maxlength="180"></div>
            <div class="ma-field"><label for="description">Description</label><textarea id="description" name="description" rows="8" maxlength="10000">{{ old('description', $artwork->description) }}</textarea></div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="artwork_type_id">Type</label><select id="artwork_type_id" name="artwork_type_id"><option value="">No type</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string)old('artwork_type_id',$artwork->artwork_type_id)===(string)$type->id)>{{ $type->name }}{{ $type->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
                <div class="ma-field"><label for="artwork_style_id">Style</label><select id="artwork_style_id" name="artwork_style_id"><option value="">No style</option>@foreach($styles as $style)<option value="{{ $style->id }}" @selected((string)old('artwork_style_id',$artwork->artwork_style_id)===(string)$style->id)>{{ $style->name }}{{ $style->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
            </div>
            <div class="ma-field"><label for="location_id">Managed location</label><select id="location_id" name="location_id"><option value="">No structured location</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((string)old('location_id',$artwork->location_id)===(string)$location->id)>{{ $location->displayLabel() }}{{ $location->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
            <div class="ma-form-grid"><div class="ma-field"><label for="location_text">Public location label</label><input id="location_text" name="location_text" value="{{ old('location_text',$artwork->location_text) }}" maxlength="180"></div><div class="ma-field"><label for="sort_order">Gallery sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order',$artwork->sort_order) }}" required></div></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save artwork</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Moderation</p><h3>Publishing control</h3></div><span class="ma-badge {{ $artwork->moderation_status->value === 'approved' ? 'ma-badge--success' : ($artwork->moderation_status->value === 'rejected' ? 'ma-badge--danger' : 'ma-badge--purple') }}">{{ ucfirst($artwork->moderation_status->value) }}</span></div>
        <dl class="ma-definition-list">
            <div><dt>Maker</dt><dd>{{ $artwork->maker?->name ?? 'Unknown' }}</dd></div>
            <div><dt>Location</dt><dd>{{ $artwork->location?->displayLabel() ?? $artwork->location_text ?? 'Not set' }}</dd></div>
            <div><dt>Images</dt><dd>{{ $artwork->media->count() }}</dd></div>
            <div><dt>Created</dt><dd>{{ $artwork->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
        @if($artwork->rejection_reason)<div class="ma-callout ma-callout--danger"><strong>Rejection reason</strong><p>{{ $artwork->rejection_reason }}</p></div>@endif
        <div class="ma-stack-actions">
            <form method="POST" action="{{ route('admin.artworks.moderate',$artwork) }}">@csrf @method('PATCH')<input type="hidden" name="moderation_status" value="approved"><button class="ma-button ma-button--primary ma-button--full" type="submit">Approve artwork</button></form>
            <form method="POST" action="{{ route('admin.artworks.moderate',$artwork) }}">@csrf @method('PATCH')<input type="hidden" name="moderation_status" value="pending"><button class="ma-button ma-button--outline ma-button--full" type="submit">Return to pending</button></form>
            <form class="ma-form" method="POST" action="{{ route('admin.artworks.moderate',$artwork) }}">@csrf @method('PATCH')<input type="hidden" name="moderation_status" value="rejected"><div class="ma-field"><label for="rejection_reason">Reject with reason</label><textarea id="rejection_reason" name="rejection_reason" maxlength="1000" placeholder="Explain what the Maker should correct">{{ old('rejection_reason') }}</textarea></div><button class="ma-button ma-button--danger ma-button--full" type="submit">Reject artwork</button></form>
            <form method="POST" action="{{ route('admin.artworks.toggle-visibility',$artwork) }}">@csrf @method('PATCH')<button class="ma-button ma-button--outline ma-button--full" type="submit">{{ $artwork->is_visible ? 'Hide artwork' : 'Unhide artwork' }}</button></form>
        </div>
    </aside>
</div>

<section class="ma-panel ma-artwork-media-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Media</p><h3>Artwork images</h3></div><span class="ma-panel__meta">JPG · PNG · WebP</span></div>
    <div class="ma-artwork-media-grid">
        @foreach($artwork->media as $media)
            <article class="ma-artwork-media-card">
                <div class="ma-artwork-media-card__visual"><img src="{{ $media->url() }}" alt="{{ $media->alt_text ?? $artwork->title }}">@if($media->is_primary)<span class="ma-artwork-primary-label">Primary</span>@endif</div>
                <div class="ma-artwork-media-card__body"><strong>{{ $media->width }} × {{ $media->height }}</strong><small>{{ strtoupper(pathinfo($media->path, PATHINFO_EXTENSION)) }} · {{ number_format($media->size_bytes / 1024) }} KB</small><div class="ma-table-actions">@unless($media->is_primary)<form method="POST" action="{{ route('admin.artworks.media.primary',[$artwork,$media]) }}">@csrf @method('PATCH')<button class="ma-button ma-button--outline ma-button--compact" type="submit">Make primary</button></form>@endunless<form method="POST" action="{{ route('admin.artworks.media.destroy',[$artwork,$media]) }}" onsubmit="return confirm('Remove this image from the artwork?')">@csrf @method('DELETE')<button class="ma-button ma-button--danger ma-button--compact" type="submit" @disabled($artwork->media->count() <= 1)>Remove</button></form></div></div>
            </article>
        @endforeach
    </div>
</section>

<section class="ma-panel ma-danger-zone-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Danger Zone</p><h3>Delete artwork</h3></div></div><p class="ma-muted-copy">Deletion is soft and preserves the artwork record for safe historical recovery. Media files are retained while the artwork is soft-deleted.</p>
    <form method="POST" action="{{ route('admin.artworks.destroy',$artwork) }}" onsubmit="return confirm('Delete this artwork?')">@csrf @method('DELETE')<button class="ma-button ma-button--danger" type="submit">Delete artwork</button></form>
</section>
@endsection

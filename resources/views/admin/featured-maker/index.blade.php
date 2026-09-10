@extends('admin.layouts.app', ['title' => 'Featured Maker'])

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Post-login Spotlight</p>
        <h2>Featured Maker</h2>
        <p>Select the Maker shown to registered users after login and control the supporting spotlight content.</p>
    </div>
    <span class="ma-pill">{{ $setting->exists && $setting->is_active ? 'Published' : 'Not published' }}</span>
</section>

<div class="ma-featured-maker-grid">
    <section class="ma-panel">
        <div class="ma-panel__header">
            <div><p class="ma-eyebrow">Selection</p><h3>Configure spotlight</h3></div>
            <span class="ma-panel__meta">One post-login slot</span>
        </div>

        <form class="ma-featured-maker-selector" method="GET" action="{{ route('admin.featured-maker.index') }}">
            <div class="ma-field">
                <label for="maker_selector">Maker</label>
                <select id="maker_selector" name="maker_id" required>
                    <option value="">Choose an active Maker</option>
                    @foreach ($makers as $maker)
                        <option value="{{ $maker->id }}" @selected($selectedMaker?->id === $maker->id)>
                            {{ $maker->name }} · {{ $maker->saves_count }} saves · {{ $maker->public_artworks_count }} public artwork
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="ma-button ma-button--outline" type="submit">Load Maker</button>
        </form>

        @if ($selectedMaker)
            <div class="ma-featured-maker-selected">
                <div>
                    <strong>{{ $selectedMaker->name }}</strong>
                    <small>{{ $selectedMaker->email }} · {{ $selectedMaker->makerProfile?->location_text ?: 'Location not set' }}</small>
                </div>
                <span class="ma-badge ma-badge--purple">{{ $selectedMaker->saves_count }} saves</span>
            </div>

            <form class="ma-form" method="POST" action="{{ route('admin.featured-maker.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="maker_id" value="{{ $selectedMaker->id }}">

                <div class="ma-form-grid">
                    <div class="ma-field">
                        <label for="eyebrow">Eyebrow</label>
                        <input id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $setting->eyebrow ?: 'Featured Maker') }}" maxlength="80" placeholder="Featured Maker">
                    </div>
                    <div class="ma-field">
                        <label for="headline">Headline</label>
                        <input id="headline" name="headline" value="{{ old('headline', $setting->maker_id === $selectedMaker->id ? $setting->headline : '') }}" maxlength="180" placeholder="Defaults to Maker name">
                    </div>
                </div>

                <div class="ma-field">
                    <label for="description">Spotlight description</label>
                    <textarea id="description" name="description" rows="6" maxlength="2000" placeholder="Optional short introduction for the Featured Maker page">{{ old('description', $setting->maker_id === $selectedMaker->id ? $setting->description : '') }}</textarea>
                    <small class="ma-field__hint">Keep this concise. The full Maker bio remains available on the Maker profile.</small>
                </div>

                <div class="ma-field">
                    <label for="featured_artwork_id">Spotlight artwork</label>
                    <select id="featured_artwork_id" name="featured_artwork_id">
                        <option value="">Automatic · use Maker gallery</option>
                        @foreach ($artworks as $artwork)
                            <option value="{{ $artwork->id }}" @selected((string) old('featured_artwork_id', $setting->maker_id === $selectedMaker->id ? $setting->featured_artwork_id : '') === (string) $artwork->id)>{{ $artwork->title }}</option>
                        @endforeach
                    </select>
                    <small class="ma-field__hint">Only approved and visible artwork owned by this Maker can be selected.</small>
                </div>

                <label class="ma-checkbox">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $setting->maker_id === $selectedMaker->id && $setting->is_active))>
                    <span>Publish this Featured Maker after registered-user login</span>
                </label>

                <div class="ma-form-actions">
                    <button class="ma-button ma-button--primary" type="submit">Save Featured Maker</button>
                </div>
            </form>
        @else
            <div class="ma-empty-state ma-empty-state--compact">
                <span class="ma-empty-state__icon" aria-hidden="true">◇</span>
                <h4>Select an active Maker</h4>
                <p>Choose a Maker above to configure the post-login spotlight.</p>
            </div>
        @endif
    </section>

    <aside class="ma-panel ma-featured-maker-preview">
        @php
            $previewMaker = $setting->maker;
            $previewArtwork = $setting->featuredArtwork;
            $previewMedia = $previewArtwork?->primaryMedia;
        @endphp

        <div class="ma-featured-maker-preview__visual">
            @if ($previewMedia)
                <img src="{{ $previewMedia->url() }}" alt="{{ $previewMedia->alt_text ?: ($previewArtwork?->title ?: 'Featured Maker artwork') }}">
            @else
                <div class="ma-featured-maker-preview__placeholder" aria-hidden="true">MA</div>
            @endif
            <span class="ma-badge {{ $setting->exists && $setting->is_active && $previewMaker?->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }} ma-featured-maker-preview__status">
                {{ $setting->exists && $setting->is_active && $previewMaker?->isActive() ? 'Published' : 'Draft / unavailable' }}
            </span>
        </div>

        <div class="ma-featured-maker-preview__body">
            <div>
                <p class="ma-eyebrow">{{ $setting->eyebrow ?: 'Featured Maker' }}</p>
                <h3>{{ $setting->headline ?: ($previewMaker?->name ?: 'No Featured Maker selected') }}</h3>
            </div>
            <p>{{ $setting->description ?: ($previewMaker?->makerProfile?->bio ?: 'The saved Featured Maker introduction will appear here.') }}</p>

            @if ($previewMaker)
                <div class="ma-featured-maker-preview__meta">
                    <span>{{ $previewMaker->makerProfile?->location_text ?: 'Location not set' }}</span>
                    <span>·</span>
                    <span>{{ $previewMaker->saves_count ?? 0 }} saves</span>
                </div>
            @endif

            <div class="ma-callout">
                <strong>Registered-user experience</strong>
                <p>The mobile app can request this spotlight after login. If the setting is unpublished or the Maker becomes inactive, the API safely returns no Featured Maker.</p>
            </div>
        </div>
    </aside>
</div>

@if ($setting->exists)
<section class="ma-panel ma-danger-zone-panel ma-featured-maker-danger">
    <div class="ma-panel__header">
        <div><p class="ma-eyebrow">Remove Spotlight</p><h3>Clear Featured Maker</h3></div>
    </div>
    <p class="ma-muted-copy">This removes the post-login Featured Maker configuration. It does not delete the Maker, artwork, profile, shows, or save statistics.</p>
    <form method="POST" action="{{ route('admin.featured-maker.destroy') }}" onsubmit="return confirm('Remove the current Featured Maker configuration?')">
        @csrf
        @method('DELETE')
        <button class="ma-button ma-button--danger" type="submit">Remove Featured Maker</button>
    </form>
</section>
@endif
@endsection

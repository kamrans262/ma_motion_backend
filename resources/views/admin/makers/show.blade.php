@extends('admin.layouts.app')

@section('title', 'Manage Maker')
@section('page_heading', 'Manage Maker')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Maker #{{ $maker->id }}</p>
        <h2>{{ $maker->name }}</h2>
        <p>Manage the Maker account, public info settings, salon image, content slots, and aggregate profile-save statistics used across MA Motion.</p>
    </div>
    <a class="ma-button ma-button--outline" href="{{ route('admin.makers.index') }}">Back to Makers</a>
</section>

@if ($errors->any())
    <div class="ma-alert ma-alert--error" role="alert"><div><strong>Please correct the following.</strong><ul class="ma-error-list">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<div class="ma-detail-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Maker Profile</p><h3>Edit profile</h3></div><span class="ma-badge {{ $maker->isActive() ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ ucfirst($maker->status->value) }}</span></div>
        <form class="ma-form" method="POST" action="{{ route('admin.makers.update', $maker) }}">
            @csrf @method('PUT')
            <div class="ma-form-grid">
                <div class="ma-field"><label for="name">Maker name</label><input id="name" name="name" type="text" value="{{ old('name', $maker->name) }}" required maxlength="120"></div>
                <div class="ma-field"><label for="email">Account email</label><input id="email" name="email" type="email" value="{{ old('email', $maker->email) }}" required maxlength="255"></div>
            </div>
            <div class="ma-field"><label for="status">Account status</label><select id="status" name="status" required>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $maker->status->value) === $value)>{{ $label }}</option>@endforeach</select><small class="ma-field__hint">Deactivation revokes active mobile API tokens.</small></div>
            <div class="ma-field"><label for="location_id">Managed location</label><select id="location_id" name="location_id"><option value="">No structured location</option>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected((string) old('location_id', $maker->makerProfile?->location_id) === (string) $location->id)>{{ $location->displayLabel() }}{{ $location->is_active ? '' : ' · Inactive' }}</option>@endforeach</select></div>
            <div class="ma-field"><label for="location_text">Profile location label</label><input id="location_text" name="location_text" type="text" value="{{ old('location_text', $maker->makerProfile?->location_text) }}" maxlength="180" placeholder="Optional public-facing location label"></div>
            <div class="ma-field"><label for="bio">Bio</label><textarea id="bio" name="bio" rows="8" maxlength="5000" placeholder="Maker biography">{{ old('bio', $maker->makerProfile?->bio) }}</textarea></div>

            <div class="ma-form-grid">
                <div class="ma-field"><label for="website_url">Website</label><input id="website_url" name="website_url" type="url" value="{{ old('website_url', $maker->makerProfile?->website_url) }}" maxlength="2048" placeholder="https://example.com"></div>
                <div class="ma-field"><label for="contact_email">Public contact email</label><input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $maker->makerProfile?->contact_email) }}" maxlength="255" placeholder="contact@example.com"></div>
            </div>

            <div class="ma-form-grid">
                <label class="ma-field">
                    <span>Show website on info page</span>
                    <input type="hidden" name="show_website_on_info_page" value="0">
                    <input type="checkbox" name="show_website_on_info_page" value="1" @checked((bool) old('show_website_on_info_page', $maker->makerProfile?->show_website_on_info_page ?? true))>
                </label>
                <label class="ma-field">
                    <span>Show email on info page</span>
                    <input type="hidden" name="show_email_on_info_page" value="0">
                    <input type="checkbox" name="show_email_on_info_page" value="1" @checked((bool) old('show_email_on_info_page', $maker->makerProfile?->show_email_on_info_page ?? false))>
                </label>
                <label class="ma-field">
                    <span>Show shows on info page</span>
                    <input type="hidden" name="show_shows_on_info_page" value="0">
                    <input type="checkbox" name="show_shows_on_info_page" value="1" @checked((bool) old('show_shows_on_info_page', $maker->makerProfile?->show_shows_on_info_page ?? true))>
                </label>
            </div>

            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save Maker profile</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Profile & Statistics</p><h3>Current details</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Bio</dt><dd>{{ filled($maker->makerProfile?->bio) ? 'Added' : 'Not added' }}</dd></div>
            <div><dt>Managed location</dt><dd>{{ $maker->makerProfile?->location?->displayLabel() ?? 'Not linked' }}</dd></div>
            <div><dt>Location label</dt><dd>{{ $maker->makerProfile?->location_text ?? 'Not set' }}</dd></div>
            <div><dt>Hearts / Saves</dt><dd><strong>{{ number_format($maker->saves_count) }}</strong><br><a class="ma-text-link" href="{{ route('admin.saves.index', ['maker_id' => $maker->id]) }}">View save details</a></dd></div>
            <div><dt>Joined</dt><dd>{{ $maker->created_at?->format('M j, Y') }}</dd></div>
        </dl>
        <div class="ma-callout"><strong>Privacy boundary</strong><p>Makers receive only their aggregate profile-saved count. Public website, email, and show visibility follows the Maker's saved settings.</p></div>
        <a class="ma-button ma-button--outline ma-button--full" href="{{ route('admin.users.show', $maker) }}">Open User Account</a>
    </aside>
</div>

<section class="ma-panel ma-artwork-media-panel">
    <div class="ma-panel__header">
        <div><p class="ma-eyebrow">Salon / Profile Image</p><h3>Maker salon image</h3></div>
    </div>

    <div class="ma-artwork-media-grid">
        <article class="ma-artwork-media-card">
            <div class="ma-artwork-media-card__visual">
                @if ($maker->makerProfile?->profile_image_path)
                    <img src="{{ asset('storage/'.$maker->makerProfile->profile_image_path) }}" alt="{{ $maker->name }} salon image">
                @else
                    <div class="ma-artwork-thumb--placeholder">No salon image uploaded</div>
                @endif
            </div>
            <div class="ma-artwork-media-card__body">
                <strong>Salon image</strong>
                <small>{{ $maker->makerProfile?->profile_image_path ? 'Uploaded' : 'Not uploaded' }}</small>
                <form class="ma-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.makers.profile-image.update', $maker) }}">
                    @csrf
                    <div class="ma-field"><label for="profile_image">Replace image</label><input id="profile_image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required></div>
                    <button class="ma-button ma-button--primary" type="submit">Upload salon image</button>
                </form>
                @if ($maker->makerProfile?->profile_image_path)
                    <form method="POST" action="{{ route('admin.makers.profile-image.destroy', $maker) }}">
                        @csrf @method('DELETE')
                        <button class="ma-button ma-button--outline" type="submit">Remove salon image</button>
                    </form>
                @endif
            </div>
        </article>
    </div>
</section>

<section class="ma-panel ma-artwork-media-panel">
    <div class="ma-panel__header">
        <div>
            <p class="ma-eyebrow">Maker Info Content</p>
            <h3>Salon content & featured artwork</h3>
        </div>
        <span class="ma-panel__meta">Content 1 + Artwork 2 / 3 / 4</span>
    </div>

    @php($salonContent = $maker->makerProfile?->contents?->firstWhere('slot', 1))

    <div class="ma-artwork-media-grid">
        <article class="ma-artwork-media-card">
            <div class="ma-artwork-media-card__visual">
                @if ($salonContent)
                    @if ($salonContent->kind === 'video')
                        <video controls preload="metadata" style="width:100%;height:100%;object-fit:cover">
                            <source src="{{ asset('storage/'.$salonContent->path) }}" type="{{ $salonContent->mime_type }}">
                        </video>
                    @else
                        <img src="{{ asset('storage/'.$salonContent->path) }}" alt="Content 1 salon content">
                    @endif
                @else
                    <div class="ma-artwork-thumb--placeholder">Content 1 not uploaded</div>
                @endif
            </div>
            <div class="ma-artwork-media-card__body">
                <strong>Content 1 · Salon / profile media</strong>
                <small>{{ $salonContent ? ucfirst($salonContent->kind) : 'Empty slot' }}</small>

                <form class="ma-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.makers.content.store', ['maker' => $maker, 'slot' => 1]) }}">
                    @csrf
                    <div class="ma-field">
                        <label for="content_1_media">{{ $salonContent ? 'Replace media (optional)' : 'Upload media' }}</label>
                        <input id="content_1_media" name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" @required(! $salonContent)>
                    </div>
                    <div class="ma-field">
                        <label for="content_1_caption">Caption</label>
                        <textarea id="content_1_caption" name="caption" rows="3" maxlength="1000">{{ old('caption', $salonContent?->caption) }}</textarea>
                    </div>
                    <button class="ma-button ma-button--primary" type="submit">{{ $salonContent ? 'Save Content 1' : 'Create Content 1' }}</button>
                </form>

                @if ($salonContent)
                    <form method="POST" action="{{ route('admin.makers.content.destroy', ['maker' => $maker, 'slot' => 1]) }}">
                        @csrf @method('DELETE')
                        <button class="ma-button ma-button--outline" type="submit">Remove Content 1</button>
                    </form>
                @endif
            </div>
        </article>

        @for ($slot = 2; $slot <= 4; $slot++)
            @php($placement = $maker->makerProfile?->artworkSlots?->firstWhere('slot', $slot))
            @php($artwork = $placement?->artwork)

            <article class="ma-artwork-media-card">
                <div class="ma-artwork-media-card__visual">
                    @if ($artwork?->primaryMedia)
                        @if($artwork->primaryMedia->kind === 'video')<video src="{{ $artwork->primaryMedia->url() }}" controls preload="metadata" playsinline style="width:100%;height:100%;object-fit:contain"></video>@else<img src="{{ $artwork->primaryMedia->url() }}" alt="{{ $artwork->title }}">@endif
                    @else
                        <div class="ma-artwork-thumb--placeholder">Content {{ $slot }} artwork not assigned</div>
                    @endif
                </div>

                <div class="ma-artwork-media-card__body">
                    <strong>Content {{ $slot }} · Artwork</strong>

                    @if ($artwork)
                        <small>
                            #{{ $artwork->id }} · {{ $artwork->title }}
                            · {{ ucfirst($artwork->moderation_status->value) }}
                            · {{ $artwork->is_visible ? 'Visible' : 'Hidden' }}
                        </small>
                        <p class="ma-muted-copy">This slot references the canonical Artwork record. Its image or video, metadata, moderation and visibility are managed in Artwork Management.</p>
                        <a class="ma-button ma-button--outline" href="{{ route('admin.artworks.show', $artwork) }}">Manage artwork</a>
                    @else
                        <small>Empty artwork slot</small>
                        <p class="ma-muted-copy">Content {{ $slot }} is reserved for a real Artwork record. It cannot use separate profile-content media.</p>
                        <a class="ma-button ma-button--outline" href="{{ route('admin.artworks.index', ['maker_id' => $maker->id]) }}">View Maker artwork</a>
                    @endif
                </div>
            </article>
        @endfor
    </div>
</section>
@endsection

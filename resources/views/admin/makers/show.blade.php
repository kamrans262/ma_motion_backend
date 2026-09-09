@extends('admin.layouts.app')

@section('title', 'Manage Maker')
@section('page_heading', 'Manage Maker')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Maker #{{ $maker->id }}</p>
        <h2>{{ $maker->name }}</h2>
        <p>Manage the Maker account and connect the profile to the structured location directory used by MA Motion discovery.</p>
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
                <div class="ma-field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $maker->email) }}" required maxlength="255"></div>
            </div>
            <div class="ma-field"><label for="status">Account status</label><select id="status" name="status" required>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $maker->status->value) === $value)>{{ $label }}</option>@endforeach</select><small class="ma-field__hint">Deactivation revokes active mobile API tokens.</small></div>
            <div class="ma-field"><label for="location_id">Managed location</label><select id="location_id" name="location_id"><option value="">No structured location</option>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected((string) old('location_id', $maker->makerProfile?->location_id) === (string) $location->id)>{{ $location->displayLabel() }}{{ $location->is_active ? '' : ' · Inactive' }}</option>@endforeach</select><small class="ma-field__hint">This structured location supports future city, ZIP/postal and radius discovery.</small></div>
            <div class="ma-field"><label for="location_text">Profile location label</label><input id="location_text" name="location_text" type="text" value="{{ old('location_text', $maker->makerProfile?->location_text) }}" maxlength="180" placeholder="Optional public-facing location label"></div>
            <div class="ma-field"><label for="bio">Bio</label><textarea id="bio" name="bio" rows="8" maxlength="5000" placeholder="Maker biography">{{ old('bio', $maker->makerProfile?->bio) }}</textarea></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save Maker profile</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Profile Readiness</p><h3>Current details</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Bio</dt><dd>{{ filled($maker->makerProfile?->bio) ? 'Added' : 'Not added' }}</dd></div>
            <div><dt>Managed location</dt><dd>{{ $maker->makerProfile?->location?->displayLabel() ?? 'Not linked' }}</dd></div>
            <div><dt>Location label</dt><dd>{{ $maker->makerProfile?->location_text ?? 'Not set' }}</dd></div>
            <div><dt>Profile image</dt><dd>{{ $maker->makerProfile?->profile_image_path ? 'Available' : 'Not uploaded' }}</dd></div>
            <div><dt>Joined</dt><dd>{{ $maker->created_at?->format('M j, Y') }}</dd></div>
        </dl>
        <div class="ma-callout"><strong>Location foundation</strong><p>Coordinates are stored centrally so later discovery can add radius search without changing Maker profile data again.</p></div>
        <a class="ma-button ma-button--outline ma-button--full" href="{{ route('admin.users.show', $maker) }}">Open User Account</a>
    </aside>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Manage User')
@section('page_heading', 'Manage User')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">User #{{ $user->id }}</p>
        <h2>{{ $user->name }}</h2>
        <p>Update account details and access status. Role changes are intentionally not exposed here to avoid accidental permission escalation.</p>
    </div>
    <a class="ma-button ma-button--outline" href="{{ route('admin.users.index') }}">Back to Users</a>
</section>

@if ($errors->any())
    <div class="ma-alert ma-alert--error" role="alert">
        <div>
            <strong>Please correct the following.</strong>
            <ul class="ma-error-list">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<div class="ma-detail-grid">
    <section class="ma-panel">
        <div class="ma-panel__header">
            <div><p class="ma-eyebrow">Account</p><h3>Edit details</h3></div>
            <span class="ma-badge ma-badge--purple">{{ ucfirst($user->role->value) }}</span>
        </div>

        <form class="ma-form" method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="ma-field">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="120">
            </div>
            <div class="ma-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255">
            </div>
            <div class="ma-field">
                <label for="status">Account status</label>
                <select id="status" name="status" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $user->status->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <small class="ma-field__hint">Deactivating an account immediately revokes its mobile API tokens.</small>
            </div>
            <div class="ma-form-actions">
                <button class="ma-button ma-button--primary" type="submit">Save changes</button>
            </div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Overview</p><h3>Account information</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Role</dt><dd>{{ ucfirst($user->role->value) }}</dd></div>
            <div><dt>Status</dt><dd>{{ ucfirst($user->status->value) }}</dd></div>
            <div><dt>Email verified</dt><dd>{{ $user->email_verified_at ? 'Yes' : 'No' }}</dd></div>
            @if ($user->role->value === 'appreciator')
                <div><dt>Location</dt><dd>{{ $user->appreciatorProfile?->location_text ?? 'Not set' }}</dd></div>
            @endif
            <div><dt>Last login</dt><dd>{{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}</dd></div>
            <div><dt>Joined</dt><dd>{{ $user->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>

        @if ($user->role->value === 'maker')
            <a class="ma-button ma-button--outline ma-button--full" href="{{ route('admin.makers.show', $user) }}">Open Maker Profile</a>
        @endif

        <div class="ma-danger-zone">
            <p class="ma-eyebrow">Danger Zone</p>
            @if ($user->role->value === 'admin')
                <p class="ma-muted-copy">Administrator accounts cannot be deleted from User Management.</p>
            @else
                <p class="ma-muted-copy">Deleting this account permanently removes the user and related account/profile records.</p>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user permanently? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="ma-button ma-button--danger ma-button--full" type="submit">Delete user</button>
                </form>
            @endif
        </div>
    </aside>
</div>

@if ($user->role->value === 'appreciator')
<section class="ma-panel">
    <div class="ma-panel__header">
        <div>
            <p class="ma-eyebrow">Saved Artwork</p>
            <h3>Saved artworks</h3>
        </div>
        <span class="ma-panel__meta">{{ number_format($user->savedArtworks->count()) }} saved</span>
    </div>

    @if ($user->savedArtworks->isEmpty())
        <div class="ma-empty-state ma-empty-state--compact">
            <span class="ma-empty-state__icon" aria-hidden="true">◇</span>
            <h4>No saved artwork</h4>
            <p>This Appreciator has not saved any artwork yet.</p>
        </div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table">
                <thead>
                    <tr>
                        <th scope="col">Artwork</th>
                        <th scope="col">Maker</th>
                        <th scope="col">Saved at</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->savedArtworks->sortByDesc(fn ($artwork) => $artwork->pivot?->created_at) as $artwork)
                        <tr>
                            <td data-label="Artwork"><strong>{{ $artwork->title }}</strong></td>
                            <td data-label="Maker">{{ $artwork->maker?->name ?? 'Unknown' }}</td>
                            <td data-label="Saved at">{{ $artwork->pivot?->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td data-label="Action"><a class="ma-text-link" href="{{ route('admin.artworks.show', $artwork) }}">Manage</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endif
@endsection

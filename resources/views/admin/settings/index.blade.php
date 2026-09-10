@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page_heading', 'Settings')

@section('content')
<section class="ma-page-heading">
    <div><p class="ma-eyebrow">Administrator Account</p><h2>Admin Profile & Settings</h2><p>Manage your administrator identity and password without changing platform data directly in the database.</p></div>
    <span class="ma-pill">{{ $admin->status?->value ?? 'active' }}</span>
</section>

<div class="ma-settings-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Profile</p><h3>Administrator details</h3></div></div>
        <form class="ma-form" method="POST" action="{{ route('admin.settings.profile.update') }}">
            @csrf @method('PUT')
            <div class="ma-field"><label for="name">Name</label><input id="name" name="name" value="{{ old('name', $admin->name) }}" maxlength="120" required></div>
            <div class="ma-field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" maxlength="255" required></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save profile</button></div>
        </form>
    </section>

    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Security</p><h3>Change password</h3></div></div>
        <form class="ma-form" method="POST" action="{{ route('admin.settings.password.update') }}">
            @csrf @method('PUT')
            <div class="ma-field"><label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
            <div class="ma-field"><label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required><small class="ma-field__hint">At least 8 characters with upper/lowercase letters and a number.</small></div>
            <div class="ma-field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Change password</button></div>
        </form>
    </section>

    <aside class="ma-panel ma-settings-account-card">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Account</p><h3>Current session</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>Role</dt><dd>Administrator</dd></div>
            <div><dt>Status</dt><dd>{{ ucfirst($admin->status?->value ?? 'active') }}</dd></div>
            <div><dt>Last login</dt><dd>{{ $admin->last_login_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</dd></div>
            <div><dt>Member since</dt><dd>{{ $admin->created_at?->format('M j, Y') }}</dd></div>
        </dl>
        <div class="ma-callout"><strong>Password safety</strong><p>Changing the password revokes any API tokens belonging to this Admin account while keeping the current Admin browser session active.</p></div>
    </aside>
</div>
@endsection

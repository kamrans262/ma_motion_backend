@extends('admin.layouts.guest')

@section('title', 'Sign in')

@section('content')
<div class="ma-auth-grid">
    <section class="ma-auth-intro" aria-labelledby="admin-login-title">
        <div class="ma-brand ma-brand--large">
            <span class="ma-brand__mark" aria-hidden="true">MA</span>
            <span>
                <strong>MA Motion</strong>
                <small>Administrator workspace</small>
            </span>
        </div>

        <div class="ma-auth-intro__copy">
            <p class="ma-eyebrow">Motion Intro Integration</p>
            <h1 id="admin-login-title">Manage the platform with one consistent creative system.</h1>
            <p>Secure access for MA Motion administrators. Makers, artwork, shows, discovery, featured content and analytics will be managed here as their modules are enabled.</p>
        </div>
    </section>

    <section class="ma-auth-card" aria-label="Administrator sign in">
        <div class="ma-auth-card__header">
            <p class="ma-eyebrow">Admin Panel</p>
            <h2>Welcome back</h2>
            <p>Sign in with an active administrator account.</p>
        </div>

        @if ($errors->any())
            <div class="ma-alert ma-alert--error" role="alert">
                <strong>Sign in failed.</strong>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form class="ma-form" method="POST" action="{{ route('admin.login.store') }}" novalidate>
            @csrf

            <div class="ma-field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required autofocus>
                @error('email')<span class="ma-field__error">{{ $message }}</span>@enderror
            </div>

            <div class="ma-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password')<span class="ma-field__error">{{ $message }}</span>@enderror
            </div>

            <label class="ma-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Keep me signed in on this device</span>
            </label>

            <button class="ma-button ma-button--primary ma-button--full" type="submit">Sign in to Admin</button>
        </form>

        <p class="ma-auth-card__security">Protected by Laravel session authentication, CSRF protection and login rate limiting.</p>
    </section>
</div>
@endsection

<header class="ma-topbar">
    <div class="ma-topbar__left">
        <button class="ma-icon-button ma-menu-button" type="button" data-sidebar-open aria-label="Open navigation" aria-expanded="false">
            <span aria-hidden="true">☰</span>
        </button>
        <div>
            <p class="ma-eyebrow">Administration</p>
            <h1 class="ma-topbar__title">@yield('page_heading', 'Dashboard')</h1>
        </div>
    </div>

    <div class="ma-topbar__right">
        <div class="ma-admin-identity">
            <span class="ma-admin-identity__avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="ma-admin-identity__copy">
                <strong>{{ auth()->user()->name }}</strong>
                <small>Administrator</small>
            </span>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="ma-button ma-button--outline ma-button--compact" type="submit">Logout</button>
        </form>
    </div>
</header>

<aside class="ma-sidebar" data-sidebar aria-label="Admin navigation">
    <div class="ma-sidebar__header">
        <a class="ma-brand" href="{{ route('admin.dashboard') }}" aria-label="MA Motion admin dashboard">
            <span class="ma-brand__mark" aria-hidden="true">MA</span>
            <span>
                <strong>MA Motion</strong>
                <small>Admin Panel</small>
            </span>
        </a>
        <button class="ma-icon-button ma-sidebar__close" type="button" data-sidebar-close aria-label="Close navigation">×</button>
    </div>

    <nav class="ma-nav">
        <p class="ma-nav__label">Overview</p>
        <a class="ma-nav__item {{ request()->routeIs('admin.dashboard') ? 'ma-nav__item--active' : '' }}"
           href="{{ route('admin.dashboard') }}"
           @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Dashboard</span>
        </a>

        <p class="ma-nav__label">Platform</p>
        <a class="ma-nav__item {{ request()->routeIs('admin.users.*') ? 'ma-nav__item--active' : '' }}"
           href="{{ route('admin.users.index') }}"
           @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Users</span>
        </a>
        <a class="ma-nav__item {{ request()->routeIs('admin.makers.*') ? 'ma-nav__item--active' : '' }}"
           href="{{ route('admin.makers.index') }}"
           @if(request()->routeIs('admin.makers.*')) aria-current="page" @endif>
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Makers</span>
        </a>
        <a class="ma-nav__item {{ request()->routeIs('admin.saves.*') ? 'ma-nav__item--active' : '' }}"
           href="{{ route('admin.saves.index') }}"
           @if(request()->routeIs('admin.saves.*')) aria-current="page" @endif>
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Hearts & Saves</span>
        </a>

        <a class="ma-nav__item {{ request()->routeIs('admin.artworks.*') ? 'ma-nav__item--active' : '' }}" href="{{ route('admin.artworks.index') }}" @if(request()->routeIs('admin.artworks.*')) aria-current="page" @endif><span class="ma-nav__icon" aria-hidden="true">◆</span><span>Artwork</span></a>
        <a class="ma-nav__item {{ request()->routeIs('admin.shows.*') ? 'ma-nav__item--active' : '' }}" href="{{ route('admin.shows.index') }}" @if(request()->routeIs('admin.shows.*')) aria-current="page" @endif><span class="ma-nav__icon" aria-hidden="true">◆</span><span>Shows</span></a>
        <a class="ma-nav__item {{ request()->routeIs('admin.types.*') ? 'ma-nav__item--active' : '' }}" href="{{ route('admin.types.index') }}" @if(request()->routeIs('admin.types.*')) aria-current="page" @endif><span class="ma-nav__icon" aria-hidden="true">◆</span><span>Types</span></a>
        <a class="ma-nav__item {{ request()->routeIs('admin.styles.*') ? 'ma-nav__item--active' : '' }}" href="{{ route('admin.styles.index') }}" @if(request()->routeIs('admin.styles.*')) aria-current="page" @endif><span class="ma-nav__icon" aria-hidden="true">◆</span><span>Styles</span></a>
        <a class="ma-nav__item {{ request()->routeIs('admin.locations.*') ? 'ma-nav__item--active' : '' }}" href="{{ route('admin.locations.index') }}" @if(request()->routeIs('admin.locations.*')) aria-current="page" @endif><span class="ma-nav__icon" aria-hidden="true">◆</span><span>Locations</span></a>
        <a class="ma-nav__item {{ request()->routeIs('admin.featured-maker.*') ? 'ma-nav__item--active' : '' }}"
           href="{{ route('admin.featured-maker.index') }}"
           @if(request()->routeIs('admin.featured-maker.*')) aria-current="page" @endif>
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Featured Maker</span>
        </a>
        <span class="ma-nav__item ma-nav__item--disabled" aria-disabled="true"><span class="ma-nav__icon" aria-hidden="true">◇</span><span>Notifications</span><span class="ma-nav__soon">Soon</span></span>

        <p class="ma-nav__label">System</p>
        @foreach (['Content', 'Analytics', 'Settings', 'Audit Logs'] as $item)
            <span class="ma-nav__item ma-nav__item--disabled" aria-disabled="true">
                <span class="ma-nav__icon" aria-hidden="true">◇</span>
                <span>{{ $item }}</span>
                <span class="ma-nav__soon">Soon</span>
            </span>
        @endforeach
    </nav>

    <div class="ma-sidebar__footer">
        <span class="ma-status-dot" aria-hidden="true"></span>
        <span>Local development</span>
    </div>
</aside>

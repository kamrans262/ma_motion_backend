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
        <a class="ma-nav__item ma-nav__item--active" href="{{ route('admin.dashboard') }}" aria-current="page">
            <span class="ma-nav__icon" aria-hidden="true">◆</span>
            <span>Dashboard</span>
        </a>

        <p class="ma-nav__label">Platform</p>
        @foreach (['Users', 'Makers', 'Artwork', 'Shows', 'Categories & Styles', 'Locations', 'Featured Maker', 'Notifications'] as $item)
            <span class="ma-nav__item ma-nav__item--disabled" aria-disabled="true">
                <span class="ma-nav__icon" aria-hidden="true">◇</span>
                <span>{{ $item }}</span>
                <span class="ma-nav__soon">Soon</span>
            </span>
        @endforeach

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

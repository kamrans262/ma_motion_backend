<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Dashboard') · MA Motion Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
    @stack('head')
</head>
<body class="ma-admin">
    <a class="ma-skip-link" href="#main-content">Skip to content</a>

    <div class="ma-admin-shell" data-admin-shell>
        @include('admin.partials.sidebar')
        <div class="ma-sidebar-overlay" data-sidebar-overlay aria-hidden="true"></div>

        <div class="ma-admin-main">
            @include('admin.partials.topbar')

            <main id="main-content" class="ma-admin-content" tabindex="-1">
                @if (session('status'))
                    <div class="ma-alert ma-alert--success" role="status">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('admin/js/admin.js') }}" defer></script>
    @stack('scripts')
</body>
</html>

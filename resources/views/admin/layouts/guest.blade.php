<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Admin') · MA Motion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
</head>
<body class="ma-admin ma-admin--guest">
    <a class="ma-skip-link" href="#main-content">Skip to content</a>
    <main id="main-content" class="ma-auth-shell">
        @yield('content')
    </main>
</body>
</html>

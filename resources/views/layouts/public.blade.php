<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SeminarKu')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.2.6/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="public-shell">
    <header class="public-header">
        <nav class="public-navbar" aria-label="Navigasi utama">
            <a class="brand-lockup" href="{{ route('home') }}" aria-label="SeminarKu, beranda">
                <span class="brand-mark" aria-hidden="true"><i class="bi bi-calendar2-check-fill"></i></span>
                <span>SeminarKu</span>
            </a>
            <a class="public-nav-link" href="{{ route('admin.login') }}">Masuk sebagai admin</a>
        </nav>
    </header>

    <main class="public-main public-main--wide">
        @yield('content')
    </main>

    <footer class="public-footer">
        <p>SeminarKu <span aria-hidden="true">·</span> Ruang untuk belajar dan bertemu.</p>
        <p>&copy; {{ now()->year }} SeminarKu</p>
    </footer>
</body>
</html>

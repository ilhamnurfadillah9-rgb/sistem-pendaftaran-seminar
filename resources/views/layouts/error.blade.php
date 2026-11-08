<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi kendala') · SeminarKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.2.6/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="error-shell">
    <header class="error-header">
        <a class="brand-lockup" href="{{ route('home') }}" aria-label="SeminarKu, beranda">
            <span class="brand-mark" aria-hidden="true"><i class="bi bi-calendar2-check-fill"></i></span>
            <span>SeminarKu</span>
        </a>
    </header>
    <main class="error-main">
        <section class="error-card" aria-labelledby="error-title">
            <span class="error-code">@yield('code')</span>
            <h1 id="error-title">@yield('heading')</h1>
            <p>@yield('description')</p>
            <div class="error-actions">
                <a class="button-primary" href="{{ route('home') }}">Kembali ke beranda</a>
                @yield('action')
            </div>
        </section>
    </main>
</body>
</html>

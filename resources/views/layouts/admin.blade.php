<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') · SeminarKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.2.6/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="admin-shell">
    @php
        $adminNavigation = [
            ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'route' => 'admin.dashboard'],
            ['label' => 'Seminar', 'icon' => 'bi-calendar-event', 'route' => 'admin.seminars.index'],
            ['label' => 'Peserta', 'icon' => 'bi-people', 'route' => 'admin.seminars.index', 'active' => 'admin.seminars.participants.*'],
            ['label' => 'Kehadiran', 'icon' => 'bi-check2-circle', 'route' => 'admin.seminars.index', 'active' => 'admin.seminars.attendance.*'],
            ['label' => 'Pindai QR', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.attendance.scan', 'active' => 'admin.attendance.scan*'],
            ['label' => 'Laporan', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*'],
        ];
    @endphp

    <aside class="admin-sidebar" aria-label="Navigasi admin">
        <a class="brand-lockup admin-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-mark" aria-hidden="true"><i class="bi bi-calendar2-check-fill"></i></span>
            <span>SeminarKu</span>
        </a>
        <p class="sidebar-caption">Menu utama</p>
        <nav class="admin-navigation">
            @include('layouts.partials.admin-navigation', ['mobile' => false])
        </nav>
        <div class="sidebar-footer">
            <span class="sidebar-admin-icon"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
            <span class="sidebar-admin-name">{{ Auth::user()->name }}</span>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="mobile-menu-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-mobile-menu" aria-controls="admin-mobile-menu" aria-label="Buka menu navigasi">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <div class="topbar-greeting">
                <span class="topbar-eyebrow">Panel administrator</span>
                <p>Selamat datang, <strong>{{ Auth::user()->name }}</strong></p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="logout-button" type="submit">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </header>

        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="admin-mobile-menu" aria-labelledby="admin-mobile-menu-title">
        <div class="offcanvas-header">
            <a class="brand-lockup" href="{{ route('admin.dashboard') }}" id="admin-mobile-menu-title">
                <span class="brand-mark" aria-hidden="true"><i class="bi bi-calendar2-check-fill"></i></span>
                <span>SeminarKu</span>
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup menu"></button>
        </div>
        <div class="offcanvas-body">
            <p class="sidebar-caption">Menu utama</p>
            <nav class="admin-navigation">
                @include('layouts.partials.admin-navigation', ['mobile' => true])
            </nav>
        </div>
    </div>

    <nav class="admin-bottom-nav" aria-label="Navigasi cepat">
        <a class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
            <span>Dashboard</span>
        </a>
        <a class="bottom-nav-item {{ request()->routeIs('admin.seminars.*') ? 'is-active' : '' }}" href="{{ route('admin.seminars.index') }}" @if (request()->routeIs('admin.seminars.*')) aria-current="page" @endif>
            <i class="bi bi-calendar-event" aria-hidden="true"></i>
            <span>Seminar</span>
        </a>
        <button class="bottom-nav-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-mobile-menu" aria-controls="admin-mobile-menu">
            <i class="bi bi-people" aria-hidden="true"></i>
            <span>Peserta</span>
        </button>
        <a class="bottom-nav-item {{ request()->routeIs('admin.attendance.scan*') ? 'is-active' : '' }}" href="{{ route('admin.attendance.scan') }}" @if (request()->routeIs('admin.attendance.scan*')) aria-current="page" @endif>
            <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
            <span>Pindai QR</span>
        </a>
    </nav>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/seminars.js') }}" defer></script>
</body>
</html>

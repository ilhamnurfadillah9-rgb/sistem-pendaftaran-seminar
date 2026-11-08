@extends('layouts.public')

@section('title', 'Masuk administrator · SeminarKu')

@section('content')
    <section class="login-section" aria-labelledby="login-title">
        <div class="login-card">
            <div class="login-brand">
                <span class="brand-mark brand-mark--large" aria-hidden="true"><i class="bi bi-calendar2-check-fill"></i></span>
                <span class="login-brand-name">SeminarKu</span>
            </div>

            <div class="login-heading">
                <h1 id="login-title">Selamat datang kembali</h1>
                <p>Masuk untuk mengelola seminar dan kehadiran peserta.</p>
            </div>

            <x-alert-flash />

            <form class="login-form" method="POST" action="{{ route('admin.login.store') }}" novalidate>
                @csrf
                <div class="form-field">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input
                            class="form-control @error('email') is-invalid-field @enderror"
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@contoh.com"
                            autocomplete="username"
                            inputmode="email"
                            maxlength="100"
                            required
                            @if ($errors->has('email')) aria-describedby="email-error" @endif
                        >
                    </div>
                    <x-form-error field="email" />
                </div>

                <div class="form-field">
                    <label class="form-label" for="password">Kata sandi</label>
                    <div class="input-with-icon input-with-action">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input
                            class="form-control @error('password') is-invalid-field @enderror"
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                            @if ($errors->has('password')) aria-describedby="password-error" @endif
                        >
                    </div>
                    <x-form-error field="password" />
                </div>

                <button class="button-primary login-submit" type="submit">
                    Masuk ke dashboard
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <p class="login-security"><i class="bi bi-shield-lock" aria-hidden="true"></i> Halaman ini khusus administrator.</p>
        </div>
    </section>
@endsection

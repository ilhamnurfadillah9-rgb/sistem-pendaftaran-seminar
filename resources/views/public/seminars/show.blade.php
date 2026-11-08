@extends('layouts.public')

@section('title', $seminar->judul.' | SeminarKu')

@section('content')
    @php
        $remainingSeats = $seminar->sisa_kuota;
        $isFull = $remainingSeats <= 0;
        $isClosed = $seminar->status !== 'dibuka';
    @endphp

    <div class="public-page seminar-detail-page">
        <a class="public-back-link" href="{{ route('home') }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Kembali ke daftar seminar
        </a>

        <div class="seminar-detail-layout">
            <div class="seminar-detail-main">
                <div class="seminar-detail-title">
                    @if ($seminar->status === 'ditutup')
                        <span class="seminar-state seminar-state--closed">Ditutup</span>
                    @elseif ($isFull)
                        <span class="seminar-state seminar-state--full">Penuh</span>
                    @else
                        <span class="seminar-state seminar-state--open">Pendaftaran dibuka</span>
                    @endif
                    <h1>{{ $seminar->judul }}</h1>
                    <p class="seminar-detail-speaker">Bersama {{ $seminar->pemapar }}</p>
                </div>

                <figure class="seminar-detail-flyer">
                    <img src="{{ asset('storage/'.$seminar->flyer) }}" alt="Flyer seminar {{ $seminar->judul }}">
                </figure>

                <section class="seminar-detail-section" aria-labelledby="seminar-description-title">
                    <h2 id="seminar-description-title">Tentang seminar ini</h2>
                    <p class="seminar-description">{{ $seminar->deskripsi }}</p>
                </section>

                <section class="seminar-detail-section seminar-facts" aria-labelledby="seminar-info-title">
                    <h2 id="seminar-info-title">Informasi seminar</h2>
                    <dl>
                        <div>
                            <dt>Tanggal</dt>
                            <dd>{{ $seminar->tanggal->locale('id')->translatedFormat('l, d F Y') }}</dd>
                        </div>
                        <div>
                            <dt>Waktu</dt>
                            <dd>{{ substr($seminar->waktu_mulai, 0, 5) }}–{{ substr($seminar->waktu_selesai, 0, 5) }}</dd>
                        </div>
                        <div>
                            <dt>Lokasi</dt>
                            <dd>{{ $seminar->lokasi }}</dd>
                        </div>
                        <div>
                            <dt>Sisa kuota</dt>
                            <dd>{{ max(0, $remainingSeats) }} dari {{ $seminar->kuota }} peserta</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="registration-panel" aria-labelledby="registration-title">
                <div class="registration-panel-heading">
                    <h2 id="registration-title">Daftar seminar</h2>
                    <p>Isi data diri untuk mengamankan tempatmu.</p>
                </div>

                @error('_form')
                    <div class="registration-form-alert" role="alert">{{ $message }}</div>
                @enderror

                <form action="{{ route('registrations.store', $seminar) }}" method="POST" class="registration-form">
                    @csrf
                    <div class="registration-field">
                        <label for="nama">Nama lengkap</label>
                        <input
                            id="nama"
                            name="nama"
                            type="text"
                            value="{{ old('nama') }}"
                            maxlength="100"
                            autocomplete="name"
                            required
                            @if ($errors->has('nama')) aria-invalid="true" aria-describedby="nama-error" @endif
                        >
                        <x-form-error field="nama" />
                    </div>
                    <div class="registration-field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="100"
                            autocomplete="email"
                            required
                            @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                        >
                        <x-form-error field="email" />
                    </div>
                    <div class="registration-field">
                        <label for="telepon">Nomor telepon</label>
                        <input
                            id="telepon"
                            name="telepon"
                            type="tel"
                            value="{{ old('telepon') }}"
                            inputmode="numeric"
                            autocomplete="tel"
                            maxlength="13"
                            placeholder="Contoh: 081234567890"
                            required
                            @if ($errors->has('telepon')) aria-invalid="true" aria-describedby="telepon-error" @endif
                        >
                        <span class="registration-field-hint">Gunakan 10–13 digit angka.</span>
                        <x-form-error field="telepon" />
                    </div>
                    <button class="registration-submit" type="submit" @disabled($isClosed || $isFull)>
                        @if ($isClosed)
                            Pendaftaran ditutup
                        @elseif ($isFull)
                            Kuota seminar penuh
                        @else
                            Daftar sekarang
                        @endif
                    </button>
                    @if (! $isClosed && ! $isFull)
                        <p class="registration-privacy-note">Email dan nomor telepon hanya digunakan untuk seminar ini.</p>
                    @endif
                </form>
            </aside>
        </div>
    </div>
@endsection

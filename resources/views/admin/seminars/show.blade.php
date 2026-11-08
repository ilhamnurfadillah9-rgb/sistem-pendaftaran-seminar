@extends('layouts.admin')

@section('title', $seminar->judul)

@section('content')
    <x-alert-flash />

    <section class="seminar-detail-page" aria-labelledby="seminar-detail-title">
        <a class="back-link" href="{{ route('admin.seminars.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke seminar</a>
        <div class="seminar-detail-heading">
            <div>
                <x-badge-status :status="$seminar->status" />
                <h1 id="seminar-detail-title">{{ $seminar->judul }}</h1>
                <p>{{ $seminar->pemapar }}</p>
            </div>
            <div class="seminar-admin-actions">
                <a class="button-secondary" href="{{ route('admin.seminars.attendance.index', $seminar) }}"><i class="bi bi-check2-circle" aria-hidden="true"></i>Kehadiran</a>
                <a class="button-secondary" href="{{ route('admin.seminars.participants.index', $seminar) }}"><i class="bi bi-people" aria-hidden="true"></i>Lihat peserta</a>
                <a class="button-primary" href="{{ route('admin.seminars.edit', $seminar) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Ubah seminar</a>
            </div>
        </div>

        <article class="seminar-detail-card">
            <img class="seminar-detail-flyer" src="{{ asset('storage/'.$seminar->flyer) }}" alt="Flyer seminar {{ $seminar->judul }}">
            <div class="seminar-detail-information">
                <h2>Informasi seminar</h2>
                <dl>
                    <div><dt>Tanggal</dt><dd>{{ $seminar->tanggal->locale('id')->translatedFormat('l, d F Y') }}</dd></div>
                    <div><dt>Waktu</dt><dd>{{ substr($seminar->waktu_mulai, 0, 5) }}–{{ substr($seminar->waktu_selesai, 0, 5) }}</dd></div>
                    <div><dt>Lokasi</dt><dd>{{ $seminar->lokasi }}</dd></div>
                    <div><dt>Kuota peserta</dt><dd>{{ $seminar->registrations_count }} dari {{ $seminar->kuota }} terdaftar</dd></div>
                </dl>
                <h2>Deskripsi</h2>
                <p class="seminar-detail-description">{{ $seminar->deskripsi }}</p>
            </div>
        </article>
    </section>
@endsection

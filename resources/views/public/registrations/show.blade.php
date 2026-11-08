@extends('layouts.public')

@section('title', 'Kartu peserta | SeminarKu')

@section('content')
    <section class="public-page confirmation-page" aria-labelledby="confirmation-title">
        <div class="confirmation-heading">
            <span class="confirmation-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
            <h1 id="confirmation-title">Tempatmu sudah tercatat.</h1>
            <p>Simpan kartu peserta ini untuk ditunjukkan saat hadir.</p>
        </div>

        <article class="attendee-card">
            <header class="attendee-card-header">
                <span class="attendee-card-mark" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                <div>
                    <strong>SeminarKu</strong>
                    <span>Kartu peserta seminar</span>
                </div>
                <x-badge-status class="attendee-status" :status="$registration->status" />
            </header>

            <div class="attendee-card-content">
                <div class="attendee-details">
                    <div class="attendee-detail attendee-detail--name">
                        <span>Nama peserta</span>
                        <strong>{{ $registration->nama }}</strong>
                    </div>
                    <div class="attendee-detail">
                        <span>Email</span>
                        <strong>{{ $registration->email }}</strong>
                    </div>
                    <div class="attendee-detail">
                        <span>Seminar</span>
                        <strong>{{ $registration->seminar->judul }}</strong>
                    </div>
                    <div class="attendee-detail">
                        <span>Tanggal dan waktu</span>
                        <strong>{{ $registration->seminar->tanggal->locale('id')->translatedFormat('l, d F Y') }}, {{ substr($registration->seminar->waktu_mulai, 0, 5) }}</strong>
                    </div>
                    <div class="attendee-detail">
                        <span>Lokasi</span>
                        <strong>{{ $registration->seminar->lokasi }}</strong>
                    </div>
                </div>
                <div class="attendee-qr">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR code pendaftaran {{ $registration->token }}">
                    <span>Pindai saat hadir</span>
                </div>
            </div>

            <footer class="attendee-card-footer">
                Tunjukkan QR ini kepada panitia. Presensi dibuka 10 menit setelah seminar dimulai.
            </footer>
        </article>

        <button class="confirmation-print-button" type="button" onclick="window.print()">
            <i class="bi bi-printer" aria-hidden="true"></i>
            Simpan/Cetak
        </button>
        <a class="confirmation-home-link" href="{{ route('home') }}">Kembali ke daftar seminar</a>
    </section>
@endsection

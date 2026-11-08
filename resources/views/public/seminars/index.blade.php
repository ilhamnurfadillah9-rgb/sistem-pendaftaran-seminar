@extends('layouts.public')

@section('title', 'Seminar kampus | SeminarKu')

@section('content')
    <div class="public-page seminar-catalog">
        <section class="catalog-hero" aria-labelledby="catalog-title">
            <div class="catalog-hero-copy">
                <p class="catalog-kicker">Temukan ruang belajar berikutnya</p>
                <h1 id="catalog-title">Gagasan baru dimulai dari satu pertemuan.</h1>
                <p>Pilih seminar, temui para pemapar, dan bawa pulang perspektif baru.</p>
            </div>
            <form class="catalog-search" action="{{ route('home') }}" method="GET" role="search">
                <label for="seminar-search">Cari seminar berdasarkan judul</label>
                <div class="catalog-search-control">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        id="seminar-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Contoh: teknologi, desain, karier"
                    >
                    <button type="submit">Cari</button>
                </div>
            </form>
        </section>

        <section class="catalog-results" aria-labelledby="seminar-list-title">
            <div class="catalog-results-heading">
                <div>
                    <h2 id="seminar-list-title">{{ $search === '' ? 'Seminar mendatang' : 'Hasil pencarian' }}</h2>
                    @if ($search !== '')
                        <p>Untuk “{{ $search }}”</p>
                    @endif
                </div>
                <p class="catalog-result-count">{{ $seminars->total() }} seminar</p>
            </div>

            @if ($seminars->isNotEmpty())
                <div class="seminar-grid">
                    @foreach ($seminars as $seminar)
                        @php
                            $remainingSeats = $seminar->sisa_kuota;
                            $capacityPercent = min(100, (int) round(($seminar->jumlah_pendaftar / $seminar->kuota) * 100));
                            $isFull = $remainingSeats <= 0;
                        @endphp
                        <article class="seminar-card">
                            <a class="seminar-card-flyer" href="{{ route('seminar.show', $seminar) }}">
                                <img src="{{ asset('storage/'.$seminar->flyer) }}" alt="Flyer seminar {{ $seminar->judul }}">
                            </a>
                            <div class="seminar-card-content">
                                <div class="seminar-card-meta">
                                    <time datetime="{{ $seminar->tanggal->toDateString() }}">
                                        {{ $seminar->tanggal->locale('id')->translatedFormat('d F Y') }}
                                    </time>
                                    @if ($seminar->status === 'ditutup')
                                        <span class="seminar-state seminar-state--closed">Ditutup</span>
                                    @elseif ($isFull)
                                        <span class="seminar-state seminar-state--full">Penuh</span>
                                    @else
                                        <span class="seminar-state seminar-state--open">Dibuka</span>
                                    @endif
                                </div>
                                <h3><a href="{{ route('seminar.show', $seminar) }}">{{ $seminar->judul }}</a></h3>
                                <p class="seminar-card-speaker">{{ $seminar->pemapar }}</p>
                                <div class="seminar-card-details">
                                    <span><i class="bi bi-clock" aria-hidden="true"></i>{{ substr($seminar->waktu_mulai, 0, 5) }}–{{ substr($seminar->waktu_selesai, 0, 5) }}</span>
                                    <span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $seminar->lokasi }}</span>
                                </div>
                                <div class="seminar-capacity">
                                    <div class="seminar-capacity-copy">
                                        <span>Sisa kuota</span>
                                        <strong>{{ max(0, $remainingSeats) }} dari {{ $seminar->kuota }}</strong>
                                    </div>
                                    <div
                                        class="seminar-capacity-track"
                                        role="progressbar"
                                        aria-label="Kuota terisi"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        aria-valuenow="{{ $capacityPercent }}"
                                    >
                                        <span style="width: {{ $capacityPercent }}%"></span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if ($seminars->hasPages())
                    <nav class="catalog-pagination" aria-label="Halaman seminar">
                        {{ $seminars->links() }}
                    </nav>
                @endif
            @else
                <div class="catalog-empty-state">
                    <span class="catalog-empty-icon" aria-hidden="true"><i class="bi {{ $search === '' ? 'bi-calendar2-plus' : 'bi-search' }}"></i></span>
                    <h3>{{ $search === '' ? 'Belum ada seminar' : 'Seminar tidak ditemukan' }}</h3>
                    <p>{{ $search === '' ? 'Seminar yang tersedia akan muncul di sini.' : 'Coba kata kunci lain atau lihat semua seminar yang tersedia.' }}</p>
                    @if ($search !== '')
                        <a href="{{ route('home') }}">Lihat semua seminar</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection

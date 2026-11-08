@extends('layouts.admin')

@section('title', 'Seminar')

@section('content')
    <x-alert-flash />

    <section class="seminar-page-heading" aria-labelledby="seminars-title">
        <div>
            <p class="dashboard-date">{{ $seminars->total() }} seminar tersimpan</p>
            <h1 id="seminars-title">Seminar</h1>
            <p class="dashboard-description">Atur jadwal, kuota, dan informasi setiap seminar.</p>
        </div>
        <a class="button-primary seminar-add-button" href="{{ route('admin.seminars.create') }}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            Tambah seminar
        </a>
    </section>

    <section class="seminar-list-panel" aria-label="Daftar seminar">
        <form class="seminar-search-form" method="GET" action="{{ route('admin.seminars.index') }}" role="search">
            <label class="sr-only" for="seminar-search">Cari judul seminar</label>
            <span class="seminar-search-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
            <input id="seminar-search" type="search" name="search" value="{{ $search }}" placeholder="Cari judul seminar" maxlength="150">
            @if ($search !== '')
                <a class="seminar-search-clear" href="{{ route('admin.seminars.index') }}">Hapus pencarian</a>
            @endif
            <button type="submit">Cari</button>
        </form>

        @if ($seminars->isNotEmpty())
            <div class="table-responsive seminar-table-wrap">
                <table class="seminar-table">
                    <thead>
                        <tr>
                            <th scope="col">Seminar</th>
                            <th scope="col">Jadwal</th>
                            <th scope="col">Kuota</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($seminars as $seminar)
                            @php
                                $terdaftar = (int) $seminar->registrations_count;
                                $persentaseKuota = $seminar->kuota > 0
                                    ? min(100, (int) round($terdaftar / $seminar->kuota * 100))
                                    : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="seminar-title-cell">
                                        <img class="seminar-flyer-thumb" src="{{ asset('storage/'.$seminar->flyer) }}" alt="" loading="lazy">
                                        <div class="seminar-title-copy">
                                            <a href="{{ route('admin.seminars.show', $seminar) }}">{{ $seminar->judul }}</a>
                                            <span>{{ $seminar->pemapar }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="seminar-date-cell">
                                        <strong>{{ $seminar->tanggal->locale('id')->translatedFormat('d M Y') }}</strong>
                                        <span>{{ substr($seminar->waktu_mulai, 0, 5) }}–{{ substr($seminar->waktu_selesai, 0, 5) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="quota-cell">
                                        <span><strong>{{ $terdaftar }}</strong> / {{ $seminar->kuota }} peserta</span>
                                        <div class="quota-progress" role="progressbar" aria-label="Kuota {{ $seminar->judul }}" aria-valuenow="{{ $terdaftar }}" aria-valuemin="0" aria-valuemax="{{ $seminar->kuota }}">
                                            <span style="width: {{ $persentaseKuota }}%"></span>
                                        </div>
                                    </div>
                                </td>
                                <td><x-badge-status :status="$seminar->status" /></td>
                                <td>
                                    <div class="seminar-actions">
                                        <a class="icon-action" href="{{ route('admin.seminars.show', $seminar) }}" aria-label="Lihat {{ $seminar->judul }}" title="Lihat"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                        <a class="icon-action" href="{{ route('admin.seminars.edit', $seminar) }}" aria-label="Ubah {{ $seminar->judul }}" title="Ubah"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                                        <button class="icon-action icon-action--danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-seminar-modal" data-delete-url="{{ route('admin.seminars.destroy', $seminar) }}" data-seminar-title="{{ $seminar->judul }}" aria-label="Hapus {{ $seminar->judul }}" title="Hapus">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="seminar-mobile-list">
                @foreach ($seminars as $seminar)
                    @php
                        $terdaftar = (int) $seminar->registrations_count;
                        $persentaseKuota = $seminar->kuota > 0
                            ? min(100, (int) round($terdaftar / $seminar->kuota * 100))
                            : 0;
                    @endphp
                    <article class="seminar-mobile-card">
                        <div class="seminar-mobile-card-heading">
                            <img class="seminar-flyer-thumb" src="{{ asset('storage/'.$seminar->flyer) }}" alt="" loading="lazy">
                            <div class="seminar-title-copy">
                                <a href="{{ route('admin.seminars.show', $seminar) }}">{{ $seminar->judul }}</a>
                                <span>{{ $seminar->pemapar }}</span>
                            </div>
                            <x-badge-status :status="$seminar->status" />
                        </div>
                        <div class="seminar-mobile-meta">
                            <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $seminar->tanggal->locale('id')->translatedFormat('d M Y') }}</span>
                            <span><i class="bi bi-clock" aria-hidden="true"></i>{{ substr($seminar->waktu_mulai, 0, 5) }}–{{ substr($seminar->waktu_selesai, 0, 5) }}</span>
                            <span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $seminar->lokasi }}</span>
                        </div>
                        <div class="quota-cell seminar-mobile-quota">
                            <span><strong>{{ $terdaftar }}</strong> / {{ $seminar->kuota }} peserta</span>
                            <div class="quota-progress" role="progressbar" aria-label="Kuota {{ $seminar->judul }}" aria-valuenow="{{ $terdaftar }}" aria-valuemin="0" aria-valuemax="{{ $seminar->kuota }}">
                                <span style="width: {{ $persentaseKuota }}%"></span>
                            </div>
                        </div>
                        <div class="seminar-mobile-actions">
                            <a class="button-secondary" href="{{ route('admin.seminars.show', $seminar) }}"><i class="bi bi-eye" aria-hidden="true"></i>Lihat</a>
                            <a class="button-secondary" href="{{ route('admin.seminars.edit', $seminar) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Ubah</a>
                            <button class="button-secondary button-secondary--danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-seminar-modal" data-delete-url="{{ route('admin.seminars.destroy', $seminar) }}" data-seminar-title="{{ $seminar->judul }}">
                                <i class="bi bi-trash3" aria-hidden="true"></i>Hapus
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($seminars->hasPages())
                <div class="seminar-pagination">
                    {{ $seminars->links() }}
                </div>
            @endif
        @else
            <div class="seminar-empty-state">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-calendar2-plus"></i></span>
                <h2>{{ $search !== '' ? 'Seminar tidak ditemukan' : 'Belum ada seminar' }}</h2>
                <p>{{ $search !== '' ? 'Coba kata kunci judul yang lain.' : 'Tambahkan seminar untuk mulai mengatur jadwal dan kuota.' }}</p>
                @if ($search === '')
                    <a class="button-primary" href="{{ route('admin.seminars.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah seminar</a>
                @endif
            </div>
        @endif
    </section>

    <div class="modal fade" id="delete-seminar-modal" tabindex="-1" aria-labelledby="delete-seminar-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content seminar-delete-modal">
                <div class="modal-header">
                    <h2 class="modal-title" id="delete-seminar-title">Hapus seminar?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p>Seminar <strong data-delete-seminar-name></strong> akan dihapus. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button class="button-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" data-delete-seminar-form>
                        @csrf
                        @method('DELETE')
                        <button class="button-danger" type="submit">Hapus seminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

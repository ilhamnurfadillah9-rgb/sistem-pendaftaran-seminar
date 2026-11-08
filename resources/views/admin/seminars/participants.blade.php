@extends('layouts.admin')

@section('title', 'Peserta · '.$seminar->judul)

@section('content')
    <x-alert-flash />

    <section class="seminar-page-heading participant-page-heading" aria-labelledby="participants-title">
        <div>
            <a class="back-link" href="{{ route('admin.seminars.show', $seminar) }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke seminar
            </a>
            <h1 id="participants-title">{{ $seminar->judul }}</h1>
            <p class="dashboard-description">
                {{ $seminar->registrations_count }} dari {{ $seminar->kuota }} peserta terdaftar
            </p>
        </div>
        <a class="button-secondary" href="{{ route('admin.seminars.show', $seminar) }}">
            <i class="bi bi-eye" aria-hidden="true"></i>Lihat seminar
        </a>
    </section>

    <section class="seminar-list-panel participant-list-panel" aria-label="Daftar peserta seminar">
        <form class="seminar-search-form" method="GET" action="{{ route('admin.seminars.participants.index', $seminar) }}" role="search">
            <label class="sr-only" for="participant-search">Cari nama atau email peserta</label>
            <span class="seminar-search-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
            <input id="participant-search" type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email" maxlength="100">
            @if ($search !== '')
                <a class="seminar-search-clear" href="{{ route('admin.seminars.participants.index', $seminar) }}">Hapus pencarian</a>
            @endif
            <button type="submit">Cari</button>
        </form>

        @if ($participants->isNotEmpty())
            <div class="table-responsive seminar-table-wrap participant-table-wrap">
                <table class="participant-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Telepon</th>
                            <th scope="col">Status</th>
                            <th scope="col">Waktu daftar</th>
                            <th scope="col"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $participant)
                            <tr>
                                <td class="participant-name-cell">{{ $participant->nama }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ $participant->telepon }}</td>
                                <td><x-badge-status :status="$participant->status" /></td>
                                <td>
                                    <time datetime="{{ $participant->created_at->toIso8601String() }}">
                                        {{ $participant->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
                                    </time>
                                </td>
                                <td>
                                    <div class="participant-actions">
                                        <button
                                            class="button-secondary participant-qr-trigger"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#participant-qr-modal"
                                            data-qr-url="{{ route('admin.seminars.participants.qr', [$seminar, $participant]) }}"
                                            data-confirmation-url="{{ route('registrations.show', $participant->token) }}"
                                            data-participant-name="{{ $participant->nama }}"
                                        >Lihat QR</button>
                                        <a
                                            class="icon-action"
                                            href="{{ route('admin.seminars.participants.edit', [$seminar, $participant]) }}"
                                            aria-label="Ubah data {{ $participant->nama }}"
                                            title="Ubah"
                                        ><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                                        <button
                                            class="icon-action icon-action--danger"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delete-participant-modal"
                                            data-delete-url="{{ route('admin.seminars.participants.destroy', [$seminar, $participant]) }}"
                                            data-participant-name="{{ $participant->nama }}"
                                            aria-label="Hapus {{ $participant->nama }}"
                                            title="Hapus"
                                        ><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="participant-mobile-list">
                @foreach ($participants as $participant)
                    <article class="participant-mobile-card">
                        <div class="participant-mobile-heading">
                            <strong>{{ $participant->nama }}</strong>
                            <x-badge-status :status="$participant->status" />
                        </div>
                        <dl class="participant-mobile-details">
                            <div><dt>Email</dt><dd>{{ $participant->email }}</dd></div>
                            <div><dt>Telepon</dt><dd>{{ $participant->telepon }}</dd></div>
                            <div>
                                <dt>Waktu daftar</dt>
                                <dd>{{ $participant->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                        <div class="participant-mobile-actions">
                            <button
                                class="button-secondary"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#participant-qr-modal"
                                data-qr-url="{{ route('admin.seminars.participants.qr', [$seminar, $participant]) }}"
                                data-confirmation-url="{{ route('registrations.show', $participant->token) }}"
                                data-participant-name="{{ $participant->nama }}"
                            ><i class="bi bi-qr-code" aria-hidden="true"></i>Lihat QR</button>
                            <a class="button-secondary" href="{{ route('admin.seminars.participants.edit', [$seminar, $participant]) }}">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>Ubah
                            </a>
                            <button
                                class="button-secondary button-secondary--danger"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#delete-participant-modal"
                                data-delete-url="{{ route('admin.seminars.participants.destroy', [$seminar, $participant]) }}"
                                data-participant-name="{{ $participant->nama }}"
                            ><i class="bi bi-trash3" aria-hidden="true"></i>Hapus</button>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($participants->hasPages())
                <div class="seminar-pagination">
                    {{ $participants->links() }}
                </div>
            @endif
        @else
            <div class="seminar-empty-state">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                <h2>{{ $search !== '' ? 'Peserta tidak ditemukan' : 'Belum ada peserta' }}</h2>
                <p>{{ $search !== '' ? 'Coba nama atau email yang lain.' : 'Peserta yang mendaftar akan tampil di sini.' }}</p>
            </div>
        @endif
    </section>

    <div class="modal fade" id="delete-participant-modal" tabindex="-1" aria-labelledby="delete-participant-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content seminar-delete-modal">
                <div class="modal-header">
                    <h2 class="modal-title" id="delete-participant-title">Hapus peserta?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p>Data peserta <strong data-delete-participant-name></strong> akan dihapus. Sisa kuota seminar akan bertambah.</p>
                </div>
                <div class="modal-footer">
                    <button class="button-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" data-delete-participant-form>
                        @csrf
                        @method('DELETE')
                        <button class="button-danger" type="submit">Hapus peserta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="participant-qr-modal" tabindex="-1" aria-labelledby="participant-qr-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content seminar-delete-modal participant-qr-modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="participant-qr-title">QR peserta</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body participant-qr-modal-body">
                    <img data-participant-qr alt="" width="220" height="220">
                    <a data-participant-confirmation href="#">Buka halaman konfirmasi peserta</a>
                </div>
            </div>
        </div>
    </div>
@endsection

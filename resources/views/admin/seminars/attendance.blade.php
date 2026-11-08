@extends('layouts.admin')

@section('title', 'Kehadiran · '.$seminar->judul)

@section('content')
    <x-alert-flash />

    <section class="seminar-page-heading participant-page-heading attendance-list-heading" aria-labelledby="attendance-list-title">
        <div>
            <a class="back-link" href="{{ route('admin.seminars.show', $seminar) }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke seminar
            </a>
            <h1 id="attendance-list-title">{{ $seminar->judul }}</h1>
            <p class="dashboard-description">
                {{ $seminar->registrations_count }} peserta terdaftar
            </p>
        </div>
        <a
            class="button-primary"
            href="{{ route('admin.attendance.scan', ['seminar_id' => $seminar->id]) }}"
        >
            <i class="bi bi-qr-code-scan" aria-hidden="true"></i>Pindai QR
        </a>
    </section>

    <section class="seminar-list-panel attendance-list-panel" aria-label="Daftar kehadiran peserta">
        @if ($participants->isNotEmpty())
            <div class="table-responsive participant-table-wrap attendance-table-wrap">
                <table class="participant-table attendance-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Telepon</th>
                            <th scope="col">Status</th>
                            <th scope="col">Waktu hadir</th>
                            <th scope="col">Koreksi status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $participant)
                            @php
                                $selectedStatus = old(
                                    'status',
                                    in_array($participant->status, ['hadir', 'tidak_hadir'], true)
                                        ? $participant->status
                                        : '',
                                );
                            @endphp
                            <tr>
                                <td class="participant-name-cell">{{ $participant->nama }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ $participant->telepon }}</td>
                                <td><x-badge-status :status="$participant->status" /></td>
                                <td>
                                    @if ($participant->waktu_hadir)
                                        <time datetime="{{ $participant->waktu_hadir->toIso8601String() }}">
                                            {{ $participant->waktu_hadir->locale('id')->translatedFormat('d M Y, H:i') }}
                                        </time>
                                    @else
                                        <span class="attendance-no-time">Belum hadir</span>
                                    @endif
                                </td>
                                <td>
                                    <form
                                        class="attendance-status-form"
                                        method="POST"
                                        action="{{ route('admin.seminars.attendance.update', [$seminar, $participant]) }}"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <label class="sr-only" for="attendance-status-{{ $participant->id }}">
                                            Status kehadiran {{ $participant->nama }}
                                        </label>
                                        <select
                                            class="form-select attendance-status-select"
                                            id="attendance-status-{{ $participant->id }}"
                                            name="status"
                                            required
                                        >
                                            <option value="" @selected($selectedStatus === '') disabled>Pilih status</option>
                                            <option value="hadir" @selected($selectedStatus === 'hadir')>Hadir</option>
                                            <option value="tidak_hadir" @selected($selectedStatus === 'tidak_hadir')>Tidak hadir</option>
                                        </select>
                                        <button class="button-secondary attendance-save-button" type="submit">Simpan</button>
                                        @error('status')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="attendance-mobile-list">
                @foreach ($participants as $participant)
                    @php
                        $selectedStatus = old(
                            'status',
                            in_array($participant->status, ['hadir', 'tidak_hadir'], true)
                                ? $participant->status
                                : '',
                        );
                    @endphp
                    <article class="attendance-mobile-card">
                        <div class="participant-mobile-heading">
                            <strong>{{ $participant->nama }}</strong>
                            <x-badge-status :status="$participant->status" />
                        </div>
                        <dl class="participant-mobile-details">
                            <div><dt>Email</dt><dd>{{ $participant->email }}</dd></div>
                            <div><dt>Telepon</dt><dd>{{ $participant->telepon }}</dd></div>
                            <div>
                                <dt>Waktu hadir</dt>
                                <dd>
                                    @if ($participant->waktu_hadir)
                                        {{ $participant->waktu_hadir->locale('id')->translatedFormat('d M Y, H:i') }}
                                    @else
                                        Belum hadir
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        <form
                            class="attendance-status-form attendance-status-form--mobile"
                            method="POST"
                            action="{{ route('admin.seminars.attendance.update', [$seminar, $participant]) }}"
                        >
                            @csrf
                            @method('PUT')
                            <label class="form-label" for="attendance-mobile-status-{{ $participant->id }}">
                                Koreksi status
                            </label>
                            <select
                                class="form-select attendance-status-select"
                                id="attendance-mobile-status-{{ $participant->id }}"
                                name="status"
                                required
                            >
                                <option value="" @selected($selectedStatus === '') disabled>Pilih status</option>
                                <option value="hadir" @selected($selectedStatus === 'hadir')>Hadir</option>
                                <option value="tidak_hadir" @selected($selectedStatus === 'tidak_hadir')>Tidak hadir</option>
                            </select>
                            <button class="button-secondary attendance-save-button" type="submit">Simpan status</button>
                            @error('status')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </form>
                    </article>
                @endforeach
            </div>

            @if ($participants->hasPages())
                <div class="participant-pagination">
                    {{ $participants->links() }}
                </div>
            @endif
        @else
            <div class="empty-state attendance-empty-state">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                <h2>Belum ada peserta</h2>
                <p>Peserta yang mendaftar akan tampil di sini.</p>
            </div>
        @endif
    </section>
@endsection

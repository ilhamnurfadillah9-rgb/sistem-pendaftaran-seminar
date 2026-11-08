@extends('layouts.admin')

@section('title', 'Pindai kehadiran')

@section('content')
    <x-alert-flash />

    <section class="seminar-page-heading attendance-page-heading" aria-labelledby="attendance-scan-title">
        <div>
            <p class="dashboard-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <h1 id="attendance-scan-title">Pindai kehadiran</h1>
            <p class="dashboard-description">Arahkan kamera ke QR peserta atau masukkan token secara manual.</p>
        </div>
        @if ($selectedSeminarId)
            <a
                class="button-secondary"
                href="{{ route('admin.seminars.attendance.index', $selectedSeminarId) }}"
                data-attendance-list-link
                data-attendance-list-url="{{ route('admin.seminars.attendance.index', ['seminar' => '__seminar__']) }}"
            >
                <i class="bi bi-list-check" aria-hidden="true"></i>Daftar kehadiran
            </a>
        @endif
    </section>

    <section class="attendance-scan-panel" aria-label="Pemindai QR" @if ($seminars->isNotEmpty()) data-attendance-scanner @endif>
        @if ($seminars->isEmpty())
            <div class="seminar-empty-state attendance-scan-empty">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-calendar2-plus"></i></span>
                <h2>Belum ada seminar</h2>
                <p>Tambahkan seminar sebelum mulai mencatat kehadiran.</p>
                <a class="button-primary" href="{{ route('admin.seminars.create') }}">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>Tambah seminar
                </a>
            </div>
        @else
            <form
            class="attendance-scan-form"
            action="{{ route('admin.attendance.scan.store') }}"
            method="POST"
            data-attendance-scan-form
        >
            @csrf
            <div class="attendance-seminar-picker">
                <label class="form-label" for="attendance-seminar">Seminar</label>
                <select
                    class="form-select"
                    id="attendance-seminar"
                    name="seminar_id"
                    data-scan-seminar
                    required
                >
                    @foreach ($seminars as $seminar)
                        <option value="{{ $seminar->id }}" @selected($selectedSeminarId === $seminar->id)>
                            {{ $seminar->judul }}
                        </option>
                    @endforeach
                </select>
                @error('seminar_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="attendance-scan-methods">
                <section class="attendance-camera-panel" aria-labelledby="camera-title">
                    <div class="attendance-method-heading">
                        <div>
                            <h2 id="camera-title">Kamera</h2>
                            <p>Arahkan QR peserta ke dalam bingkai.</p>
                        </div>
                        <span class="attendance-camera-indicator" aria-hidden="true">
                            <i class="bi bi-camera-video"></i>
                        </span>
                    </div>
                    <div class="attendance-qr-reader" id="attendance-qr-reader" data-qr-reader>
                        <span class="attendance-qr-corner attendance-qr-corner--top-left" aria-hidden="true"></span>
                        <span class="attendance-qr-corner attendance-qr-corner--top-right" aria-hidden="true"></span>
                        <span class="attendance-qr-corner attendance-qr-corner--bottom-left" aria-hidden="true"></span>
                        <span class="attendance-qr-corner attendance-qr-corner--bottom-right" aria-hidden="true"></span>
                    </div>
                    <button class="button-secondary attendance-camera-button" type="button" data-start-camera>
                        <i class="bi bi-camera-video" aria-hidden="true"></i>Aktifkan kamera
                    </button>
                    <p class="attendance-camera-message" data-camera-message role="status">
                        Kamera hanya tersedia di HTTPS atau localhost.
                    </p>
                </section>

                <section class="attendance-manual-panel" aria-labelledby="manual-token-title">
                    <div class="attendance-method-heading">
                        <div>
                            <h2 id="manual-token-title">Masukkan token</h2>
                            <p>Gunakan cara ini bila kamera tidak tersedia.</p>
                        </div>
                        <span class="attendance-manual-icon" aria-hidden="true">
                            <i class="bi bi-keyboard"></i>
                        </span>
                    </div>
                    <label class="form-label" for="attendance-token">Token peserta</label>
                    <input
                        class="form-control attendance-token-input"
                        id="attendance-token"
                        type="text"
                        name="token"
                        maxlength="64"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        placeholder="Masukkan token QR"
                        data-scan-token
                        required
                    >
                    @error('token')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                    <button class="button-primary attendance-submit-button" type="submit">
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>Catat kehadiran
                    </button>
                </section>
            </div>
            </form>

            <div class="attendance-scan-result" data-scan-result role="status" aria-live="polite" hidden>
                <span class="attendance-result-icon" aria-hidden="true" data-result-icon></span>
                <div class="attendance-result-copy">
                    <p data-result-message></p>
                    <strong data-result-name hidden></strong>
                    <span data-result-seminar hidden></span>
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js" defer></script>
@endpush

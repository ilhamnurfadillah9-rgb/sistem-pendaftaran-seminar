@extends('layouts.admin')

@section('title', 'Ubah data peserta')

@section('content')
    <section class="seminar-form-page participant-edit-page" aria-labelledby="participant-edit-title">
        <a class="back-link" href="{{ route('admin.seminars.participants.index', $seminar) }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke peserta
        </a>
        <div class="seminar-form-heading">
            <h1 id="participant-edit-title">Ubah data peserta</h1>
            <p>{{ $registration->nama }} · {{ $seminar->judul }}</p>
        </div>
        <x-alert-flash />

        <form
            class="seminar-form-panel participant-edit-form"
            method="POST"
            action="{{ route('admin.seminars.participants.update', [$seminar, $registration]) }}"
        >
            @csrf
            @method('PUT')

            <div class="seminar-form-grid">
                <div class="form-field seminar-form-field">
                    <label class="form-label" for="nama">Nama peserta</label>
                    <input
                        class="form-control @error('nama') is-invalid-field @enderror"
                        id="nama"
                        name="nama"
                        type="text"
                        value="{{ old('nama', $registration->nama) }}"
                        maxlength="100"
                        required
                        @if ($errors->has('nama')) aria-describedby="nama-error" @endif
                    >
                    <x-form-error field="nama" />
                </div>

                <div class="form-field seminar-form-field">
                    <label class="form-label" for="email">Email</label>
                    <input
                        class="form-control @error('email') is-invalid-field @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $registration->email) }}"
                        maxlength="100"
                        required
                        @if ($errors->has('email')) aria-describedby="email-error" @endif
                    >
                    <x-form-error field="email" />
                </div>

                <div class="form-field seminar-form-field">
                    <label class="form-label" for="telepon">Nomor telepon</label>
                    <input
                        class="form-control @error('telepon') is-invalid-field @enderror"
                        id="telepon"
                        name="telepon"
                        type="tel"
                        value="{{ old('telepon', $registration->telepon) }}"
                        inputmode="numeric"
                        maxlength="13"
                        required
                        @if ($errors->has('telepon')) aria-describedby="telepon-error" @endif
                    >
                    <span class="field-hint">Gunakan 10–13 digit angka.</span>
                    <x-form-error field="telepon" />
                </div>
            </div>

            <div class="seminar-form-actions">
                <a class="button-secondary" href="{{ route('admin.seminars.participants.index', $seminar) }}">Batal</a>
                <button class="button-primary" type="submit">
                    <i class="bi bi-check2" aria-hidden="true"></i>Simpan perubahan
                </button>
            </div>
        </form>
    </section>
@endsection

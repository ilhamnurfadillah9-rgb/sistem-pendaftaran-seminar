@php
    $isEditing = isset($seminar);
    $nilai = fn (string $field, mixed $fallback = '') => old($field, $fallback);
@endphp

<form class="seminar-form-panel" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="seminar-form-grid">
        <div class="form-field seminar-form-field">
            <label class="form-label" for="judul">Judul seminar</label>
            <input class="form-control @error('judul') is-invalid-field @enderror" id="judul" name="judul" type="text" value="{{ $nilai('judul', $seminar->judul ?? '') }}" maxlength="150" required @if ($errors->has('judul')) aria-describedby="judul-error" @endif>
            <x-form-error field="judul" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="pemapar">Nama pemapar materi</label>
            <input class="form-control @error('pemapar') is-invalid-field @enderror" id="pemapar" name="pemapar" type="text" value="{{ $nilai('pemapar', $seminar->pemapar ?? '') }}" maxlength="150" required @if ($errors->has('pemapar')) aria-describedby="pemapar-error" @endif>
            <x-form-error field="pemapar" />
        </div>

        <div class="form-field seminar-form-field seminar-form-field--full">
            <label class="form-label" for="deskripsi">Deskripsi singkat</label>
            <textarea class="form-control @error('deskripsi') is-invalid-field @enderror" id="deskripsi" name="deskripsi" rows="4" minlength="20" maxlength="500" required @if ($errors->has('deskripsi')) aria-describedby="deskripsi-error" @endif>{{ $nilai('deskripsi', $seminar->deskripsi ?? '') }}</textarea>
            <span class="field-hint">20–500 karakter.</span>
            <x-form-error field="deskripsi" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="lokasi">Lokasi</label>
            <input class="form-control @error('lokasi') is-invalid-field @enderror" id="lokasi" name="lokasi" type="text" value="{{ $nilai('lokasi', $seminar->lokasi ?? '') }}" maxlength="150" required @if ($errors->has('lokasi')) aria-describedby="lokasi-error" @endif>
            <x-form-error field="lokasi" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="tanggal">Tanggal seminar</label>
            <input class="form-control @error('tanggal') is-invalid-field @enderror" id="tanggal" name="tanggal" type="date" value="{{ $nilai('tanggal', isset($seminar) ? $seminar->tanggal->format('Y-m-d') : '') }}" required @if (! $isEditing) min="{{ now()->toDateString() }}" @endif @if ($errors->has('tanggal')) aria-describedby="tanggal-error" @endif>
            <x-form-error field="tanggal" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="waktu_mulai">Waktu mulai</label>
            <input class="form-control @error('waktu_mulai') is-invalid-field @enderror" id="waktu_mulai" name="waktu_mulai" type="time" value="{{ $nilai('waktu_mulai', $seminar->waktu_mulai ?? '') }}" required @if ($errors->has('waktu_mulai')) aria-describedby="waktu_mulai-error" @endif>
            <x-form-error field="waktu_mulai" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="waktu_selesai">Waktu selesai</label>
            <input class="form-control @error('waktu_selesai') is-invalid-field @enderror" id="waktu_selesai" name="waktu_selesai" type="time" value="{{ $nilai('waktu_selesai', $seminar->waktu_selesai ?? '') }}" required @if ($errors->has('waktu_selesai')) aria-describedby="waktu_selesai-error" @endif>
            <x-form-error field="waktu_selesai" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="kuota">Kuota peserta</label>
            <input class="form-control @error('kuota') is-invalid-field @enderror" id="kuota" name="kuota" type="number" value="{{ $nilai('kuota', $seminar->kuota ?? '') }}" min="{{ $isEditing ? $seminar->jumlah_pendaftar : 1 }}" max="1000" step="1" required @if ($errors->has('kuota')) aria-describedby="kuota-error" @endif>
            <span class="field-hint">{{ $isEditing ? 'Jumlah pendaftar saat ini: '.$seminar->jumlah_pendaftar.'. Maksimal 1.000 peserta.' : 'Isi 1–1.000 peserta.' }}</span>
            <x-form-error field="kuota" />
        </div>

        <div class="form-field seminar-form-field">
            <label class="form-label" for="status">Status seminar</label>
            <select class="form-select @error('status') is-invalid-field @enderror" id="status" name="status" required @if ($errors->has('status')) aria-describedby="status-error" @endif>
                <option value="dibuka" @selected($nilai('status', $seminar->status ?? 'dibuka') === 'dibuka')>Dibuka</option>
                <option value="ditutup" @selected($nilai('status', $seminar->status ?? 'dibuka') === 'ditutup')>Ditutup</option>
            </select>
            <x-form-error field="status" />
        </div>

        <div class="form-field seminar-form-field seminar-form-field--full">
            <label class="form-label" for="flyer">Flyer seminar</label>
            <input class="form-control @error('flyer') is-invalid-field @enderror" id="flyer" name="flyer" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-flyer-input @if (! $isEditing) required @endif @if ($errors->has('flyer')) aria-describedby="flyer-hint flyer-error" @else aria-describedby="flyer-hint" @endif>
            <span class="field-hint" id="flyer-hint">{{ $isEditing ? 'JPG, JPEG, atau PNG. Maksimal 2 MB. Kosongkan jika tidak ingin mengganti flyer.' : 'JPG, JPEG, atau PNG. Maksimal 2 MB.' }}</span>
            <x-form-error field="flyer" />
            <div class="flyer-preview" data-flyer-preview-container>
                <img src="{{ asset('storage/'.($seminar->flyer ?? 'flyers/placeholder.svg')) }}" alt="Pratinjau flyer seminar" data-flyer-preview>
                <span data-flyer-preview-label>{{ $isEditing ? 'Flyer saat ini' : 'Pratinjau flyer' }}</span>
            </div>
        </div>
    </div>

    <div class="seminar-form-actions">
        <a class="button-secondary" href="{{ route('admin.seminars.index') }}">Batal</a>
        <button class="button-primary" type="submit"><i class="bi bi-check2" aria-hidden="true"></i>{{ $submitLabel }}</button>
    </div>
</form>

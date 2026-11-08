@extends('layouts.admin')

@section('title', 'Ubah seminar')

@section('content')
    <section class="seminar-form-page" aria-labelledby="seminar-form-title">
        <a class="back-link" href="{{ route('admin.seminars.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke seminar</a>
        <div class="seminar-form-heading">
            <h1 id="seminar-form-title">Ubah seminar</h1>
            <p>Perbarui informasi seminar sesuai kebutuhan.</p>
        </div>
        <x-alert-flash />
        @include('admin.seminars.form', [
            'action' => route('admin.seminars.update', $seminar),
            'submitLabel' => 'Simpan perubahan',
            'seminar' => $seminar,
        ])
    </section>
@endsection

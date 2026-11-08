@extends('layouts.admin')

@section('title', 'Tambah seminar')

@section('content')
    <section class="seminar-form-page" aria-labelledby="seminar-form-title">
        <a class="back-link" href="{{ route('admin.seminars.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke seminar</a>
        <div class="seminar-form-heading">
            <h1 id="seminar-form-title">Tambah seminar</h1>
            <p>Lengkapi informasi seminar yang akan dibuka untuk peserta.</p>
        </div>
        <x-alert-flash />
        @include('admin.seminars.form', ['action' => route('admin.seminars.store'), 'submitLabel' => 'Simpan seminar'])
    </section>
@endsection

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class SeminarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function commonRules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            'pemapar' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'min:20', 'max:500'],
            'lokasi' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'kuota' => ['required', 'integer', 'between:1,1000'],
            'status' => ['required', 'in:dibuka,ditutup'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul seminar wajib diisi.',
            'judul.string' => 'Judul seminar wajib diisi.',
            'judul.max' => 'Judul seminar maksimal 150 karakter.',
            'pemapar.required' => 'Nama pemapar materi wajib diisi.',
            'pemapar.string' => 'Nama pemapar materi wajib diisi.',
            'pemapar.max' => 'Nama pemapar materi maksimal 150 karakter.',
            'deskripsi.required' => 'Deskripsi singkat wajib diisi.',
            'deskripsi.string' => 'Deskripsi singkat wajib diisi.',
            'deskripsi.min' => 'Deskripsi singkat minimal 20 karakter.',
            'deskripsi.max' => 'Deskripsi singkat maksimal 500 karakter.',
            'flyer.required' => 'Flyer seminar wajib diunggah.',
            'flyer.image' => 'Flyer harus berformat JPG, JPEG, atau PNG.',
            'flyer.mimes' => 'Flyer harus berformat JPG, JPEG, atau PNG.',
            'flyer.max' => 'Ukuran flyer maksimal 2 MB.',
            'lokasi.required' => 'Lokasi seminar wajib diisi.',
            'lokasi.string' => 'Lokasi seminar wajib diisi.',
            'lokasi.max' => 'Lokasi seminar maksimal 150 karakter.',
            'tanggal.required' => 'Tanggal seminar wajib diisi.',
            'tanggal.date_format' => 'Tanggal seminar wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_mulai.date_format' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'waktu_selesai.date_format' => 'Waktu selesai wajib diisi.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
            'kuota.required' => 'Kuota wajib diisi.',
            'kuota.integer' => 'Kuota harus berupa bilangan bulat 1 sampai 1.000.',
            'kuota.between' => 'Kuota harus berupa bilangan bulat 1 sampai 1.000.',
            'status.required' => 'Status seminar tidak valid.',
            'status.in' => 'Status seminar tidak valid.',
        ];
    }
}

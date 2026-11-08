<?php

namespace Database\Seeders;

use App\Models\Seminar;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal aplikasi.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@seminar.test'],
            [
                'name' => 'Administrator Seminar',
                'password' => Hash::make('password123'),
            ],
        );

        $now = Carbon::now();
        $waktuMulaiHariIni = $now->copy()->subMinutes(11);
        $waktuSelesaiHariIni = $now->copy()->addHour();

        if (! $waktuMulaiHariIni->isSameDay($now) || ! $waktuSelesaiHariIni->isSameDay($now)) {
            $waktuMulaiHariIni = $now->copy()->startOfDay();
            $waktuSelesaiHariIni = $now->copy()->endOfDay();
        }

        $seminarContoh = [
            [
                'judul' => 'Presensi Seminar Teknologi Hari Ini',
                'pemapar' => 'Dr. Nadia Prameswari',
                'deskripsi' => 'Seminar teknologi dengan sesi presensi yang dapat dicoba pada hari ini.',
                'flyer' => 'flyers/placeholder.svg',
                'lokasi' => 'Auditorium Utama',
                'tanggal' => $now->toDateString(),
                'waktu_mulai' => $waktuMulaiHariIni->format('H:i:s'),
                'waktu_selesai' => $waktuSelesaiHariIni->format('H:i:s'),
                'kuota' => 60,
                'status' => 'dibuka',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Membangun Produk Digital yang Inklusif',
                'pemapar' => 'Raka Mahendra, M.Des.',
                'deskripsi' => 'Diskusi praktik merancang produk digital yang mudah digunakan oleh semua orang.',
                'flyer' => 'flyers/placeholder.svg',
                'lokasi' => 'Ruang Seminar A',
                'tanggal' => $now->copy()->addDays(7)->toDateString(),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '12:00:00',
                'kuota' => 120,
                'status' => 'dibuka',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Keamanan Data untuk Aplikasi Modern',
                'pemapar' => 'Dimas Wiratama, S.Kom., M.Kom.',
                'deskripsi' => 'Mengenal pendekatan praktis untuk menjaga data dalam pengembangan aplikasi.',
                'flyer' => 'flyers/placeholder.svg',
                'lokasi' => 'Ruang Konferensi B',
                'tanggal' => $now->copy()->addDays(10)->toDateString(),
                'waktu_mulai' => '13:00:00',
                'waktu_selesai' => '16:00:00',
                'kuota' => 80,
                'status' => 'ditutup',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Lokakarya Laravel untuk Pemula',
                'pemapar' => 'Sinta Ayuningtyas',
                'deskripsi' => 'Lokakarya pengenalan Laravel melalui latihan membangun aplikasi web sederhana.',
                'flyer' => 'flyers/placeholder.svg',
                'lokasi' => 'Laboratorium Komputer 2',
                'tanggal' => $now->copy()->addDays(14)->toDateString(),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '15:00:00',
                'kuota' => 2,
                'status' => 'dibuka',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'judul' => 'Etika dan Peluang Kecerdasan Buatan',
                'pemapar' => 'Prof. Bima Santoso, Ph.D.',
                'deskripsi' => 'Menimbang peluang dan tanggung jawab dalam penerapan kecerdasan buatan.',
                'flyer' => 'flyers/placeholder.svg',
                'lokasi' => 'Aula Cendekia',
                'tanggal' => $now->copy()->addDays(21)->toDateString(),
                'waktu_mulai' => '10:00:00',
                'waktu_selesai' => '12:30:00',
                'kuota' => 300,
                'status' => 'dibuka',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($seminarContoh as $dataSeminar) {
            Seminar::updateOrCreate(
                ['judul' => $dataSeminar['judul']],
                $dataSeminar,
            );
        }
    }
}

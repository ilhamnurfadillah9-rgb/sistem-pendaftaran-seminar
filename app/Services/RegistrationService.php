<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Seminar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    /**
     * @param  array{nama: string, email: string, telepon: string}  $data
     */
    public function register(Seminar $seminar, array $data): Registration
    {
        $email = Str::lower(trim($data['email']));

        return DB::transaction(function () use ($seminar, $data, $email): Registration {
            $lockedSeminar = Seminar::query()
                ->lockForUpdate()
                ->findOrFail($seminar->getKey());

            if ($lockedSeminar->status !== 'dibuka') {
                throw ValidationException::withMessages([
                    '_form' => 'Pendaftaran untuk seminar ini telah ditutup.',
                ]);
            }

            if ($lockedSeminar->registrations()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Email sudah terdaftar pada seminar ini.',
                ]);
            }

            if ($lockedSeminar->registrations()->where('telepon', $data['telepon'])->exists()) {
                throw ValidationException::withMessages([
                    'telepon' => 'Nomor telepon sudah terdaftar pada seminar ini.',
                ]);
            }

            if ($lockedSeminar->registrations()->count() >= $lockedSeminar->kuota) {
                throw ValidationException::withMessages([
                    '_form' => 'Kuota seminar telah penuh.',
                ]);
            }

            return $lockedSeminar->registrations()->create([
                'nama' => $data['nama'],
                'email' => $email,
                'telepon' => $data['telepon'],
                'token' => Str::random(40),
                'status' => 'terdaftar',
            ]);
        });
    }
}

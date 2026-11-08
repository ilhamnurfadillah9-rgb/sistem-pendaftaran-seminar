<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * @return array{success: false, message: string}|array{success: true, message: string, registration: Registration}
     */
    public function scan(string $token, ?Seminar $selectedSeminar = null): array
    {
        return DB::transaction(function () use ($token, $selectedSeminar): array {
            $registration = Registration::query()
                ->where('token', $token)
                ->lockForUpdate()
                ->first();

            if ($registration === null) {
                return ['success' => false, 'message' => 'Kode QR tidak valid.'];
            }

            $registration->load('seminar');

            $now = Carbon::now('Asia/Jakarta');
            $seminar = $registration->seminar;
            $startsAt = Carbon::parse(
                $seminar->tanggal->format('Y-m-d').' '.$seminar->waktu_mulai,
                'Asia/Jakarta',
            );
            $endsAt = Carbon::parse(
                $seminar->tanggal->format('Y-m-d').' '.$seminar->waktu_selesai,
                'Asia/Jakarta',
            );

            if ($now->lt($startsAt->addMinutes((int) config('seminar.menit_buka_presensi')))) {
                return [
                    'success' => false,
                    'message' => 'Kode QR belum aktif. Presensi dibuka 10 menit setelah seminar dimulai.',
                ];
            }

            if ($now->gt($endsAt)) {
                return [
                    'success' => false,
                    'message' => 'Kode QR telah kedaluwarsa karena seminar telah selesai.',
                ];
            }

            if ($registration->status === 'hadir') {
                return ['success' => false, 'message' => 'Kode QR sudah digunakan.'];
            }

            if ($selectedSeminar !== null && $registration->seminar_id !== $selectedSeminar->id) {
                return ['success' => false, 'message' => 'Kode QR bukan milik seminar yang dipilih.'];
            }

            $registration->update([
                'status' => 'hadir',
                'waktu_hadir' => $now,
            ]);

            return [
                'success' => true,
                'message' => 'Hadir',
                'registration' => $registration->refresh()->load('seminar'),
            ];
        });
    }

    public function markExpiredAsAbsent(?Seminar $seminar = null): int
    {
        $now = Carbon::now('Asia/Jakarta');

        return Registration::query()
            ->where('status', 'terdaftar')
            ->whereHas('seminar', function (Builder $query) use ($now, $seminar): void {
                $query->when($seminar !== null, fn (Builder $query): Builder => $query->whereKey($seminar->getKey()))
                    ->where(function (Builder $query) use ($now): void {
                        $query->whereDate('tanggal', '<', $now->toDateString())
                            ->orWhere(function (Builder $query) use ($now): void {
                                $query->whereDate('tanggal', $now->toDateString())
                                    ->where('waktu_selesai', '<', $now->format('H:i:s'));
                            });
                    });
            })
            ->update(['status' => 'tidak_hadir']);
    }
}

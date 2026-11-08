<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\SeminarFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seminar extends Model
{
    /** @use HasFactory<SeminarFactory> */
    use HasFactory;

    protected $fillable = [
        'judul',
        'pemapar',
        'deskripsi',
        'flyer',
        'lokasi',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'kuota',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kuota' => 'integer',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    protected function jumlahPendaftar(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) ($this->registrations_count ?? $this->registrations()->count()),
        );
    }

    protected function sisaKuota(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->kuota - $this->jumlah_pendaftar,
        );
    }

    protected function apakahPenuh(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->sisa_kuota <= 0,
        );
    }

    protected function waktuMulaiLengkap(): Attribute
    {
        return Attribute::make(
            get: fn (): Carbon => Carbon::parse(
                $this->tanggal->format('Y-m-d').' '.$this->waktu_mulai,
                config('app.timezone'),
            ),
        );
    }

    protected function waktuSelesaiLengkap(): Attribute
    {
        return Attribute::make(
            get: fn (): Carbon => Carbon::parse(
                $this->tanggal->format('Y-m-d').' '.$this->waktu_selesai,
                config('app.timezone'),
            ),
        );
    }
}

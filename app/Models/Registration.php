<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'seminar_id',
        'nama',
        'email',
        'telepon',
        'token',
        'status',
        'waktu_hadir',
    ];

    protected function casts(): array
    {
        return [
            'waktu_hadir' => 'datetime',
        ];
    }

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(Seminar::class);
    }
}

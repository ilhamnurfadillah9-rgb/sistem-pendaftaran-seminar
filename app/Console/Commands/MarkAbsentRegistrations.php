<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seminar:tandai-tidak-hadir')]
#[Description('Tandai peserta terdaftar dari seminar yang telah selesai sebagai tidak hadir')]
class MarkAbsentRegistrations extends Command
{
    public function handle(AttendanceService $attendanceService): int
    {
        $updatedCount = $attendanceService->markExpiredAsAbsent();

        $this->info("{$updatedCount} peserta ditandai tidak hadir.");

        return self::SUCCESS;
    }
}

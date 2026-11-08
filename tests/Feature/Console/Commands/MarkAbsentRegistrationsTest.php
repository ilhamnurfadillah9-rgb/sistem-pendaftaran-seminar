<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Registration;
use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarkAbsentRegistrationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $participantSequence = 0;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_command_marks_only_registered_participants_of_finished_seminars_absent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 11:01:00', 'Asia/Jakarta'));
        $finishedSeminar = $this->createSeminar('2026-11-08', '09:00:00', '11:00:00');
        $attendedParticipant = $this->createParticipant($finishedSeminar, 'hadir');
        $absentParticipant = $this->createParticipant($finishedSeminar, 'tidak_hadir');
        $registeredParticipant = $this->createParticipant($finishedSeminar);
        $futureSeminar = $this->createSeminar('2026-11-08', '12:00:00', '13:00:00');
        $futureParticipant = $this->createParticipant($futureSeminar);
        $this->artisan('seminar:tandai-tidak-hadir')
            ->expectsOutputToContain('1 peserta ditandai tidak hadir.')
            ->assertSuccessful();

        $this->assertDatabaseHas('registrations', [
            'id' => $registeredParticipant->id,
            'status' => 'tidak_hadir',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $attendedParticipant->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $absentParticipant->id,
            'status' => 'tidak_hadir',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $futureParticipant->id,
            'status' => 'terdaftar',
        ]);
    }

    public function test_command_does_not_mark_a_seminar_absent_until_after_its_end_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 11:00:00', 'Asia/Jakarta'));
        $seminar = $this->createSeminar('2026-11-08', '09:00:00', '11:00:00');
        $participant = $this->createParticipant($seminar);

        $this->artisan('seminar:tandai-tidak-hadir')
            ->expectsOutputToContain('0 peserta ditandai tidak hadir.')
            ->assertSuccessful();

        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
        ]);
    }

    private function createSeminar(string $date, string $startsAt, string $endsAt): Seminar
    {
        return Seminar::factory()->create([
            'tanggal' => $date,
            'waktu_mulai' => $startsAt,
            'waktu_selesai' => $endsAt,
        ]);
    }

    private function createParticipant(Seminar $seminar, string $status = 'terdaftar'): Registration
    {
        $this->participantSequence++;

        return $seminar->registrations()->create([
            'nama' => 'Peserta seminar',
            'email' => 'peserta'.$this->participantSequence.'@example.test',
            'telepon' => sprintf('08123456%04d', $this->participantSequence),
            'token' => Str::random(40),
            'status' => $status,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_scan_page_shows_a_pinned_camera_scanner_and_manual_token_input(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.scan'));

        $response->assertOk()
            ->assertSee($seminar->judul)
            ->assertSee('html5-qrcode@2.3.8')
            ->assertSee('name="token"', false)
            ->assertSee('Kamera hanya tersedia di HTTPS atau localhost.');
    }

    public function test_scan_page_redirects_unauthenticated_visitors_to_login(): void
    {
        $this->get(route('admin.attendance.scan'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_scan_page_shows_a_friendly_empty_state_when_no_seminars_exist(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.attendance.scan'));

        $response->assertOk()
            ->assertSee('Belum ada seminar')
            ->assertSee('Tambah seminar')
            ->assertDontSee('data-attendance-scan-form', false);
    }

    public function test_unknown_qr_token_returns_the_invalid_code_message(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();

        $response = $this->actingAs($admin)
            ->postJson(route('admin.attendance.scan.store'), [
                'token' => Str::random(40),
                'seminar_id' => $seminar->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Kode QR tidak valid.');
    }

    public function test_qr_token_is_rejected_one_minute_before_the_attendance_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 09:09:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.attendance.scan.store'), [
                'token' => $participant->token,
                'seminar_id' => $seminar->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Kode QR belum aktif. Presensi dibuka 10 menit setelah seminar dimulai.');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
            'waktu_hadir' => null,
        ]);
    }

    public function test_qr_token_is_accepted_exactly_ten_minutes_after_the_seminar_starts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 09:10:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.attendance.scan.store'), [
                'token' => $participant->token,
                'seminar_id' => $seminar->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Hadir')
            ->assertJsonPath('participant.nama', 'Nadia Prameswari');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'hadir',
        ]);
        $this->assertNotNull($participant->fresh()->waktu_hadir);
    }

    public function test_qr_token_is_rejected_one_minute_after_the_seminar_ends(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 11:01:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.attendance.scan.store'), [
                'token' => $participant->token,
                'seminar_id' => $seminar->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Kode QR telah kedaluwarsa karena seminar telah selesai.');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
            'waktu_hadir' => null,
        ]);
    }

    public function test_scanning_an_attended_qr_again_returns_the_used_code_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 10:00:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);
        $this->actingAs($admin);

        $this->postJson(route('admin.attendance.scan.store'), [
            'token' => $participant->token,
            'seminar_id' => $seminar->id,
        ])->assertOk();

        $this->postJson(route('admin.attendance.scan.store'), [
            'token' => $participant->token,
            'seminar_id' => $seminar->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Kode QR sudah digunakan.');

        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'hadir',
        ]);
    }

    public function test_qr_from_another_seminar_is_not_marked_present(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 10:00:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $participantSeminar = $this->createSeminar();
        $selectedSeminar = $this->createSeminar();
        $participant = $this->createParticipant($participantSeminar);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.attendance.scan.store'), [
                'token' => $participant->token,
                'seminar_id' => $selectedSeminar->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Kode QR bukan milik seminar yang dipilih.');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
            'waktu_hadir' => null,
        ]);
    }

    public function test_admin_can_correct_attendance_status_and_see_it_after_reloading(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->put(route('admin.seminars.attendance.update', [$seminar, $participant]), [
                'status' => 'hadir',
            ]);

        $response->assertRedirect(route('admin.seminars.attendance.index', $seminar))
            ->assertSessionHas('success', 'Status kehadiran berhasil diperbarui.');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'hadir',
        ]);

        $this->get(route('admin.seminars.attendance.index', $seminar))
            ->assertOk()
            ->assertSee('Hadir');
    }

    public function test_admin_cannot_correct_attendance_back_to_registered(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->from(route('admin.seminars.attendance.index', $seminar))
            ->put(route('admin.seminars.attendance.update', [$seminar, $participant]), [
                'status' => 'terdaftar',
            ]);

        $response->assertRedirect(route('admin.seminars.attendance.index', $seminar))
            ->assertSessionHasErrors([
                'status' => 'Status tidak dapat dikembalikan menjadi terdaftar.',
            ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
        ]);
    }

    public function test_admin_cannot_set_an_unsupported_attendance_status(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $response = $this->actingAs($admin)
            ->from(route('admin.seminars.attendance.index', $seminar))
            ->put(route('admin.seminars.attendance.update', [$seminar, $participant]), [
                'status' => 'izin',
            ]);

        $response->assertRedirect(route('admin.seminars.attendance.index', $seminar))
            ->assertSessionHasErrors([
                'status' => 'Status kehadiran tidak valid.',
            ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
        ]);
    }

    public function test_attendance_correction_cannot_update_a_participant_from_another_seminar(): void
    {
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $otherSeminar = $this->createSeminar();
        $participant = $this->createParticipant($otherSeminar);

        $this->actingAs($admin)
            ->put(route('admin.seminars.attendance.update', [$seminar, $participant]), [
                'status' => 'hadir',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'terdaftar',
        ]);
    }

    public function test_opening_the_participant_list_marks_expired_registrations_absent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 11:01:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $this->actingAs($admin)
            ->get(route('admin.seminars.participants.index', $seminar))
            ->assertOk();

        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'tidak_hadir',
        ]);
    }

    public function test_opening_the_attendance_list_marks_expired_registrations_absent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 11:01:00', 'Asia/Jakarta'));
        $admin = User::factory()->create();
        $seminar = $this->createSeminar();
        $participant = $this->createParticipant($seminar);

        $this->actingAs($admin)
            ->get(route('admin.seminars.attendance.index', $seminar))
            ->assertOk();

        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'status' => 'tidak_hadir',
        ]);
    }

    private function createSeminar(): Seminar
    {
        return Seminar::factory()->create([
            'tanggal' => '2026-11-08',
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '11:00:00',
        ]);
    }

    private function createParticipant(Seminar $seminar): Registration
    {
        return $seminar->registrations()->create([
            'nama' => 'Nadia Prameswari',
            'email' => 'nadia@example.test',
            'telepon' => '081234567890',
            'token' => Str::random(40),
            'status' => 'terdaftar',
        ]);
    }
}

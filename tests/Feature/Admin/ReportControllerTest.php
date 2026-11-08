<?php

namespace Tests\Feature\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_report_counts_each_status_and_remaining_quota_from_the_selected_seminar(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create([
            'judul' => 'Seminar Rekap Khusus',
            'kuota' => 8,
        ]);
        $otherSeminar = Seminar::factory()->create([
            'judul' => 'Seminar Lain',
            'kuota' => 20,
        ]);

        $this->createRegistration($seminar, 'Hadir Satu', 'hadir');
        $this->createRegistration($seminar, 'Hadir Dua', 'hadir');
        $this->createRegistration($seminar, 'Tidak Hadir', 'tidak_hadir');
        $this->createRegistration($seminar, 'Terdaftar Satu', 'terdaftar');
        $this->createRegistration($seminar, 'Terdaftar Dua', 'terdaftar');
        $this->createRegistration($otherSeminar, 'Peserta Seminar Lain', 'hadir');

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'seminar_id' => $seminar->id,
        ]));

        $response->assertOk()
            ->assertSee('Seminar Rekap Khusus')
            ->assertSee('Jumlah pendaftar')
            ->assertSee('Belum dicatat')
            ->assertViewHas('reportMetrics', function (array $metrics): bool {
                return $metrics === [
                    'registrations' => 5,
                    'attended' => 2,
                    'absent' => 1,
                    'unrecorded' => 2,
                    'remaining_quota' => 3,
                    'attendance_percentage' => 40,
                    'quota_percentage' => 63,
                ];
            });
    }

    public function test_report_exports_the_selected_seminars_registrations_as_csv(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create(['judul' => 'Rekap CSV']);
        $registration = $this->createRegistration($seminar, '=Peserta CSV', 'hadir');

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export', ['seminar_id' => $seminar->id]));

        $response->assertOk()
            ->assertDownload('laporan-seminar-'.$seminar->id.'.csv')
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString("'=Peserta CSV", $response->streamedContent());
        $this->assertStringContainsString($registration->email, $response->streamedContent());
    }

    public function test_report_requires_authentication(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('admin.login'));
    }

    private function createRegistration(Seminar $seminar, string $name, string $status): Registration
    {
        return $seminar->registrations()->create([
            'nama' => $name,
            'email' => Str::slug($name).'@example.test',
            'telepon' => fake()->unique()->numerify('08##########'),
            'token' => Str::random(40),
            'status' => $status,
        ]);
    }
}

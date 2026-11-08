<?php

namespace Tests\Feature\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_displays_real_statistics_and_related_seminars(): void
    {
        $admin = User::factory()->create();
        $firstSeminar = Seminar::factory()->create([
            'judul' => 'Seminar Data Terbuka',
            'tanggal' => now()->addDays(1)->toDateString(),
            'status' => 'dibuka',
            'kuota' => 10,
        ]);
        $secondSeminar = Seminar::factory()->create([
            'judul' => 'Seminar Desain Terbuka',
            'tanggal' => now()->addDays(2)->toDateString(),
            'status' => 'dibuka',
            'kuota' => 8,
        ]);
        $pastSeminar = Seminar::factory()->create([
            'judul' => 'Seminar Lampau',
            'tanggal' => now()->subDay()->toDateString(),
            'status' => 'ditutup',
        ]);

        $this->createRegistration($firstSeminar, 'Peserta Hadir Satu', 'hadir');
        $this->createRegistration($firstSeminar, 'Peserta Hadir Dua', 'hadir');
        $this->createRegistration($firstSeminar, 'Peserta Terdaftar Satu', 'terdaftar');
        $this->createRegistration($secondSeminar, 'Peserta Tidak Hadir', 'tidak_hadir');
        $this->createRegistration($secondSeminar, 'Peserta Terdaftar Dua', 'terdaftar');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Total seminar')
            ->assertSee('3')
            ->assertSee('Total pendaftar')
            ->assertSee('5')
            ->assertSee('Seminar dibuka')
            ->assertSee('2')
            ->assertSee('40%')
            ->assertSee('Seminar Data Terbuka')
            ->assertSee('Seminar Desain Terbuka')
            ->assertDontSee('Seminar Lampau')
            ->assertSee('Peserta Tidak Hadir')
            ->assertViewHas('statistics', function (array $statistics): bool {
                return $statistics === [
                    'total_seminars' => 3,
                    'total_registrations' => 5,
                    'attended_registrations' => 2,
                    'open_seminars' => 2,
                    'attendance_percentage' => 40,
                ];
            });
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_dashboard_shows_zero_attendance_when_there_are_no_registrations(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('statistics', function (array $statistics): bool {
                return $statistics['total_registrations'] === 0
                    && $statistics['attended_registrations'] === 0
                    && $statistics['attendance_percentage'] === 0;
            });
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

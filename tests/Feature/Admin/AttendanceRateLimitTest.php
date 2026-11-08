<?php

namespace Tests\Feature\Admin;

use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceRateLimitTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_scan_endpoint_returns_too_many_requests_after_the_configured_limit(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $this->actingAs($admin);

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->postJson(route('admin.attendance.scan.store'), [
                'token' => Str::random(40),
                'seminar_id' => $seminar->id,
            ])->assertUnprocessable();
        }

        $response = $this->postJson(route('admin.attendance.scan.store'), [
            'token' => Str::random(40),
            'seminar_id' => $seminar->id,
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu banyak pemindaian dalam waktu singkat. Coba lagi nanti.');
        $this->assertDatabaseCount('registrations', 0);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_not_found_error_is_rendered_in_indonesian(): void
    {
        config(['app.debug' => false]);
        Route::get('/__test-errors/not-found', fn () => abort(404));

        $response = $this->get('/__test-errors/not-found');

        $response->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Kembali ke beranda')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_forbidden_error_is_rendered_in_indonesian(): void
    {
        config(['app.debug' => false]);
        Route::get('/__test-errors/forbidden', fn () => abort(403));

        $response = $this->get('/__test-errors/forbidden');

        $response->assertForbidden()
            ->assertSee('Akses tidak diizinkan');
    }

    public function test_expired_session_error_is_rendered_in_indonesian(): void
    {
        config(['app.debug' => false]);
        Route::get('/__test-errors/session-expired', fn () => abort(419));

        $response = $this->get('/__test-errors/session-expired');

        $response->assertStatus(419)
            ->assertSee('Sesi berakhir')
            ->assertSee('Kembali ke beranda');
    }

    public function test_server_error_is_rendered_without_exposing_exception_details(): void
    {
        config(['app.debug' => false]);
        Route::get('/__test-errors/server-error', function (): never {
            throw new RuntimeException('internal secret diagnostic');
        });

        $response = $this->get('/__test-errors/server-error');

        $response->assertServerError()
            ->assertSee('Layanan sedang mengalami gangguan')
            ->assertDontSee('internal secret diagnostic');
    }
}

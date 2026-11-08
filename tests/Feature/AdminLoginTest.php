<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays_email_and_password_fields(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk()
            ->assertSee('Masuk ke dashboard')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_missing_email_displays_the_required_message(): void
    {
        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['password' => 'password123']);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'Email wajib diisi.']);
    }

    public function test_missing_password_displays_the_required_message(): void
    {
        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['email' => 'admin@seminar.test']);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['password' => 'Kata sandi wajib diisi.']);
    }

    public function test_invalid_email_format_displays_the_validation_message(): void
    {
        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'alamat-tidak-valid',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'Format email tidak valid.']);
    }

    public function test_invalid_email_or_password_displays_the_generic_error(): void
    {
        $response = $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin@seminar.test',
                'password' => 'kata-sandi-salah',
            ]);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.'])
            ->assertSessionHas('_old_input.email', 'admin@seminar.test');
        $this->assertGuest();
    }

    public function test_valid_credentials_authenticate_and_open_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Seminar',
            'email' => 'admin@seminar.test',
            'password' => 'password123',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => ' ADMIN@SEMINAR.TEST ',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Selamat datang,')
            ->assertSee('Admin Seminar')
            ->assertSee('Seminar dibuka')
            ->assertSee('Total pendaftar')
            ->assertSee('Rata-rata kehadiran')
            ->assertSee('Kehadiran keseluruhan');
    }

    public function test_unauthenticated_dashboard_request_redirects_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_login_attempts_are_limited_and_show_an_indonesian_message(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), [
                'email' => 'admin@seminar.test',
                'password' => 'kata-sandi-salah',
            ]);
        }

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@seminar.test',
            'password' => 'kata-sandi-salah',
        ]);

        $response->assertStatus(429)
            ->assertSessionHasErrors(['email' => 'Terlalu banyak percobaan masuk. Coba lagi nanti.']);
    }

    public function test_logout_invalidates_the_admin_session(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}

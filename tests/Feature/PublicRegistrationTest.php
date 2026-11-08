<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Seminar;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_catalog_explains_when_no_seminars_are_available(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Belum ada seminar')
            ->assertSee('Seminar yang tersedia akan muncul di sini.');
    }

    public function test_valid_registration_reduces_remaining_capacity_and_shows_a_qr_card(): void
    {
        $seminar = Seminar::factory()->create(['kuota' => 2]);

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'email' => '  Budi@Example.com ',
            ]));

        $registration = Registration::query()->sole();
        $response->assertRedirect(route('registrations.show', $registration->token));
        $this->assertSame(40, strlen($registration->token));
        $this->assertSame('budi@example.com', $registration->email);
        $this->assertSame('terdaftar', $registration->status);
        $this->assertDatabaseHas('registrations', [
            'seminar_id' => $seminar->id,
            'email' => 'budi@example.com',
            'status' => 'terdaftar',
        ]);
        $seminar->loadCount('registrations');
        $this->assertSame(1, $seminar->sisa_kuota);

        $this->get(route('registrations.show', $registration->token))
            ->assertOk()
            ->assertSee($registration->nama)
            ->assertSee($registration->email)
            ->assertSee($seminar->judul)
            ->assertSee('Tunjukkan QR ini kepada panitia. Presensi dibuka 10 menit setelah seminar dimulai.')
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_closed_seminar_rejects_registration_before_duplicate_checks(): void
    {
        $seminar = Seminar::factory()->create();
        $this->post(route('registrations.store', $seminar), $this->registrationData());
        $seminar->update(['status' => 'ditutup']);

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData());

        $response->assertRedirect(route('seminar.show', $seminar))
            ->assertSessionHasErrors([
                '_form' => 'Pendaftaran untuk seminar ini telah ditutup.',
            ]);
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_email_is_trimmed_lowercased_and_cannot_be_reused_in_the_same_seminar(): void
    {
        $seminar = Seminar::factory()->create();
        $this->post(route('registrations.store', $seminar), $this->registrationData([
            'email' => ' BUDI@Example.com ',
        ]))->assertRedirect();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'email' => ' budi@example.COM ',
                'telepon' => '081234567891',
            ]));

        $response->assertSessionHasErrors([
            'email' => 'Email sudah terdaftar pada seminar ini.',
        ]);
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_phone_number_cannot_be_reused_in_the_same_seminar(): void
    {
        $seminar = Seminar::factory()->create();
        $this->post(route('registrations.store', $seminar), $this->registrationData())
            ->assertRedirect();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'email' => 'other@example.test',
            ]));

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon sudah terdaftar pada seminar ini.',
        ]);
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_third_registration_is_rejected_when_two_places_are_available(): void
    {
        $seminar = Seminar::factory()->create(['kuota' => 2]);
        $this->post(route('registrations.store', $seminar), $this->registrationData())
            ->assertRedirect();
        $this->post(route('registrations.store', $seminar), $this->registrationData([
            'email' => 'second@example.test',
            'telepon' => '081234567891',
        ]))->assertRedirect();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'email' => 'third@example.test',
                'telepon' => '081234567892',
            ]));

        $response->assertSessionHasErrors(['_form' => 'Kuota seminar telah penuh.']);
        $this->assertDatabaseCount('registrations', 2);
    }

    public function test_same_email_can_register_for_a_different_seminar(): void
    {
        $firstSeminar = Seminar::factory()->create();
        $secondSeminar = Seminar::factory()->create();
        $this->post(route('registrations.store', $firstSeminar), $this->registrationData())
            ->assertRedirect();

        $response = $this->post(
            route('registrations.store', $secondSeminar),
            $this->registrationData(),
        );

        $response->assertRedirect();
        $this->assertDatabaseCount('registrations', 2);
        $this->assertDatabaseHas('registrations', [
            'seminar_id' => $secondSeminar->id,
            'email' => 'peserta@example.test',
        ]);
    }

    public function test_empty_fields_show_their_messages_and_keep_other_values(): void
    {
        $seminar = Seminar::factory()->create();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), [
                'nama' => '',
                'email' => '',
                'telepon' => '',
            ]);

        $response->assertRedirect(route('seminar.show', $seminar))
            ->assertSessionHasErrors([
                'nama' => 'Nama wajib diisi.',
                'email' => 'Email wajib diisi.',
                'telepon' => 'Nomor telepon wajib diisi.',
            ])
            ->assertSessionHas('_old_input.nama', '');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_name_longer_than_one_hundred_characters_is_rejected(): void
    {
        $seminar = Seminar::factory()->create();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'nama' => str_repeat('a', 101),
            ]));

        $response->assertSessionHasErrors(['nama' => 'Nama maksimal 100 karakter.']);
        $this->assertDatabaseCount('registrations', 0);
    }

    #[DataProvider('invalidPhoneNumbers')]
    public function test_phone_number_must_contain_ten_to_thirteen_digits(string $phone): void
    {
        $seminar = Seminar::factory()->create();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'telepon' => $phone,
            ]));

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon harus terdiri dari 10 sampai 13 digit angka.',
        ]);
        $this->assertDatabaseCount('registrations', 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidPhoneNumbers(): array
    {
        return [
            'sembilan digit' => [str_repeat('1', 9)],
            'empat belas digit' => [str_repeat('1', 14)],
            'mengandung huruf' => ['08abc'],
        ];
    }

    #[DataProvider('validPhoneNumbers')]
    public function test_phone_number_accepts_ten_and_thirteen_digits(string $phone): void
    {
        $seminar = Seminar::factory()->create();

        $response = $this->post(
            route('registrations.store', $seminar),
            $this->registrationData(['telepon' => $phone]),
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'seminar_id' => $seminar->id,
            'telepon' => $phone,
        ]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validPhoneNumbers(): array
    {
        return [
            'sepuluh digit' => [str_repeat('1', 10)],
            'tiga belas digit' => [str_repeat('1', 13)],
        ];
    }

    #[DataProvider('invalidEmails')]
    public function test_invalid_email_formats_are_rejected(string $email): void
    {
        $seminar = Seminar::factory()->create();

        $response = $this->from(route('seminar.show', $seminar))
            ->post(route('registrations.store', $seminar), $this->registrationData([
                'email' => $email,
            ]));

        $response->assertSessionHasErrors(['email' => 'Format email tidak valid.']);
        $this->assertDatabaseCount('registrations', 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEmails(): array
    {
        return [
            'alamat tanpa domain' => ['budi@'],
            'alamat tanpa tanda at' => ['budi.example.com'],
        ];
    }

    public function test_homepage_searches_seminar_titles_and_shows_capacity_states(): void
    {
        $seminar = Seminar::factory()->create([
            'judul' => 'Lokakarya Desain Kampus',
            'kuota' => 1,
        ]);
        $this->post(route('registrations.store', $seminar), $this->registrationData())
            ->assertRedirect();
        Seminar::factory()->create([
            'judul' => 'Diskusi Sains Terbuka',
            'status' => 'ditutup',
        ]);

        $response = $this->get(route('home', ['search' => 'Desain']));

        $response->assertOk()
            ->assertSee('Lokakarya Desain Kampus')
            ->assertSee('Penuh')
            ->assertDontSee('Diskusi Sains Terbuka');
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array{nama: string, email: string, telepon: string}
     */
    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Nadia Prameswari',
            'email' => 'peserta@example.test',
            'telepon' => '081234567890',
        ], $overrides);
    }
}

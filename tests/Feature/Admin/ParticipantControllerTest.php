<?php

namespace Tests\Feature\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ParticipantControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_list_searches_by_name_and_email(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $this->createParticipant($seminar, 'Nadia Prameswari', 'nadia@example.test', '081234567890');
        $this->createParticipant($seminar, 'Raka Mahendra', 'raka@example.test', '081234567891');

        $nameResponse = $this->actingAs($admin)
            ->get(route('admin.seminars.participants.index', [
                'seminar' => $seminar,
                'search' => 'Nadia',
            ]));

        $nameResponse->assertOk()
            ->assertSee('Nadia Prameswari')
            ->assertDontSee('Raka Mahendra');

        $emailResponse = $this->get(route('admin.seminars.participants.index', [
            'seminar' => $seminar,
            'search' => 'raka@example.test',
        ]));

        $emailResponse->assertOk()
            ->assertSee('Raka Mahendra')
            ->assertDontSee('Nadia Prameswari');
    }

    public function test_participant_list_paginates_results(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();

        for ($number = 1; $number <= 11; $number++) {
            $this->createParticipant(
                $seminar,
                'Peserta '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'peserta'.$number.'@example.test',
                '081234567'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            );
        }

        $firstPage = $this->actingAs($admin)
            ->get(route('admin.seminars.participants.index', $seminar));

        $firstPage->assertOk()
            ->assertSee('page=2', false)
            ->assertDontSee('Peserta 01');

        $this->get(route('admin.seminars.participants.index', [
            'seminar' => $seminar,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Peserta 01');
    }

    public function test_valid_participant_update_normalizes_email_and_persists_changes(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $participant = $this->createParticipant(
            $seminar,
            'Nama lama',
            'lama@example.test',
            '081234567890',
        );

        $response = $this->actingAs($admin)
            ->put(route('admin.seminars.participants.update', [$seminar, $participant]), [
                'nama' => 'Nama baru',
                'email' => '  BARU@Example.test ',
                'telepon' => '081234567891',
            ]);

        $response->assertRedirect(route('admin.seminars.participants.index', $seminar))
            ->assertSessionHas('success', 'Data peserta berhasil diperbarui.');
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'nama' => 'Nama baru',
            'email' => 'baru@example.test',
            'telepon' => '081234567891',
        ]);
    }

    public function test_duplicate_email_is_rejected_without_changing_participant_data(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $this->createParticipant($seminar, 'Peserta pertama', 'pertama@example.test', '081234567890');
        $participant = $this->createParticipant(
            $seminar,
            'Peserta kedua',
            'kedua@example.test',
            '081234567891',
        );

        $response = $this->actingAs($admin)
            ->from(route('admin.seminars.participants.edit', [$seminar, $participant]))
            ->put(route('admin.seminars.participants.update', [$seminar, $participant]), [
                'nama' => 'Peserta kedua',
                'email' => ' PERTAMA@EXAMPLE.TEST ',
                'telepon' => '081234567891',
            ]);

        $response->assertRedirect(route('admin.seminars.participants.edit', [$seminar, $participant]))
            ->assertSessionHasErrors([
                'email' => 'Email sudah terdaftar pada seminar ini.',
            ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'email' => 'kedua@example.test',
        ]);
    }

    public function test_invalid_email_format_is_rejected_with_the_email_error(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $participant = $this->createParticipant(
            $seminar,
            'Peserta seminar',
            'peserta@example.test',
            '081234567890',
        );

        $response = $this->actingAs($admin)
            ->from(route('admin.seminars.participants.edit', [$seminar, $participant]))
            ->put(route('admin.seminars.participants.update', [$seminar, $participant]), [
                'nama' => 'Peserta seminar',
                'email' => 'budi@',
                'telepon' => '081234567890',
            ]);

        $response->assertSessionHasErrors(['email' => 'Format email tidak valid.']);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'email' => 'peserta@example.test',
        ]);
    }

    public function test_email_can_be_reused_from_a_different_seminar(): void
    {
        $admin = User::factory()->create();
        $firstSeminar = Seminar::factory()->create();
        $secondSeminar = Seminar::factory()->create();
        $this->createParticipant($firstSeminar, 'Peserta pertama', 'sama@example.test', '081234567890');
        $participant = $this->createParticipant(
            $secondSeminar,
            'Peserta kedua',
            'lain@example.test',
            '081234567891',
        );

        $response = $this->actingAs($admin)
            ->put(route('admin.seminars.participants.update', [$secondSeminar, $participant]), [
                'nama' => 'Peserta kedua',
                'email' => 'sama@example.test',
                'telepon' => '081234567891',
            ]);

        $response->assertRedirect(route('admin.seminars.participants.index', $secondSeminar));
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'seminar_id' => $secondSeminar->id,
            'email' => 'sama@example.test',
        ]);
    }

    public function test_duplicate_phone_number_is_rejected_on_update(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $this->createParticipant($seminar, 'Peserta pertama', 'pertama@example.test', '081234567890');
        $participant = $this->createParticipant(
            $seminar,
            'Peserta kedua',
            'kedua@example.test',
            '081234567891',
        );

        $response = $this->actingAs($admin)
            ->put(route('admin.seminars.participants.update', [$seminar, $participant]), [
                'nama' => 'Peserta kedua',
                'email' => 'kedua@example.test',
                'telepon' => '081234567890',
            ]);

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon sudah terdaftar pada seminar ini.',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'telepon' => '081234567891',
        ]);
    }

    #[DataProvider('invalidPhoneNumbers')]
    public function test_invalid_phone_number_is_rejected_on_update(string $phone): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $participant = $this->createParticipant(
            $seminar,
            'Peserta seminar',
            'peserta@example.test',
            '081234567890',
        );

        $response = $this->actingAs($admin)
            ->put(route('admin.seminars.participants.update', [$seminar, $participant]), [
                'nama' => 'Peserta seminar',
                'email' => 'peserta@example.test',
                'telepon' => $phone,
            ]);

        $response->assertSessionHasErrors([
            'telepon' => 'Nomor telepon harus terdiri dari 10 sampai 13 digit angka.',
        ]);
        $this->assertDatabaseHas('registrations', [
            'id' => $participant->id,
            'telepon' => '081234567890',
        ]);
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

    public function test_deleting_participant_restores_available_capacity(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create(['kuota' => 3]);
        $participant = $this->createParticipant(
            $seminar,
            'Peserta yang dihapus',
            'hapus@example.test',
            '081234567890',
        );
        $this->createParticipant($seminar, 'Peserta tersisa', 'sisa@example.test', '081234567891');

        $response = $this->actingAs($admin)
            ->delete(route('admin.seminars.participants.destroy', [$seminar, $participant]));

        $response->assertRedirect(route('admin.seminars.participants.index', $seminar))
            ->assertSessionHas('success', 'Peserta berhasil dihapus.');
        $this->assertDatabaseMissing('registrations', ['id' => $participant->id]);
        $seminar->loadCount('registrations');
        $this->assertSame(2, $seminar->sisa_kuota);
    }

    public function test_participant_qr_modal_links_to_the_confirmation_page(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $participant = $this->createParticipant(
            $seminar,
            'Nadia Prameswari',
            'nadia@example.test',
            '081234567890',
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.seminars.participants.index', $seminar));

        $response->assertOk()
            ->assertSee(route('admin.seminars.participants.qr', [$seminar, $participant]), false)
            ->assertSee(route('registrations.show', $participant->token), false);

        $this->get(route('admin.seminars.participants.qr', [$seminar, $participant]))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
            ->assertSee('<svg', false);
    }

    public function test_nested_participant_from_another_seminar_returns_not_found(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $otherSeminar = Seminar::factory()->create();
        $participant = $this->createParticipant(
            $otherSeminar,
            'Peserta seminar lain',
            'lain@example.test',
            '081234567890',
        );

        $this->actingAs($admin)
            ->get(route('admin.seminars.participants.edit', [$seminar, $participant]))
            ->assertNotFound();
    }

    public function test_participant_management_redirects_unauthenticated_visitors_to_login(): void
    {
        $seminar = Seminar::factory()->create();

        $this->get(route('admin.seminars.participants.index', $seminar))
            ->assertRedirect(route('admin.login'));
    }

    public function test_participant_name_is_escaped_in_the_admin_list(): void
    {
        $admin = User::factory()->create();
        $seminar = Seminar::factory()->create();
        $this->createParticipant(
            $seminar,
            '<script>alert(1)</script>',
            'aman@example.test',
            '081234567890',
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.seminars.participants.index', $seminar));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function createParticipant(
        Seminar $seminar,
        string $name,
        string $email,
        string $phone,
    ): Registration {
        return $seminar->registrations()->create([
            'nama' => $name,
            'email' => $email,
            'telepon' => $phone,
            'token' => Str::random(40),
            'status' => 'terdaftar',
        ]);
    }
}

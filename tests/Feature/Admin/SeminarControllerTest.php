<?php

namespace Tests\Feature\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeminarControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_seminar_is_created_with_a_random_public_flyer(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('admin.seminars.store'), $this->seminarData());

        $seminar = Seminar::query()->firstOrFail();
        $response->assertRedirect(route('admin.seminars.show', $seminar))
            ->assertSessionHas('success', 'Seminar berhasil ditambahkan.');
        $this->assertModelExists($seminar);
        $this->assertStringStartsWith('flyers/', $seminar->flyer);
        $this->assertSame('png', pathinfo($seminar->flyer, PATHINFO_EXTENSION));
        $this->assertNotSame('seminar.png', basename($seminar->flyer));
        Storage::disk('public')->assertExists($seminar->flyer);
    }

    public function test_missing_seminar_fields_display_their_exact_required_messages(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->from(route('admin.seminars.create'))
            ->post(route('admin.seminars.store'), []);

        $response->assertRedirect(route('admin.seminars.create'))
            ->assertSessionHasErrors([
                'judul' => 'Judul seminar wajib diisi.',
                'pemapar' => 'Nama pemapar materi wajib diisi.',
                'deskripsi' => 'Deskripsi singkat wajib diisi.',
                'flyer' => 'Flyer seminar wajib diunggah.',
                'lokasi' => 'Lokasi seminar wajib diisi.',
                'tanggal' => 'Tanggal seminar wajib diisi.',
                'waktu_mulai' => 'Waktu mulai wajib diisi.',
                'waktu_selesai' => 'Waktu selesai wajib diisi.',
                'kuota' => 'Kuota wajib diisi.',
                'status' => 'Status seminar tidak valid.',
            ]);
        $this->assertDatabaseCount('seminars', 0);
    }

    #[DataProvider('blankTextFields')]
    public function test_text_fields_containing_only_spaces_are_rejected(
        string $field,
        string $message,
    ): void {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post(
            route('admin.seminars.store'),
            $this->seminarData([$field => '   ']),
        );

        $response->assertSessionHasErrors([$field => $message]);
        $this->assertDatabaseCount('seminars', 0);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function blankTextFields(): array
    {
        return [
            'judul' => ['judul', 'Judul seminar wajib diisi.'],
            'pemapar' => ['pemapar', 'Nama pemapar materi wajib diisi.'],
            'deskripsi' => ['deskripsi', 'Deskripsi singkat wajib diisi.'],
        ];
    }

    #[DataProvider('overlongTextFields')]
    public function test_text_fields_respect_their_maximum_lengths(
        string $field,
        string $value,
        string $message,
    ): void {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post(
            route('admin.seminars.store'),
            $this->seminarData([$field => $value]),
        );

        $response->assertSessionHasErrors([$field => $message]);
        $this->assertDatabaseCount('seminars', 0);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function overlongTextFields(): array
    {
        return [
            'judul 151 karakter' => [
                'judul',
                str_repeat('a', 151),
                'Judul seminar maksimal 150 karakter.',
            ],
            'pemapar 151 karakter' => [
                'pemapar',
                str_repeat('a', 151),
                'Nama pemapar materi maksimal 150 karakter.',
            ],
            'deskripsi 501 karakter' => [
                'deskripsi',
                str_repeat('a', 501),
                'Deskripsi singkat maksimal 500 karakter.',
            ],
            'lokasi 151 karakter' => [
                'lokasi',
                str_repeat('a', 151),
                'Lokasi seminar maksimal 150 karakter.',
            ],
        ];
    }

    public function test_description_must_have_at_least_twenty_characters(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post(
            route('admin.seminars.store'),
            $this->seminarData(['deskripsi' => 'Terlalu pendek']),
        );

        $response->assertSessionHasErrors([
            'deskripsi' => 'Deskripsi singkat minimal 20 karakter.',
        ]);
        $this->assertDatabaseCount('seminars', 0);
    }

    #[DataProvider('quotaCases')]
    public function test_quota_obeys_the_integer_range(int|string $quota, bool $isValid): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $data = $this->seminarData(['kuota' => $quota]);

        $response = $this->post(route('admin.seminars.store'), $data);

        if ($isValid) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('seminars', ['kuota' => $quota]);

            return;
        }

        $response->assertSessionHasErrors([
            'kuota' => 'Kuota harus berupa bilangan bulat 1 sampai 1.000.',
        ]);
        $this->assertDatabaseCount('seminars', 0);
    }

    /**
     * @return array<string, array{int|string, bool}>
     */
    public static function quotaCases(): array
    {
        return [
            'batas bawah tidak valid' => [0, false],
            'batas bawah valid' => [1, true],
            'batas atas valid' => [1000, true],
            'melewati batas atas' => [1001, false],
            'bilangan desimal' => ['2.5', false],
            'teks bukan bilangan' => ['abc', false],
        ];
    }

    #[DataProvider('invalidTimeRanges')]
    public function test_end_time_must_be_after_start_time(string $start, string $end): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $data = $this->seminarData([
            'waktu_mulai' => $start,
            'waktu_selesai' => $end,
        ]);

        $response = $this->post(route('admin.seminars.store'), $data);

        $response->assertSessionHasErrors([
            'waktu_selesai' => 'Waktu selesai harus setelah waktu mulai.',
        ]);
        $this->assertDatabaseCount('seminars', 0);
        Storage::disk('public')->assertDirectoryEmpty('flyers');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidTimeRanges(): array
    {
        return [
            'waktu sama' => ['09:00', '09:00'],
            'waktu selesai lebih awal' => ['09:01', '09:00'],
        ];
    }

    public function test_past_date_is_rejected_when_creating_a_seminar(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->travelTo(now()->startOfDay());
        $data = $this->seminarData(['tanggal' => now()->subDay()->toDateString()]);

        $response = $this->post(route('admin.seminars.store'), $data);

        $response->assertSessionHasErrors([
            'tanggal' => 'Tanggal seminar tidak boleh sebelum hari ini.',
        ]);
        $this->assertDatabaseCount('seminars', 0);
    }

    public function test_invalid_seminar_status_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post(
            route('admin.seminars.store'),
            $this->seminarData(['status' => 'ditunda']),
        );

        $response->assertSessionHasErrors(['status' => 'Status seminar tidak valid.']);
        $this->assertDatabaseCount('seminars', 0);
    }

    #[DataProvider('invalidFlyers')]
    public function test_invalid_flyer_type_and_size_are_rejected(string $flyerType): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $flyer = $flyerType === 'pdf'
            ? UploadedFile::fake()->create('brosur.pdf', 100, 'application/pdf')
            : UploadedFile::fake()->image('terlalu-besar.jpg')->size(2049);
        $data = $this->seminarData(['flyer' => $flyer]);

        $response = $this->post(route('admin.seminars.store'), $data);

        $message = $flyerType === 'pdf'
            ? 'Flyer harus berformat JPG, JPEG, atau PNG.'
            : 'Ukuran flyer maksimal 2 MB.';
        $response->assertSessionHasErrors(['flyer' => $message]);
        $this->assertDatabaseCount('seminars', 0);
        Storage::disk('public')->assertDirectoryEmpty('flyers');
    }

    public function test_flyer_with_a_fake_image_extension_is_rejected_by_its_actual_content(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $temporaryPath = tempnam(sys_get_temp_dir(), 'seminar-flyer-');

        if ($temporaryPath === false) {
            self::fail('Unable to create the temporary upload file.');
        }

        file_put_contents($temporaryPath, '<script>alert(1)</script>');
        $flyer = new UploadedFile($temporaryPath, 'palsu.jpg', 'image/jpeg', null, true);

        try {
            $response = $this->post(route('admin.seminars.store'), $this->seminarData([
                'flyer' => $flyer,
            ]));

            $response->assertSessionHasErrors([
                'flyer' => 'Flyer harus berformat JPG, JPEG, atau PNG.',
            ]);
            $this->assertDatabaseCount('seminars', 0);
            Storage::disk('public')->assertDirectoryEmpty('flyers');
        } finally {
            unlink($temporaryPath);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidFlyers(): array
    {
        return [
            'dokumen PDF' => ['pdf'],
            'ukuran melebihi 2 MB' => ['oversized'],
        ];
    }

    public function test_update_rejects_a_quota_below_the_number_of_registrations(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create(['kuota' => 3]);
        $this->createRegistrations($seminar, 3);

        $response = $this->from(route('admin.seminars.edit', $seminar))
            ->put(route('admin.seminars.update', $seminar), $this->seminarData([
                'kuota' => 2,
                'tanggal' => now()->subDays(3)->toDateString(),
            ], includeFlyer: false));

        $response->assertRedirect(route('admin.seminars.edit', $seminar))
            ->assertSessionHasErrors([
                'kuota' => 'Kuota tidak boleh lebih kecil daripada jumlah pendaftar.',
            ]);
        $this->assertDatabaseHas('seminars', ['id' => $seminar->id, 'kuota' => 3]);
        $this->assertDatabaseCount('registrations', 3);
    }

    public function test_update_allows_past_dates_and_keeps_the_existing_flyer(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('flyers/tersimpan.jpg', 'flyer');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create([
            'flyer' => 'flyers/tersimpan.jpg',
            'tanggal' => now()->addDay()->toDateString(),
        ]);
        $pastDate = now()->subDay()->toDateString();

        $response = $this->put(route('admin.seminars.update', $seminar), $this->seminarData([
            'tanggal' => $pastDate,
        ], includeFlyer: false));

        $response->assertRedirect(route('admin.seminars.show', $seminar))
            ->assertSessionHas('success', 'Perubahan seminar berhasil disimpan.');
        $this->assertDatabaseHas('seminars', [
            'id' => $seminar->id,
            'tanggal' => $pastDate.' 00:00:00',
            'flyer' => 'flyers/tersimpan.jpg',
        ]);
        Storage::disk('public')->assertExists('flyers/tersimpan.jpg');
    }

    public function test_replacing_a_flyer_removes_the_old_unshared_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('flyers/lama.jpg', 'flyer lama');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create(['flyer' => 'flyers/lama.jpg']);
        $data = $this->seminarData([
            'flyer' => UploadedFile::fake()->image('baru.png', 100, 100),
        ], includeFlyer: false);

        $response = $this->put(route('admin.seminars.update', $seminar), $data);

        $seminar->refresh();
        $response->assertRedirect(route('admin.seminars.show', $seminar));
        $this->assertNotSame('flyers/lama.jpg', $seminar->flyer);
        Storage::disk('public')->assertMissing('flyers/lama.jpg');
        Storage::disk('public')->assertExists($seminar->flyer);
    }

    public function test_seminar_with_registrations_cannot_be_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('flyers/dipakai.jpg', 'flyer');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create(['flyer' => 'flyers/dipakai.jpg']);
        $this->createRegistrations($seminar, 1);

        $response = $this->delete(route('admin.seminars.destroy', $seminar));

        $response->assertRedirect(route('admin.seminars.index'))
            ->assertSessionHas(
                'error',
                'Seminar tidak dapat dihapus karena sudah memiliki pendaftar.',
            );
        $this->assertModelExists($seminar);
        Storage::disk('public')->assertExists('flyers/dipakai.jpg');
    }

    public function test_seminar_without_registrations_is_deleted_with_its_flyer(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('flyers/dihapus.jpg', 'flyer');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create(['flyer' => 'flyers/dihapus.jpg']);

        $response = $this->delete(route('admin.seminars.destroy', $seminar));

        $response->assertRedirect(route('admin.seminars.index'))
            ->assertSessionHas('success', 'Seminar berhasil dihapus.');
        $this->assertDatabaseMissing('seminars', ['id' => $seminar->id]);
        Storage::disk('public')->assertMissing('flyers/dihapus.jpg');
    }

    public function test_shared_flyer_remains_after_one_seminar_is_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('flyers/bersama.jpg', 'flyer');
        $this->actingAs(User::factory()->create());
        $seminar = Seminar::factory()->create(['flyer' => 'flyers/bersama.jpg']);
        Seminar::factory()->create(['flyer' => 'flyers/bersama.jpg']);

        $this->delete(route('admin.seminars.destroy', $seminar))
            ->assertRedirect(route('admin.seminars.index'));

        Storage::disk('public')->assertExists('flyers/bersama.jpg');
    }

    public function test_seminar_list_can_be_searched_and_displays_detail_actions(): void
    {
        $this->actingAs(User::factory()->create());
        Seminar::factory()->create(['judul' => 'Lokakarya Desain Produk']);
        Seminar::factory()->create(['judul' => 'Diskusi Sains Terbuka']);

        $response = $this->get(route('admin.seminars.index', ['search' => 'Desain']));

        $response->assertOk()
            ->assertSee('Lokakarya Desain Produk')
            ->assertDontSee('Diskusi Sains Terbuka')
            ->assertSee('data-delete-url', false);
    }

    public function test_seminar_management_routes_require_authentication(): void
    {
        $seminar = Seminar::factory()->create();

        $this->get(route('admin.seminars.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.seminars.store'), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.seminars.destroy', $seminar))->assertRedirect(route('admin.login'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function seminarData(array $overrides = [], bool $includeFlyer = true): array
    {
        $data = [
            'judul' => 'Lokakarya desain layanan digital',
            'pemapar' => 'Nadia Prameswari',
            'deskripsi' => 'Belajar merancang layanan digital melalui latihan bersama.',
            'lokasi' => 'Auditorium Utama',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'kuota' => 20,
            'status' => 'dibuka',
        ];

        if ($includeFlyer) {
            $data['flyer'] = UploadedFile::fake()->image('seminar.png', 120, 80);
        }

        return [...$data, ...$overrides];
    }

    private function createRegistrations(Seminar $seminar, int $count): void
    {
        foreach (range(1, $count) as $number) {
            Registration::create([
                'seminar_id' => $seminar->id,
                'nama' => 'Peserta '.$number,
                'email' => 'peserta'.$number.'@contoh.test',
                'telepon' => '0812345678'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'token' => Str::random(40),
                'status' => 'terdaftar',
            ]);
        }
    }
}

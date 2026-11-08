# Matriks Keterlacakan Kebutuhan

Dokumen ini memetakan kebutuhan pada RTM laporan UTS ke bagian aplikasi,
pengujian otomatis yang tersedia, dan checklist uji manual. Path pada tabel
relatif terhadap root repository.

## Kebutuhan fungsional

| ID | Kebutuhan pada RTM | Implementasi utama | Pengujian otomatis dan manual |
|---|---|---|---|
| FR-01 | Login admin dan pembatasan akses pengelolaan | `app/Http/Controllers/Admin/AuthController.php`; `app/Models/User.php`; `routes/web.php`; `resources/views/admin/login.blade.php` | `tests/Feature/AdminLoginTest.php` — autentikasi, validasi, throttle, logout, dan akses dashboard. Manual: TC-001–TC-003. |
| FR-02 | Tambah, lihat, ubah, dan hapus seminar | `app/Http/Controllers/Admin/SeminarController.php`; `app/Http/Requests/SeminarRequest.php`; `StoreSeminarRequest.php`; `UpdateSeminarRequest.php`; `app/Models/Seminar.php`; `resources/views/admin/seminars/` | `tests/Feature/Admin/SeminarControllerTest.php` — validasi, upload, CRUD, pencarian, serta larangan menghapus seminar yang memiliki pendaftar. Manual: TC-006–TC-010. |
| FR-03 | Pendaftaran peserta pada seminar yang dibuka | `app/Http/Requests/RegistrationRequest.php`; `app/Services/RegistrationService.php`; `app/Http/Controllers/Public/RegistrationController.php`; `resources/views/public/registrations/show.blade.php` | `tests/Feature/PublicRegistrationTest.php` — pendaftaran valid, kartu QR, dan validasi input. Manual: TC-011–TC-013. |
| FR-04 | Pemeriksaan kuota saat pendaftaran disimpan | `app/Services/RegistrationService.php` — transaksi dan `lockForUpdate()` pada seminar; `app/Models/Seminar.php` | `tests/Feature/PublicRegistrationTest.php` — kuota penuh dan peserta terakhir. Manual: TC-016. |
| FR-05 | Cegah email/telepon ganda dalam seminar yang sama | `app/Http/Requests/RegistrationRequest.php` — normalisasi email; `app/Services/RegistrationService.php` — pemeriksaan duplikasi per seminar | `tests/Feature/PublicRegistrationTest.php` — email ter-normalisasi, telepon duplikat, dan email sama pada seminar lain. Manual: TC-014–TC-016. |
| FR-06 | Pencarian, perubahan, penghapusan, dan QR peserta | `app/Http/Controllers/Admin/ParticipantController.php`; `app/Http/Requests/Admin/UpdateRegistrationRequest.php`; `resources/views/admin/seminars/participants.blade.php`; `participant-edit.blade.php` | `tests/Feature/Admin/ParticipantControllerTest.php` — pencarian, pagination, perubahan valid/duplikat/tidak valid, penghapusan dan kapasitas, QR, serta pembatasan relasi seminar. Manual: TC-017–TC-022. |
| FR-07 | Pemindaian QR, koreksi status, dan status otomatis tidak hadir | `app/Services/AttendanceService.php`; `app/Http/Controllers/Admin/AttendanceController.php`; `app/Http/Requests/Admin/UpdateAttendanceRequest.php`; `app/Console/Commands/MarkAbsentRegistrations.php`; `routes/console.php`; `config/seminar.php`; `resources/views/admin/attendance/scan.blade.php`; `resources/views/admin/seminars/attendance.blade.php` | `tests/Feature/Admin/AttendanceControllerTest.php`; `tests/Feature/Console/Commands/MarkAbsentRegistrationsTest.php`; `tests/Feature/Admin/AttendanceRateLimitTest.php`. Manual: TC-023–TC-029. |
| FR-08 | Rekap pendaftar, status kehadiran, sisa kuota dan infografis | `app/Http/Controllers/Admin/ReportController.php`; `app/Http/Controllers/Admin/DashboardController.php`; `resources/views/admin/reports/index.blade.php`; `resources/views/admin/dashboard.blade.php` | `tests/Feature/Admin/ReportControllerTest.php`; `tests/Feature/Admin/DashboardControllerTest.php` — penghitungan data dan ekspor CSV. Manual: TC-030. |

## Kebutuhan nonfungsional

| ID | Kebutuhan pada RTM | Implementasi utama | Verifikasi |
|---|---|---|---|
| NFR-01 | Pesan validasi jelas, berbahasa Indonesia, dekat dengan kolom salah; input lain dipertahankan | Pesan khusus pada `app/Http/Requests/`; komponen `resources/views/components/form-error.blade.php`; form Blade publik dan admin | `tests/Feature/PublicRegistrationTest.php`, `tests/Feature/AdminLoginTest.php`, `tests/Feature/Admin/SeminarControllerTest.php`, dan `tests/Feature/Admin/ParticipantControllerTest.php`. Manual: TC-002, TC-007, TC-012, TC-018–TC-020. |
| NFR-02 | Pembatasan admin, password ter-hash, dan perlindungan CSRF | Middleware `auth` dan `guest` di `routes/web.php`; `password => hashed` di `app/Models/User.php`; token CSRF pada form Blade; `app/Http/Middleware/AddSecurityHeaders.php` | `tests/Feature/AdminLoginTest.php`; `tests/Feature/Admin/SecurityHeadersTest.php`; `tests/Feature/Admin/AttendanceRateLimitTest.php`. Manual: TC-003. |
| NFR-03 | Data tersimpan permanen pada basis data | Model Eloquent di `app/Models/`; migrasi di `database/migrations/`; MySQL dipakai pada konfigurasi lokal di `.env` | Assertion database pada Feature Tests, termasuk `tests/Feature/PublicRegistrationTest.php` dan tes CRUD admin. Manual: TC-009, TC-011, TC-018, TC-021, TC-028–TC-030. |
| NFR-04 | Daftar seminar dan penyimpanan pendaftaran masing-masing kurang dari 3 detik di lokal | Query dan agregasi pada controller; `withCount()` untuk ringkasan; indeks pendaftaran pada `database/migrations/2026_11_08_151626_add_seminar_status_index_to_registrations_table.php` | Assertion fungsional ada di `tests/Feature/PublicRegistrationTest.php` dan tes dashboard. Pengukuran durasi bukan assertion otomatis; ukur dari tab Network Chrome. Manual: TC-030. |
| NFR-05 | Fungsi dan tampilan berjalan pada Chrome, lebar 1.366 px dan 375 px tanpa elemen terpotong | CSS responsif `public/css/app.css`; kartu mobile peserta/kehadiran pada `resources/views/admin/`; bottom navigation pada `resources/views/layouts/admin.blade.php` | Tidak ada browser test otomatis pada suite PHPUnit. Verifikasi dengan emulasi Chrome sesuai RTM. Manual: TC-004 dan TC-030. |

## Catatan cakupan

- Suite PHPUnit memverifikasi perilaku request, validasi, dan database; bukan
  rendering browser pada beragam ukuran layar.
- NFR-04 perlu dicatat dengan waktu aktual dari Chrome pada komputer pengujian;
  angka dari mesin lain bukan hasil pengukuran yang dapat dibandingkan.
- Jendela pemindaian yang diterapkan adalah tepat 10 menit setelah waktu mulai,
  sesuai nilai `menit_buka_presensi` di `config/seminar.php`. Presensi QR juga
  ditolak setelah waktu selesai seminar.

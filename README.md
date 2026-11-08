# Sistem Pendaftaran Seminar

Sistem Pendaftaran Seminar adalah aplikasi web untuk mengumumkan seminar,
menerima pendaftaran peserta, mencatat kehadiran dengan QR, serta mengelola
data seminar dan laporan melalui panel admin. Peserta dapat mencari seminar,
melihat detail dan kuota, mendaftar, lalu menyimpan kartu konfirmasi berisi QR
unik. Admin mengelola peserta, memindai QR, mengoreksi status kehadiran, dan
mengekspor laporan.

## Fitur

- Katalog publik dengan pencarian judul, status pendaftaran, informasi kuota,
  dan detail seminar.
- Pendaftaran peserta dengan validasi, pencegahan duplikasi per seminar,
  penguncian kuota saat transaksi, dan token QR unik.
- Kartu konfirmasi peserta yang dapat dicetak.
- Login admin dan pengelolaan seminar (tambah, lihat, ubah, hapus).
- Pencarian, pengubahan, penghapusan, dan penayangan QR peserta.
- Pemindaian presensi dengan kamera atau token manual; status hadir dan tidak
  hadir dapat dikoreksi admin.
- Penandaan otomatis peserta tidak hadir setelah seminar selesai.
- Dashboard, rekap laporan, infografis, cetak laporan, dan ekspor CSV.
- Antarmuka responsif, pesan validasi berbahasa Indonesia, serta halaman error
  yang ramah pengguna.

## Tech stack

| Komponen | Teknologi |
|---|---|
| Bahasa dan runtime | PHP 8.3+ |
| Framework | Laravel 13 (versi pada lock file: 13.40.0) |
| Basis data | MySQL |
| Pembuatan QR | `simplesoftwareio/simple-qrcode` 4.2 |
| Pemindaian QR kamera | `html5-qrcode` 2.3.8 melalui CDN |
| Tampilan | Blade, CSS, JavaScript, Bootstrap 5.3.3 dan Bootstrap Icons melalui CDN |
| Pengujian | PHPUnit 12 dan Laravel Feature Tests |

Bootstrap, ikon, font, dan pemindai kamera dimuat dari CDN; koneksi internet
diperlukan untuk memuat aset tersebut. Aset aplikasi berada di `public/`, jadi
tidak diperlukan proses build frontend untuk menjalankan aplikasi.

## Prasyarat

- PHP 8.3 atau lebih baru dengan ekstensi PDO MySQL dan ekstensi yang diwajibkan
  Laravel.
- Composer 2.
- MySQL yang berjalan dan akun yang memiliki izin membuat/mengakses database.
- Browser modern; Google Chrome direkomendasikan untuk pengujian kamera dan
  tampilan.
- Git dan koneksi internet untuk clone serta mengunduh dependensi/aset CDN.

## Instalasi lokal

Contoh berikut menggunakan PowerShell. Sesuaikan direktori clone, kredensial
MySQL, dan nilai `.env` dengan lingkungan Anda.

1. Clone repository dan masuk ke direktori proyek:

   ```powershell
   git clone https://github.com/ilhamnurfadillah9-rgb/sistem-pendaftaran-seminar.git
   Set-Location sistem-pendaftaran-seminar
   ```

2. Pasang dependensi PHP:

   ```powershell
   composer install
   ```

3. Buat berkas konfigurasi lokal:

   ```powershell
   Copy-Item .env.example .env
   ```

   Pastikan pengaturan dasar berikut ada di `.env`, lalu isi nama pengguna dan
   kata sandi MySQL yang benar:

   ```dotenv
   APP_ENV=local
   APP_DEBUG=false
   APP_URL=http://localhost:8000
   APP_TIMEZONE=Asia/Jakarta
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=seminar_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Buat database MySQL kosong, misalnya dari MySQL client atau phpMyAdmin:

   ```sql
   CREATE DATABASE seminar_db
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```

5. Atur batas unggahan PHP. Jalankan `php --ini` untuk mengetahui `php.ini`
   yang digunakan PHP CLI, lalu pastikan nilainya:

   ```ini
   upload_max_filesize=4M
   post_max_size=8M
   ```

   Bila Anda menggunakan PHP-FPM atau layanan web lain, ubah juga `php.ini`
   untuk layanan tersebut dan mulai ulang prosesnya. Aplikasi menerima flyer
   JPG/JPEG/PNG dengan batas validasi aplikasi **2 MB**; batas PHP 4M/8M
   menyediakan ruang yang cukup untuk request multipart.

6. Buat kunci aplikasi:

   ```powershell
   php artisan key:generate
   ```

7. Jalankan migrasi dan seeder:

   ```powershell
   php artisan migrate --seed
   ```

   Seeder membuat akun admin serta seminar contoh. Untuk mengulang seluruh
   database pada lingkungan pengembangan saja, gunakan `php artisan
   migrate:fresh --seed`; perintah ini menghapus semua tabel dan data.

8. Publikasikan file flyer:

   ```powershell
   php artisan storage:link
   ```

9. Jalankan server aplikasi:

   ```powershell
   php artisan serve
   ```

   Buka [http://localhost:8000](http://localhost:8000).

10. Di terminal kedua, aktifkan scheduler:

    ```powershell
    php artisan schedule:work
    ```

    Scheduler menjalankan `seminar:tandai-tidak-hadir` setiap menit. Perintah
    ini menandai pendaftaran berstatus `terdaftar` menjadi `tidak_hadir` setelah
    seminar selesai, tanpa mengubah status `hadir` atau `tidak_hadir`. Beberapa
    halaman peserta, presensi, dan laporan juga memperbarui status yang
    kedaluwarsa saat dibuka.

## Akun admin awal

| Email | Kata sandi |
|---|---|
| `admin@seminar.test` | `password123` |

Akun ini dibuat oleh `DatabaseSeeder` untuk pengembangan dan pengujian lokal.
Ganti kredensial awal sebelum aplikasi digunakan atau dibuka ke jaringan.

## Konfigurasi penting

- **Zona waktu:** `APP_TIMEZONE=Asia/Jakarta` pada `.env`; nilai default aplikasi
  juga `Asia/Jakarta`. Waktu seminar dan jendela presensi mengikuti zona waktu
  ini.
- **Jendela mulai presensi:** `config/seminar.php`, key
  `menit_buka_presensi` (default 10). QR baru aktif 10 menit setelah waktu mulai.
- **Flyer:** PHP `upload_max_filesize=4M`, `post_max_size=8M`; validasi aplikasi
  membatasi file gambar yang diterima hingga 2 MB.
- **Mode lokal aman:** `.env.example` menggunakan `APP_DEBUG=false`. Jangan
  menyalakan debug untuk instalasi yang dapat diakses pihak lain.
- **Batas percobaan:** login dibatasi 10 percobaan per menit dan endpoint scan
  memiliki rate limit tersendiri.
- Setelah mengubah file konfigurasi pada instalasi yang memakai cache
  konfigurasi, jalankan `php artisan config:clear`.

## Menguji kamera QR

1. Jalankan server dengan `php artisan serve` dan buka **tepat**
   [http://localhost:8000](http://localhost:8000). Browser hanya mengizinkan
   akses kamera pada konteks aman seperti HTTPS atau `localhost`; alamat
   `http://127.0.0.1` belum tentu diperlakukan sama oleh semua browser.
2. Masuk ke `/admin/login` menggunakan akun awal.
3. Dari beranda publik, buka seminar yang pendaftarannya dibuka dan daftarkan
   peserta uji. Halaman konfirmasi menampilkan QR unik dan token.
4. Pada perangkat/browser yang memiliki kamera, buka
   `/admin/attendance/scan`, pilih seminar yang sama, lalu pilih **Aktifkan
   kamera** dan izinkan akses kamera.
5. Arahkan kamera ke QR pada layar lain. Seminar uji harus sudah mencapai waktu
   mulai + 10 menit dan belum melewati waktu selesai agar presensi diterima.
6. Bila kamera tidak tersedia, uji kolom token manual menggunakan token pada
   URL kartu konfirmasi. Jangan gunakan token produksi untuk uji publik.

Seeder membuat satu seminar contoh yang waktunya disiapkan agar jendela
presensinya dapat diuji segera setelah seeding. Jika waktu tersebut telah lewat,
buat seminar uji baru dengan tanggal dan waktu yang sesuai.

## Struktur folder

```text
app/
  Console/Commands/       Perintah Artisan penandaan tidak hadir
  Http/Controllers/       Controller publik dan admin
  Http/Requests/          Validasi request/form
  Models/                 Model Eloquent
  Services/               Layanan pendaftaran dan presensi
bootstrap/                Bootstrap aplikasi dan middleware
config/                   Konfigurasi aplikasi, database, dan seminar
database/
  factories/              Factory data pengujian
  migrations/             Skema dan indeks database
  seeders/                Akun admin dan seminar contoh
docs/                     Matriks keterlacakan dan kasus uji manual
public/                   CSS, JavaScript, dan entry point web
resources/views/          Tampilan Blade publik, admin, dan error
routes/                   Route web dan jadwal Artisan
storage/                  Log, cache, dan file flyer
tests/
  Feature/                Pengujian alur HTTP, database, dan command
  Unit/                   Pengujian unit
```

## Daftar route

Route admin selain login berada di balik middleware `auth`. Rute tulis menerima
CSRF token melalui formulir.

| Metode | URL | Akses | Nama route |
|---|---|---|---|
| GET | `/` | Publik | `home` |
| GET | `/seminar/{seminar}` | Publik | `seminar.show` |
| POST | `/seminar/{seminar}/pendaftaran` | Publik | `registrations.store` |
| GET | `/pendaftaran/{token}` | Publik | `registrations.show` |
| GET | `/admin/login` | Tamu | `admin.login` |
| POST | `/admin/login` | Tamu, throttle | `admin.login.store` |
| POST | `/admin/logout` | Admin | `admin.logout` |
| GET | `/admin/dashboard` | Admin | `admin.dashboard` |
| GET | `/admin/seminars` | Admin | `admin.seminars.index` |
| GET | `/admin/seminars/create` | Admin | `admin.seminars.create` |
| POST | `/admin/seminars` | Admin | `admin.seminars.store` |
| GET | `/admin/seminars/{seminar}` | Admin | `admin.seminars.show` |
| GET | `/admin/seminars/{seminar}/edit` | Admin | `admin.seminars.edit` |
| PUT/PATCH | `/admin/seminars/{seminar}` | Admin | `admin.seminars.update` |
| DELETE | `/admin/seminars/{seminar}` | Admin | `admin.seminars.destroy` |
| GET | `/admin/seminars/{seminar}/participants` | Admin | `admin.seminars.participants.index` |
| GET | `/admin/seminars/{seminar}/participants/{registration}/edit` | Admin | `admin.seminars.participants.edit` |
| PUT | `/admin/seminars/{seminar}/participants/{registration}` | Admin | `admin.seminars.participants.update` |
| DELETE | `/admin/seminars/{seminar}/participants/{registration}` | Admin | `admin.seminars.participants.destroy` |
| GET | `/admin/seminars/{seminar}/participants/{registration}/qr` | Admin | `admin.seminars.participants.qr` |
| GET | `/admin/attendance/scan` | Admin | `admin.attendance.scan` |
| POST | `/admin/attendance/scan` | Admin, rate limit | `admin.attendance.scan.store` |
| GET | `/admin/seminars/{seminar}/attendance` | Admin | `admin.seminars.attendance.index` |
| PUT | `/admin/seminars/{seminar}/attendance/{registration}` | Admin | `admin.seminars.attendance.update` |
| GET | `/admin/reports` | Admin | `admin.reports.index` |
| GET | `/admin/reports/export` | Admin | `admin.reports.export` |

Untuk melihat daftar route dari instalasi yang sedang berjalan:

```powershell
php artisan route:list
```

## Menjalankan pengujian

```powershell
php artisan test
```

Suite pengujian menggunakan SQLite in-memory sesuai `phpunit.xml`, sehingga
tidak menggunakan database MySQL lokal. Untuk menjalankan satu file atau
kelompok tes:

```powershell
php artisan test tests/Feature/PublicRegistrationTest.php
php artisan test --filter=AttendanceControllerTest
```

Lihat [matriks keterlacakan FR/NFR](docs/traceability.md) dan
[checklist 30 kasus uji manual](docs/test-cases.md) untuk hubungan kebutuhan
dengan implementasi dan langkah pengujian browser.

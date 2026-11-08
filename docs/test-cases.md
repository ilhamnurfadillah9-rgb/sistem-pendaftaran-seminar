# Checklist Uji Manual

Checklist berikut merangkum keputusan dan data batas dari RTM laporan UTS.
Lakukan pada database pengujian lokal dan catat browser, versi Chrome, waktu,
serta bukti tangkapan layar untuk hasil yang gagal atau terblokir.

**Akun admin awal:** `admin@seminar.test` / `password123`.  
**Data peserta contoh:** nama `Budi Santoso`, email `budi@example.test`,
telepon `081234567890`.  
**Data presensi:** atur tanggal/waktu seminar pada hari pengujian di zona
`Asia/Jakarta`.

| ID | Kebutuhan | Langkah uji | Data uji | Hasil yang diharapkan | Hasil |
|---|---|---|---|---|---|
| TC-001 | FR-01 | Buka `/admin/login`, masukkan kredensial admin awal, lalu masuk. | Email dan kata sandi valid. | Login berhasil dan dashboard admin terbuka. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-002 | FR-01, NFR-01 | Kirim form login kosong, lalu coba email admin dengan kata sandi salah. | Kosong; `admin@seminar.test` / kata sandi salah. | Pesan wajib tampil pada field; kredensial salah ditolak dengan pesan Indonesia dan tetap di halaman login. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-003 | FR-01, NFR-02 | Tanpa login, buka URL admin langsung. Setelah login, periksa form tulis memiliki CSRF token dan nilai password di database bukan teks biasa. | `/admin/dashboard`; form seminar/peserta; tabel `users`. | Pengunjung dialihkan ke login; form memuat token CSRF; password tersimpan sebagai hash. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-004 | FR-03, NFR-05 | Buka beranda di Chrome pada lebar 1.366 px dan 375 px. Cari judul seminar, lalu buka hasilnya. | Judul yang ada dan judul tanpa hasil; dua lebar viewport. | Kartu dan pencarian berfungsi, status seminar terlihat, hasil pencarian sesuai; tidak ada konten terpotong atau scroll horizontal. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-005 | FR-03, FR-04 | Buka detail seminar dibuka, ditutup, dan yang kuotanya habis. | Seminar dengan status `dibuka`, `ditutup`, dan pendaftar sebanyak kuota. | Detail, flyer, jadwal, lokasi, dan sisa kuota benar; seminar tutup/penuh menampilkan status yang sesuai dan tidak dapat didaftarkan. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-006 | FR-02 | Admin tambah seminar dengan semua data valid lalu simpan. | Judul `Seminar Uji`; tanggal mendatang; waktu selesai setelah mulai; kuota 10; flyer PNG valid. | Seminar tersimpan dan tampil di daftar serta halaman detail; flyer tampil dengan nama file acak. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-007 | FR-02, NFR-01 | Coba simpan seminar dengan field wajib kosong, judul/lokasi >150 karakter, deskripsi di luar 20–500 karakter, kuota 0/1.001, dan waktu selesai sebelum mulai. | Data invalid tersebut; variasikan satu kegagalan per kiriman. | Setiap nilai invalid ditolak dengan pesan Indonesia dekat field terkait; nilai lain tetap terisi. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-008 | FR-02, NFR-01 | Uji unggah flyer yang valid lalu kirim beberapa file tidak valid. | PNG/JPG <2 MB; PDF; file teks bernama `.jpg`; gambar sekitar 2,1 MB. | PNG/JPG valid diterima; PDF, konten bukan gambar, dan file di atas batas 2 MB ditolak dengan pesan yang jelas. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-009 | FR-02, NFR-03 | Ubah informasi seminar tersimpan, simpan, muat ulang halaman, dan periksa database. | Ubah judul/lokasi/kuota menjadi nilai valid. | Data perubahan tetap tampil setelah reload dan tersimpan di database; kuota tidak boleh lebih kecil dari jumlah pendaftar. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-010 | FR-02, NFR-02 | Coba hapus seminar yang sudah memiliki pendaftar, lalu hapus seminar kosong setelah menyetujui dialog. | Satu seminar dengan peserta; satu seminar tanpa peserta. | Penghapusan seminar berisi peserta ditolak dengan pesan; seminar kosong terhapus hanya setelah konfirmasi. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-011 | FR-03, FR-04, NFR-03 | Daftarkan peserta ke seminar dibuka, buka kartu konfirmasi, lalu muat ulang halaman detail seminar. | Data peserta valid; seminar memiliki kuota tersedia. | Pendaftaran tersimpan dengan status `terdaftar`, token/QR unik dibuat, kartu konfirmasi tampil, sisa kuota turun satu dan tetap turun setelah reload. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-012 | FR-03, NFR-01 | Kirim pendaftaran dengan field kosong dan format batas; coba juga email invalid. | Nama kosong dan 101 karakter; email `budi@`, `budi.example.com`; telepon kosong, 9 digit, 10 digit, 13 digit, 14 digit, `08abc`. | Nilai tidak valid ditolak dengan pesan di field dan input lain dipertahankan; telepon 10/13 digit diterima bila field lain valid. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-013 | FR-03 | Kirim pendaftaran pada seminar yang statusnya ditutup. | Data peserta valid, seminar `ditutup`. | Pendaftaran ditolak dengan pesan “Pendaftaran untuk seminar ini telah ditutup.”; tidak ada baris baru di database. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-014 | FR-05 | Daftarkan email, lalu coba email yang sama pada seminar sama dengan kapitalisasi berbeda dan spasi di ujung. | `Budi@Example.test`, lalu ` budi@example.test `. | Pendaftaran kedua ditolak: “Email sudah terdaftar pada seminar ini.”; email dinormalisasi menjadi huruf kecil tanpa spasi. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-015 | FR-05 | Coba telepon sama pada seminar yang sama, lalu gunakan email/telepon tersebut di seminar berbeda yang dibuka. | Telepon peserta pertama; seminar berbeda. | Telepon duplikat pada seminar yang sama ditolak; data yang sama dapat dipakai untuk seminar berbeda. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-016 | FR-04, FR-05 | Pada seminar kuota 2, daftarkan dua peserta, kemudian kirim pendaftaran ketiga. | Kuota 2; tiga alamat email dan telepon berbeda. | Dua pendaftar pertama diterima; pendaftar ketiga ditolak dengan pesan kuota penuh dan tidak tersimpan. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-017 | FR-06 | Buka peserta pada suatu seminar, cari nama lalu email; isi data secukupnya agar pagination muncul dan pindah halaman. | Kata kunci nama/email yang cocok dan tidak cocok. | Hanya peserta yang cocok tampil; pencarian kedua berfungsi dan pagination berpindah halaman tanpa mencampur seminar lain. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-018 | FR-06, NFR-01, NFR-03 | Ubah nama, email, dan telepon peserta dengan nilai valid; simpan dan muat ulang. | Nama `Budi Santoso Baru`; email baru valid; telepon 10–13 digit. | Perubahan tersimpan dan tetap tampil setelah reload; nilai lain tetap terisi bila terjadi kesalahan. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-019 | FR-06 | Ubah email peserta menjadi email peserta lain pada seminar yang sama. | Email yang sudah dimiliki peserta lain. | Perubahan ditolak dengan “Email sudah terdaftar pada seminar ini.”; data lama tidak berubah. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-020 | FR-06, NFR-01 | Ubah email peserta memakai format invalid. | `budi@` atau `budi.example.com`. | Perubahan ditolak dengan “Format email tidak valid.”; kesalahan tampil dekat field dan data lama tidak berubah. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-021 | FR-06, NFR-03 | Hapus peserta dan setujui dialog konfirmasi; periksa jumlah pendaftar dan sisa kuota. | Seminar memiliki setidaknya satu peserta. | Peserta terhapus dan sisa kuota yang dihitung dinamis bertambah satu. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-022 | FR-06, FR-07 | Buka “Lihat QR” untuk dua peserta, lalu ikuti tautan konfirmasi masing-masing. | Dua peserta berbeda pada seminar. | QR/token tiap peserta unik; modal menampilkan QR yang sesuai dan tautan membuka kartu konfirmasi peserta yang tepat. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-023 | FR-07 | Masuk admin, buka pemindai, pilih seminar, lalu kirim token yang tidak terdaftar. | Token acak yang tidak tersimpan. | Hasil scan berwarna merah dengan pesan “Kode QR tidak valid.”; status peserta tidak berubah. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-024 | FR-07 | Scan token peserta sebelum jendela presensi terbuka. | Waktu saat ini satu menit sebelum waktu mulai + 10 menit. | Scan ditolak dengan pesan “Kode QR belum aktif. Presensi dibuka 10 menit setelah seminar dimulai.” | [ ] Pass [ ] Fail [ ] Blocked |
| TC-025 | FR-07 | Scan token tepat saat waktu mulai + 10 menit. | Waktu uji disetel tepat pada batas 10 menit; seminar belum selesai. | Scan diterima; kartu hasil berwarna hijau menampilkan nama peserta dan “Hadir”; waktu hadir tersimpan. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-026 | FR-07 | Scan token setelah seminar selesai. | Waktu saat ini satu menit setelah waktu selesai. | Scan ditolak dengan pesan “Kode QR telah kedaluwarsa karena seminar telah selesai.”; status tidak menjadi hadir. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-027 | FR-07 | Scan ulang token peserta yang sudah berstatus hadir. | Token yang telah berhasil dipakai pada TC-025. | Scan kedua ditolak dengan pesan “Kode QR sudah digunakan.”; status dan waktu hadir sebelumnya tetap. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-028 | FR-07, NFR-01 | Dari daftar kehadiran, ubah status menjadi hadir/tidak hadir dan muat ulang. Lalu kirim `terdaftar` serta nilai status lain melalui developer tools. | Status valid; `terdaftar`; `invalid`. | Status valid tersimpan setelah reload; `terdaftar` ditolak dengan pesan khusus dan nilai lain dengan “Status kehadiran tidak valid.” | [ ] Pass [ ] Fail [ ] Blocked |
| TC-029 | FR-07 | Siapkan peserta berstatus `terdaftar` pada seminar yang berakhir, jalankan command dan periksa status peserta hadir/tidak hadir. | Seminar telah selesai; peserta berstatus `terdaftar`, `hadir`, dan `tidak_hadir`. | `terdaftar` menjadi `tidak_hadir`; status lain tidak berubah. Scheduler dapat dijalankan dengan `php artisan schedule:work`. | [ ] Pass [ ] Fail [ ] Blocked |
| TC-030 | FR-08, NFR-03, NFR-04, NFR-05 | Isi data seminar dengan status kehadiran yang diketahui. Cocokkan laporan dengan hitungan manual; muat ulang dan mulai ulang server. Ukur request beranda dan simpan pendaftaran pada tab Network. Periksa beranda, detail, dan halaman admin pada lebar 1.366 px serta 375 px. | Contoh: 5 pendaftar (2 hadir, 1 tidak hadir, 2 terdaftar), kuota 8; gunakan Chrome. | Laporan menampilkan 5/2/1/2 dan sisa kuota 3; CSV sesuai data. Data tetap tersimpan setelah reload/restart; tiap request utama <3 detik; tidak ada elemen terpotong atau scroll horizontal di kedua lebar. | [ ] Pass [ ] Fail [ ] Blocked |

## Catatan eksekusi

- Jalankan kasus secara berurutan dan siapkan/bersihkan data sebelum kasus yang
  bergantung pada data kasus sebelumnya.
- Untuk TC-024–TC-027, atur tanggal dan waktu seminar mengikuti jam perangkat
  dan `Asia/Jakarta`; jangan menguji tepat pada detik waktu selesai.
- Untuk kasus kamera, akses aplikasi melalui `http://localhost:8000` atau HTTPS
  dan izinkan akses kamera. Token manual dapat dipakai sebagai alternatif.
- Isikan hasil aktual, tanggal, versi Chrome, bukti, dan alasan pada setiap
  kasus berstatus `Fail` atau `Blocked`.

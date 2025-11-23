# Jasamedika — Aplikasi Pemesanan Travel

Ini adalah aplikasi pemesanan travel sederhana yang dibangun dengan Laravel + Inertia.js (Vue 3).

Teknologi utama yang digunakan:

- Laravel 12 (PHP 8.2+)
- Inertia.js + Vue 3
- PrimeVue untuk komponen UI
- Axios untuk pemanggilan API

README ini berisi petunjuk pemasangan, penggunaan, rute penting, serta catatan untuk pengembang.

---

## Persyaratan

- PHP 8.2 atau lebih baru
- Composer
- Node.js (disarankan v16+) dan `npm` atau `pnpm`
- Database (postgree)

Perintah di bawah memakai sintaks PowerShell pada Windows; sesuaikan bila menggunakan shell lain.

---

## Langkah Cepat Instalasi (Windows / PowerShell)

Di direktori proyek:

1. Salin file `.env` lalu sesuaikan variabel lingkungan

```pwsh
copy .env.example .env
# Edit .env: set DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL, dll.
```

2. Install dependensi PHP

```pwsh
composer install
```

3. Install dependensi frontend dan jalankan build (untuk development)

```pwsh
npm install
npm run dev
```

4. Buat symlink storage agar file yang diunggah dapat diakses publik

```pwsh
php artisan storage:link
```

5. Jalankan migrasi dan seeder

```pwsh
php artisan migrate --seed
```

6. Jalankan server lokal

```pwsh
php artisan serve
```

Buka `http://127.0.0.1:8000` atau `APP_URL` yang sudah dikonfigurasi.

---

## Opsi: Menghasilkan PDF di server (opsional)

Jika Anda ingin server menghasilkan PDF untuk tiket, tambahkan paket berikut:

```pwsh
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider" # opsional
```

Setelah terpasang, route unduh tiket akan menghasilkan PDF; bila belum terpasang, akan mengembalikan file HTML sebagai fallback.

---

## Rute Penting

Web (memerlukan autentikasi):

- `GET /jadwal-travel` — Daftar jadwal travel (mendukung filter: `departure_date`, `origin`, `destination`).
- `GET /jadwal-travel/{travel}` — Halaman detail travel.
- `GET /my-ticket` — Halaman tiket milik pengguna (hanya role `passenger`).
- `GET /tickets/{id}` — Lihat tiket (HTML) (hanya pemilik atau admin).
- `GET /tickets/{id}/pdf` — Unduh tiket sebagai PDF (atau HTML fallback) (hanya pemilik atau admin).

API (dilindungi Sanctum):

- `POST /api/bookings` — Membuat booking.
- `GET /api/travels/{travel}/bookings` — Mengambil daftar booking untuk travel.
- `POST /api/payments` — Unggah bukti pembayaran (multipart/form-data).

---

## Kredensial Contoh (Admin & Passenger)

Untuk mempermudah pengujian, seeder proyek biasanya membuat user contoh. Jika Anda membutuhkan akun cepat untuk login selama pengembangan, gunakan kredensial sampel berikut (jika seeder berbeda, sesuaikan dengan data di `database/seeders`):


- Admin:
	- Email: `wahyuwijaya@gmail.com`
	- Password: `wahyu123`

- Passenger (pengguna biasa):
	- Email: `penumpang1@gmail.com`
	- Password: `penumpang123`

- Passenger (pengguna biasa):
	- Email: `penumpang2@gmail.com`
	- Password: `penumpang123`

Catatan: Kredensial di atas adalah contoh; pastikan menggantinya di lingkungan produksi. Jika seeder Anda menggunakan email/password yang berbeda, lihat file `database/seeders/UserSeeder.php` untuk nilai yang sebenarnya.


## Catatan Frontend

- File halaman ada di `resources/js/Pages/`.
	- `JadwalTravel.vue` — daftar jadwal dengan filter (PrimeVue `Calendar` dan `Dropdown`).
	- `Travel/Show.vue` — halaman detail travel, form pemesanan, dialog pembayaran dengan upload gambar.
	- `MyTickets.vue` — daftar booking pengguna.
- Layout dan komponen bersama ada di `resources/js/layout`.

Perilaku filter:

- `departure_date` dikirim dalam format `YYYY-MM-DD` (tanggal lokal) untuk menghindari pergeseran tanggal karena timezone.
- Filter kosong tidak dikirim pada query string; pagination akan menyertakan filter aktif.

Upload file:

- Bukti pembayaran diunggah sebagai `multipart/form-data` dan disimpan pada disk `public`. Pastikan menjalankan `php artisan storage:link`.

---

## Catatan Backend

- `app/Http/Controllers/TicketController.php` — menampilkan tiket sebagai HTML dan mengunduh PDF (jika DomPDF terpasang).
- `app/Http/Controllers/TravelController.php` — menambahkan `booked_tickets_sum` (jumlah `ticket_count` dari booking yang statusnya bukan `cancelled`) sehingga frontend dapat menghitung sisa kursi.
- Model: `Travel`, `Booking`, `BookingDetail`, `Payment`.

Keamanan:

- Route tiket dibatasi hanya untuk pemilik booking atau admin.
- Endpoint API yang membuat booking/pembayaran sebaiknya dipanggil dengan token Sanctum dari frontend.

---

## Menjalankan Test

Menjalankan test backend dengan PHPUnit/Pest:

```pwsh
php artisan test
# atau
vendor/bin/pest
```

---

## Troubleshooting

- Gambar yang diupload muncul 404: jalankan `php artisan storage:link`.
- Filter tidak mengembalikan data: periksa query string di DevTools; controller menerima `YYYY-MM-DD` dan beberapa format ISO umum.
- Error saat upload pembayaran: cek log server dan pastikan `storage/app/public` dapat ditulis.

---

## Tips Pengembangan

- Gunakan `npm run dev` untuk pengembangan frontend dengan hot-reload.
- Gunakan `php artisan migrate:fresh --seed` untuk mereset database selama pengembangan (opsi destruktif).

---

## Kontribusi

- Ikuti gaya kode yang ada; tambahkan test untuk logika backend bila memungkinkan.

---

## Lisensi

MIT

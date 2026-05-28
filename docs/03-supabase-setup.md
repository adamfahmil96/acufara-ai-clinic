# Panduan Setup Supabase PostgreSQL untuk Acufara AI Clinic
*Terakhir Diperbarui: Mei 2026*

Dokumen ini menjelaskan cara menghubungkan aplikasi Laravel (Acufara AI Clinic) ke database PostgreSQL yang di-hosting oleh **Supabase** — sebagai pengganti database Docker lokal (`DB_HOST=db`) saat deploy ke Google Cloud Run.

> [!NOTE]
> **Mengapa Supabase?**
> Supabase menyediakan database PostgreSQL gratis yang bisa diakses dari mana saja (termasuk Cloud Run). Tier gratisnya sudah cukup untuk aplikasi skala kecil hingga menengah, dengan batasan 500 MB storage dan 2 database projects.

---

## Prasyarat

- ✅ Anda sudah memiliki akun Supabase ([supabase.com](https://supabase.com)).
- ✅ Anda sudah membuat sebuah project di Supabase.
- ✅ Project Laravel Anda sudah menggunakan `DB_CONNECTION=pgsql`.

---

## Langkah 1: Dapatkan Kredensial Koneksi Database

1. **Login ke Supabase Dashboard**
   - Buka [supabase.com/dashboard](https://supabase.com/dashboard) dan login.
   - Klik project yang sudah Anda buat.

2. **Buka Pengaturan Database**
   - Di sidebar kiri, klik ikon **⚙️ Project Settings** (gear/roda gigi) di bagian paling bawah.
   - Klik menu **Database** di panel kiri.

3. **Salin Informasi Koneksi**
   - Scroll ke bagian **Connection parameters** atau **Connection string**.
   - Anda akan melihat tabel berisi informasi berikut:

   | Parameter | Contoh Nilai | Keterangan |
   |---|---|---|
   | **Host** | `db.abcdefghijklmnop.supabase.co` | Alamat server database |
   | **Port** | `5432` | Port standar PostgreSQL |
   | **Database name** | `postgres` | Nama database (default Supabase) |
   | **User** | `postgres.abcdefghijklmnop` | Username koneksi (format baru 2025+) |
   | **Password** | *(password yang Anda buat saat membuat project)* | Password database |

   > [!IMPORTANT]
   > **Password** adalah yang Anda masukkan saat pertama kali membuat project Supabase. Jika lupa, Anda bisa reset di **Database > Settings > Database Password > Reset database password**.

   > [!WARNING]
   > **Catat semua nilai di atas!** Anda akan membutuhkannya untuk mengisi variabel `DB_*` di `.env` dan di Cloud Run.

---

## Langkah 2: Pilih Mode Koneksi (Direct vs Connection Pooler)

Supabase menyediakan dua cara koneksi:

### Opsi A: Direct Connection (Port 5432)
- Koneksi langsung ke PostgreSQL.
- **Cocok untuk:** Menjalankan migrasi (`php artisan migrate`), seeder, atau operasi DDL lainnya.
- **Port:** `5432`

### Opsi B: Connection Pooler / Supavisor (Port 6543)
- Menggunakan connection pooler bawaan Supabase (Supavisor).
- **Cocok untuk:** Aplikasi produksi (Cloud Run) karena mengelola koneksi database lebih efisien.
- **Port:** `6543`
- **Tipe pooling yang disarankan:** `Transaction mode`

> [!TIP]
> **Rekomendasi untuk project ini:**
> - Gunakan **Direct Connection (port 5432)** untuk menjalankan migrasi dari laptop lokal.
> - Gunakan **Connection Pooler (port 6543)** untuk koneksi dari Cloud Run (produksi).
>
> Untuk saat ini (tahap awal), Anda bisa menggunakan **Direct Connection** saja untuk keduanya. Beralih ke Pooler bisa dilakukan nanti.

### Cara Mendapatkan Connection String Pooler:
1. Di halaman **Database** settings, scroll ke bagian **Connection Pooling** atau cari bagian **Connection string** dan pilih mode **Transaction** atau **Session**.
2. Anda akan melihat host yang sama tapi dengan port berbeda (`6543`).

---

## Langkah 3: Konfigurasi `.env` Lokal untuk Supabase

Buka file `.env` di project Anda, lalu ubah bagian database menjadi:

```env
# ============================================================
# DATABASE (PostgreSQL — Supabase)
# ============================================================
DB_CONNECTION=pgsql
DB_HOST=db.XXXXXXXXXXXX.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.XXXXXXXXXXXX
DB_PASSWORD=password_project_supabase_anda
```

**Penjelasan perubahan:**
| Variabel | Sebelumnya (Docker Lokal) | Sekarang (Supabase) |
|---|---|---|
| `DB_HOST` | `db` | `db.XXXX.supabase.co` *(dari Langkah 1)* |
| `DB_PORT` | `5432` | `5432` *(sama)* |
| `DB_DATABASE` | `acufara_db` | `postgres` *(default Supabase)* |
| `DB_USERNAME` | `postgres` | `postgres.XXXX` *(format baru Supabase)* |
| `DB_PASSWORD` | `secret` | *(password project Supabase Anda)* |

> [!IMPORTANT]
> Ganti `XXXXXXXXXXXX` dengan ID project Supabase Anda yang sebenarnya (terlihat di host: `db.XXXXXXXXXXXX.supabase.co`).

---

## Langkah 4: Tes Koneksi dari Lokal

Setelah mengubah `.env`, tes apakah Laravel bisa terhubung ke Supabase:

```bash
# Jika menggunakan Docker Compose (artisan via container):
docker compose exec app php artisan db:monitor

# Atau jika menggunakan PHP langsung:
php artisan db:monitor
```

Jika berhasil, Anda akan melihat output yang menampilkan status koneksi database.

Anda juga bisa mengecek koneksi dengan perintah:
```bash
php artisan tinker
# Lalu ketik:
DB::connection()->getPDO();
```

Jika tidak ada error, koneksi berhasil! ✅

---

## Langkah 5: Jalankan Migrasi ke Supabase

Setelah koneksi berhasil, jalankan migrasi untuk membuat semua tabel di database Supabase:

```bash
# Jika via Docker Compose:
docker compose exec app php artisan migrate

# Jika via PHP langsung:
php artisan migrate
```

> [!WARNING]
> **Perhatian!** Perintah `migrate` akan membuat tabel-tabel baru di database Supabase. Pastikan Anda menjalankan ini di database Supabase yang benar (bukan database produksi yang sudah berisi data penting).

Jika Anda juga ingin mengisi data awal (seeder):
```bash
php artisan migrate --seed
```

---

## Langkah 6: Verifikasi Tabel di Supabase Dashboard

1. Kembali ke [Supabase Dashboard](https://supabase.com/dashboard).
2. Pilih project Anda.
3. Di sidebar kiri, klik **Table Editor**.
4. Anda seharusnya melihat semua tabel Laravel yang sudah dibuat (seperti `users`, `migrations`, `sessions`, dll).

> [!TIP]
> Supabase juga menyediakan **SQL Editor** (di sidebar kiri) di mana Anda bisa menjalankan query SQL langsung — sangat berguna untuk debugging atau melihat data.

---

## Langkah 7: Konfigurasi di Google Cloud Run (Produksi)

Setelah migrasi berhasil, Anda perlu mengatur variabel database di Cloud Run agar aplikasi produksi bisa terhubung ke Supabase:

1. Buka [Google Cloud Console > Cloud Run](https://console.cloud.google.com/run).
2. Klik layanan `acufara-ai-clinic`.
3. Klik **Edit & Deploy New Revision**.
4. Buka tab **Variables & Secrets**.
5. Tambahkan variabel berikut (atau perbarui jika sudah ada):

   | Name | Value |
   |---|---|
   | `DB_CONNECTION` | `pgsql` |
   | `DB_HOST` | `db.XXXXXXXXXXXX.supabase.co` |
   | `DB_PORT` | `5432` (atau `6543` jika menggunakan Pooler) |
   | `DB_DATABASE` | `postgres` |
   | `DB_USERNAME` | `postgres.XXXXXXXXXXXX` |
   | `DB_PASSWORD` | *(password project Supabase Anda)* |

6. Klik **Deploy**.

---

## Langkah 8: Keamanan Database Supabase

### 8.1 Batasi Akses IP (Opsional)

Secara default, database Supabase bisa diakses dari IP mana saja. Untuk keamanan tambahan:

1. Di Supabase Dashboard > **Project Settings** > **Database**.
2. Scroll ke bagian **Network Restrictions** / **Allowed IP addresses**.
3. Anda bisa menambahkan IP tertentu yang diizinkan (misalnya IP Cloud Run Anda).

> [!NOTE]
> Untuk Cloud Run, IP keluar bersifat dinamis sehingga pembatasan IP bisa kompleks. Untuk saat ini, password yang kuat sudah cukup sebagai lapisan keamanan.

### 8.2 Gunakan Password yang Kuat

Pastikan password database Anda:
- Minimal 16 karakter
- Kombinasi huruf besar, kecil, angka, dan simbol
- **Tidak sama** dengan password akun lain

### 8.3 Aktifkan SSL (Sudah Default)

Koneksi ke Supabase sudah menggunakan SSL secara default. Konfigurasi Laravel Anda sudah mendukung ini melalui `'sslmode' => env('DB_SSLMODE', 'prefer')` di `config/database.php`.

---

## Referensi Cepat: Variabel `.env` Database

```env
# === DEVELOPMENT (Lokal via Supabase) ===
DB_CONNECTION=pgsql
DB_HOST=db.XXXXXXXXXXXX.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.XXXXXXXXXXXX
DB_PASSWORD=password_anda

# === PRODUCTION (Cloud Run via Supabase Pooler - Opsional) ===
# DB_PORT=6543    # Gunakan port pooler untuk produksi
```

---

## Troubleshooting

### ❌ Error: "could not find driver"
**Penyebab:** Ekstensi `pdo_pgsql` belum terinstall di PHP.
**Solusi:** Jika menggunakan Docker, pastikan Dockerfile sudah menginstall ekstensi `pgsql` dan `pdo_pgsql` (sudah ada di Dockerfile Anda).

### ❌ Error: "connection refused"
**Penyebab:** Host/port salah, atau database belum bisa diakses.
**Solusi:**
- Pastikan `DB_HOST` menggunakan format `db.XXXX.supabase.co` (bukan hanya project ID).
- Pastikan `DB_PORT` sesuai (5432 untuk direct, 6543 untuk pooler).
- Pastikan project Supabase tidak sedang di-pause (project gratis akan di-pause jika tidak aktif selama 7 hari).

### ❌ Error: "password authentication failed"
**Penyebab:** Password database salah.
**Solusi:**
- Reset password di Supabase Dashboard: **Project Settings > Database > Database Password > Reset**.
- Pastikan password di `.env` tidak mengandung karakter yang perlu di-escape.

### ❌ Error: "too many connections"
**Penyebab:** Terlalu banyak koneksi terbuka (sering terjadi di Cloud Run dengan banyak instance).
**Solusi:** Gunakan Connection Pooler (port `6543`) di Cloud Run, bukan Direct Connection.

### ⚠️ Database Supabase di-Pause
**Penyebab:** Project pada tier gratis otomatis di-pause setelah 7 hari tidak aktif.
**Solusi:** Buka Supabase Dashboard, klik project yang di-pause, lalu klik **Restore**. Proses ini memakan waktu beberapa menit.

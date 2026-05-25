# 🗺️ Development Roadmap — Acufara AI Clinic

> **Kompetisi**: #JuaraVibeCoding · **Deadline**: 31 Mei 2026  
> **Environment**: WSL Ubuntu 26.04 · Laravel 13 · PHP 8.4 · PostgreSQL · Docker (FrankenPHP)

Dokumen ini adalah panduan urutan pengerjaan sesi vibe coding.  
Jika token AI habis di tengah jalan, cukup sebutkan **"lanjutkan dari Langkah X"** pada sesi berikutnya.

---

## Status Legenda

| Simbol | Arti |
|--------|------|
| `[ ]`  | Belum dikerjakan |
| `[/]`  | Sedang dikerjakan |
| `[x]`  | Selesai |

---

## FASE 0 — Fondasi Infrastruktur & Instalasi

> Tujuan: Repo siap jalan di Docker lokal. Semua package terinstall. Database bisa di-migrate.

### Langkah 1 — Docker & Konfigurasi Lingkungan `[x]`

- Buat `Dockerfile` multi-stage (FrankenPHP, PHP 8.4)
  - Sertakan ekstensi: `ext-gd`, `ext-exif`, `ext-pgsql`, `ext-pcntl`
- Buat `docker-compose.yml` dengan service:
  - `app` (FrankenPHP)
  - `db` (PostgreSQL 16)
- Buat `.env.example` sesuai template di `blueprint.md` § 6
- Verifikasi: `docker-compose up -d` berjalan tanpa error

### Langkah 2 — Instalasi Laravel 13 & Package Utama `[x]`

Urutan instalasi:
1. Install Laravel 13 ke temp dir lalu merge ke project (folder tidak kosong):
   ```bash
   composer create-project laravel/laravel:"^13.0" /tmp/acufara-temp
   rsync -av --ignore-existing /tmp/acufara-temp/ .
   rm -rf /tmp/acufara-temp
   ```
   > `--ignore-existing` memastikan file kustom kita (Dockerfile, `.env.example`, dll.) **tidak tertimpa** oleh default Laravel.
2. `composer require filament/filament:"^5.0" -W`
3. `php artisan filament:install --panels`
4. `composer require spatie/laravel-permission`
5. `composer require bezhansalleh/filament-shield:"^4.0"`
6. `composer require spatie/laravel-medialibrary`
7. `composer require spatie/image-optimizer`
8. `composer require league/flysystem-google-cloud-storage`
9. `composer require saade/filament-fullcalendar:"^4.0@beta"`

> Catatan kompatibilitas: roadmap awal memakai Filament v3, tetapi Laravel 13 membutuhkan ekosistem Filament yang lebih baru. Project ini memakai Filament v5; plugin yang terikat Filament harus memakai rilis yang mendukung Filament v5.

### Langkah 3 — Konfigurasi Awal Laravel `[x]`

- Set `config/app.php`: timezone → `Asia/Jakarta`, locale → `id`
- Set `config/database.php`: default connection → `pgsql`
- Set `config/filesystems.php`: tambahkan disk `gcs` (dari template `.env`)
- Publish migration Spatie MediaLibrary: `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"`
- Publish config Spatie MediaLibrary: `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"`
- Publish config Spatie Permission: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

---

## FASE 1 — Database Schema & Migrasi

> Tujuan: Semua tabel terbuat sesuai blueprint. Seeder bisa dijalankan.

### Langkah 4 — Migrasi Tabel Inti `[x]`

Buat migration files (urutan sesuai foreign key dependency):

1. Modifikasi `create_users_table`: tambah kolom
   - `whatsapp_number` (string, unique)
   - `email` (nullable)
   - `password` (nullable)
   - `branch_id` (foreignId, nullable)
   - `otp_code` (string, nullable)
   - `otp_expires_at` (timestamp, nullable)
   - `deleted_at` (softDeletes)
2. Buat `create_branches_table`: `nama_cabang`, `alamat`, `is_active`, `deleted_at`
3. Buat `create_site_settings_table`: `setting_key` (unique), `setting_value` (text)
4. Buat `create_articles_table`: `title`, `slug`, `excerpt`, `content`, `is_published`, `author_id`, `meta_title`, `meta_description`, `deleted_at`
5. Buat `create_patients_table`: `user_id`, `date_of_birth`, `gender`, `default_address`, `deleted_at`
6. Buat `create_services_table`: `name`, `base_price`, `is_active`
7. Buat `create_appointments_table`:
   - `branch_id`, `patient_id`, `service_id`
   - `complaint_summary`, `status` (enum: scheduled/in_progress/completed/cancelled)
   - `service_location_type` (enum: clinic/homecare)
   - `address_at_time`, `lat` (decimal 10,8), `lng` (decimal 11,8)
   - `final_price`, `scheduled_at`, `deleted_at`
8. Buat `create_soap_notes_table`:
   - `appointment_id` (unique FK)
   - `raw_transcript`, `subjective`, `objective`
   - `treatment_details` (json)
   - `assessment`, `plan`, `deleted_at`

- Jalankan: `php artisan migrate`

### Langkah 5 — Model Eloquent `[x]`

Buat Model untuk setiap tabel dengan:
- `$fillable`, `$casts`, relasi Eloquent
- SoftDeletes trait di mana diperlukan
- Model `Article` → tambahkan trait `InteractsWithMedia` + konversi thumbnail WebP
- Model `SoapNote` → tambahkan trait `InteractsWithMedia` + konversi thumbnail WebP
- Model `User` → custom login field (`whatsapp_number`), OTP helper methods

### Langkah 6 — Seeder & Akun Superadmin `[x]`

- Buat `DatabaseSeeder` yang memanggil:
  - `RolePermissionSeeder` → seed roles: `super_admin`, `branch_admin`, `patient`
  - `BranchSeeder` → buat 1 cabang contoh: "Klinik Utama"
  - `SuperAdminSeeder` → buat akun superadmin bisa login ke Filament
  - `ServiceSeeder` → seed 3 layanan default: Akupunktur, Bekam, Baby Spa
  - `SiteSettingSeeder` → seed key-value setting awal landing page
- Verifikasi: `php artisan db:seed` tidak error, bisa login ke `/admin`

---

## FASE 2 — Panel Admin (Filament)

> Tujuan: CRUD lengkap semua entitas utama. Role-based access terkonfigurasi.

### Langkah 7 — Setup Filament Panel & Shield `[x]`

- Konfigurasi `AdminPanelProvider`: path `/admin`, auth, brand, warna tema
- Install Filament Shield: `php artisan shield:install`
- Buat policies untuk semua Resource
- Jalankan: `php artisan shield:generate --all`

### Langkah 8 — Resource CRUD Dasar `[x]`

Buat Filament Resource untuk (urutan dari yang paling sederhana):

1. `BranchResource` — CRUD cabang klinik
2. `ServiceResource` — CRUD layanan & harga
3. `UserResource` — manajemen pengguna (tampilkan role, branch)
4. `PatientResource` — data pasien (lihat-only untuk branch admin)
5. `ArticleResource` — CMS Blog + SpatieMediaLibraryFileUpload (gambar artikel)
6. `SiteSettingResource` — Key-Value settings dengan Tabs UI:
   - Tab Header, Tab Konten, Tab Footer, Tab SEO

### Langkah 9 — Resource Transaksional `[x]`

1. `AppointmentResource`:
   - Form: pilih pasien, layanan, cabang, jadwal, lokasi (clinic/homecare)
   - Jika homecare → tampilkan field alamat + lat/lng (dengan Google Maps atau input manual)
   - Status management dengan action buttons
2. `SoapNoteResource`:
   - Section **"Anamnesa"**: Subjektif, Objektif, Upload Foto (override label dari SOAP standard)
   - Section **"Therapy"**: `treatment_details` (Dynamic Form JSON), Plan, Upload Foto
   - Field `raw_transcript` (textarea) + tombol **AcuVoice** (ditambahkan di Langkah 13)

### Langkah 10 — Dashboard & Kalender `[x]`

- Buat widget Filament:
  - `StatsOverviewWidget`: jumlah appointment hari ini, pasien baru, pendapatan
  - Multi-tenant: Superadmin lihat semua cabang, Branch Admin lihat cabang sendiri
- Integrasikan `saade/filament-fullcalendar`:
  - Event dari tabel `appointments`
  - Klik event → modal detail appointment
- Tambahkan filter cabang di dashboard (khusus Superadmin)

---

## FASE 3 — Otentikasi & Landing Page Publik

> Tujuan: Pasien bisa registrasi/login via WhatsApp OTP. Landing page live.

### Langkah 11 — WhatsApp OTP Authentication `[x]`

- Buat `OtpService`:
  - `generate(string $waNumber)`: generate 4 digit OTP, simpan ke DB, kirim ke provider
  - `verify(string $waNumber, string $otp)`: validasi OTP + expiry (5 menit)
  - Driver `log` (dev): tulis OTP ke `storage/logs/laravel.log`
  - Siapkan interface untuk driver `fonnte` (produksi)
- Buat routes + controller:
  - `GET /login` → form input nomor WA
  - `POST /login/otp` → kirim OTP, redirect ke form verifikasi
  - `POST /login/verify` → verifikasi OTP, login user, redirect ke booking
- Buat Blade view: halaman login pasien (desain Sage Green + Beige)

### Langkah 12 — Landing Page Publik `[x]`

- Buat layout utama `layouts/app.blade.php` (Tailwind CSS, Alpine.js, palet Sage Green/Beige)
- Buat sections landing page (konten dari `site_settings`):
  - Hero section
  - Layanan section (kartu 3 layanan)
  - Cara booking section
  - Blog/artikel preview (3 artikel terbaru)
  - Footer
- Buat halaman: `/` (landing), `/blog`, `/blog/{slug}` (detail artikel)
- Meta SEO dinamis dari `site_settings` (landing) dan `articles.meta_*` (blog)
- Buat form booking pasien dengan WhatsApp OTP flow

---

## FASE 4 — Fitur AI

> Tujuan: Dua fitur AI utama berfungsi end-to-end.

### Langkah 13 — AcuVoice: Hands-Free SOAP Notes `[x]`

- Buat `GeminiService` (HTTP Client wrapper):
  - Method `formatSoapNote(string $rawTranscript): array` → kirim ke Gemini API, kembalikan array SOAP terstruktur
  - Method `analyzeComplaint(string $complaint, array $location): array` → untuk routing
- Tambahkan ke `SoapNoteResource` (Form):
  - Tombol **"🎙️ Mulai Rekam"** (Alpine.js + Web Speech API)
  - JavaScript: `SpeechRecognition` → isi field `raw_transcript`
  - Tombol **"✨ Format dengan AI"** → kirim `raw_transcript` ke endpoint Filament Action
  - Filament Action → panggil `GeminiService::formatSoapNote()` → auto-fill field SOAP
- Verifikasi: rekam suara → teks muncul → klik AI → field SOAP terisi otomatis

### Langkah 14 — Smart Homecare Routing & Triage `[x]`

- Ditambahkan ke form booking publik (`/book`):
  - Field keluhan berganti dari `notes` ke `complaint_summary` (sesuai skema DB)
  - Tombol **"🔍 Analisis Keluhan dengan AI"** (Alpine.js + `fetch` ke `POST /triage`)
  - Card hasil analisis dengan badge urgensi (Rendah/Sedang/Tinggi), rekomendasi, dan catatan
  - Endpoint `POST /triage` di `BookingController::triage()` (auth + throttle 10/menit)
- Ditambahkan ke `AppointmentResource` (Admin):
  - Filament Action **"🔍 Analisis Keluhan dengan AI"** di Section Keluhan Pasien
  - Auto-fill field `ai_urgency`, `ai_recommendation`, `ai_notes`
  - Section **"🤖 Hasil Analisis AI (Triage)"** di form (collapsed by default)
- Migration: tambah kolom `ai_urgency`, `ai_recommendation`, `ai_notes` ke tabel `appointments`
- Model `Appointment`: kolom baru ditambahkan ke `$fillable`

### Langkah 14b — Smart Homecare Routing (Rute Perjalanan & Jadwal) `[x]`

- Buat custom page di Filament: `HomecareRoutingPage`
- **Fitur**: Memilih tanggal untuk melihat jadwal `homecare`
- **Tampilan**: List jadwal homecare hari tersebut (nama pasien, alamat, layanan)
- **AI Integration**: Tombol **"Optimasi Rute & Jadwal"** -> mengirimkan daftar alamat dan jam ke Gemini AI -> Gemini mengembalikan urutan kunjungan yang paling efisien berdasarkan lokasi agar terapis (adik ipar) tidak bolak-balik.
- **Peta (Opsional)**: Tampilkan integrasi Leaflet/Maps sederhana untuk memvisualisasikan rute jika memungkinkan.

### Langkah 14c — Koordinat & Peta Interaktif Cabang (Branch) `[x]`

- Migration: tambah kolom `lat` dan `lng` (decimal) pada tabel `branches`
- Model `Branch`: tambah `lat`, `lng` ke `$fillable` dan `booted()` observer untuk auto-geocoding via OSM (Nominatim) saat alamat disimpan
- **Filament Form** `BranchResource`: tambah input `Latitude`, `Longitude`, tombol **"📍 Geocode Alamat Otomatis"**, dan peta Leaflet interaktif
- Peta bisa diklik dan marker-nya bisa di-drag untuk mengubah koordinat secara manual
- Nilai Latitude & Longitude tersinkronisasi dua arah dengan peta secara real-time

---

## FASE 5 — Polish, Testing & Deployment

> Tujuan: Aplikasi siap demo dan deploy ke Google Cloud Run.

### Langkah 15 — Media & Storage `[x]`

- Verifikasi Spatie MediaLibrary berjalan:
  - Upload gambar di `ArticleResource` → tersimpan di `storage/app/public`
  - Konversi WebP thumbnail berjalan
- Siapkan konfigurasi GCS di `filesystems.php` (sudah via env, tinggal test switch)
- Jalankan `php artisan storage:link`

### Langkah 16 — UI Polish & Responsivitas `[x]`

- Review landing page di mobile (responsive)
- Pastikan warna, tipografi, dan komponen konsisten (Sage Green `#87A878`, Beige `#F5F0E8`)
- Tambahkan loading states dan feedback toast/notification
- Pastikan semua label Filament menggunakan Bahasa Indonesia
- **(NEW) Progressive Web App (PWA):** Tambahkan `manifest.json`, `sw.js`, dan meta tags agar website bisa diinstal ke *home screen* pengguna.

### Langkah 17 — Optimasi & Security `[x]`

- Tambahkan rate limiting di route OTP (max 5 request/menit per IP)
- Pastikan semua route admin diproteksi middleware auth + permission
- Jalankan `php artisan route:list` dan audit
- Set `APP_DEBUG=false` di `.env.example` untuk produksi

### Langkah 18 — Build Docker & Test Container `[ ]`

- Build image: `docker build -t acufara:latest .`
- Jalankan full stack via `docker-compose up -d`
- Verifikasi semua fitur berjalan di dalam container
- Pastikan environment variable GCS dan Gemini API terbaca

### Langkah 19 — Deploy ke Google Cloud Run `[ ]`

- Push Docker image ke Google Artifact Registry
- Deploy ke Cloud Run dengan env variables dari Secret Manager
- Set `FILESYSTEM_DISK=gcs`, `WA_GATEWAY_PROVIDER=fonnte` (jika ada)
- Jalankan `php artisan migrate --force` via Cloud Run Job
- Verifikasi URL produksi live dan semua fitur berfungsi

### Langkah 20 — Demo & Dokumentasi Final `[ ]`

- Update `README.md` dengan cara deploy lengkap
- Siapkan demo account (superadmin + branch admin + 1 pasien contoh)
- Record demo video singkat untuk submission kompetisi
- Submit ke #JuaraVibeCoding sebelum 31 Mei 2026 ✅

---

## Ringkasan Fase & Estimasi

| Fase | Deskripsi | Langkah | Estimasi |
|------|-----------|---------|----------|
| 0 | Fondasi Infrastruktur | 1–3 | ~2 jam |
| 1 | Database Schema | 4–6 | ~2 jam |
| 2 | Panel Admin (Filament) | 7–10 | ~4 jam |
| 3 | Auth & Landing Page | 11–12 | ~3 jam |
| 4 | Fitur AI | 13–14 | ~3 jam |
| 5 | Polish & Deploy | 15–20 | ~4 jam |
| **Total** | | | **~18 jam** |

---

## Catatan Penting

> **Cara melanjutkan sesi baru**: Sebutkan ke AI — *"Lanjutkan dari Langkah X"* dan AI akan membaca dokumen ini serta konteks blueprint untuk melanjutkan dari poin yang tepat.

> **Urutan prioritas jika waktu terbatas**: Langkah 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → 9 → 11 → 12 → 13. Langkah 10 (kalender), 14 (routing AI), dan 15–20 (polish/deploy) bisa menyusul.

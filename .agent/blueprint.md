# Blueprint Pengembangan MVP: Sistem Manajemen Akupunktur, Bekam, dan Baby Spa by Acufara

**Target Rilis**: Google Cloud Run (Kompetisi #JuaraVibeCoding - Deadline: 31 Mei 2026)

## 1. Instruksi Khusus untuk AI Agent (Antigravity)

Anda bertindak sebagai Senior Software Engineer. Tugas Anda adalah membangun sistem aplikasi berbasis web sesuai dengan spesifikasi di bawah ini. Harap patuhi standar clean code, gunakan best practices Laravel 13, dan prioritaskan arsitektur yang siap di-deploy ke Google Cloud Run menggunakan Docker (FrankenPHP) sejak Hari Pertama.

**Filosofi Reverse Thinking yang Diterapkan:**

- **Otentikasi**: Pasien kesulitan dengan password. Solusi: Passwordless Login via WhatsApp OTP. (Gunakan kolom database untuk menyimpan OTP sementara. Saat dev local, log OTP ke terminal/log file).

- **Routing Data**: Titik koordinat lokasi menempel pada tabel appointments, BUKAN di tabel patients demi fleksibilitas homecare.

- **UX Admin Multi-Layanan**: Gunakan kolom JSON treatment_details pada rekam medis agar Filament PHP bisa merender Dynamic Forms.

- **UI Labeling (Domain Language)**: Database TETAP menggunakan standar SOAP, namun label form di Filament harus di- override menjadi "Anamnesa" dan "Therapy".

- **Manajemen Media (Gambar) & Stateless Storage**: Cloud Run bersifat sementara (ephemeral). Solusi: Wajib gunakan Spatie MediaLibrary. Selama development gunakan disk public (local), namun siapkan infrastruktur untuk gcs (Google Cloud Storage) di .env. Wajib gunakan konversi thumbnail (WebP).

- **Manajemen SEO**: Simpan meta SEO Landing Page di tabel site_settings, dan meta SEO Blog langsung di dalam tabel articles.

- **Landing Page & CMS**: Landing page dikontrol menggunakan sistem Key-Value Settings yang dibagi menggunakan "Tabs" di Filament UI.

## 2. Tech Stack Requirements

- **Infrastruktur & Deployment**: Docker Multi-stage Build, FrankenPHP, **PostgreSQL**.

    - **KRUSIAL**: Dockerfile wajib menyertakan ekstensi PHP ext-gd atau ext-imagick, serta ext-exif.

- **Backend**: Laravel 13, PHP 8.4.

- **Penyimpanan Awan**: Package league/flysystem-google-cloud-storage (Siapkan konfigurasi di filesystems.php).

- **Frontend Dashboard**: Filament PHP v5 + saade/filament-fullcalendar v4 beta + SpatieMediaLibraryFileUpload.

- **Frontend Landing Page**: Blade Templates + Alpine.js + Tailwind CSS (Palet: Sage Green, Beige).

- **AI Integration**: Google Gemini API (Laravel HTTP Client).

- **Native API**: Web Speech API (HTML5) untuk fitur AcuVoice.

- **Role Management**: Spatie Laravel Permission + Filament Shield.

## 3. Daftar Fitur Inti & Alur Kerja (Workflow)

### A. Fitur Otentikasi: Passwordless WhatsApp Login

- **Alur**: Pasien membuka landing page -> Masukkan Nomor WA -> Sistem men-generate 4 digit OTP & menyimpan ke kolom otp_code beserta kedaluwarsa di otp_expires_at -> Pasien memverifikasi OTP -> Login berhasil.

### B. Fitur AI 1: Smart Homecare Routing & Triage

- **Alur Triage (Pasien)**: Pasien form Booking -> Isi keluhan (`complaint_summary`) -> Klik tombol "Analisis Keluhan dengan AI" -> Sistem memanggil `POST /triage` (auth-protected, throttle 10/menit) -> Gemini AI menganalisis urgensi dan saran kunjungan -> Hasil ditampilkan di card dan disimpan ke kolom `ai_urgency`, `ai_recommendation`, `ai_notes` di tabel `appointments`.

- **Panel Admin (Triage)**: Di `AppointmentResource` → tombol "🔍 Analisis Keluhan dengan AI" (Filament Action) tersedia di form edit untuk menganalisis keluhan pasien secara manual.

- **Alur Routing (Homecare)**: Admin/Terapis (Adik Ipar) membuka halaman khusus "Homecare Routing" di panel admin. Sistem mengambil daftar pasien `homecare` pada hari tertentu (berikut `lat`, `lng`, dan `address_at_time`). Gemini AI digunakan untuk menganalisis dan memberikan saran rute perjalanan serta jadwal kunjungan yang paling efisien berdasarkan jarak dan waktu tempuh.

### C. Fitur AI 2: AcuVoice (Hands-Free SOAP Notes) + Bukti Visual

- **Alur**: Admin di Dashboard -> Menekan tombol "Mulai Rekam" (Web Speech API). Bisa upload foto.

- **Logika AI**: Suara -> Teks raw -> Gemini API merapikan ke standar rekam medis SOAP.

- **Tampilan Filament**: Section "Anamnesa" (Subjektif, Objektif, Foto), Section "Therapy" (Detail json, Plan, Foto).

### D. Fitur CMS & SEO

- **Alur**: Menu "Pengaturan Web" (Tab: Header, Konten, Footer, Pengaturan SEO). Pengelolaan Blog dengan meta SEO tersendiri.

### E. Fitur UI & Analitik (Dashboard)

- Kalender interaktif dan widget analitik multi-tenant (Superadmin vs Branch Admin).

## 4. Struktur Skema Database

Nama Database Lokal: `acufara_db`

### 4.1 Tabel Organisasi, Autentikasi & CMS

1. `branches`: `id`, `nama_cabang`, `alamat`, `is_active`, `timestamps`, `deleted_at`.

2. `users`:
    - `id`, `name`, `whatsapp_number` (unique), `email` (nullable), `password` (nullable), `branch_id` (nullable).
    - OTP Columns: `otp_code` (string/nullable), `otp_expires_at` (timestamp/nullable).
    - `timestamps`, `deleted_at`.

3. `site_settings`: `id`, `setting_key` (string, unique), `setting_value` (text), `timestamps`.

4. `articles`: `id`, `title`, `slug`, `excerpt`, `content`, `is_published`, `author_id`, `meta_title`, `meta_description`, `timestamps`, `deleted_at`.

### 4.2 Tabel Pasien & Layanan

5. `patients`: `id`, `user_id`, `date_of_birth`, `gender`, `default_address`, `timestamps`, `deleted_at`.

6. `services`: `id`, `name`, `base_price`, `is_active`, `timestamps`.

### 4.3 Tabel Transaksional

7. `appointments`:
    - `id`, `branch_id`, `patient_id`, `service_id`.
    - `complaint_summary`, `status` (scheduled, in_progress, completed, cancelled).
    - `service_location_type` (clinic, homecare).
    - `address_at_time`, `lat` (decimal, 10,8), `lng` (decimal, 11,8).
    - `ai_urgency` (string, nullable), `ai_recommendation` (text, nullable), `ai_notes` (text, nullable) — hasil analisis Gemini Triage.
    - `final_price`, `scheduled_at`, `timestamps`, `deleted_at`.

### 4.4 Tabel Rekam Medis

8. `soap_notes`:
    - `id`, `appointment_id` (unique).
    - `raw_transcript`, `subjective`, `objective`, `treatment_details` (json), `assessment`, `plan`.
    - `timestamps`, `deleted_at`.

## 5. Langkah Eksekusi Pertama (Task for AI)

Jalankan instruksi berikut secara berurutan:

1. Buatkan `Dockerfile` produksi dengan FrankenPHP, PHP 8.4, lib gambar (gd, exif), dan `docker-compose.yml` untuk PostgreSQL.

2. Lakukan instalasi Laravel 13, Filament v5, Spatie Permissions, dan konfigurasikan `league/flysystem-google-cloud-storage`.

3. Instal plugin pendukung: `saade/filament-fullcalendar:^4.0@beta`, `spatie/laravel-medialibrary`, `spatie/image-optimizer`.

4. Publish migrasi Spatie MediaLibrary.

5. Buatkan migrasi untuk seluruh tabel utama di atas (branches, users, site_settings, dll) lengkap dengan kolom OTP di users.

6. KRUSIAL: Buatkan DatabaseSeeder atau command khusus yang men-generate akun Superadmin pertama dan akun cabang percobaan agar developer bisa langsung login ke Filament tanpa error.

7. Buatkan implementasi Model untuk Article dan SoapNote yang menyertakan trait InteractsWithMedia dan konversi gambar thumb (WebP).

## 6. Referensi `.env.example`

Tambahkan blok ini ke dalam file `.env` dan `.env.example` sebagai panduan environment:

```bash
APP_NAME="Acufara Klinik & Spa"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

# DATABASE (PostgreSQL Lokal / Docker)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=acufara_db
DB_USERNAME=postgres
DB_PASSWORD=secret

# STORAGE (Ubah ke 'gcs' saat deploy ke Cloud Run)
FILESYSTEM_DISK=public

# GOOGLE CLOUD STORAGE (Persiapan Produksi)
GOOGLE_CLOUD_PROJECT_ID=
GOOGLE_CLOUD_STORAGE_BUCKET=acufara-media-bucket
GOOGLE_CLOUD_STORAGE_PATH_PREFIX=
# Jika pakai service account key (JSON):
GOOGLE_CLOUD_KEY_FILE=

# AI INTEGRATION (Google AI Studio)
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_DEFAULT_MODEL=gemini-1.5-flash

# WHATSAPP OTP CONFIGURATION
# Gunakan 'log' saat development agar tidak menghabiskan kuota API sungguhan.
# Ganti menjadi nama provider (misal: 'fonnte') saat rilis produksi.
WA_GATEWAY_PROVIDER=log
WA_API_KEY=
```

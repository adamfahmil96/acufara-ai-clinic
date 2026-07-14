# Acufara AI Clinic — Comprehensive Codebase Analysis

> **Analyst:** Muhammad Adam Fahmil 'Ilmi (Tech Consultant & Senior Software Engineer)
> **Date:** 14 Juli 2026
> **Version Analyzed:** v2.1 (Post-competition, Maintenance phase)

---

## Daftar Isi

1. [Executive Summary](#1-executive-summary)
2. [System Overview](#2-system-overview)
3. [Architecture & Tech Stack](#3-architecture--tech-stack)
4. [Database Schema & Entity Relationship](#4-database-schema--entity-relationship)
5. [Feature Catalog](#5-feature-catalog)
6. [Business Process Flow](#6-business-process-flow)
7. [AI Integration Deep Dive](#7-ai-integration-deep-dive)
8. [Authentication & Security](#8-authentication--security)
9. [Multi-Tenancy & Access Control](#9-multi-tenancy--access-control)
10. [Deployment Architecture](#10-deployment-architecture)
11. [Monitoring & Observability](#11-monitoring--observability)
12. [Frontend Architecture](#12-frontend-architecture)
13. [Code Quality Assessment](#13-code-quality-assessment)
14. [Strengths & Recommendations](#14-strengths--recommendations)

---

## 1. Executive Summary

**Acufara AI Clinic** adalah sistem manajemen klinik berbasis web yang dirancang untuk praktisi medis solo (akupunktur, bekam, dan baby spa) dengan layanan homecare mobile. Sistem ini dibangun untuk kompetisi **#JuaraVibeCoding** (deadline 31 Mei 2026) dan kini dalam fase maintenance serta pengembangan fitur berkelanjutan.

### Nilai Jual Utama:
- **AcuVoice** — Hands-free SOAP notes via voice recording + AI formatting
- **Smart Homecare Routing** — AI-powered route optimization untuk kunjungan pasien
- **Passwordless WhatsApp Login** — Autentikasi tanpa password via OTP WhatsApp
- **Self-Registration Booking** — Pasien bisa booking tanpa registrasi sebelumnya
- **Dynamic Treatment Forms** — Form rekam medis yang adaptif per layanan
- **Integrated CMS & SEO** — Landing page + blog untuk SEO lokal

### Target Users:
| Role | Deskripsi | Akses |
|------|-----------|-------|
| **Pasien** | Masyarakat umum yang ingin booking layanan | Public web (landing page, booking, profile) |
| **Branch Admin** | Admin cabang klinik (misal: adik ipar) | Filament panel (scoped ke cabang sendiri) |
| **Super Admin** | Pemilik klinik | Full Filament panel (semua cabang) |
| **Developer** | Pengembang sistem | Full panel + Log Viewer + Developer Info |

---

## 2. System Overview

### 2.1 Domain Context
Acufara beroperasi di domain **healthcare management** untuk klinik kecil-menengah dengan model bisnis:
- **Layanan Tetap:** Akupunktur (Rp150.000), Bekam (Rp125.000), Baby Spa (Rp175.000)
- **Dua Tipe Kunjungan:** Klinik (pasien datang ke lokasi) dan Homecare (terapis datang ke lokasi pasien)
- **Multi-Cabang:** Mendukung beberapa cabang klinik dengan data terpisah
- **Solo Practitioner:** Satu terapis utama yang menangani semua layanan

### 2.2 Core Business Entities
```
┌─────────────────────────────────────────────────────────────────┐
│                        ORGANISASI                                │
│  Branch (Cabang) ──── User (Pengguna) ──── Patient (Pasien)     │
├─────────────────────────────────────────────────────────────────┤
│                        TRANSAKSI                                 │
│  Appointment (Janji Temu) ──── SoapNote (Rekam Medis)           │
│       │                                                          │
│       ├── Service (Layanan)                                      │
│       ├── AI Triage Results                                      │
│       └── Location Data (lat/lng untuk homecare)                │
├─────────────────────────────────────────────────────────────────┤
│                        KONTEN                                    │
│  Article (Blog/CMS) ──── SiteSetting (Pengaturan Website)       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Architecture & Tech Stack

### 3.1 Technology Matrix

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend** | Laravel | 13 | PHP framework utama |
| **Language** | PHP | 8.4 | Runtime |
| **Admin Panel** | Filament PHP | v5 | TALL Stack admin dashboard |
| **Frontend** | Blade + Alpine.js + Tailwind CSS | v4 | Public-facing UI |
| **Database** | PostgreSQL | 16 | Primary data store |
| **DB Hosting** | Supabase | - | Managed PostgreSQL (production) |
| **Container** | Docker + FrankenPHP | - | Application runtime |
| **Web Server** | Caddy (via FrankenPHP) | - | HTTP server with auto-HTTPS |
| **Object Storage** | Google Cloud Storage | - | Media/file storage (production) |
| **AI Engine** | Google Gemini API | 2.5 Flash | AI-powered features |
| **WhatsApp Gateway** | Fonnte | - | OTP & notification delivery |
| **Geocoding** | Nominatim (OpenStreetMap) | - | Address-to-coordinates conversion |
| **Deployment** | Google Cloud Run | - | Serverless container platform |
| **CI/CD** | GitHub Actions | - | Automated build & deploy |
| **Media Library** | Spatie MediaLibrary | - | File upload & image conversion |
| **Role Management** | Spatie Permission + Filament Shield | - | RBAC implementation |
| **Calendar** | saade/filament-fullcalendar | v4 beta | Interactive calendar widget |
| **PWA** | Service Worker + Manifest | - | Installable web app experience |

### 3.2 Architecture Pattern
Sistem mengadopsi **Monolithic Architecture** dengan komponen:
- **MVC Pattern** — Laravel's default architecture
- **Service Layer** — Business logic terencapsulasi dalam Service classes
- **Repository Pattern** — Implicit via Eloquent Models
- **Event-Driven** — Model events (booted) untuk auto-geocoding
- **Multi-tenant by Branch** — Data isolation via `branch_id` scoping

### 3.3 Directory Structure (Relevan)
```
app/
├── Console/                    # Artisan commands
├── Database/Connectors/        # Custom PostgresConnector (Supabase compatibility)
├── Filament/
│   ├── Pages/                  # 4 custom pages (Dashboard, Analytics, HomecareRouting, DeveloperInfo)
│   ├── Resources/              # 8 resources with extracted Form/Table schemas
│   └── Widgets/                # 3 widgets (Stats, Calendar, Info)
├── Http/
│   ├── Controllers/            # 8 controllers (public + API)
│   ├── Controllers/Api/        # FonnteCheckController
│   └── Controllers/Auth/       # WhatsAppAuthController
├── Mail/                       # FonnteDisconnectedMail
├── Models/                     # 8 Eloquent models
├── Providers/                  # AppServiceProvider, AdminPanelProvider
└── Services/                   # 5 service classes (Gemini, Geocode, OTP, WhatsApp, FonnteMonitoring)
```

---

## 4. Database Schema & Entity Relationship

### 4.1 Entity Relationship Diagram

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   branches   │     │    users     │     │   patients   │
├──────────────┤     ├──────────────┤     ├──────────────┤
│ id           │◄──┐ │ id           │◄──┐ │ id           │
│ nama_cabang  │   │ │ name         │   │ │ user_id      │──┐
│ alamat       │   │ │ whatsapp_number│  │ │ date_of_birth│  │
│ whatsapp_number│ │ │ email        │   │ │ gender       │  │
│ lat/lng      │   │ │ password     │   │ │ default_address│ │
│ is_active    │   │ │ branch_id    │───┘ │ deleted_at   │  │
│ deleted_at   │   │ │ otp_code     │     └──────────────┘  │
└──────────────┘   │ │ otp_expires_at│          │            │
                   │ │ deleted_at   │          │            │
                   │ └──────────────┘          │            │
                   │          │                │            │
                   │          ▼                ▼            │
                   │  ┌──────────────────────────────┐     │
                   │  │        appointments           │     │
                   │  ├──────────────────────────────┤     │
                   │  │ id                           │     │
                   │  │ branch_id ───────────────────┘     │
                   │  │ patient_id ────────────────────────┘
                   │  │ service_id ──────┐
                   │  │ complaint_summary│
                   │  │ medical_history  │     ┌──────────────┐
                   │  │ allergy_history  │     │   services   │
                   │  │ status           │     ├──────────────┤
                   │  │ service_location_type   │ id           │
                   │  │ address_at_time  │     │ name         │
                   │  │ lat/lng          │     │ base_price   │
                   │  │ ai_urgency       │     │ is_active    │
                   │  │ ai_recommendation│     └──────────────┘
                   │  │ ai_notes         │
                   │  │ final_price      │
                   │  │ scheduled_at     │
                   │  │ source           │
                   │  │ deleted_at       │
                   │  └────────┬─────────┘
                   │           │ 1:1
                   │           ▼
                   │  ┌──────────────────────────────┐
                   │  │         soap_notes            │
                   │  ├──────────────────────────────┤
                   │  │ id                           │
                   │  │ appointment_id               │
                   │  │ raw_transcript               │
                   │  │ subjective                   │
                   │  │ objective                    │
                   │  │ treatment_details (JSON)     │
                   │  │ assessment                   │
                   │  │ plan                         │
                   │  │ deleted_at                   │
                   │  └──────────────────────────────┘
                   │
                   │  ┌──────────────────────────────┐     ┌──────────────────┐
                   └─►│         articles             │     │  site_settings   │
                      ├──────────────────────────────┤     ├──────────────────┤
                      │ id                           │     │ id               │
                      │ title                        │     │ setting_key      │
                      │ slug                         │     │ setting_value    │
                      │ excerpt                      │     └──────────────────┘
                      │ content                      │
                      │ is_published                 │
                      │ author_id ───────────────────┘
                      │ meta_title                   │
                      │ meta_description             │
                      │ deleted_at                   │
                      └──────────────────────────────┘

Supporting Tables:
- permission_tables (Spatie Permission: roles, permissions, model_has_roles)
- media (Spatie MediaLibrary: file attachments)
- cache (Laravel cache)
- sessions (Laravel sessions)
- fonnte_status_logs (WhatsApp gateway monitoring)
```

### 4.2 Tabel Detail

#### `branches`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `nama_cabang` | string | Nama cabang klinik |
| `alamat` | text | Alamat lengkap |
| `whatsapp_number` | string | Nomor WA cabang (untuk notifikasi booking) |
| `lat` | decimal(10,8) | Latitude (auto-geocode via OSM) |
| `lng` | decimal(11,8) | Longitude (auto-geocode via OSM) |
| `is_active` | boolean | Status aktif cabang |
| `deleted_at` | timestamp | Soft delete |

#### `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `name` | string | Nama lengkap |
| `whatsapp_number` | string UNIQUE | Login identifier utama |
| `email` | string NULLABLE | Email (opsional) |
| `password` | string NULLABLE | Hash password (nullable untuk patient) |
| `branch_id` | FK NULLABLE | Relasi ke branches |
| `otp_code` | string NULLABLE | Kode OTP sementara |
| `otp_expires_at` | timestamp NULLABLE | Waktu kedaluwarsa OTP |
| `deleted_at` | timestamp | Soft delete |

#### `patients`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `user_id` | FK | Relasi 1:1 ke users |
| `date_of_birth` | date | Tanggal lahir |
| `gender` | enum | Laki-laki / Perempuan |
| `default_address` | text | Alamat default |
| `deleted_at` | timestamp | Soft delete |

#### `services`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `name` | string | Nama layanan |
| `base_price` | integer | Harga dasar (Rp) |
| `is_active` | boolean | Status aktif |

#### `appointments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `branch_id` | FK | Relasi ke branches |
| `patient_id` | FK | Relasi ke patients |
| `service_id` | FK | Relasi ke services |
| `complaint_summary` | text | Keluhan pasien |
| `medical_history` | text NULLABLE | Riwayat penyakit |
| `allergy_history` | text NULLABLE | Riwayat alergi |
| `status` | enum | scheduled / in_progress / completed / cancelled |
| `service_location_type` | enum | clinic / homecare |
| `address_at_time` | text NULLABLE | Alamat saat homecare |
| `lat` | decimal(10,8) | Latitude lokasi homecare |
| `lng` | decimal(11,8) | Longitude lokasi homecare |
| `ai_urgency` | string NULLABLE | Hasil AI: rendah/sedang/tinggi |
| `ai_recommendation` | text NULLABLE | Hasil AI: rekomendasi |
| `ai_notes` | text NULLABLE | Hasil AI: catatan |
| `final_price` | integer | Harga final |
| `scheduled_at` | datetime | Jadwal kunjungan |
| `source` | enum | self_register / admin |
| `deleted_at` | timestamp | Soft delete |

#### `soap_notes`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `appointment_id` | FK UNIQUE | Relasi 1:1 ke appointments |
| `raw_transcript` | text | Teks mentah dari rekaman suara |
| `subjective` | text | SOAP: Subjektif (keluhan pasien) |
| `objective` | text | SOAP: Objektif (pemeriksaan) |
| `treatment_details` | JSON | Detail treatment (key-value pairs) |
| `assessment` | text | SOAP: Assessment (diagnosis) |
| `plan` | text | SOAP: Plan (rencana terapi) |
| `deleted_at` | timestamp | Soft delete |

#### `articles`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `title` | string | Judul artikel |
| `slug` | string UNIQUE | URL slug |
| `excerpt` | text | Ringkasan |
| `content` | longtext | Konten lengkap (rich text) |
| `is_published` | boolean | Status publikasi |
| `author_id` | FK | Relasi ke users |
| `meta_title` | string NULLABLE | SEO meta title |
| `meta_description` | text NULLABLE | SEO meta description |
| `deleted_at` | timestamp | Soft delete |

#### `site_settings`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint PK | Primary key |
| `setting_key` | string UNIQUE | Key (e.g., `header.brand_name`) |
| `setting_value` | text | Value |

### 4.3 Seed Data

**Roles (5):**
- `super_admin` — Full access
- `developer` — Full access + Log Viewer
- `demo_super_admin` — Read-only (view only permissions)
- `branch_admin` — Branch-scoped CRUD
- `patient` — Public access only

**Services (3):**
| Layanan | Harga |
|---------|-------|
| Akupunktur | Rp150.000 |
| Bekam | Rp125.000 |
| Baby Spa | Rp175.000 |

**Default Users (4):**
| User | Email | WA | Role |
|------|-------|-----|------|
| Super Admin | dari .env | dari .env | super_admin |
| Mas Adam | developer@acufara.com | 089999999999 | super_admin + developer |
| Demo Admin | demo@acufara.com | 081234567890 | demo_super_admin |
| Demo Patient | - | 08111111111 | patient (OTP bypass: 1234) |

---

## 5. Feature Catalog

### 5.1 AcuVoice — Hands-Free SOAP Notes
**Status:** Production-ready

Fitur flagship yang memungkinkan praktisi merekam diagnosis via suara dan AI mengubahnya menjadi catatan medis SOAP terstruktur.

**Teknologi:**
- Web Speech API (HTML5) untuk speech-to-text (bahasa Indonesia: `id-ID`)
- Alpine.js component (`acuvoice.js`) untuk UI recorder
- Gemini API untuk formatting ke standar SOAP

**Alur Kerja:**
1. Praktisi menekan tombol "Mulai Rekam" di form SoapNoteResource
2. Web Speech API merekam suara dan mengubah ke teks real-time
3. Teks mentah tersimpan di field `raw_transcript`
4. Praktisi menekan "Format dengan AI"
5. Gemini API memproses teks dan mengembalikan JSON terstruktur
6. Field `subjective`, `objective`, `assessment`, `plan` terisi otomatis
7. Praktisi bisa upload foto sebelum/sesudah treatment

**Guardrails:**
- Jika input bukan konteks medis, AI mengembalikan error di field `assessment`
- Auto-restart recording jika timeout (Web Speech API limitation)

### 5.2 Smart Homecare Routing & Triage
**Status:** Production-ready

Dua fitur AI yang saling melengkapi:

#### 5.2.1 AI Triage
Menganalisis keluhan pasien untuk menentukan urgensi dan rekomendasi.

**Output:**
- `ai_urgency`: rendah / sedang / tinggi
- `ai_recommendation`: rekomendasi tindakan
- `ai_notes`: catatan tambahan

**Akses:**
- Public: `/triage` endpoint (authenticated, throttle 10/menit)
- Admin: Inline button di AppointmentResource form

#### 5.2.2 Smart Routing
Mengoptimalkan rute perjalanan terapis untuk kunjungan homecare.

**Alur:**
1. Admin membuka halaman "Homecare Routing" di Filament
2. Memilih tanggal untuk melihat jadwal homecare
3. Sistem menampilkan peta dengan lokasi pasien
4. Admin menekan "Optimasi Rute dengan AI"
5. Gemini menganalisis alamat dan mengembalikan urutan kunjungan optimal

### 5.3 Passwordless WhatsApp Login
**Status:** Production-ready (via Fonnte)

**Alur:**
1. Pasien memasukkan nomor WhatsApp di halaman login
2. Sistem generate OTP 4 digit, simpan ke cache (5 menit)
3. OTP dikirim via Fonnte API (production) atau log (development)
4. Pasien memverifikasi OTP
5. Jika user baru: auto-create User + Patient + assign role `patient`
6. Login berhasil, redirect ke profile/booking

**Special Case:**
- Demo bypass: nomor `08111111111` + OTP `1234` bisa login langsung

### 5.4 Self-Registration Booking
**Status:** Production-ready

Pasien bisa booking tanpa registrasi sebelumnya melalui `/daftar`.

**Alur:**
1. Pasien mengisi nomor WhatsApp
2. AJAX lookup: cek apakah sudah terdaftar
3. Jika baru: isi data diri (nama, tanggal lahir, gender, alamat)
4. Pilih layanan, cabang, jadwal, tipe kunjungan
5. Isi keluhan + opsional: riwayat penyakit & alergi
6. Opsional: jalankan AI triage
7. Submit → DB transaction: create User + Patient + Appointment
8. WhatsApp notification dikirim ke cabang (clinic) atau nomor Acufara (homecare)

### 5.5 Dynamic Treatment Forms
**Status:** Production-ready

Form SOAP notes menggunakan `treatment_details` (JSON) dengan komponen `KeyValue` di Filament, memungkinkan:
- Akupunktur: "Titik LI4" → "Keterangan: jarum 0.25x25mm"
- Bekam: "Area Punggung" → "Keterangan: 7 cup"
- Baby Spa: "Berat Badan" → "3.5 kg"

### 5.6 Integrated CMS & SEO
**Status:** Production-ready

**Landing Page CMS:**
- Key-value settings dengan 5 tab: Header, Hero, Content, Footer, SEO
- Managed via SiteSettingResource di Filament
- Dynamic rendering di welcome.blade.php

**Blog:**
- Article model dengan Spatie MediaLibrary untuk gambar
- SEO fields: meta_title, meta_description
- Paginated index (9/page) + single article view
- Published/draft toggle

### 5.7 Interactive Calendar & Dashboard
**Status:** Production-ready

**Dashboard Components:**
- StatsOverviewWidget: Jadwal hari ini, Pasien baru, Pendapatan (masked for demo)
- AppointmentCalendarWidget: FullCalendar dengan color-coded events
- AcufaraInfoWidget: Branding/info widget
- Branch filter (super_admin only)

**Analytics Page:**
- Month/year/branch filters
- KPI cards: total appointments, completed, revenue, new patients
- Service breakdown table
- 6-month revenue trend chart

### 5.8 Progressive Web App (PWA)
**Status:** Production-ready

- `manifest.json` — App metadata, icons, theme colors
- `sw.js` — Service Worker with cache-first strategy
- PWA icons (192x192, 512x512)
- Installable to home screen from browser

### 5.9 Fonnte Monitoring
**Status:** Production-ready

Automated monitoring untuk WhatsApp gateway:
- API endpoint `/api/fonnte/check` (Bearer token auth)
- Google Cloud Scheduler: setiap 5 menit
- Cache status 5 menit
- Email alert jika disconnect (throttle 30 menit)
- Gmail SMTP integration

---

## 6. Business Process Flow

### 6.1 Patient Journey (End-to-End)

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PATIENT JOURNEY                                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  1. DISCOVERY                                                         │
│     ┌──────────┐    ┌──────────┐    ┌──────────┐                    │
│     │ Landing  │───►│  Blog    │───►│ Service  │                    │
│     │ Page (/) │    │ (/blog)  │    │ Pages    │                    │
│     └──────────┘    └──────────┘    └──────────┘                    │
│                                                                       │
│  2. BOOKING (Pilih salah satu)                                       │
│     ┌──────────────────────┐    ┌──────────────────────┐            │
│     │ Self-Register (/daftar)│   │ Auth Booking (/book) │            │
│     │ (Tanpa registrasi)    │   │ (Perlu login OTP)    │            │
│     └──────────┬───────────┘    └──────────┬───────────┘            │
│                │                            │                        │
│                ▼                            ▼                        │
│     ┌──────────────────────────────────────────────┐                │
│     │         AI TRIAGE (opsional)                  │                │
│     │  "Analisis Keluhan dengan AI"                 │                │
│     │  → Urgency: rendah/sedang/tinggi              │                │
│     │  → Recommendation + Notes                     │                │
│     └──────────────────────┬───────────────────────┘                │
│                            │                                         │
│                            ▼                                         │
│     ┌──────────────────────────────────────────────┐                │
│     │         BOOKING CONFIRMED                     │                │
│     │  → Appointment created (status: scheduled)    │                │
│     │  → WhatsApp notification to clinic            │                │
│     └──────────────────────┬───────────────────────┘                │
│                            │                                         │
│  3. POST-BOOKING                                                     │
│     ┌──────────────────────▼───────────────────────┐                │
│     │         PATIENT PORTAL                        │                │
│     │  → Profile (/profile)                         │                │
│     │  → Appointment history                        │                │
│     │  → Edit profile                               │                │
│     └──────────────────────────────────────────────┘                │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

### 6.2 Admin Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ADMIN WORKFLOW (/admin)                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  1. LOGIN                                                             │
│     WhatsApp OTP login (same as patient, but with admin role)        │
│                                                                       │
│  2. DASHBOARD                                                         │
│     ┌──────────────────────────────────────────────┐                │
│     │ Stats: Jadwal Hari Ini | Pasien Baru | Omset │                │
│     │ Calendar: Visual appointment schedule         │                │
│     │ Branch Filter (super_admin only)              │                │
│     └──────────────────────┬───────────────────────┘                │
│                            │                                         │
│  3. APPOINTMENT MANAGEMENT                                            │
│     ┌──────────────────────▼───────────────────────┐                │
│     │ List → View → Edit                            │                │
│     │                                               │                │
│     │ Status Workflow:                              │                │
│     │ scheduled ──► in_progress ──► completed       │                │
│     │      │                            │           │                │
│     │      └──────────► cancelled ◄─────┘           │                │
│     │                                               │                │
│     │ AI Features:                                  │                │
│     │ • "Analisis Keluhan" button → auto-fill       │                │
│     │ • Triage section (collapsed)                  │                │
│     └──────────────────────┬───────────────────────┘                │
│                            │                                         │
│  4. SOAP NOTES (per appointment)                                     │
│     ┌──────────────────────▼───────────────────────┐                │
│     │ AcuVoice Recorder → raw_transcript            │                │
│     │ "Format dengan AI" → auto-fill SOAP fields    │                │
│     │ Anamnesa section: subjective + objective      │                │
│     │ Therapy section: assessment + plan            │                │
│     │ Treatment Details: flexible JSON key-value    │                │
│     │ Photo uploads: anamnesa + therapy images      │                │
│     └──────────────────────────────────────────────┘                │
│                                                                       │
│  5. HOMECARE ROUTING                                                  │
│     ┌──────────────────────────────────────────────┐                │
│     │ Select date → View homecare schedule          │                │
│     │ Map: patient locations with markers           │                │
│     │ "Optimasi Rute dengan AI" → route suggestion  │                │
│     └──────────────────────────────────────────────┘                │
│                                                                       │
│  6. MASTER DATA                                                       │
│     • Branches (with geocoding + interactive map)                    │
│     • Services (name, price, active toggle)                          │
│     • Users (role assignment, branch scoping)                        │
│     • Patients (view/edit, gender filter)                            │
│                                                                       │
│  7. CONTENT MANAGEMENT                                                │
│     • Articles (blog CMS with media upload)                          │
│     • Site Settings (landing page key-value CMS)                     │
│                                                                       │
│  8. ANALYTICS (super_admin + developer)                              │
│     • Month/year/branch filters                                      │
│     • KPI cards + service breakdown                                  │
│     • 6-month revenue trend                                          │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

### 6.3 Appointment Status Lifecycle

```
                    ┌─────────────┐
                    │  scheduled  │ (initial state)
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              ▼            │            ▼
     ┌──────────────┐      │   ┌──────────────┐
     │ in_progress  │      │   │  cancelled   │ (terminal)
     └──────┬───────┘      │   └──────────────┘
            │              │
            ▼              │
     ┌──────────────┐      │
     │  completed   │      │
     └──────────────┘      │
                           │
                    (can also cancel from scheduled)
```

---

## 7. AI Integration Deep Dive

### 7.1 GeminiService Architecture

**File:** `app/Services/GeminiService.php`

**Configuration:**
- API Key: `GEMINI_API_KEY` env variable
- Default Model: `gemini-2.5-flash` (constructor override)
- Fallback Models: `gemini-2.5-flash-lite`
- Timeout: 120 seconds
- Temperature: 0.2 (deterministic output)
- Max Output Tokens: 2048

**Fallback Strategy:**
```
Primary Model (gemini-2.5-flash)
    ↓ [503 High Demand / 429 Rate Limit]
Fallback Model (gemini-2.5-flash-lite)
    ↓ [Still failing]
Error returned to user
```

### 7.2 AI Capabilities

#### 7.2.1 `formatSoapNote()` — AcuVoice
**Input:** Raw voice transcript (string)
**Output:** Structured JSON with `subjective`, `objective`, `assessment`, `plan`

**Prompt Strategy:**
- System instruction: "Anda adalah asisten medis AI yang memformat catatan klinis"
- Output format: strict JSON
- Language: Bahasa Indonesia
- Guardrails: non-medical input returns error in `assessment` field

#### 7.2.2 `analyzeComplaint()` — AI Triage
**Input:** Complaint text + optional location data
**Output:** JSON with `urgency`, `recommendation`, `notes`

**Prompt Strategy:**
- Medical triage context
- Urgency levels: rendah/sedang/tinggi
- Guardrails: non-medical requests rejected

#### 7.2.3 `optimizeRoute()` — Homecare Routing
**Input:** List of appointments + branch address
**Output:** Markdown-formatted route optimization

**Prompt Strategy:**
- Geographic optimization context
- Time/distance efficiency focus
- Guardrails: prompt injection protection (ignores hidden instructions in patient names/addresses)

#### 7.2.4 `geocodeAddress()` — AI Geocoding
**Input:** Indonesian address text
**Output:** Estimated lat/lng coordinates

**Prompt Strategy:**
- Indonesia-specific geocoding
- Strips `<think>` tags from Gemini 2.5 Flash thinking mode
- Used as fallback when Nominatim fails

### 7.3 GeocodeService Architecture

**File:** `app/Services/GeocodeService.php`

**Strategy: Progressive Address Relaxation**
1. Try full address via Nominatim
2. If fail → try last 3 words (typically city/district)
3. If fail → try last 2 words
4. If all fail → return randomized coordinates near Surakarta (-7.5666, 110.8166)

**Used by:**
- `Branch::booted()` — auto-geocode on save
- `Appointment::booted()` — auto-geocode homecare addresses
- BranchResource form — manual "Geocode Alamat Otomatis" button

---

## 8. Authentication & Security

### 8.1 Authentication Mechanisms

#### 8.1.1 WhatsApp OTP (Patient)
- **Provider:** Fonnte API (production) / Log (development)
- **OTP:** 4-digit, cached for 5 minutes
- **Rate Limit:** 5 requests/minute per IP on `/login/otp`
- **Auto-Registration:** New users auto-created on first OTP verification

#### 8.1.2 Standard Login (Admin)
- **Method:** WhatsApp number + password (via Filament login)
- **Users:** Admin, Super Admin, Developer
- **Panel:** `/admin` (Filament)

### 8.2 Security Measures

| Layer | Implementation |
|-------|---------------|
| **CSRF Protection** | Laravel's built-in CSRF tokens |
| **Rate Limiting** | `throttle:5,1` on OTP, `throttle:10,1` on triage |
| **Password Hashing** | Laravel's Hash facade (bcrypt) |
| **Session Security** | Encrypted cookies, session regeneration on login |
| **API Authentication** | Bearer token for Fonnte monitoring endpoint |
| **HTTPS** | Forced in production (detected via `K_SERVICE` env) |
| **Input Validation** | Laravel validation on all form inputs |
| **SQL Injection** | Eloquent ORM parameterized queries |
| **XSS Protection** | Blade's `{{ }}` auto-escaping |
| **Soft Deletes** | Data preservation, no hard deletes |
| **Role-Based Access** | Spatie Permission + Filament Shield |
| **Proxy Trust** | `trustProxies(at: '*')` for Cloud Run/Load Balancer |

### 8.3 Demo Mode Protections
- Prices masked as `Rp ***` for `demo_super_admin` role
- OTP bypass for demo patient (`08111111111` + `1234`)
- Read-only permissions for demo accounts

---

## 9. Multi-Tenancy & Access Control

### 9.1 Tenancy Model
**Pattern:** Shared database, row-level isolation via `branch_id`

**Scoping:**
- `branch_admin` sees only their branch's data
- `super_admin` sees all branches (with optional filter)
- `patient` sees only their own data

### 9.2 Role-Permission Matrix

| Resource | super_admin | developer | demo_super_admin | branch_admin | patient |
|----------|:-----------:|:---------:|:----------------:|:------------:|:-------:|
| Appointments | CRUD | CRUD | View | CRUD (own branch) | Create (own) |
| SoapNotes | CRUD | CRUD | View | CRUD (own branch) | - |
| Branches | CRUD | CRUD | View | View (own) | - |
| Services | CRUD | CRUD | View | View | - |
| Patients | CRUD | CRUD | View | View (own branch) | - |
| Users | CRUD | CRUD | View | View (own branch) | - |
| Articles | CRUD | CRUD | View | CRUD | - |
| Site Settings | CRUD | CRUD | View | - | - |
| Analytics | Full | Full | - | - | - |
| Homecare Routing | Full | Full | View | Full | - |
| Log Viewer | - | Full | - | - | - |

### 9.3 Data Scoping Implementation

**Filament Resources:**
```php
// In getEloquentQuery()
if (! auth()->user()->hasRole('super_admin')) {
    $query->where('branch_id', auth()->user()->branch_id);
}
```

**Dashboard Widgets:**
- `InteractsWithPageFilters` trait to respond to branch filter
- Stats filtered by `branch_id` for non-super-admin users

---

## 10. Deployment Architecture

### 10.1 Production Infrastructure

```
┌─────────────────────────────────────────────────────────────────┐
│                    GOOGLE CLOUD PLATFORM                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────┐     ┌─────────────────┐                    │
│  │  Cloud Run       │     │  Cloud Storage   │                    │
│  │  (FrankenPHP)    │────►│  (GCS Bucket)    │                    │
│  │  Port: 8080      │     │  Media/Files     │                    │
│  │  Region: Jakarta │     │                  │                    │
│  └────────┬─────────┘     └─────────────────┘                    │
│           │                                                       │
│           │                                                       │
│  ┌────────▼─────────┐     ┌─────────────────┐                    │
│  │  Load Balancer   │     │  Cloud Scheduler │                    │
│  │  (GCP)           │     │  (Fonnte Check)  │                    │
│  │  Session Affinity│     │  */5 * * * *     │                    │
│  └──────────────────┘     └────────┬────────┘                    │
│                                    │                              │
│                                    ▼                              │
│                           ┌─────────────────┐                    │
│                           │  /api/fonnte/   │                    │
│                           │  check          │                    │
│                           └─────────────────┘                    │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                    EXTERNAL SERVICES                              │
│                                                                   │
│  ┌─────────────────┐     ┌─────────────────┐                    │
│  │  Supabase        │     │  Fonnte API      │                    │
│  │  (PostgreSQL 16) │     │  (WhatsApp)      │                    │
│  │  Port: 6543      │     │                  │                    │
│  │  (Connection     │     └─────────────────┘                    │
│  │   Pooler)        │                                             │
│  └─────────────────┘     ┌─────────────────┐                    │
│                          │  Google Gemini   │                    │
│  ┌─────────────────┐     │  API             │                    │
│  │  Docker Hub      │     └─────────────────┘                    │
│  │  (Image Store)   │                                             │
│  └─────────────────┘     ┌─────────────────┐                    │
│                          │  Nominatim       │                    │
│  ┌─────────────────┐     │  (OpenStreetMap) │                    │
│  │  GitHub Actions  │     └─────────────────┘                    │
│  │  (CI/CD)         │                                             │
│  └─────────────────┘                                             │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### 10.2 Deployment Flow

```
Developer Laptop
       │
       ▼
git push origin main
       │
       ▼
GitHub Actions (.github/workflows/deploy.yml)
       │
       ├── 1. Checkout code
       ├── 2. Login to Docker Hub
       ├── 3. Build Docker image (multi-stage)
       ├── 4. Push to Docker Hub (adamfahmil96/acufara-ai-clinic:latest)
       ├── 5. Authenticate to GCP
       └── 6. Deploy to Cloud Run
              │
              ▼
       Google Cloud Run
              │
              ├── Container starts
              ├── entrypoint.sh runs
              │   ├── Clear all caches
              │   ├── Run migrations (if RUN_MIGRATIONS=true)
              │   └── Start FrankenPHP
              │
              └── Service available at port 8080
```

### 10.3 Docker Configuration

**Dockerfile (Multi-stage):**
1. **Stage 1:** PHP 8.4 FrankenPHP Alpine + extensions (gd, exif, pgsql, pcntl)
2. **Stage 2:** Composer dependencies install
3. **Stage 3:** Node 22 frontend build (Vite + TailwindCSS)
4. **Stage 4:** Production FrankenPHP image

**docker-compose.yml:**
- `app`: FrankenPHP (port 8000:8080)
- `db`: PostgreSQL 16 Alpine
- `mailpit`: Local email catcher (port 8025)

**Environment Variables (Production):**
| Variable | Value | Purpose |
|----------|-------|---------|
| `SERVER_NAME` | `:8080` | Disable auto-HTTPS (GCP LB handles it) |
| `FILESYSTEM_DISK` | `gcs` | Use Google Cloud Storage |
| `DB_PORT` | `6543` | Supabase Connection Pooler |
| `DB_EMULATE_PREPARES` | `true` | Supavisor compatibility |

### 10.4 Database Migration Strategy

**Production:** Cloud Run Jobs
- Manual execution via GCP Console
- Command: `php artisan migrate`
- No `--force` flag (Cloud Run issue)
- Environment variables inherited from service

---

## 11. Monitoring & Observability

### 11.1 Fonnte WhatsApp Monitoring

**Architecture:**
```
Google Cloud Scheduler (*/5 * * * *)
       │
       ▼
POST /api/fonnte/check (Bearer token auth)
       │
       ▼
FonnteMonitoringService::checkStatus()
       │
       ├── Call Fonnte /device API
       ├── Cache result (5 min TTL)
       ├── If disconnected + throttle (30 min):
       │   └── Send email via FonnteDisconnectedMail
       └── Return JSON status
```

### 11.2 Application Logging

**Development:**
- Laravel log: `storage/logs/laravel.log`
- OTP logged to log file
- Mailpit UI: `http://localhost:8025`

**Production:**
- Cloud Run logs (stdout/stderr)
- Opcodes LogViewer (developer role only)
- Fonnte status logs in database

### 11.3 Health Checks

| Check | Endpoint | Method | Auth |
|-------|----------|--------|------|
| Fonnte Status | `/api/fonnte/check` | POST | Bearer token |
| Application | Cloud Run built-in | - | - |
| Database | Laravel `db:monitor` | - | - |

---

## 12. Frontend Architecture

### 12.1 Public-Facing (Blade + Alpine.js + TailwindCSS)

**Layout:** `resources/views/layouts/app.blade.php`
- TailwindCSS v4 with custom theme
- Alpine.js for interactivity
- PWA meta tags + service worker registration
- Color scheme: Sage Green (#87A878) + Beige (#F5F0E8)
- Font: Instrument Sans (from Bunny CDN)

**Pages:**
| Page | Route | Description |
|------|-------|-------------|
| Landing | `/` | Hero, services, blog preview |
| Blog Index | `/blog` | Paginated articles (9/page) |
| Blog Detail | `/blog/{slug}` | Single article with SEO meta |
| Service Pages | `/layanan/{slug}` | Individual service info |
| Login | `/login` | WhatsApp OTP form |
| Verify OTP | `/login/verify` | OTP verification |
| Self-Register | `/daftar` | Public booking without auth |
| Booking | `/book` | Authenticated booking with triage |
| Profile | `/profile` | Patient profile + history |

### 12.2 Admin Panel (Filament v5)

**Panel:** `/admin` (AdminPanelProvider)
- Theme: Sage Green primary, Inter font
- Custom CSS: rounded corners, pill-shaped sidebar items, pastel stat cards
- Plugins: FilamentShield, FilamentFullCalendar
- Asset: acuvoice.js (registered globally)

**Custom Components:**
- `filament.components.acuvoice-recorder` — Alpine.js voice recorder
- `filament.forms.components.branch-map` — Leaflet/OpenStreetMap interactive map

### 12.3 PWA Implementation

**Files:**
- `public/manifest.json` — App metadata
- `public/sw.js` — Service Worker (cache-first strategy)
- `public/pwa-192x192.png` — Icon 192px
- `public/pwa-512x512.png` — Icon 512px

**Capabilities:**
- Installable to home screen
- Offline caching (landing page, manifest, icons)
- Standalone display mode
- Theme color: #87A878 (Sage Green)

---

## 13. Code Quality Assessment

### 13.1 Strengths

| Aspect | Rating | Notes |
|--------|:------:|-------|
| **Architecture** | ⭐⭐⭐⭐ | Clean separation: Models, Services, Controllers, Filament Resources |
| **Code Organization** | ⭐⭐⭐⭐⭐ | Extracted Form/Table schemas (Filament v5 best practice) |
| **Security** | ⭐⭐⭐⭐ | OTP auth, RBAC, rate limiting, HTTPS, input validation |
| **AI Integration** | ⭐⭐⭐⭐⭐ | Guardrails, fallback models, prompt injection protection |
| **Documentation** | ⭐⭐⭐⭐⭐ | Comprehensive docs in `docs/` and `.agent/` |
| **Deployment** | ⭐⭐⭐⭐ | CI/CD, Docker multi-stage, Cloud Run Jobs |
| **Error Handling** | ⭐⭐⭐⭐ | Graceful fallbacks, logging, monitoring |
| **Multi-tenancy** | ⭐⭐⭐⭐ | Branch-scoped data, role-based access |

### 13.2 Technical Debt

| Item | Severity | Description |
|------|:--------:|-------------|
| Migration duplication | Low | 3 attempts for `add_coordinates_to_branches` (14, 15, 16) |
| Test coverage | High | Only default example tests, no custom tests |
| Custom PostgresConnector | Medium | Required for Supabase Supavisor compatibility |

### 13.3 Dependencies Health

| Package | Version | Status |
|---------|---------|--------|
| Laravel | 13 | Latest |
| Filament | v5 | Latest |
| PHP | 8.4 | Latest stable |
| PostgreSQL | 16 | Latest stable |
| Spatie Permission | Latest | Stable |
| Spatie MediaLibrary | Latest | Stable |
| FilamentFullCalendar | v4 beta | Beta (acceptable for competition) |

---

## 14. Strengths & Recommendations

### 14.1 Key Strengths

1. **AI-First Design** — AcuVoice dan Smart Routing adalah fitur yang sangat membedakan dari kompetitor. Guardrails dan fallback strategy menunjukkan pemahaman production-grade.

2. **Reverse Thinking Philosophy** — Keputusan desain yang berpusat pada masalah (passwordless login, dynamic forms, stateless storage) menunjukkan pemahaman domain yang kuat.

3. **Complete Documentation** — 7 dokumentasi teknis + blueprint + roadmap sangat membantu maintenance dan onboarding developer baru.

4. **Production-Ready Deployment** — CI/CD pipeline, Docker multi-stage, Cloud Run Jobs, entrypoint script, dan session affinity menunjukkan kesiapan production.

5. **Multi-Tenant Architecture** — Branch-scoped data dengan role-based access control yang granular.

### 14.2 Recommendations

#### High Priority
1. **Implement Automated Testing** — Tidak ada custom test. Tambahkan:
   - Feature tests untuk booking flow
   - Unit tests untuk Services (GeminiService, OtpService)
   - Dusk tests untuk critical user journeys

2. **Database Backup Strategy** — Supabase free tier auto-pause after 7 days. Pertimbangkan:
   - Automated backup cron
   - Migration ke Supabase Pro atau Cloud SQL

#### Medium Priority
3. **WhatsApp Gateway Stability** — Fonnte (unofficial) bisa disconnect. Pertimbangkan:
   - Upgrade ke Official WhatsApp Business API (Qontak/360dialog)
   - Implementasi retry queue untuk failed notifications

4. **Performance Optimization** — Tambahkan:
   - Database query caching untuk site_settings
   - Image CDN untuk media uploads
   - Lazy loading untuk dashboard widgets

5. **Error Tracking** — Integrasi error tracking service:
   - Sentry atau Bugsnag untuk production error monitoring

#### Low Priority
6. **Code Refactoring:**
   - Consolidate duplicate branch coordinate migrations
   - Add PHPStan/Larastan for static analysis
   - Implement Repository pattern for complex queries

7. **Feature Enhancements:**
   - Patient appointment reminders (WhatsApp)
   - Online payment integration
   - Multi-language support
   - Patient medical history timeline

---

## Appendix A: File Inventory

### Models (8)
| File | Path |
|------|------|
| User.php | `app/Models/User.php` |
| Branch.php | `app/Models/Branch.php` |
| Patient.php | `app/Models/Patient.php` |
| Service.php | `app/Models/Service.php` |
| Appointment.php | `app/Models/Appointment.php` |
| SoapNote.php | `app/Models/SoapNote.php` |
| Article.php | `app/Models/Article.php` |
| SiteSetting.php | `app/Models/SiteSetting.php` |

### Services (5)
| File | Path |
|------|------|
| GeminiService.php | `app/Services/GeminiService.php` |
| GeocodeService.php | `app/Services/GeocodeService.php` |
| OtpService.php | `app/Services/OtpService.php` |
| WhatsAppNotificationService.php | `app/Services/WhatsAppNotificationService.php` |
| FonnteMonitoringService.php | `app/Services/FonnteMonitoringService.php` |

### Filament Resources (8)
| File | Path |
|------|------|
| AppointmentResource.php | `app/Filament/Resources/Appointments/AppointmentResource.php` |
| SoapNoteResource.php | `app/Filament/Resources/SoapNotes/SoapNoteResource.php` |
| BranchResource.php | `app/Filament/Resources/Branches/BranchResource.php` |
| PatientResource.php | `app/Filament/Resources/Patients/PatientResource.php` |
| ServiceResource.php | `app/Filament/Resources/Services/ServiceResource.php` |
| ArticleResource.php | `app/Filament/Resources/Articles/ArticleResource.php` |
| UserResource.php | `app/Filament/Resources/Users/UserResource.php` |
| SiteSettingResource.php | `app/Filament/Resources/SiteSettings/SiteSettingResource.php` |

### Filament Pages (4)
| File | Path |
|------|------|
| Dashboard.php | `app/Filament/Pages/Dashboard.php` |
| Analytics.php | `app/Filament/Pages/Analytics.php` |
| HomecareRouting.php | `app/Filament/Pages/HomecareRouting.php` |
| DeveloperInfo.php | `app/Filament/Pages/DeveloperInfo.php` |

### Filament Widgets (3)
| File | Path |
|------|------|
| StatsOverviewWidget.php | `app/Filament/Widgets/StatsOverviewWidget.php` |
| AppointmentCalendarWidget.php | `app/Filament/Widgets/AppointmentCalendarWidget.php` |
| AcufaraInfoWidget.php | `app/Filament/Widgets/AcufaraInfoWidget.php` |

### Controllers (8)
| File | Path |
|------|------|
| Controller.php | `app/Http/Controllers/Controller.php` |
| HomeController.php | `app/Http/Controllers/HomeController.php` |
| BlogController.php | `app/Http/Controllers/BlogController.php` |
| BookingController.php | `app/Http/Controllers/BookingController.php` |
| SelfRegisterController.php | `app/Http/Controllers/SelfRegisterController.php` |
| ProfileController.php | `app/Http/Controllers/ProfileController.php` |
| WhatsAppAuthController.php | `app/Http/Controllers/Auth/WhatsAppAuthController.php` |
| FonnteCheckController.php | `app/Http/Controllers/Api/FonnteCheckController.php` |

### Migrations (20)
| File | Description |
|------|-------------|
| `0001_01_01_000000_create_users_table.php` | Users + OTP + sessions |
| `0001_01_01_000000_create_branches_table.php` | Branches |
| `0001_01_01_000001_create_cache_table.php` | Cache |
| `0001_01_01_000002_create_jobs_table.php` | Jobs |
| `2026_05_16_053603_create_permission_tables.php` | Spatie Permission |
| `2026_05_16_054011_create_media_table.php` | Spatie MediaLibrary |
| `2026_05_16_060000_create_site_settings_table.php` | Site Settings |
| `2026_05_16_060100_create_articles_table.php` | Articles |
| `2026_05_16_060200_create_patients_table.php` | Patients |
| `2026_05_16_060300_create_services_table.php` | Services |
| `2026_05_16_060400_create_appointments_table.php` | Appointments |
| `2026_05_16_060500_create_soap_notes_table.php` | SOAP Notes |
| `2026_05_20_112958_add_ai_triage_to_appointments_table.php` | AI Triage columns |
| `2026_05_21_062803_add_coordinates_to_branches_table.php` | Branch coords (1) |
| `2026_05_21_062955_add_coordinates_to_branches_table2.php` | Branch coords (2) |
| `2026_05_21_063015_add_coordinates_to_branches_table.php` | Branch coords (3) |
| `2026_06_18_093324_add_indexes_to_appointments_table.php` | Performance indexes |
| `2026_06_23_000001_add_whatsapp_number_to_branches_and_source_to_appointments_table.php` | WA + source |
| `2026_06_28_224245_add_medical_history_and_allergy_history_to_appointments_table.php` | Medical history |
| `2026_07_04_000001_create_fonnte_status_logs_table.php` | Fonnte logs |

### Seeders (6)
| File | Path |
|------|------|
| DatabaseSeeder.php | `database/seeders/DatabaseSeeder.php` |
| RolePermissionSeeder.php | `database/seeders/RolePermissionSeeder.php` |
| BranchSeeder.php | `database/seeders/BranchSeeder.php` |
| SuperAdminSeeder.php | `database/seeders/SuperAdminSeeder.php` |
| ServiceSeeder.php | `database/seeders/ServiceSeeder.php` |
| SiteSettingSeeder.php | `database/seeders/SiteSettingSeeder.php` |

---

## Appendix B: Environment Variables

```bash
# Application
APP_NAME="Acufara Klinik & Spa"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://acufara-ai-clinic-xxxx.asia-southeast2.run.app

# Database (Supabase)
DB_CONNECTION=pgsql
DB_HOST=db.XXXX.supabase.co
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.XXXX
DB_PASSWORD=...
DB_EMULATE_PREPARES=true

# Storage
FILESYSTEM_DISK=gcs
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
GOOGLE_CLOUD_PROJECT_ID=...
GOOGLE_CLOUD_STORAGE_BUCKET=acufara-media-bucket-2026

# AI
GEMINI_API_KEY=...
GEMINI_DEFAULT_MODEL=gemini-2.5-flash

# WhatsApp
FONNTE_TOKEN=...

# Monitoring
FONNTE_CHECK_ENABLED=true
FONNTE_CHECK_SECRET=...
MONITORING_EMAIL=...

# Server
SERVER_NAME=:8080
```

---

*Dokumen ini dibuat berdasarkan analisis menyeluruh terhadap seluruh codebase, dokumentasi, dan arsitektur sistem Acufara AI Clinic.*

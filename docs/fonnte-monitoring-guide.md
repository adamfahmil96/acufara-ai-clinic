# Panduan Monitoring & Notifikasi Fonntee WhatsApp Gateway
*Terakhir Diperbarui: Juli 2026*

> [!IMPORTANT]
> **Ruang lingkup dokumen ini kini terbatas pada OTP login pasien.**
> Sejak 27 Juli 2026, notifikasi booking ke klinik/admin **tidak lagi lewat Fonntee**
> melainkan lewat Telegram — lihat
> [docs/decisions/ADR-001-telegram-notifikasi-internal.md](decisions/ADR-001-telegram-notifikasi-internal.md).
> Monitoring di bawah tetap diperlukan selama OTP masih memakai Fonntee.

Dokumen ini menjelaskan analisis masalah, solusi, dan implementasi sistem monitoring Fonntee WhatsApp Gateway untuk Acufara AI Clinic.

---

## 1. Latar Belakang Masalah

### Issue yang Ditemukan
- Log Laravel menunjukkan status success: `[WA NOTIFY] Notifikasi berhasil dikirim ke 6285728528600`
- Kenyataannya WhatsApp tidak terkirim karena Fonntee status **disconnect**
- Fonntee menggunakan WhatsApp Web unofficial yang bisa terputus sewaktu-waktu

### Root Cause
Code di `WhatsAppNotificationService.php` hanya mengecek HTTP status (2xx), tetapi tidak mengecek response body dari Fonntee. Fonntee bisa mengembalikan HTTP 200 tetapi dengan status `false` di body-nya.

---

## 2. Analisis Alternatif Pengiriman WhatsApp

### Apakah Bisa Pakai `wa.me` Langsung?

**Tidak bisa.** Berikut penjelasannya:

| Metode | Fungsi | Bisa Kirim Otomatis? |
|--------|--------|---------------------|
| `wa.me` | URL scheme untuk buka chat WhatsApp di browser | ❌ Tidak |
| `api.whatsapp.com/send` | Sama dengan wa.me | ❌ Tidak |
| WhatsApp Gateway API | Server-to-server API | ✅ Ya |

**Cara kerja `wa.me`:**
```
User klik link → Browser terbuka → User harus klik "Send" manual
```

**Yang dibutuhkan (API server-to-server):**
```
Server kirim request → Pesan langsung terkirim otomatis
```

**Kesimpulan:** Untuk mengirim pesan WhatsApp otomatis dari backend Laravel, **wajib** menggunakan gateway (Fonntee, Wablas, WhatsApp Business API, dll).

---

## 3. Perbandingan Gateway WhatsApp

| Gateway | Tipe | Stabilitas | Harga | Catatan |
|---------|------|-----------|-------|---------|
| **Qontak** | Official API | ⭐⭐⭐⭐⭐ | Mahal | Resmi Meta, butuh verifikasi bisnis |
| **Zenziva** | Official API | ⭐⭐⭐⭐ | Menengah | Provider Indonesia, support baik |
| **Wablas** | Unofficial | ⭐⭐⭐ | Murah | Populer, tapi tetap bisa disconnect |
| **Damcorp** | Unofficial | ⭐⭐⭐ | Murah | Alternatif Fonntee |
| **360dialog** | Official API | ⭐⭐⭐⭐⭐ | Menengah | Partner resmi Meta |
| **Fonntee** | Unofficial | ⭐⭐⭐ | Murah | Saat ini digunakan |

### Rekomendasi

1. **Mau paling stabil?** → **Qontak** atau **360dialog** (official API, tidak akan disconnect)
2. **Budget terbatas?** → **Wablas** (lebih murah dari Fonntee, fitur mirip)
3. **Solusi tengah?** → **Zenziva** (official, harga lebih terjangkau)

**Catatan:** Gateway unofficial (Fonntee, Wablas, Damcorp) tetap ada risiko disconnect karena menggunakan WhatsApp Web. Hanya **Official WhatsApp Business API** yang dijamin stabil.

---

## 4. Arsitektur Sistem Monitoring

### Masalah dengan Pendekatan Langsung

Jika mengecek Fonntee di **setiap request**:
- ❌ **Performance lambat** - setiap page load harus tunggu response Fonntee
- ❌ **Email spam** - jika 100 user akses bersamaan saat Fonntee down = 100 email terkirim
- ❌ **Rate limit** - Fonntee bisa ban IP jika terlalu sering cek status

### Solusi: Scheduled Task + Cache + Email Notification

```
┌─────────────────────────────────────────────────────────┐
│                  Google Cloud Scheduler                  │
│            (Setiap 5 menit, HTTP POST)                  │
└─────────────────────────┬───────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Laravel Route: /api/fonnte/check            │
│                   (dengan secret token)                  │
└─────────────────────────┬───────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│           FonnteCheckController::check()                 │
│  1. Cek status Fonntee API                              │
│  2. Simpan hasil ke database cache (TTL 5 menit)        │
│  3. Jika disconnect → cek kapan terakhir kirim email    │
│  4. Jika > 30 menit → kirim email & update timestamp    │
└─────────────────────────────────────────────────────────┘
```

### Keuntungan Arsitektur Ini

- ✅ Tidak memperlambat website
- ✅ Tidak spam email (throttle 30 menit)
- ✅ Efisien dan reliable
- ✅ Cocok untuk serverless architecture (Cloud Run)

---

## 5. Analisis Infrastruktur

### Google Cloud Scheduler (2026)

**Status:** Masih tersedia dan aktif digunakan.

Fitur utama:
- Mendukung HTTP target (termasuk Cloud Run)
- Format cron fleksibel
- Ada free tier (3 job pertama gratis)
- Bisa dikonfigurasi via Console atau gcloud CLI

### Laravel Scheduler di Cloud Run

**Problem:** Cloud Run bersifat **serverless** - container bisa mati saat tidak ada request. Laravel Scheduler tidak bisa jalan terus-menerus.

**Solusi:** Gunakan Google Cloud Scheduler untuk memanggil HTTP endpoint secara berkala.

### Cache Implementation

**Status:** Bisa diterapkan dengan konfigurasi saat ini.

Konfigurasi cache dari `.env`:
```env
CACHE_STORE=database
```

Keuntungan database cache:
- Tidak perlu tambah service baru (Redis, dll)
- Konsisten dengan stack yang ada (PostgreSQL)
- Mudah di-maintain

---

## 6. Analisis Mail Service

### Apa itu Mailpit?

**Mailpit adalah local email catcher** - hanya untuk development, bukan production.

| Fitur | Mailpit | Email Real |
|-------|---------|------------|
| Kirim email | ✅ Tapi hanya lokal | ✅ Ke inbox real |
| Terima email | ❌ Tidak bisa | ✅ Bisa |
| Production | ❌ Tidak cocok | ✅ Cocok |
| Harga | Gratis | Bervariasi |

**Cara kerja Mailpit:**
```
Laravel kirim email → Mailpit terima → Tampil di UI (localhost:8025)
                    ↓
            Email TIDAK sampai ke inbox real
```

### Konfigurasi Saat Ini

Dari `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit  # ← Hanya untuk local Docker
MAIL_PORT=1025
```

**Masalah:** Di Cloud Run, `MAIL_HOST=mailpit` tidak ada, sehingga email tidak akan terkirim.

### Opsi Email Service untuk Cloud Run

| Service | Harga | Kemudahan | Keandalan |
|---------|-------|-----------|-----------|
| **Gmail SMTP** | Gratis (limit 500/hari) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Mailgun** | Gratis (1000/bulan) | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **SendGrid** | Gratis (100/bulan) | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Brevo (ex-Sendinblue)** | Gratis (300/hari) | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

### Rekomendasi: Gmail SMTP

Alasan:
- ✅ Gratis (cukup untuk notifikasi internal)
- ✅ Mudah setup (pakai App Password)
- ✅ Tidak perlu verifikasi bisnis
- ✅ Cocok untuk notifikasi ke diri sendiri

---

## 7. Rencana Implementasi

### File yang Perlu Dibuat/Diubah

| No | File | Keterangan |
|----|------|------------|
| 1 | `database/migrations/xxxx_create_fonnte_status_logs_table.php` | Tabel untuk log status Fonntee |
| 2 | `app/Http/Controllers/Api/FonnteCheckController.php` | Controller untuk handle request dari scheduler |
| 3 | `app/Services/FonnteMonitoringService.php` | Service untuk logic monitoring |
| 4 | `app/Mail/FonnteDisconnectedMail.php` | Mailable class untuk notifikasi |
| 5 | `resources/views/emails/fonnte-disconnected.blade.php` | Template email notifikasi |
| 6 | `routes/api.php` | Tambah route untuk endpoint monitoring |
| 7 | `config/services.php` | Tambah konfigurasi monitoring |
| 8 | `.env` | Tambah variabel konfigurasi |
| 9 | `docker/entrypoint.sh` | Script untuk clear cache saat container start |
| 10 | `Dockerfile` | Update untuk menggunakan entrypoint script |

### Auto-Clear Cache Saat Deploy

Untuk memastikan route dan config selalu fresh setiap deploy, digunakan entrypoint script yang akan menjalankan clear cache saat container start.

**File: `docker/entrypoint.sh`**
```bash
#!/bin/sh
set -e

echo "🚀 Starting Acufara AI Clinic..."

# Clear all caches to ensure fresh start
echo "🧹 Clearing caches..."
php artisan route:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# Run migrations if needed
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "📦 Running migrations..."
    php artisan migrate --force
fi

echo "✅ Ready!"

# Start FrankenPHP
exec "$@"
```

**Perubahan di Dockerfile:**
```dockerfile
# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
```

**Cara kerja:**
1. Setiap kali container start, entrypoint script akan dijalankan terlebih dahulu
2. Script akan clear semua cache (route, config, view, cache)
3. Jika variabel `RUN_MIGRATIONS=true`, migration akan dijalankan
4. Setelah selesai, FrankenPHP akan dimulai

**Keuntungan:**
- ✅ Route baru selalu terdeteksi setiap deploy
- ✅ Config cache selalu fresh
- ✅ Tidak perlu manual clear cache
- ✅ Migration bisa dijalankan otomatis jika diperlukan

### Variabel Environment yang Dibutuhkan

```env
# Fonnte Monitoring
FONNTE_CHECK_ENABLED=true
FONNTE_CHECK_SECRET=your-random-secret-token
FONNTE_CHECK_INTERVAL=5
FONNTE_EMAIL_THROTTLE=30

# Gmail SMTP (Production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Acufara Monitoring"

# Notification Target
MONITORING_EMAIL=adamfahmil020@gmail.com
```

### Generate FONNTE_CHECK_SECRET

`FONNTE_CHECK_SECRET` adalah token rahasia yang **kamu buat sendiri** untuk mengamankan endpoint `/api/fonnte/check`. Token ini digunakan oleh Google Cloud Scheduler untuk autentikasi.

**Fungsi:** Mencegah orang lain memanggil endpoint monitoring secara sembarangan.

**Cara generate menggunakan terminal:**

```bash
openssl rand -hex 32
```

Contoh output:
```
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2
```

**Cara generate menggunakan PHP (alternatif):**

```bash
php -r "echo bin2hex(random_bytes(32));"
```

**Cara generate menggunakan Laravel Tinker:**

```bash
php artisan tinker --execute="echo Str::random(64);"
```

> [!NOTE]
> Simpan token ini di tempat aman. Kamu akan membutuhkannya untuk:
> 1. Konfigurasi `.env` Cloud Run (variabel `FONNTE_CHECK_SECRET`)
> 2. Konfigurasi Google Cloud Scheduler (header `Authorization: Bearer {token}`)

### Setup Google Cloud Scheduler

1. Buka Google Cloud Console → Cloud Scheduler
2. Buat job baru:
   - **Name:** `fonnte-health-check`
   - **Frequency:** `*/5 * * * *` (setiap 5 menit)
   - **Target type:** HTTP
   - **URL:** `https://your-cloud-run-url/api/fonnte/check`
   - **HTTP method:** POST
   - **Headers:** `Authorization: Bearer {FONNTE_CHECK_SECRET}`
   - **Body:** `{}`

### Setup Gmail App Password

> [!IMPORTANT]
> **Prasyarat:** 2-Step Verification harus AKTIF terlebih dahulu. Tanpa ini, menu App Passwords tidak akan muncul.

**Langkah-langkah:**

1. **Aktifkan 2-Step Verification** (jika belum):
   - Buka: https://myaccount.google.com/security
   - Cari bagian "How you sign in to Google" → "2-Step Verification"
   - Klik "Get Started" dan ikuti instruksi

2. **Buka halaman App Passwords:**
   - Langsung kunjungi: **https://myaccount.google.com/apppasswords**
   - Atau via menu: Google Account → Security → 2-Step Verification → scroll ke bawah → "App passwords"

3. **Buat App Password baru:**
   - Klik "Select app" → Pilih **"Other (Custom name)"**
   - Ketik nama: `Acufara Monitoring`
   - Klik **"Generate"**

4. **Copy password:**
   - Akan muncul password 16 karakter (contoh: `abcd efgh ijkl mnop`)
   - Copy password tersebut ke variabel `MAIL_PASSWORD` di Cloud Run

> [!NOTE]
> Password hanya ditampilkan SEKALI. Pastikan langsung copy dan simpan di tempat aman.

> [!TIP]
> Jika menu "App passwords" tidak muncul, kemungkinan:
> - 2-Step Verification belum aktif
> - Menggunakan akun organisasi/kantor (dibatasi admin)
> - Menggunakan Security Key saja (tambah metode verifikasi lain)

---

## 8. Testing & Verifikasi

### Local Development

```bash
# Jalankan migration
docker compose exec app php artisan migrate

# Test endpoint monitoring
curl -X POST http://localhost:8000/api/fonnte/check \
  -H "Authorization: Bearer your-secret-token" \
  -H "Content-Type: application/json"

# Cek log
docker compose exec app tail -f storage/logs/laravel.log

# Cek email di Mailpit UI
open http://localhost:8025
```

### Production (Cloud Run)

```bash
# Jalankan migration via Cloud Run Jobs
# (lihat cloud_run_deployment_guide.md)

# Test endpoint
curl -X POST https://your-url/api/fonnte/check \
  -H "Authorization: Bearer your-secret-token"

# Cek Cloud Scheduler logs di GCP Console
```

---

## 9. Monitoring & Maintenance

### Checklist Harian

- [ ] Cek status Fonntee di Fonntee Dashboard
- [ ] Cek email notifikasi (jika ada)
- [ ] Cek Laravel logs untuk error

### Checklist Mingguan

- [ ] Review `fonnte_status_logs` table untuk pattern disconnect
- [ ] Cek Cloud Scheduler job status
- [ ] Test email notification

### Checklist Bulanan

- [ ] Review Gmail SMTP usage (limit 500/day)
- [ ] Evaluasi apakah perlu upgrade ke WhatsApp Business API
- [ ] Backup database (termasuk logs)

---

## 10. Troubleshooting

### Email Tidak Terkirim

1. Cek `MAIL_MAILER` di `.env` production
2. Cek `MAIL_USERNAME` dan `MAIL_PASSWORD` benar
3. Cek Gmail App Password belum expired
4. Cek Laravel logs untuk error SMTP

### Fonntee Tidak Bisa Dicek

1. Cek `FONNTE_TOKEN` masih valid
2. Cek Fonntee Dashboard untuk status device
3. Cek network dari Cloud Run ke Fonntee API

### Cloud Scheduler Tidak Jalan

1. Cek job status di GCP Console
2. Cek URL target benar
3. Cek `Authorization` header sesuai `FONNTE_CHECK_SECRET`
4. Cek Cloud Run logs untuk request masuk

---

## 11. Referensi

- [Google Cloud Scheduler Documentation](https://cloud.google.com/scheduler/docs)
- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Fonntee API Documentation](https://fonntee.com/docs)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)

---

## 12. Catatan Penting

> [!WARNING]
> **Gateway unofficial (Fonntee, Wablas, dll) tetap ada risiko disconnect.** Sistem monitoring ini hanya membantu mendeteksi dan notifikasi, bukan mencegah disconnect.

> [!TIP]
> **Jika membutuhkan stabilitas tinggi**, pertimbangkan untuk menggunakan Official WhatsApp Business API (Qontak, 360dialog, Zenziva).

> [!NOTE]
> **Sistem ini dirancang untuk notifikasi internal** (ke admin/developer). Jika membutuhkan notifikasi ke pasien, pertimbangkan gateway yang lebih stabil.

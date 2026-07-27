# Analisis Migrasi Notifikasi: Fonnte (WhatsApp) → Telegram Bot API

> **Analis:** Muhammad Adam Fahmil 'Ilmi
> **Tanggal:** 27 Juli 2026
> **Branch:** `feat/telegram-notification`
> **Status:** Analisis pra-implementasi (belum ada kode yang diubah)
> **Pemicu:** Nomor WhatsApp pengirim diklasifikasikan Meta sebagai spammer; akun diblokir sepekan lalu (sudah pulih)

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Temuan: Fonnte Punya 3 Peran, Bukan 1](#2-temuan-fonnte-punya-3-peran-bukan-1)
3. [Akar Masalah Ban Meta (Koreksi Asumsi)](#3-akar-masalah-ban-meta-koreksi-asumsi)
4. [Kesesuaian Telegram dengan Cloud Run](#4-kesesuaian-telegram-dengan-cloud-run)
5. [Perbandingan Kanal](#5-perbandingan-kanal)
6. [Desain Teknis yang Diusulkan](#6-desain-teknis-yang-diusulkan)
7. [Jebakan Implementasi (Gotchas)](#7-jebakan-implementasi-gotchas)
8. [Strategi untuk Jalur OTP](#8-strategi-untuk-jalur-otp)
9. [Rencana Implementasi Bertahap](#9-rencana-implementasi-bertahap)
10. [Inventaris File yang Terdampak](#10-inventaris-file-yang-terdampak)
11. [Risiko & Mitigasi](#11-risiko--mitigasi)

---

## 1. Ringkasan Eksekutif

**Tiga kesimpulan utama:**

1. **Telegram sangat cocok untuk Cloud Run** — bahkan secara arsitektural *lebih cocok* daripada Fonnte.
   Fonnte bergantung pada satu HP yang harus terus terhubung ke WhatsApp Web (stateful,
   di luar kendali kita). Telegram Bot API adalah HTTPS server-to-server murni (stateless),
   yang persis sesuai model serverless. **Tidak ada blocker teknis sama sekali.**

2. **Telegram hanya bisa menggantikan sebagian kecil dari peran Fonnte.**
   Fonnte dipakai untuk *dua hal berbeda* di codebase ini. Yang bisa pindah ke Telegram
   hanyalah notifikasi booking internal (ke nomor milik sendiri). Login OTP pasien
   **tidak bisa** pindah ke Telegram karena Telegram Bot API secara desain tidak bisa
   mengirim pesan ke nomor telepon sembarang — penerima harus lebih dulu menekan `/start`
   pada bot. Itu membunuh USP *passwordless zero-friction* untuk pasien baru.

3. **Migrasi notifikasi ke Telegram TIDAK menyelesaikan masalah ban Meta.**
   Traffic yang memicu klasifikasi spam adalah **OTP ke nomor asing**, bukan notifikasi
   booking ke nomor sendiri. Detail di [§3](#3-akar-masalah-ban-meta-koreksi-asumsi) —
   ada celah abuse di `requestOtp()` yang membuat sistem ini bisa dipakai orang lain
   sebagai relay spam WhatsApp gratis. Itu kandidat kuat penyebab ban dan **harus
   diperbaiki lebih dulu, terlepas dari keputusan Telegram**.

**Rekomendasi:** Kerjakan Telegram untuk notifikasi internal (murah, cepat, bebas ban,
gratis, menghapus seluruh subsistem monitoring Fonnte) — tapi jangan berhenti di situ,
karena akar masalah ban ada di jalur OTP.

---

## 2. Temuan: Fonnte Punya 3 Peran, Bukan 1

Hasil penelusuran seluruh pemanggil Fonnte di codebase:

| # | Peran | Lokasi Kode | Penerima | Sifat Traffic | Bisa → Telegram? |
|:-:|-------|-------------|----------|---------------|:----------------:|
| **A** | **OTP login pasien** | [OtpService.php:31-46](../app/Services/OtpService.php#L31-L46) | Nomor WA **pasien** (asing, tak dikenal, publik) | Otomatis, ke nomor baru, dipicu anonim | ❌ **Tidak** |
| **B** | **Notifikasi booking baru** | [WhatsAppNotificationService.php:104-131](../app/Services/WhatsAppNotificationService.php#L104-L131) | Nomor WA **cabang** atau `ACUFARA_WHATSAPP_NUMBER` (**milik sendiri**) | Internal, penerima tetap & sedikit | ✅ **Sangat cocok** |
| **C** | **Monitoring device Fonnte** | [FonnteMonitoringService.php](../app/Services/FonnteMonitoringService.php) + Cloud Scheduler + email | — (alert ke `MONITORING_EMAIL`) | Health check tiap 5 menit | ⚠️ Jadi mubazir **hanya jika** A ikut pindah |

**Pemanggil Peran B hanya satu titik:**
[SelfRegisterController.php:164](../app/Http/Controllers/SelfRegisterController.php#L164)
(dipanggil sinkron setelah `DB::commit()`, sudah dibungkus `try/catch`).

> [!NOTE]
> [BookingController.php:76](../app/Http/Controllers/BookingController.php#L76) (booking
> untuk pasien yang sudah login) **tidak** mengirim notifikasi sama sekali. Ini
> inkonsistensi yang sudah ada sebelumnya — booking dari `/book` tidak pernah
> memberi tahu klinik. Layak diperbaiki sekalian saat migrasi.

**Implikasi:** Peran B adalah *satu titik pemanggilan, satu format pesan, penerima
terbatas*. Ini scope migrasi paling kecil dan paling aman yang bisa dibayangkan —
efektif hanya menukar isi satu method transport.

---

## 3. Akar Masalah Ban Meta (Koreksi Asumsi)

Ini bagian terpenting dari analisis ini.

### 3.1 Traffic mana yang memicu spam classifier Meta?

Meta menandai nomor sebagai spammer berdasarkan pola: **pesan otomatis ke nomor yang
bukan kontak, dalam volume, tanpa balasan dari penerima** (rasio balasan rendah + laporan
"Block/Report" dari penerima).

Bandingkan dua jalur:

| | Peran A (OTP) | Peran B (Notifikasi booking) |
|---|---|---|
| Penerima | Nomor asing yang belum pernah chat | Nomor sendiri (adik ipar, cabang, HP sendiri) |
| Volume | 1× per **percobaan login** (bisa berkali-kali) | 1× per booking sukses |
| Rasio balasan | ~0% (tidak ada yang balas OTP) | Tidak relevan (chat sendiri) |
| Risiko dilaporkan | **Tinggi** — penerima tak dikenal bisa Block/Report | ~0% |
| **Kontribusi ke ban** | **Dominan** | **Nyaris nol** |

**Kesimpulan: memindahkan Peran B ke Telegram tidak menurunkan risiko ban secara
berarti.** Yang perlu diperbaiki adalah Peran A.

### 3.2 Celah abuse di jalur OTP (kandidat kuat penyebab ban)

Ditemukan di [WhatsAppAuthController.php:26-46](../app/Http/Controllers/Auth/WhatsAppAuthController.php#L26-L46):

```php
$request->validate(['whatsapp_number' => 'required|string|min:10|max:15']);
$waNumber = $request->input('whatsapp_number');
$this->otpService->generate($waNumber);   // ← langsung kirim, tanpa syarat apa pun
```

Masalahnya berlapis:

1. **Tidak ada pengecekan apakah nomor tersebut terdaftar.** Siapa pun bisa memasukkan
   nomor orang lain dan sistem akan mengirim WhatsApp ke nomor itu — dari nomor Acufara.
   Secara efektif ini **open relay WhatsApp gratis**.

2. **Rate limit hanya per-IP, bukan per-nomor.**
   [routes/web.php:37](../routes/web.php#L37) memakai `throttle:5,1` (default Laravel:
   key = user ID atau IP). Artinya:
   - 1 IP → 5 pesan WA/menit = **300 pesan/jam** ke nomor berbeda-beda.
   - Dengan IP rotasi / botnet → tanpa batas praktis.
   - Tidak ada cooldown per nomor, jadi 1 nomor bisa dibombardir berulang.

3. **Tidak ada validasi format nomor Indonesia.** `min:10|max:15` string bebas — komentar
   di kode pun mengakui `// Untuk sekarang simpan as is.` Nomor asal-asalan tetap dikirim
   ke Fonnte, menambah bounce/invalid-recipient — sinyal buruk lain bagi Meta.

> [!WARNING]
> Kombinasi ketiganya berarti: **satu pihak jahat (atau crawler/bot iseng) bisa membuat
> nomor Acufara mengirim ratusan WhatsApp tak diminta ke nomor asing.** Ini persis pola
> yang dideteksi Meta sebagai spam. Sangat mungkin ini penyebab blokir sepekan lalu,
> bukan volume booking yang wajar.

**Perbaikan ini murah, tidak bergantung pada keputusan Telegram, dan harus dikerjakan
lebih dulu.** Rinciannya di [Fase 0](#fase-0--tutup-celah-abuse-otp-prioritas-tertinggi).

---

## 4. Kesesuaian Telegram dengan Cloud Run

**Verdict: sangat cocok. Nol blocker. Secara arsitektur lebih baik dari Fonnte.**

### 4.1 Mengapa Fonnte sebenarnya *tidak* cocok dengan serverless

Fonnte adalah gateway *unofficial* yang menumpang WhatsApp Web. Konsekuensinya:
ada **satu HP fisik** yang harus terus online. Ini dependensi *stateful* di luar kendali
kita — dan itulah sebabnya seluruh subsistem di
[fonnte-monitoring-guide.md](fonnte-monitoring-guide.md) harus dibangun (Cloud Scheduler,
tabel log, email alert, throttle 30 menit). Semua kompleksitas itu ada **hanya untuk
memantau apakah HP masih nyambung.**

Telegram Bot API tidak punya konsep "device". Tidak ada yang bisa disconnect.

### 4.2 Checklist kesesuaian Cloud Run

| Aspek Cloud Run | Telegram Bot API | Catatan |
|---|---|---|
| **Egress HTTPS keluar** | ✅ `POST https://api.telegram.org/bot<TOKEN>/sendMessage` | Bentuknya **identik** dengan `Http::post()` ke Fonnte yang sudah ada. Tidak perlu VPC Connector, Cloud NAT, atau firewall khusus. |
| **Stateless container** | ✅ | Tidak ada session/device/QR yang harus dipertahankan antar request. |
| **Scale-to-zero** | ✅ | Untuk kirim-saja tidak butuh proses latar. (`--min-instances=1` sudah diset di [deploy.yml:51](../.github/workflows/deploy.yml#L51), jadi cold start pun bukan isu.) |
| **Multi-instance (`--max-instances=5`)** | ✅ | Tidak ada state bersama yang bisa bentrok antar instance. |
| **Request timeout** | ✅ | `sendMessage` biasanya <500ms dari `asia-southeast2`. |
| **Inbound (jika nanti perlu)** | ✅ **Webhook** | `setWebhook` → Telegram yang POST ke URL Cloud Run. Ini justru model *ideal* serverless. |
| **Secret management** | ✅ | `TELEGRAM_BOT_TOKEN` sebagai env var atau Secret Manager (lihat [cloud_run_deployment_guide.md](cloud_run_deployment_guide.md) Tahap 4). |
| **Cloud Scheduler** | ✅ Tidak diperlukan lagi untuk notifikasi | Job `fonnte-health-check` bisa dinonaktifkan jika Fonnte benar-benar pensiun. |
| **Biaya infra tambahan** | ✅ Rp0 | Telegram Bot API gratis tanpa kuota pesan. |

### 4.3 Dua jebakan Cloud Run yang harus dihindari

> [!WARNING]
> **1. JANGAN gunakan long polling (`getUpdates`).**
> Jika nanti butuh interaksi masuk (mis. perintah `/daftar` untuk mendapatkan `chat_id`),
> wajib pakai **webhook**, bukan polling. `getUpdates` membutuhkan proses yang hidup terus —
> mustahil di Cloud Run yang scale-to-zero, dan dengan `--max-instances=5` beberapa
> instance akan saling merebut update yang sama (pesan hilang / diproses dobel).

> [!WARNING]
> **2. `QUEUE_CONNECTION=database` tapi TIDAK ADA queue worker di Cloud Run.**
> [.env.example:44](../.env.example#L44) mengatur queue ke database, namun tidak ada
> proses `queue:work` di container ([entrypoint hanya menjalankan FrankenPHP](fonnte-monitoring-guide.md)).
> Artinya: jika nanti notifikasi dibungkus menjadi Job dan dipanggil `dispatch()` atau
> `Notification::queue()`, **pesan akan masuk tabel `jobs` dan tidak pernah terkirim —
> tanpa error apa pun.** Ini bug senyap yang sangat mahal untuk didiagnosis.
>
> Pilihan yang benar:
> - **Sinkron** (rekomendasi saat ini) — tetap kirim di dalam request seperti sekarang.
>   Telegram cukup cepat dan sudah dibungkus `try/catch`, jadi gagal kirim tidak
>   menggagalkan booking.
> - Jika kelak butuh async: **Cloud Tasks** (native serverless), atau Cloud Scheduler yang
>   memanggil endpoint `queue:work --stop-when-empty`. Bukan worker daemon.

### 4.4 Catatan non-teknis: akses Telegram di Indonesia

- Server-side (`Cloud Run → api.telegram.org`) tidak terpengaruh pemblokiran ISP lokal —
  yang memanggil API adalah GCP, bukan perangkat pengguna. (Pemblokiran Kominfo 2017
  sudah lama dibuka.)
- Yang perlu dipastikan: **admin/cabang bersedia memasang aplikasi Telegram.** Ini biaya
  *manusia*, bukan teknis, dan satu-satunya hambatan nyata migrasi ini. Untuk penerima
  internal berjumlah 2-3 orang, ini sekali setup saja.

---

## 5. Perbandingan Kanal

| Kriteria | Fonnte (sekarang) | **Telegram Bot API** | WhatsApp Cloud API (resmi Meta) |
|---|---|---|---|
| Status legal | Unofficial (melanggar ToS WA) | **Resmi & gratis** | Resmi |
| Risiko diblokir | **Tinggi** (sudah terjadi) | **Nol** | Nol (jika template disetujui) |
| Kirim ke nomor sembarang | ✅ Bisa | ❌ **Tidak** — penerima harus `/start` dulu | ✅ Bisa |
| Cocok untuk **OTP pasien** | ✅ (tapi berisiko) | ❌ **Tidak cocok** | ✅ **Paling tepat** |
| Cocok untuk **notifikasi internal** | ✅ | ✅ **Paling tepat** | Berlebihan (overkill) |
| Biaya | Langganan bulanan | **Rp0, tanpa kuota** | Per pesan (tarif *authentication* Indonesia — cek pricing terkini Meta) |
| Kesesuaian Cloud Run | ⚠️ Butuh subsistem monitoring device | ✅ **Native** | ✅ Native |
| Bisa disconnect | ✅ Ya (HP harus online) | ❌ Tidak ada device | ❌ Tidak ada device |
| Effort setup | Sudah jalan | **Rendah** (~2-4 jam) | **Tinggi** (verifikasi bisnis Meta, WABA, approval template) |
| Format pesan | Markdown WA | HTML/Markdown + **tombol inline** | Template terdaftar (kaku) |
| Batas panjang pesan | ~65.000 char | **4.096 char** ⚠️ | Sesuai template |

**Pembacaan tabel ini:** tidak ada satu kanal yang menang di semua baris. Yang tepat
adalah **memisahkan kanal per peran** — Telegram untuk internal, dan keputusan terpisah
untuk OTP.

---

## 6. Desain Teknis yang Diusulkan

### 6.1 Pemisahan tanggung jawab

Struktur sekarang mencampur *format pesan*, *routing penerima*, dan *transport* dalam satu
class. Usul pemisahan (tetap mengikuti gaya codebase: service polos + facade `Http`,
tanpa dependensi baru):

```
BookingNotificationService     ← format pesan + tentukan penerima  (dari WhatsAppNotificationService)
        │
        ├─► TelegramService::send(chatId, html)   ← transport utama
        └─► Mail (fallback)                       ← jika Telegram gagal
```

> **Mengapa tidak pakai `laravel-notification-channels/telegram` atau Laravel Notification?**
> Paket itu bagus dan memberi multi-channel + `Notification::fake()` gratis. Tapi codebase
> ini konsisten memakai *plain service + `Http` facade* di kelima service-nya dan belum
> punya satu pun class Notification. `TelegramService` tulis sendiri hanya ~60 baris,
> nol dependensi baru, dan konsisten dengan pola yang sudah ada. **Yang penting dihindari
> justru `Notification::queue()`** — lihat peringatan queue worker di [§4.3](#43-dua-jebakan-cloud-run-yang-harus-dihindari).

### 6.2 Routing penerima multi-cabang

Sekarang routing memakai `branches.whatsapp_number`
([WhatsAppNotificationService.php:35-42](../app/Services/WhatsAppNotificationService.php#L35-L42)).
Padanan Telegram-nya perlu kolom baru:

```php
// migration: add_telegram_chat_id_to_branches_table
$table->string('telegram_chat_id')->nullable()->after('whatsapp_number');
```

`whatsapp_number` **jangan dihapus** — masih berguna sebagai info kontak cabang dan jalur
darurat.

**Rekomendasi: gunakan Telegram *group* per cabang, bukan chat pribadi.** Alasannya:

- `chat_id` grup tetap stabil meski personel berganti (chat pribadi terikat 1 orang).
- Beberapa admin melihat notifikasi yang sama — tidak ada single point of failure manusia.
- Bisa dibalas/di-thread sebagai koordinasi ringan, gratis.
- Untuk mulai, cukup **satu grup "Acufara Ops"**; pecah per cabang saat memang perlu.

Env yang diperlukan:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_DEFAULT_CHAT_ID=      # untuk homecare + fallback jika cabang belum punya chat_id
```

Blok `config/services.php` baru (sejajar dengan blok `fonnte` yang sudah ada):

```php
'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),
],
```

### 6.3 Cara memperoleh `chat_id` (sekali setup)

1. Buat bot lewat [@BotFather](https://t.me/BotFather) → dapat token.
2. Buat grup, masukkan bot ke grup, kirim satu pesan apa pun di grup.
3. Ambil `chat_id`:
   ```bash
   curl -s "https://api.telegram.org/bot<TOKEN>/getUpdates" | jq '.result[].message.chat'
   ```
4. Simpan ke `TELEGRAM_DEFAULT_CHAT_ID` / kolom `branches.telegram_chat_id`.

> [!NOTE]
> `chat_id` grup **bernilai negatif**; supergroup berawalan `-100`
> (mis. `-1001234567890`). Simpan sebagai **string**, jangan integer — supaya tanda minus
> dan panjangnya aman.
>
> Langkah `getUpdates` di atas dijalankan **manual dari laptop, sekali saja** — itu bukan
> polling di aplikasi, jadi tidak melanggar aturan di [§4.3](#43-dua-jebakan-cloud-run-yang-harus-dihindari).

### 6.4 Peningkatan gratis yang didapat

Karena Telegram mendukung *inline keyboard*, link ke admin panel
([saat ini teks polos](../app/Services/WhatsAppNotificationService.php#L96-L97)) bisa
menjadi tombol:

```php
'reply_markup' => json_encode([
    'inline_keyboard' => [[
        ['text' => '🔗 Buka di Admin Panel', 'url' => $adminLink],
    ]],
]),
```

---

## 7. Jebakan Implementasi (Gotchas)

Delapan hal yang akan menggigit kalau tidak diantisipasi. Nomor 1-4 hampir pasti terjadi.

### 1. Batas 4.096 karakter — **akan terjadi**

Pesan notifikasi menggabungkan `complaint_summary`, `medical_history`, `allergy_history`,
dan `ai_recommendation` — semuanya **teks bebas dari pasien**, dan `ai_recommendation`
berasal dari Gemini (`maxOutputTokens: 2048` ≈ bisa >4.000 karakter sendirian).

WhatsApp memberi ruang ~65.000 karakter sehingga masalah ini tak pernah muncul. Telegram
akan menolak dengan `400 Bad Request: message is too long`.

**Mitigasi:** potong per-field saat memformat (mis. `Str::limit($v, 300)`), bukan memotong
pesan akhir — supaya struktur pesan tidak rusak di tengah. Detail lengkap tetap ada di
admin panel via tombol link.

### 2. Escaping MarkdownV2 — **akan terjadi**

Template sekarang memakai gaya WhatsApp `*BOOKING BARU*`. Di `parse_mode=MarkdownV2`,
Telegram mewajibkan escape untuk `_ * [ ] ( ) ~ ` > # + - = | { } . !` pada **seluruh**
teks. Alamat pasien seperti `Jl. Solo-Sragen No.5` langsung memicu
`400 Bad Request: can't parse entities`.

**Mitigasi (kuat):** pakai **`parse_mode=HTML`** dengan tag `<b>`, dan jalankan
`htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` pada setiap nilai yang diinterpolasi. Hanya 3
karakter yang perlu di-escape (`< > &`) alih-alih 18, dan itu ditangani oleh fungsi
bawaan PHP. Jauh lebih tahan terhadap input pasien yang tak terduga.

### 3. `403 Forbidden: bot can't initiate conversation with a user`

Muncul jika target adalah chat pribadi yang belum pernah menekan `/start`. Ini konsekuensi
langsung dari batasan Telegram di [§5](#5-perbandingan-kanal).

**Mitigasi:** pakai grup (§6.2), dan tangkap error ini secara eksplisit dengan pesan log
yang jelas — bukan `Log::error` generik, agar penyebabnya langsung terbaca.

### 4. Periksa `ok` di response body, bukan cuma HTTP status — **jangan ulangi bug lama**

Ini persis akar masalah yang sudah didokumentasikan di
[fonnte-monitoring-guide.md §1](fonnte-monitoring-guide.md): log bilang sukses padahal
pesan tidak terkirim.

Kode sekarang di
[WhatsAppNotificationService.php:123](../app/Services/WhatsAppNotificationService.php#L123)
masih hanya memeriksa `$response->successful()` — **bug yang sama akan terbawa** jika
disalin apa adanya.

**Mitigasi:** Telegram membalas `{"ok":false,"description":"...","error_code":400}`.
Verifikasi `$response->json('ok') === true`, dan log `description` saat gagal.
Bedanya dengan Fonnte: Telegram konsisten mengirim HTTP 4xx bersama `ok:false`, jadi
verifikasi ini murah dan deterministik.

### 5. Rate limit Telegram

Kurang lebih: ~30 pesan/detik global, ~1 pesan/detik per chat, ~20 pesan/menit per grup.
Untuk volume klinik ini **tidak akan tersentuh**. Dicatat untuk kelengkapan saja.

### 6. Token bocor melalui URL

Token Telegram ada di **path URL** (`/bot<TOKEN>/sendMessage`), bukan header. Jangan
pernah mem-log URL penuh, dan jangan sertakan response mentah yang memuat URL.
Simpan di Secret Manager, bukan plaintext env var, untuk produksi.

### 7. Privacy mode bot di grup

Default privacy mode hanya memengaruhi kemampuan bot **membaca** pesan orang lain di grup.
**Mengirim tetap bebas.** Jadi tidak perlu mengubah apa pun untuk skenario kirim-saja.

### 8. `chat_id` sebagai string, bukan integer

Sudah disinggung di §6.3 — ulangi di sini karena ini penyebab bug yang sering: cast ke
integer bisa merusak nilai `-100...` yang panjang.

---

## 8. Strategi untuk Jalur OTP

Karena Telegram tidak bisa menangani OTP pasien, ini keputusan terpisah. Diurutkan dari
rasio manfaat/biaya terbaik:

### Opsi 1 — Perkeras jalur OTP yang ada (WAJIB, kerjakan sekarang)

Tanpa mengganti gateway sama sekali, tutup celah di [§3.2](#32-celah-abuse-di-jalur-otp-kandidat-kuat-penyebab-ban):

- **Rate limit per nomor**, bukan hanya per-IP: `throttle` dengan key nomor telepon,
  mis. maksimum 3 permintaan/jam/nomor + cooldown 60 detik antar permintaan.
- **Normalisasi & validasi nomor Indonesia** (`08…` → `62…`, regex prefiks operator,
  tolak yang tidak valid **sebelum** memanggil Fonnte).
- **CAPTCHA / honeypot** di form login untuk menahan bot.
- Pertimbangkan **hanya kirim OTP ke nomor yang sudah terdaftar** untuk alur login,
  dan pakai alur pendaftaran terpisah untuk nomor baru. Ini mengurangi drastis pesan
  ke nomor asing — sinyal utama bagi spam classifier Meta.

Ini yang paling mungkin menghentikan ban berulang, dan biayanya paling murah.

### Opsi 2 — WhatsApp Cloud API resmi (Meta) untuk OTP

Satu-satunya cara **menghilangkan** risiko ban secara struktural sambil mempertahankan
UX WhatsApp yang sudah menjadi USP.

- Perlu: verifikasi Meta Business, WhatsApp Business Account, nomor pengirim, dan
  **approval template kategori `authentication`**.
- Berbayar per pesan (tarif *authentication* Indonesia — **verifikasi pricing terkini di
  dokumentasi Meta**, jangan asumsikan angka lama).
- Effort setup tinggi (hitungan hari-minggu, termasuk menunggu approval), tapi sekali
  jalan langsung stabil.

### Opsi 3 — Email OTP sebagai kanal alternatif

Kolom `users.email` sudah ada dan nullable, serta Gmail SMTP sudah terkonfigurasi untuk
monitoring — jadi biaya teknisnya nyaris nol. Kelemahannya UX: demografi target
(pasien klinik homecare) jauh lebih responsif ke WhatsApp daripada email. Cocok sebagai
*pilihan kedua*, bukan pengganti.

### Opsi 4 — Telegram OTP opsional untuk pasien yang mau

Pasien yang bersedia menautkan Telegram sekali (deep link `t.me/<bot>?start=<token>`) bisa
menerima OTP via Telegram di login berikutnya. Menarik untuk pasien berulang, **tapi
tidak bisa menjadi jalur utama** karena login pertama tetap butuh kanal lain.

**Rekomendasi:** Opsi 1 sekarang (wajib, murah) → evaluasi Opsi 2 sebagai target
jangka menengah. Opsi 3 sebagai fallback jika Fonnte diblokir lagi mendadak.

---

## 9. Rencana Implementasi Bertahap

### Fase 0 — Tutup celah abuse OTP *(prioritas tertinggi)*

**Tidak bergantung pada Telegram sama sekali.** Implementasi Opsi 1 di
[§8](#opsi-1--perkeras-jalur-otp-yang-ada-wajib-kerjakan-sekarang).
Estimasi: 2-3 jam. Dampak: langsung menurunkan risiko ban berulang.

### Fase 1 — Telegram untuk notifikasi booking ✅ SELESAI (27 Juli 2026)

1. Buat bot via BotFather, buat grup "Acufara Ops", ambil `chat_id`.
2. Migration `add_telegram_chat_id_to_branches_table` + blok `config/services.php`.
3. `TelegramService` (transport) — `parse_mode=HTML`, verifikasi `ok`, tangani 403.
4. `WhatsAppNotificationService` → `BookingNotificationService`: format HTML +
   truncation per-field + tombol inline.
5. Isi field Telegram di BranchResource (Filament) agar bisa dikelola dari panel.
6. Perbaiki inkonsistensi: kirim notifikasi juga dari `BookingController` (§2).
7. Tambah `TELEGRAM_BOT_TOKEN` + `TELEGRAM_DEFAULT_CHAT_ID` ke env Cloud Run.

Terimplementasi dengan tambahan di luar rencana awal: **email fallback**
(`BookingNotificationFallbackMail`) yang otomatis jalan bila Telegram gagal, sehingga
booking tidak pernah hilang tanpa jejak.

Diverifikasi lewat `Http::fake()` + `Mail::fake()` untuk 7 skenario (20 pengecekan):
token kosong, kirim sukses, routing homecare, cabang tanpa `chat_id`, Telegram 403,
HTTP 200 dengan `ok:false`, dan pesan melebihi 4.096 karakter — seluruhnya lulus.

### Fase 2 — Alihkan alert operasional ke Telegram

Ganti/lengkapi `FonnteDisconnectedMail` dengan alert Telegram (email tetap sebagai
fallback). Keuntungan: alert masuk instan ke HP, tidak terkubur di inbox, dan lepas dari
limit Gmail 500/hari.

### Fase 3 — Keputusan jalur OTP

Evaluasi Opsi 2 (Cloud API) vs tetap di Fonnte-yang-sudah-diperkeras. Butuh data dari
Fase 0: berapa volume OTP riil per bulan setelah abuse ditutup? Angka itu menentukan
apakah biaya per pesan Cloud API masuk akal.

### Fase 4 — Pensiunkan subsistem monitoring Fonnte *(hanya jika OTP ikut pindah)*

Selama OTP masih lewat Fonnte, **`FonnteMonitoringService` dan Cloud Scheduler job tetap
diperlukan** — jangan dihapus di Fase 1. Setelah Fonnte benar-benar tak dipakai:
`FonnteMonitoringService`, `FonnteCheckController`, `FonnteDisconnectedMail`, route
`/api/fonnte/check`, dan job Cloud Scheduler `fonnte-health-check` semuanya bisa dihapus.

> [!NOTE]
> **Utang teknis yang ditemukan sekalian:** tabel `fonnte_status_logs` dibuat oleh
> [migration 2026_07_04_000001](../database/migrations/2026_07_04_000001_create_fonnte_status_logs_table.php)
> tetapi **tidak pernah ditulis maupun dibaca oleh kode mana pun** — tidak ada model
> `FonnteStatusLog`, dan `FonnteMonitoringService` hanya memakai `Cache`. Padahal
> [checklist mingguan di fonnte-monitoring-guide.md](fonnte-monitoring-guide.md) menyuruh
> "review tabel `fonnte_status_logs` untuk pattern disconnect" — instruksi yang mustahil
> dijalankan karena tabelnya selalu kosong. Perlu diputuskan: implementasikan logging-nya,
> atau hapus tabel + koreksi dokumentasinya.

---

## 10. Inventaris File yang Terdampak

### Fase 0 (hardening OTP)

| File | Perubahan |
|------|-----------|
| [routes/web.php](../routes/web.php#L35-L37) | Rate limiter per-nomor untuk `login.otp` |
| [app/Http/Controllers/Auth/WhatsAppAuthController.php](../app/Http/Controllers/Auth/WhatsAppAuthController.php) | Normalisasi + validasi nomor Indonesia, cooldown per nomor |
| `app/Providers/AppServiceProvider.php` | Definisi `RateLimiter` kustom |

### Fase 1 (Telegram)

| File | Aksi |
|------|------|
| `app/Services/TelegramService.php` | **Baru** — transport |
| `app/Services/BookingNotificationService.php` | **Baru** (dari `WhatsAppNotificationService`) — format & routing |
| `app/Services/WhatsAppNotificationService.php` | Dihapus setelah migrasi |
| `database/migrations/*_add_telegram_chat_id_to_branches_table.php` | **Baru** |
| [config/services.php](../config/services.php) | Tambah blok `telegram` |
| [.env.example](../.env.example) | Tambah `TELEGRAM_BOT_TOKEN`, `TELEGRAM_DEFAULT_CHAT_ID` |
| [app/Http/Controllers/SelfRegisterController.php:164](../app/Http/Controllers/SelfRegisterController.php#L164) | Ganti service yang di-inject |
| [app/Http/Controllers/BookingController.php](../app/Http/Controllers/BookingController.php#L76) | Tambahkan notifikasi (sebelumnya tidak ada) |
| `app/Filament/Resources/Branches/Schemas/BranchForm.php` | Field `telegram_chat_id` |
| `app/Models/Branch.php` | `$fillable` |
| Cloud Run env vars | 2 variabel baru (via Secret Manager) |

**Yang TIDAK berubah:** `OtpService`, `FonnteMonitoringService`, `FonnteCheckController`,
route `/api/fonnte/check`, Cloud Scheduler job — semua tetap sampai Fase 3/4 diputuskan.

---

## 11. Risiko & Mitigasi

| Risiko | Dampak | Kemungkinan | Mitigasi |
|--------|:------:|:-----------:|----------|
| Admin/cabang tidak mau pasang Telegram | Tinggi | **Sedang** | Konfirmasi kesediaan **sebelum** menulis kode. Ini satu-satunya blocker nyata migrasi. |
| Pesan >4.096 char ditolak | Sedang | **Tinggi** | Truncation per-field (§7.1) |
| Error escaping karena input pasien | Sedang | **Tinggi** | `parse_mode=HTML` + `htmlspecialchars` (§7.2) |
| Kegagalan kirim tidak terdeteksi | Tinggi | **Sedang** | Verifikasi `ok` di response body (§7.4) |
| Ban Meta terulang | Tinggi | **Tinggi jika Fase 0 dilewat** | Fase 0 wajib dikerjakan lebih dulu |
| Token bot bocor | Tinggi | Rendah | Secret Manager; jangan log URL (§7.6) |
| Notifikasi ke queue yang tak punya worker | Tinggi | Rendah | Tetap sinkron; jangan `dispatch()`/`queue()` (§4.3) |
| Telegram tidak bisa diandalkan untuk OTP | — | Pasti | Sudah diperhitungkan: OTP tidak dimigrasikan (§8) |

---

## Lampiran: Ringkasan Satu Halaman

**Pertanyaan:** apakah notifikasi Telegram cocok untuk Acufara di Cloud Run?

**Jawaban:** Ya, sangat — untuk **notifikasi internal**. Nol blocker teknis, gratis,
menghapus seluruh kebutuhan monitoring device, dan secara arsitektur lebih pas dengan
serverless dibanding Fonnte.

**Tapi:** Telegram tidak bisa menggantikan **OTP login pasien** (Bot API tidak bisa
mengirim ke nomor asing tanpa `/start` lebih dulu), dan justru **jalur OTP itulah**
sumber risiko ban Meta — bukan notifikasi booking. Migrasi notifikasi saja tidak akan
mencegah blokir berikutnya.

**Urutan yang benar:** perkeras jalur OTP dulu (Fase 0) → Telegram untuk notifikasi
(Fase 1) → putuskan strategi OTP jangka panjang (Fase 3).

---

*Keputusan formal dicatat di [docs/decisions/ADR-001-telegram-notifikasi-internal.md](decisions/ADR-001-telegram-notifikasi-internal.md).*

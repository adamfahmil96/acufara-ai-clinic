# ADR-001: Telegram Bot API untuk notifikasi internal, WhatsApp tetap untuk OTP pasien

## Status

**Accepted** — diimplementasikan 27 Juli 2026 di branch `feat/telegram-notification`.

Pemilik menegaskan bahwa notifikasi booking ikut menyumbang risiko ban (nomor cabang
belum tentu tersimpan sebagai kontak, pesannya panjang dan memuat URL), dan bahwa fitur
OTP pada akhirnya akan dihilangkan sama sekali — tetapi untuk sekarang dibiarkan apa
adanya. Ruang lingkup keputusan ini karena itu dibatasi pada Peran B saja.

## Date

2026-07-27

## Context

Notifikasi keluar Acufara AI Clinic saat ini seluruhnya lewat Fonnte, gateway WhatsApp
*unofficial* yang menumpang WhatsApp Web. Ini bermasalah:

- **Nomor pengirim diklasifikasikan Meta sebagai spammer.** Akun WhatsApp pemilik diblokir
  pada minggu ketiga Juli 2026 (sudah pulih), dan risiko itu akan terulang.
- **Bergantung pada perangkat stateful.** Fonnte butuh satu HP yang terus terhubung.
  Ini tidak selaras dengan Cloud Run yang stateless & scale-to-zero, dan memaksa
  dibangunnya subsistem monitoring (Cloud Scheduler, email alert, throttle) yang ada
  *semata-mata* untuk memantau apakah HP masih nyambung.
- **Kegagalan senyap.** Fonnte bisa membalas HTTP 200 dengan `status:false` di body;
  kode lama hanya memeriksa HTTP status sehingga log melaporkan sukses padahal pesan
  tidak terkirim.

Penelusuran codebase menemukan Fonnte melayani **tiga peran berbeda** dengan karakteristik
traffic yang sangat berlainan:

| Peran | Penerima | Karakter |
|---|---|---|
| A. OTP login pasien (`OtpService`) | Nomor WA pasien — **asing, publik** | Otomatis ke nomor baru, dipicu anonim |
| B. Notifikasi booking (`WhatsAppNotificationService`) | Nomor WA cabang / Acufara — **milik sendiri** | Internal, penerima tetap & sedikit |
| C. Monitoring device (`FonnteMonitoringService`) | — (alert email) | Health check per 5 menit |

Pembedaan ini menentukan keputusan, karena **batasan mendasar Telegram Bot API**: bot
tidak bisa mengirim pesan ke nomor telepon sembarang — penerima harus lebih dulu menekan
`/start` pada bot atau berada di grup bersama bot.

Ditemukan pula bahwa risiko ban **tidak** berasal dari Peran B (yang mengirim ke nomor
sendiri, rasio laporan ~0%) melainkan dari Peran A. Terlebih lagi
`WhatsAppAuthController::requestOtp()` mengirim OTP ke nomor apa pun yang lolos validasi
`min:10|max:15` tanpa memeriksa apakah nomor itu terdaftar, dengan rate limit hanya
per-IP (`throttle:5,1`) dan tanpa cooldown per nomor — efektif menjadikan sistem ini
relay WhatsApp terbuka.

## Decision

**Pisahkan kanal berdasarkan peran, jangan mengganti Fonnte secara menyeluruh:**

1. **Peran B (notifikasi booking internal) → Telegram Bot API.**
   Kirim via `POST https://api.telegram.org/bot<TOKEN>/sendMessage` dengan
   `parse_mode=HTML`. Penerima adalah **grup Telegram per cabang** (`chat_id` disimpan di
   kolom baru `branches.telegram_chat_id`, dengan `TELEGRAM_DEFAULT_CHAT_ID` untuk
   homecare & fallback). Panggilan tetap **sinkron** di dalam request.

2. **Peran A (OTP pasien) tetap di WhatsApp.** Tidak dimigrasikan ke Telegram.
   Sebagai gantinya, jalur ini **diperkeras lebih dulu** (rate limit per-nomor,
   normalisasi & validasi nomor Indonesia, cooldown, batasi pengiriman ke nomor asing).
   Keputusan jangka menengah antara WhatsApp Cloud API resmi vs Fonnte-yang-diperkeras
   ditunda sampai ada data volume OTP riil pasca-hardening.

3. **Peran C (monitoring Fonnte) dipertahankan** selama Peran A masih lewat Fonnte.

## Alternatives Considered

### Migrasi total ke Telegram (termasuk OTP)

- Pros: satu kanal, gratis, nol risiko ban.
- Cons: mustahil untuk pasien baru — Bot API tidak bisa menjangkau nomor telepon yang
  belum pernah berinteraksi dengan bot. Menautkan Telegram lebih dulu membunuh USP
  *passwordless WhatsApp login* yang menjadi nilai jual utama produk.
- **Ditolak:** menghancurkan alur akuisisi pasien.

### Tetap di Fonnte, hanya perbaiki monitoring

- Pros: nol perubahan kode notifikasi.
- Cons: tidak menghilangkan risiko ban maupun ketergantungan perangkat stateful.
  Monitoring hanya mendeteksi, tidak mencegah — sudah dinyatakan eksplisit di
  `docs/fonnte-monitoring-guide.md §12`.
- **Ditolak:** membiarkan akar masalah.

### Pindah ke gateway unofficial lain (Wablas, Damcorp)

- Pros: perubahan kode minimal (bentuk API mirip), lebih murah.
- Cons: tetap unofficial, tetap menumpang WhatsApp Web, tetap bisa disconnect dan
  tetap berisiko ban. Hanya memindahkan masalah.
- **Ditolak:** tidak mengubah apa pun secara struktural.

### WhatsApp Cloud API resmi untuk *semua* peran

- Pros: resmi, nol risiko ban, bisa kirim ke nomor sembarang (cocok untuk OTP).
- Cons: verifikasi Meta Business + WABA + approval template (hitungan hari sampai
  minggu); berbayar per pesan; berlebihan untuk notifikasi internal yang bisa gratis.
- **Ditunda, bukan ditolak:** ini kandidat terbaik untuk **Peran A** dan dievaluasi di
  Fase 3. Untuk Peran B, Telegram gratis sudah lebih dari cukup.

### Laravel Notification + `laravel-notification-channels/telegram`

- Pros: multi-channel `via()` bawaan, `Notification::fake()` untuk testing.
- Cons: dependensi baru dan pola yang tidak dipakai di mana pun di codebase ini (kelima
  service memakai *plain service + facade `Http`*, belum ada satu class Notification pun).
  `TelegramService` tulis sendiri hanya ~60 baris.
- **Ditolak untuk sekarang:** konsistensi gaya lebih bernilai daripada abstraksi yang
  belum diperlukan. Terutama karena API `queue()` paket tersebut adalah jebakan di
  environment ini (lihat Consequences).

### `Mail::raw()` untuk email fallback

- Pros: tanpa class Mailable maupun view — isinya sudah teks siap kirim.
- Cons: **`MailFake::raw()` adalah no-op**, sehingga jalur fallback tidak akan pernah bisa
  diverifikasi lewat `Mail::fake()`. Buruk untuk kode yang justru hanya berjalan ketika
  sesuatu sudah rusak: kalau ia diam-diam rusak, tidak ada yang tahu.
- **Ditolak:** dipakai `BookingNotificationFallbackMail` (Mailable + view teks), sejalan
  dengan `FonnteDisconnectedMail` yang sudah ada dan bisa diassert.

## Consequences

### Positif

- Notifikasi internal menjadi **gratis, resmi, dan tidak bisa diblokir Meta**.
- Hilangnya ketergantungan pada perangkat yang harus online untuk jalur notifikasi.
- Mendapat *inline keyboard* gratis — link admin panel jadi tombol, bukan teks polos.
- Grup Telegram membuat notifikasi terlihat oleh beberapa admin sekaligus dan `chat_id`
  tetap stabil meski personel berganti.
- Jika Peran A kelak pindah dari Fonnte, seluruh `FonnteMonitoringService`,
  `FonnteCheckController`, `FonnteDisconnectedMail`, route `/api/fonnte/check`, dan job
  Cloud Scheduler bisa dihapus.

### Negatif / biaya

- **Admin dan cabang wajib memasang Telegram.** Ini satu-satunya blocker nyata migrasi
  dan sifatnya organisasional, bukan teknis. Harus dikonfirmasi sebelum implementasi.
- Dua kanal keluar yang harus dipelihara selama masa transisi (Telegram + Fonnte).
- Kolom `branches.telegram_chat_id` perlu diisi manual sekali per cabang.

### Batasan teknis yang mengikat implementasi

- **Batas pesan 4.096 karakter** (WhatsApp ~65.000). Karena pesan memuat teks bebas pasien
  dan output Gemini, **truncation per-field wajib** — bukan memotong pesan akhir.
- **Wajib `parse_mode=HTML` + `htmlspecialchars()`, bukan MarkdownV2.** MarkdownV2
  mewajibkan escape 18 karakter termasuk `.` dan `-`; alamat seperti
  `Jl. Solo-Sragen No.5` langsung memicu `400 can't parse entities`.
- **Wajib memeriksa `ok` di response body**, bukan hanya `$response->successful()` —
  ini pengulangan bug Fonnte yang sudah terdokumentasi dan akan terbawa jika kode
  lama disalin apa adanya.
- **JANGAN gunakan queue.** `QUEUE_CONNECTION=database` tetapi tidak ada proses
  `queue:work` di container Cloud Run. `dispatch()` atau `Notification::queue()` akan
  memasukkan pesan ke tabel `jobs` dan **tidak pernah mengirimnya, tanpa error apa pun**.
  Jika kelak butuh async: Cloud Tasks, bukan worker daemon.
- **JANGAN gunakan long polling (`getUpdates`) di aplikasi.** Butuh proses hidup terus —
  mustahil di Cloud Run scale-to-zero, dan dengan `--max-instances=5` update akan
  diperebutkan antar instance. Jika perlu inbound, pakai webhook (`setWebhook`).
- `chat_id` grup bernilai negatif (supergroup berawalan `-100`) — **simpan sebagai string.**

### Yang secara eksplisit TIDAK berubah

`OtpService`, `FonnteMonitoringService`, `FonnteCheckController`, route
`/api/fonnte/check`, dan job Cloud Scheduler `fonnte-health-check` tetap ada.
Menghapusnya bersamaan dengan Fase 1 akan mematikan monitoring untuk jalur OTP yang
masih aktif memakai Fonnte.

### Prasyarat yang mendahului keputusan ini

Hardening jalur OTP (Fase 0) **tidak bergantung pada ADR ini dan harus dikerjakan lebih
dulu.** Tanpa itu, ban Meta akan terulang meskipun seluruh notifikasi sudah pindah ke
Telegram, karena traffic yang memicu klasifikasi spam ada di jalur OTP.

## References

- Analisis lengkap: [docs/telegram-notification-analysis.md](../telegram-notification-analysis.md)
- Konteks masalah Fonnte: [docs/fonnte-monitoring-guide.md](../fonnte-monitoring-guide.md)
- Kendala environment: [docs/cloud_run_deployment_guide.md](../cloud_run_deployment_guide.md)

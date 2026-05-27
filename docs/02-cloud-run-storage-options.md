# 🏗️ Arsitektur Upload File di Google Cloud Run
*(Livewire Temporary Uploads & Spatie MediaLibrary)*

Aplikasi Acufara AI Clinic dibangun menggunakan ekosistem **Laravel Livewire** (lewat Filament) dan di-deploy ke **Google Cloud Run**.

## Akar Permasalahan (The Challenge)
Google Cloud Run beroperasi dengan sifat **Stateless** dan **Ephemeral**.
Semua penulisan file lokal (disk `local`) sebenarnya disimpan ke dalam **RAM (In-Memory)** milik container.

Saat user mengunggah file (contoh: Gambar Artikel) via komponen Filament, **Livewire memecah file dan mengunggahnya sebagai file sementara (temporary chunk) ke server.**
- Jika file sementara ini disimpan ke **GCS**, sering muncul error *MIME Type Validation* (`The field must be an image`) akibat file disimpan sebagai `application/octet-stream`.
- Jika file sementara disimpan ke **Lokal**, akan muncul risiko **File Not Found (Error 500)** apabila Cloud Run menduplikasi instance saat trafik tinggi (Server A menyimpan file sementara, tapi eksekusi simpan akhir diarahkan ke Server B).

---

## 🛠️ Opsi Solusi Deployment

Berikut adalah dua jalur arsitektur untuk mengatasi tantangan tersebut:

### Opsi 1: Disk Lokal + Session Affinity (🌟 Paling Direkomendasikan)
Opsi ini sangat ideal untuk aplikasi skala menengah atau panel admin internal seperti Acufara Clinic.

**Konsep:**
Kita tetap menyimpan file sementara Livewire di RAM lokal (`LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`), lalu menginstruksikan Cloud Run (Load Balancer) untuk selalu mengarahkan pengguna ke server yang sama selama proses tersebut berlangsung.

**Pengaturan:**
1. Di `.env` aplikasi:
   ```env
   FILESYSTEM_DISK=gcs
   LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
   ```
2. Saat deployment **Cloud Run**, aktifkan opsi **Session Affinity** (Sticky Sessions) di tab pengaturan.
   - Buka Google Cloud Console > Cloud Run > Edit Service.
   - Ke Tab **Networking**.
   - Centang kotak **Session Affinity**.

**Keuntungan:**
- Sangat mudah dikonfigurasi (Hanya 1 klik di Cloud Console).
- Tidak pusing dengan konfigurasi CORS.
- Validasi file (gambar, PDF, ukuran) dari Laravel berfungsi 100% sempurna karena file mampir di backend.

---

### Opsi 2: Direct Upload via Presigned URL (Tingkat Lanjut)
Opsi ini cocok jika aplikasi kelak menerima unggahan *file* video berukuran masif (GBs) dari pengguna eksternal dan Anda tidak ingin menyita RAM container Cloud Run sedikit pun.

**Konsep:**
Aplikasi Backend (Laravel) hanya bertugas membuat URL khusus (Presigned URL), lalu Browser pengguna mengirimkan file **langsung** ke Google Cloud Storage melewati backend.

**Pengaturan:**
1. Di `.env` aplikasi, ubah konfigurasi Livewire:
   ```env
   LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=gcs
   ```
2. **Setup CORS GCS:**
   Anda wajib mengizinkan domain website Anda (`https://acufara.id`) untuk mengirim PUT/POST request langsung ke Bucket GCS via CLI `gsutil`:
   ```bash
   echo '[{"origin": ["https://acufara.id"], "method": ["GET", "OPTIONS", "PUT"], "responseHeader": ["Content-Type"], "maxAgeSeconds": 3600}]' > cors.json
   gsutil cors set cors.json gs://acufara-media-bucket-2026
   ```
3. Mengatasi validasi MIME: Jika menggunakan Opsi ini, Anda mungkin perlu melakukan *override* atau mematikan validasi MIME ketat (`->image()`) pada Filament menjadi sekadar ekstensi file saja, karena *direct upload* terkadang gagal menyisipkan meta-type yang tepat.

**Keuntungan:**
- Zero impact pada RAM container Cloud Run.
- Kecepatan maksimal karena file tidak perlu "transit" di backend.

---

## 🏆 Kesimpulan untuk Acufara Clinic
Untuk saat ini, gunakan **Opsi 1 (Session Affinity)**. 
Pastikan nilai `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` tetap ada di dalam `.env` server produksi kelak.

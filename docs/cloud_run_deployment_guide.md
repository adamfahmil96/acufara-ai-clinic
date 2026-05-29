# Panduan Deployment ke Google Cloud Run

Karena `gcloud` CLI belum terinstal di laptop Anda, cara paling mudah dan cepat untuk merilis aplikasi ini adalah melalui **Google Cloud Web Console**. Ikuti langkah-langkah berikut:

## Tahap 1: Persiapan Database Produksi
Sebelum men-deploy, pastikan Anda sudah memiliki *Database PostgreSQL* yang online (misalnya Supabase, Neon, atau Google Cloud SQL). Database lokal di laptop tidak akan bisa diakses oleh server Google.
Siapkan URL koneksi atau kredensial database Anda (Host, Port, DB Name, User, Password).

## Tahap 2: Membuat Layanan Cloud Run
1. Buka [Google Cloud Console - Cloud Run](https://console.cloud.google.com/run).
2. Klik tombol biru **Create Service** (Buat Layanan) di bagian atas.
3. Pada opsi pertama *"Deploy one revision from an existing container image"*, masukkan URL image berikut:
   `docker.io/adamfahmil96/acufara-ai-clinic:latest`
4. Di bagian **Authentication**, pilih:
   ✅ **Allow unauthenticated invocations** *(agar pasien bisa mengakses website secara publik).*

## Tahap 3: Konfigurasi Kontainer & Port
1. Buka bagian/dropdown **Container, Networking, Security**.
2. Di tab **Container**, pastikan **Container port** diisi dengan angka `8080` *(standar port trafik dari Load Balancer Cloud Run).*
3. Untuk **Memory**, disarankan minimal `512 MiB` atau `1 GiB`.

## Tahap 4: Mengatur Variabel Lingkungan (Environment Variables)
Masih di tab **Container**, *scroll* ke bawah untuk menemukan menu **Environment Variables**. Tambahkan *key* dan *value* yang sama seperti di file `.env` lokal Anda:

| Name | Value | Keterangan |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Wajib untuk produksi |
| `APP_DEBUG` | `false` | Mematikan pesan error teknis |
| `APP_KEY` | *(copy dari `.env`)* | Kunci enkripsi aplikasi |
| `APP_URL` | `https://[NANTI-DISESUAIKAN-DENGAN-URL-CLOUD-RUN]` | URL utama website |
| `DB_CONNECTION` | `pgsql` | |
| `DB_HOST` | *(Host Database Cloud Anda)* | |
| `DB_PORT` | `5432` | |
| `DB_DATABASE` | *(Nama Database)* | |
| `DB_USERNAME` | *(User Database)* | |
| `DB_PASSWORD` | *(Password Database)* | |
| `SERVER_NAME` | `:8080` | **Wajib!** Mematikan fitur Auto-HTTPS FrankenPHP/Caddy agar tidak berbenturan dengan Load Balancer GCP (mencegah error *ERR_TOO_MANY_ACCEPT_CH_RESTARTS* atau *Redirect Loop*). |
| `FONNTE_TOKEN` | *(copy dari `.env`)* | Untuk WhatsApp |
| `GEMINI_API_KEY`| *(copy dari `.env`)* | Untuk fitur AI |
| `FILESYSTEM_DISK`| `gcs` | Agar foto tersimpan di Cloud Storage |
| `GOOGLE_CLOUD_PROJECT_ID`| *(ID Proyek GCP Anda)* | |
| `GOOGLE_CLOUD_STORAGE_BUCKET`| *(Nama Bucket)* | |

*(Tips: Jika Anda menggunakan Secret Manager, Anda bisa me-reference variabel-variabel sensitif langsung dari Secret Manager).*

## Tahap 5: Eksekusi Deploy
1. Setelah semuanya terisi, klik tombol **Create** (Buat) di bagian paling bawah.
2. Tunggu proses loading beberapa saat (ikon *spinner* berputar).
3. Jika berhasil, Google akan memberikan sebuah **URL publik (berawalan https://...run.app)**.

## Tahap 6: Migrasi Database
Aplikasi sudah *live*, tetapi tabel database di *cloud* masih kosong. Kita perlu menjalankan migrasi.
1. Masih di halaman Cloud Run, buka tab **Jobs** (atau ke menu Cloud Run Jobs).
2. Buat Job baru menggunakan *image* yang sama (`docker.io/adamfahmil96/acufara-ai-clinic:latest`).
3. Set **Command/Arguments** untuk menjalankan perintah: `php artisan migrate --force`.
4. Beri akses koneksi Database yang sama di *Environment Variables*, lalu jalankan Job tersebut.

Selamat! Aplikasi Acufara AI Clinic Anda sekarang sudah bisa diakses oleh seluruh masyarakat dari internet! 🚀

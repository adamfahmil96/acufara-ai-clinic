# ☁️ Panduan Setup Google Cloud Storage (GCS) untuk Laravel
*(Update: Mei 2026)*

Untuk menyimpan *file* dan gambar (Spatie MediaLibrary) ke Google Cloud Storage, Anda memerlukan sebuah **Bucket** dan sebuah **Service Account** agar Laravel memiliki izin untuk menulis ke bucket tersebut.

Di tahun 2026, praktik terbaik (best practice) Google Cloud sangat menyarankan arsitektur **Keyless (Tanpa File JSON)** untuk tahap produksi (terutama jika Anda men-deploy ke Cloud Run). Namun, untuk pengujian lokal, kita tetap menggunakan Service Account Key.

Berikut adalah langkah-langkah lengkapnya:

---

## 🏗️ Tahap 1: Membuat Bucket GCS

1. Buka [Google Cloud Console](https://console.cloud.google.com/) dan pastikan Anda sudah memilih **Project** Anda (misal: `acufara-clinic-123`).
2. Gunakan bilah pencarian di atas, ketik **Cloud Storage**, lalu pilih menu **Buckets**.
3. Klik tombol **+ CREATE** (Buat).
4. **Name your bucket**: Masukkan nama unik secara global (misal: `acufara-media-bucket-2026`).
5. **Location type**: Pilih **Region**, lalu pilih `asia-southeast2 (Jakarta)` agar akses aplikasi sangat cepat.
6. **Storage class**: Pilih **Standard** (cocok untuk akses gambar web rutin).
7. **Control access**: 
   - Pilih **Uniform** (Wajib dipilih agar kontrol akses lebih rapi via IAM).
   - *Penting*: Hapus centang pada opsi *Enforce public access prevention on this bucket*. (Karena gambar artikel dan klinik harus bisa dilihat oleh pasien/publik di internet).
8. Klik **Create**.

---

## 🌍 Tahap 2: Membuka Akses Baca Publik

Agar pengunjung situs bisa melihat gambar tanpa harus login ke Google, kita harus membuat isi *bucket* ini menjadi publik untuk akses "baca".

1. Setelah Bucket selesai dibuat, Anda akan berada di halaman detail bucket tersebut.
2. Pindah ke tab **PERMISSIONS** (Izin).
3. Klik tombol **+ GRANT ACCESS** (Beri Akses).
4. Pada kolom **New principals**, ketik persis: `allUsers`
5. Pada bagian **Select a role**, cari dan pilih **Cloud Storage** > **Storage Object Viewer**.
   > [!WARNING]
   > Pastikan Anda hanya memilih *Storage Object Viewer*. Jangan memilih *Admin* karena itu akan mengizinkan siapa saja untuk menghapus file Anda.
6. Klik **Save**. Google akan memberikan peringatan keamanan "Are you sure you want to make this resource public?". Klik **Allow Public Access**.

---

## 🔐 Tahap 3: Membuat Akses untuk Laravel (Service Account)

Laravel membutuhkan hak untuk *mengunggah* (Upload) dan *menghapus* (Delete) gambar ke dalam bucket.

1. Di menu navigasi Google Cloud (kiri atas), pilih **IAM & Admin** > **Service Accounts**.
2. Klik **+ CREATE SERVICE ACCOUNT**.
3. Beri nama, misalnya `acufara-storage-sa`. Klik **Create and Continue**.
4. Di bagian *Grant this service account access to project*, pilih *Role*: **Cloud Storage** > **Storage Object Admin**. (Ini memberi hak tulis & hapus, tapi hanya sebatas pada Cloud Storage).
5. Klik **Done**.

### A. Untuk Testing di Lokal (Menggunakan JSON Key)
Jika Anda ingin menguji integrasi GCS di komputer lokal (Laptop Anda):
1. Klik *Email* Service Account yang baru saja dibuat.
2. Pindah ke tab **KEYS**.
3. Klik **ADD KEY** > **Create new key**.
4. Pilih **JSON**, lalu klik **Create**. File akan terunduh ke komputer Anda.
5. Pindahkan file tersebut ke dalam proyek Laravel Anda. 
   > [!TIP]
   > Tempat terbaik untuk menyimpannya adalah di dalam folder `storage/app/private/`, contoh: `storage/app/private/gcp-key.json`. File di folder `storage/` secara bawaan sudah masuk ke dalam `.gitignore` sehingga tidak akan bocor ke repositori Git.

### B. Untuk Produksi di Cloud Run (Keyless 2026 Best Practice)
Saat Anda men-deploy `acufara-ai-clinic` ke **Google Cloud Run**, Anda **TIDAK PERLU** lagi mengunggah file JSON ini. 
Anda cukup masuk ke konfigurasi Cloud Run, ke bagian *Security* atau *Identity*, dan pilih *Service Account* `acufara-storage-sa` yang kita buat tadi. Paket `google-cloud-storage` milik PHP otomatis mendeteksi identitas Cloud Run (Metadata server) dengan sendirinya tanpa password/key!

---

## ⚙️ Tahap 4: Mengatur `.env` di Laravel Lokal

Terakhir, perbarui pengaturan Storage di file `.env` Anda:

```env
# Ubah default penyimpanan menjadi GCS
FILESYSTEM_DISK=gcs

# Masukkan Project ID Anda (Lihat di dasbor Google Cloud)
GOOGLE_CLOUD_PROJECT_ID=acufara-clinic-123

# Masukkan nama Bucket dari Tahap 1
GOOGLE_CLOUD_STORAGE_BUCKET=acufara-media-bucket-2026

# Kosongkan prefix jika ingin menyimpan langsung di root folder bucket
GOOGLE_CLOUD_STORAGE_PATH_PREFIX=

# Path ke file JSON Anda di lokal relatif terhadap root project.
GOOGLE_CLOUD_KEY_FILE=storage/app/private/gcp-key.json

# Wajib Set Livewire Tmp Disk ke local untuk menghindari issue MIME validation
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local
```

### Selesai! 🎉
Jalankan perintah `php artisan optimize:clear` atau restart Docker Anda untuk memastikan `.env` yang baru telah dimuat. Coba upload gambar dari *Filament Admin Panel* untuk mengujinya.

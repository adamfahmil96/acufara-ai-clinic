# Panduan Update & Rilis Aplikasi (Manual vs CI/CD)
*Terakhir Diperbarui: Mei 2026*

Dokumen ini menjelaskan dua cara untuk memperbarui aplikasi Acufara AI Clinic ketika Anda membuat perubahan kode di laptop dan ingin mengirimnya ke *live server* di Google Cloud Run.

---

## 1. Pendekatan Manual (Untuk Belajar & Skala Kecil)

Jika Anda belum terbiasa dengan otomatisasi, Anda bisa memperbarui aplikasi secara manual kapan saja Anda selesai mengedit kode.

### Langkah-langkah:
1. **Rakit Image Baru** di terminal VSCode Anda:
   ```bash
   docker build -t adamfahmil96/acufara-ai-clinic:latest .
   ```
2. **Kirim (Push) ke Docker Hub**:
   ```bash
   docker push adamfahmil96/acufara-ai-clinic:latest
   ```
3. **Perbarui Google Cloud Run** *(layanan harus sudah dibuat sebelumnya, lihat Bagian 3)*:
   - Buka Google Cloud Console > menu **Cloud Run**.
   - Klik layanan `acufara-ai-clinic` Anda.
   - Di bagian atas, klik tombol **Edit & Deploy New Revision**.
   - Tanpa mengubah settingan apa pun, langsung *scroll* ke bawah dan klik **Deploy**. 
   - Google Cloud otomatis akan menarik versi `:latest` terbaru dari Docker Hub.

---

## 2. Pendekatan CI/CD Otomatis via GitHub Actions (Praktik Industri Profesional)

Pendekatan ini mengotomatiskan seluruh langkah manual di atas. Anda hanya perlu menekan `git push` ke GitHub, dan robot GitHub yang akan merakit dan merilis aplikasi Anda.

> [!IMPORTANT]
> **Prasyarat (Lakukan Sekali Saja)**
> Anda harus menyiapkan 3 kunci rahasia rahasia (*Secrets*) di pengaturan repositori GitHub Anda (**Settings > Secrets and variables > Actions > New repository secret**):
> 
> 1. `DOCKERHUB_USERNAME`: Isi dengan `adamfahmil96`
> 2. `DOCKERHUB_TOKEN`: Buat *Access Token* di pengaturan keamanan akun Docker Hub Anda (jangan gunakan password utama demi keamanan).
> 3. `GCP_SA_KEY`: Anda harus membuat *Service Account* di Google Cloud Console, berikan izin/Role "Cloud Run Admin" dan "Service Account User", lalu unduh kunci tersebut dalam format JSON. *Copy-paste* seluruh isi file JSON tersebut ke sini. (Catatan: Standar terbaik di 2026 adalah menggunakan *Workload Identity Federation*, namun untuk permulaan, *Service Account JSON Key* adalah yang paling mudah di-setup).

### Konfigurasi GitHub Actions

Buat file baru di proyek Anda dengan path persis seperti ini: `.github/workflows/deploy.yml`. Kemudian isi dengan kode standar industri 2026 berikut:

```yaml
name: Deploy to Google Cloud Run

# Trigger robot hanya saat ada "git push" ke branch "main" (atau development)
on:
  push:
    branches:
      - main

# Mendefinisikan lingkungan kerja (menggunakan standar Ubuntu 2026 terbaru)
jobs:
  build-and-deploy:
    runs-on: ubuntu-latest

    steps:
      # 1. Kloning kode sumber dari GitHub ke server robot
      - name: Checkout Code
        uses: actions/checkout@v4

      # 2. Login ke akun Docker Hub Anda
      - name: Login to Docker Hub
        uses: docker/login-action@v3
        with:
          username: ${{ secrets.DOCKERHUB_USERNAME }}
          password: ${{ secrets.DOCKERHUB_TOKEN }}

      # 3. Rakit (Build) dan Kirim (Push) image ke Docker Hub
      - name: Build and Push Docker Image
        uses: docker/build-push-action@v5
        with:
          context: .
          push: true
          tags: ${{ secrets.DOCKERHUB_USERNAME }}/acufara-ai-clinic:latest

      # 4. Otentikasi ke Google Cloud (Standar API V2 2026)
      - name: Google Auth
        id: auth
        uses: google-github-actions/auth@v2
        with:
          credentials_json: ${{ secrets.GCP_SA_KEY }}

      # 5. Rilis (Deploy) ke Cloud Run secara otomatis
      - name: Deploy to Cloud Run
        uses: google-github-actions/deploy-cloudrun@v2
        with:
          service: acufara-ai-clinic      # Sesuaikan dengan nama layanan Anda di GCP
          region: asia-southeast2         # Region Jakarta
          image: docker.io/${{ secrets.DOCKERHUB_USERNAME }}/acufara-ai-clinic:latest
          flags: '--allow-unauthenticated' # Agar website bisa diakses publik
```

### Cara Kerja Setelah Disetel:
1. Anda membuat fitur baru atau memperbaiki *bug* di laptop.
2. Anda melakukan komit:
   ```bash
   git add .
   git commit -m "Fix UI Bug"
   git push origin main
   ```
3. Buka tab **Actions** di repo GitHub Anda. Anda akan melihat animasi robot sedang bekerja (sekitar 3-5 menit).
4. Ketika indikator berubah menjadi hijau (Sukses), website Anda yang online sudah otomatis diperbarui! 🚀

---

## 3. Membuat Layanan Cloud Run Pertama Kali (Deploy Awal)

> [!IMPORTANT]
> **Langkah ini hanya perlu dilakukan SATU KALI.** Setelah layanan berhasil dibuat, Anda tidak perlu mengulanginya lagi. Langkah-langkah di Bagian 1 (Manual) dan Bagian 2 (CI/CD) mengasumsikan layanan ini sudah ada.

Jika Anda baru memiliki **project** dan **Service Account** di Google Cloud tetapi belum pernah membuat layanan Cloud Run, ada **dua opsi** untuk membuat layanan pertama kali:

---

### Opsi A: Buat Manual via Google Cloud Console

Opsi ini cocok jika Anda ingin memahami setiap konfigurasi secara visual dan memiliki kontrol penuh.

#### Prasyarat:
- Anda sudah memiliki project di Google Cloud Console.
- Anda sudah memiliki Docker Image yang ter-push ke Docker Hub (lihat Bagian 1, langkah 1 & 2).
- API **Cloud Run Admin API** sudah diaktifkan di project Anda (biasanya otomatis aktif saat pertama kali membuka halaman Cloud Run).

#### Langkah-langkah:

1. **Buka Halaman Cloud Run**
   - Masuk ke [Google Cloud Console](https://console.cloud.google.com).
   - Pastikan Anda sudah memilih project yang benar di dropdown pojok kiri atas.
   - Di panel navigasi kiri, klik **Cloud Run** (atau ketik "Cloud Run" di *Search Bar* atas lalu pilih hasilnya).

2. **Buat Layanan Baru**
   - Di halaman Cloud Run, klik tombol **+ CREATE SERVICE** (atau **+ Buat Layanan**) di bagian atas halaman.

3. **Konfigurasi Sumber Container Image**
   - Pilih opsi **Deploy one revision from an existing container image**.
   - Di kolom **Container image URL**, masukkan URL image Docker Hub Anda:
     ```
     docker.io/adamfahmil96/acufara-ai-clinic:latest
     ```
   - Klik **Select** untuk mengonfirmasi.

4. **Konfigurasi Nama & Region Layanan**
   - Di kolom **Service name**, isi dengan: `acufara-ai-clinic`
   - Di kolom **Region**, pilih: **asia-southeast2 (Jakarta)** agar server dekat dengan pengguna di Indonesia.

5. **Konfigurasi Autentikasi (Penting!)**
   - Di bagian **Authentication**, pilih: **Allow unauthenticated invocations** (Izinkan pemanggilan tanpa autentikasi).
   - Ini diperlukan agar website Anda dapat diakses secara publik oleh siapa saja melalui browser.

6. **Konfigurasi Port Container**
   - Buka bagian **Container(s), Volumes, Networking, Security** (klik untuk membuka dropdown jika tertutup).
   - Di tab **Container**, pastikan **Container port** diisi dengan port yang diekspos oleh Dockerfile Anda (biasanya `8080`).

7. **Konfigurasi Resource (Opsional tapi Disarankan)**
   - Masih di tab **Container**, Anda bisa menyesuaikan:
     - **Memory**: `512 MiB` (cukup untuk Laravel skala kecil; naikkan ke `1 GiB` jika perlu).
     - **CPU**: `1` (cukup untuk awal).
   - Di bagian **Autoscaling**, atur:
     - **Minimum number of instances**: `0` (agar tidak kena biaya saat tidak ada traffic).
     - **Maximum number of instances**: `2` (batasi agar biaya tidak membengkak).

8. **Deploy!**
   - Setelah semua konfigurasi selesai, klik tombol **CREATE** (atau **Buat**) di bagian bawah halaman.
   - Tunggu beberapa menit hingga proses deploy selesai. Anda akan melihat indikator centang hijau jika berhasil.
   - Setelah berhasil, Google Cloud akan memberikan URL publik untuk layanan Anda (contoh: `https://acufara-ai-clinic-xxxxx-et.a.run.app`).

---

### Opsi B: Biarkan GitHub Actions Membuatkan Otomatis (Rekomendasi)

Opsi ini adalah cara paling praktis. Anda **tidak perlu membuat layanan secara manual** di Console. Action `deploy-cloudrun@v2` pada file `deploy.yml` akan **otomatis membuat layanan Cloud Run baru** jika layanan dengan nama tersebut belum ada.

#### Prasyarat:
- Anda sudah menyelesaikan setup **Secrets** di GitHub (lihat Bagian 2: `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`, `GCP_SA_KEY`).
- API **Cloud Run Admin API** sudah diaktifkan di project Google Cloud Anda.
- Service Account Anda memiliki Role: **Cloud Run Admin** dan **Service Account User**.

#### Langkah-langkah:

1. **Pastikan `deploy.yml` sudah benar**
   File `.github/workflows/deploy.yml` Anda sudah berisi konfigurasi yang tepat, termasuk:
   - `service: acufara-ai-clinic` — nama layanan yang akan dibuat.
   - `region: asia-southeast2` — region Jakarta.
   - `flags: '--allow-unauthenticated'` — agar website bisa diakses publik.
   
   > [!NOTE]
   > Baris `flags: '--allow-unauthenticated'` sangat penting! Tanpa flag ini, layanan yang dibuat otomatis akan bersifat **private** dan tidak bisa diakses dari browser.

2. **Lakukan `git push` pertama ke branch `main`**
   ```bash
   git add .
   git commit -m "Initial deploy to Cloud Run"
   git push origin main
   ```

3. **Pantau proses di GitHub**
   - Buka tab **Actions** di repo GitHub Anda.
   - Anda akan melihat workflow `Deploy to Google Cloud Run` sedang berjalan.
   - Tunggu hingga semua step selesai (biasanya 3-5 menit untuk push pertama).

4. **Dapatkan URL publik**
   - Setelah workflow berhasil (centang hijau), buka [Google Cloud Console > Cloud Run](https://console.cloud.google.com/run).
   - Anda akan melihat layanan `acufara-ai-clinic` sudah terbuat secara otomatis.
   - Klik layanan tersebut untuk melihat URL publik di bagian atas halaman.

> [!TIP]
> **Kapan memilih Opsi A vs Opsi B?**
> - Pilih **Opsi A** jika Anda ingin mengatur resource (memory, CPU, autoscaling) secara detail dari awal.
> - Pilih **Opsi B** jika Anda ingin cara tercepat dan paling praktis. Anda selalu bisa mengubah konfigurasi resource nanti melalui Console setelah layanan sudah terbuat.

> [!TIP]
> Simpan URL publik yang diberikan oleh Cloud Run. URL ini adalah alamat website Anda yang dapat diakses dari mana saja. Anda juga bisa menghubungkan *custom domain* di kemudian hari melalui tab **Integrations** atau **Custom Domains** di halaman layanan.

---

## 4. Manajemen Variabel Lingkungan (.env) di Cloud Run & CI/CD

Penting untuk dipahami bahwa **file `.env` TIDAK BOLEH di-push ke GitHub atau di-build ke dalam Docker Image** demi keamanan (agar password database atau API key tidak bocor ke publik).

Lalu, bagaimana aplikasi Laravel membaca variabel `.env` saat berada di Cloud Run?

### Mekanisme Cloud Run & Laravel:
Ketika Laravel berjalan di dalam lingkungan Docker, framework ini secara otomatis akan membaca variabel lingkungan sistem (*System Environment Variables*) OS terlebih dahulu sebelum mencari file `.env` fisik. Di Google Cloud Run, kita menyuntikkan variabel ini langsung ke konfigurasi sistem server, sehingga kita sama sekali **tidak butuh** file `.env` di dalam Docker.

### Tutorial Setup yang Aman (Best Practice):

**1. Setel Variabel Sekali Saja di GCP Web Console**
Daripada memindahkan file `.env` ke GitHub (yang sangat berisiko), praktik paling aman adalah memasukkannya langsung di server Google:
- Buka antarmuka Google Cloud Console > menu **Cloud Run**.
- Klik layanan `acufara-ai-clinic` Anda (yang sudah dibuat di Bagian 3).
- Tekan tombol **Edit & Deploy New Revision**.
- Buka tab **Variables & Secrets**.
- Tambahkan variabel dari `.env` lokal Anda satu per satu (seperti `DB_HOST`, `DB_PASSWORD`, `APP_KEY`, `FONNTE_TOKEN`).
- Klik **Deploy**.

**2. Biarkan CI/CD Hanya Mengurus Pembaruan Kode**
- Skrip GitHub Actions (`deploy.yml`) yang kita buat di atas sengaja dirancang **hanya untuk memperbarui *Image* (kode aplikasi)**.
- Ketika robot GitHub merilis versi terbaru dari kode Anda, **Cloud Run akan secara otomatis mewariskan (*inherit*) seluruh variabel lingkungan yang sudah Anda setel secara manual sebelumnya**.
- Artinya, Anda tidak perlu mengirim atau mengatur `.env` di dalam GitHub Actions. Cukup atur di Google Cloud satu kali seumur hidup!

*(Catatan: Jika di masa depan Anda menambahkan fitur baru yang membutuhkan variabel `.env` baru, cukup masuk ke Google Cloud Console dan tambahkan variabel tersebut secara manual di tab Variables & Secrets).*

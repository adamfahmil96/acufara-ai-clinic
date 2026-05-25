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
3. **Perbarui Google Cloud Run**:
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

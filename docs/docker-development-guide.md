# Docker Development Guide

Panduan ini merangkum konfigurasi dan keputusan Docker untuk project Acufara AI Clinic selama setup local development di WSL Ubuntu 26.04.

## Ringkasan Stack

Project ini berjalan dengan Docker Compose dan terdiri dari tiga service utama:

- `app`: Laravel 13 di atas FrankenPHP dan PHP 8.4.
- `db`: PostgreSQL 16.
- `mailpit`: local mail catcher untuk development.

Port yang dipakai:

- App: `http://localhost:8000`
- PostgreSQL: `localhost:5432`
- Mailpit UI: `http://localhost:8025`
- Mailpit SMTP: `localhost:1025`

## File Penting

- `Dockerfile`: build image aplikasi Laravel dengan FrankenPHP, PHP extensions, Composer dependencies, dan asset build.
- `docker-compose.yml`: definisi service utama untuk app, PostgreSQL, Mailpit, network, dan volume.
- `docker-compose.override.yml`: konfigurasi khusus development, termasuk source-code mount dan `--watch`.
- `.env`: konfigurasi local runtime.
- `.env.example`: template konfigurasi environment.
- `docker/caddy/Caddyfile.d/placeholder.caddyfile`: placeholder agar Caddy tidak spam warning import glob.

## Menjalankan Stack Lokal

Jalankan semua service:

```bash
docker compose up -d
```

Cek status container:

```bash
docker compose ps
```

Status normal untuk `app` adalah:

```text
Up ... (healthy)
```

Jika hanya ingin menjalankan database dulu:

```bash
docker compose up -d db
```

## Workflow Laravel di WSL dan Docker

File generation tetap nyaman dilakukan dari WSL:

```bash
php artisan make:migration create_patients_table
php artisan make:model Patient
```

Untuk command yang harus menyentuh database Docker, jalankan dari container `app`:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan about
```

Alasannya: `.env` memakai `DB_HOST=db`, dan hostname `db` hanya dikenali di dalam Docker network.

Jika menjalankan artisan dari WSL host dan ingin konek ke database Docker, override host database:

```bash
DB_HOST=127.0.0.1 php artisan migrate
```

Namun rekomendasi utama tetap menjalankan migrasi dari container:

```bash
docker compose exec app php artisan migrate
```

## Database PostgreSQL

Database lokal dibuat otomatis oleh container PostgreSQL saat volume pertama kali diinisialisasi.

Konfigurasi default:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=acufara_db
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Tidak perlu membuat database manual di PostgreSQL lokal WSL selama memakai service `db` dari Docker Compose.

## APP_KEY Laravel

Jika `.env` masih berisi:

```env
APP_KEY=
```

Laravel akan gagal boot dan log dapat menampilkan:

```text
Illuminate\Encryption\MissingAppKeyException
```

Solusi:

```bash
php artisan key:generate --force
```

Setelah mengubah `.env`, recreate container app agar Docker Compose membaca ulang environment:

```bash
docker compose up -d --force-recreate app
```

Catatan penting: `docker compose restart app` tidak selalu cukup untuk perubahan `env_file`, karena environment dibaca saat container dibuat.

## Restart Loop pada `acufara_app`

Gejala:

```text
acufara_app   Restarting (1)
```

Penyebab yang ditemukan:

1. `APP_KEY` kosong, sehingga Laravel gagal boot.
2. Konfigurasi FrankenPHP worker mengarah ke `./public/index.php`.

Konfigurasi worker yang bermasalah:

```yaml
FRANKENPHP_CONFIG: |
  worker {
    file ./public/index.php
    num 4
  }
```

`public/index.php` Laravel biasa bukan worker script FrankenPHP karena tidak memanggil `frankenphp_handle_request()`. Akibatnya FrankenPHP gagal initialize worker:

```text
failed to initialize workers: worker /app/public/index.php has not reached frankenphp_handle_request()
```

Solusi yang diterapkan:

- Generate `APP_KEY`.
- Hapus `FRANKENPHP_CONFIG` worker dari `docker-compose.yml`.
- Recreate container:

```bash
docker compose up -d --force-recreate app
```

Untuk local development, FrankenPHP cukup berjalan dengan mode `php_server` bawaan Caddyfile.

## Warning Caddyfile.d

Warning yang sempat muncul terus menerus:

```json
{"level":"warn","msg":"No files matching import glob pattern","pattern":"Caddyfile.d/*.caddyfile"}
```

Artinya Caddyfile bawaan FrankenPHP punya baris:

```caddyfile
import Caddyfile.d/*.caddyfile
```

Caddy mencoba import konfigurasi tambahan, tetapi tidak menemukan file dengan ekstensi `.caddyfile` di folder tersebut.

Ini bukan error dan tidak menyebabkan aplikasi crash. Warning menjadi berulang karena `docker-compose.override.yml` menjalankan FrankenPHP dengan `--watch`.

Solusi yang diterapkan:

1. Tambah placeholder permanen di image melalui `Dockerfile`.
2. Tambah mount lokal untuk development:

```yaml
- ./docker/caddy/Caddyfile.d:/etc/caddy/Caddyfile.d:ro
```

Isi placeholder:

```caddyfile
# Placeholder for optional local Caddy snippets.
```

Dengan begitu glob import punya file yang cocok dan warning tidak lagi memenuhi log.

## Rebuild Image

Rebuild image app:

```bash
docker compose build app
```

Recreate container setelah build:

```bash
docker compose up -d --force-recreate app
```

Jika build gagal karena masalah network/TLS saat mengakses Alpine repository, itu bukan selalu kesalahan Dockerfile. Untuk local development, mount `docker/caddy/Caddyfile.d` tetap membuat solusi Caddy warning aktif tanpa rebuild.

## Command Harian

Start semua service:

```bash
docker compose up -d
```

Stop semua service:

```bash
docker compose down
```

Cek container:

```bash
docker compose ps
```

Cek log app:

```bash
docker logs -f --tail 100 acufara_app
```

Masuk shell app:

```bash
docker compose exec app sh
```

Jalankan artisan:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan migrate
```

Recreate app setelah `.env` atau compose config berubah:

```bash
docker compose up -d --force-recreate app
```

## Troubleshooting Cepat

### App Restarting

Cek log:

```bash
docker logs --tail 100 acufara_app
```

Cek `APP_KEY`:

```bash
rg '^APP_KEY=' .env
```

Generate key jika kosong:

```bash
php artisan key:generate --force
docker compose up -d --force-recreate app
```

### Database Tidak Terkoneksi dari WSL

Jika menjalankan artisan langsung dari WSL, `DB_HOST=db` tidak dikenali.

Gunakan salah satu:

```bash
docker compose exec app php artisan migrate
```

atau:

```bash
DB_HOST=127.0.0.1 php artisan migrate
```

### Warning Caddyfile.d Muncul Lagi

Pastikan file ini ada:

```text
docker/caddy/Caddyfile.d/placeholder.caddyfile
```

Pastikan `docker-compose.override.yml` memuat mount:

```yaml
- ./docker/caddy/Caddyfile.d:/etc/caddy/Caddyfile.d:ro
```

Lalu recreate:

```bash
docker compose up -d --force-recreate app
```

## Keputusan Teknis

- Database lokal menggunakan PostgreSQL container, bukan PostgreSQL manual di WSL.
- Artisan untuk generate file boleh dijalankan di WSL.
- Artisan yang butuh database disarankan dijalankan di container `app`.
- FrankenPHP worker tidak dipakai dulu di local development karena Laravel default `public/index.php` bukan worker script.
- Placeholder Caddyfile dipakai untuk membersihkan log warning dari import glob bawaan image.

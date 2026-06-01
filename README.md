# Acufara - AI-Powered Clinic Management System 🌿

Acufara is a modern, all-in-one web application built to manage a home-based clinic and mobile homecare services (Acupuncture, Cupping, and Baby Spa).

Built specifically for the **#JuaraVibeCoding** competition, this system eliminates administrative friction for solo medical practitioners by integrating advanced AI workflows, allowing them to focus 100% on patient care.

## 🚀 Live Demo

- **URL:** [https://acufara-ai-clinic-314489778183.asia-southeast2.run.app/](https://acufara-ai-clinic-314489778183.asia-southeast2.run.app/)
- **Admin Panel:** [https://acufara-ai-clinic-314489778183.asia-southeast2.run.app/admin](https://acufara-ai-clinic-314489778183.asia-southeast2.run.app/admin)
  - **Username/Email:** `demo@acufara.com`
  - **Password:** `password`

## ✨ Core Features

### AcuVoice (AI SOAP Notes)
Hands-free medical record generation. Practitioners simply speak their diagnosis, and Gemini AI formats it into standard SOAP notes.

### Smart Homecare Routing
Gemini AI analyzes patient complaints and locations to suggest the most optimal travel route for mobile clinic services.

### Passwordless WhatsApp Login
Frictionless authentication for patients using a 4-digit OTP sent via WhatsApp.

### Dynamic Treatment Forms
Adapts the medical form based on the service (e.g., showing acupuncture points for adults, or body weight for baby spa).

### Integrated CMS & SEO
Simple Key-Value settings to manage the landing page and an educational blog to boost local SEO.

### Interactive Calendar & Dashboard
Visual scheduling and multi-tenant operational analytics (Superadmin vs. Branch Admin).

### Progressive Web App (PWA)
Installable web app experience directly from the browser to your device's home screen, complete with offline caching and native app feel.

## 🛠️ Tech Stack

**Environment & Infrastructure:**
- **OS:** WSL Ubuntu 26.04
- **Container:** Docker with FrankenPHP
- **Deployment Server:** Google Cloud Run
- **Database Server:** PostgreSQL 16 (via Supabase)
- **Object Storage:** Google Cloud Storage (GCS)

**Application Stack:**
- **Backend:** Laravel 13, PHP 8.4
- **Frontend (Admin):** Filament PHP v5 (TALL Stack)
- **Frontend (Public):** Blade, Alpine.js, Tailwind CSS
- **AI Integration:** Google Gemini API (3.5 Flash)
- **Media Management:** Spatie MediaLibrary

## ☁️ Deployment Summary (Google Cloud Run)

The application is containerized and deployed seamlessly to Google Cloud Run:
1. **Database:** Hosted externally via Supabase (PostgreSQL 16) to ensure data persistence across container restarts.
2. **Containerization:** A multi-stage `Dockerfile` is built using the image `adamfahmil96/acufara-ai-clinic:latest` and pushed to Docker Hub.
3. **Cloud Run Setup:** Deployed with port `8080` exposed and variables matching `.env` for production. `SERVER_NAME=:8080` is used to prevent HTTPS conflicts with the GCP Load Balancer.
4. **Cloud Storage:** Integrated with Google Cloud Storage (`FILESYSTEM_DISK=gcs`) for persistent media uploads instead of local storage.
5. **Database Migration:** Handled securely via Cloud Run Jobs running `php artisan migrate --force`.

For a full step-by-step guide, please refer to the [Cloud Run Deployment Guide](docs/cloud_run_deployment_guide.md) and [Docker Development Guide](docs/docker-development-guide.md).

## 💻 Getting Started (Local Development)

1. Clone the repository.

2. Copy `.env.example` to `.env` and configure your database and Gemini API key.

3. Run the Docker container:

    ```bash
    docker-compose up -d
    ```

4. Install dependencies, run migrations, generate permissions, and seed data:

    ```bash
    composer install
    php artisan migrate
    php artisan shield:generate --all
    php artisan db:seed
    ```

5. Access the app at `http://localhost:8000` and the admin panel at `http://localhost:8000/admin`.

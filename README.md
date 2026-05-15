# Acufara - AI-Powered Clinic Management System 🌿

Acufara is a modern, all-in-one web application built to manage a home-based clinic and mobile homecare services (Acupuncture, Cupping, and Baby Spa).

Built specifically for the **#JuaraVibeCoding** competition, this system eliminates administrative friction for solo medical practitioners by integrating advanced AI workflows, allowing them to focus 100% on patient care.

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

## 🛠️ Tech Stack

- **Backend**: Laravel 13, PHP 8.4

- **Frontend (Admin)**: Filament PHP v5 (TALL Stack)

- **Frontend (Public)**: Blade, Alpine.js, Tailwind CSS

- **Database**: PostgreSQL

- **AI Integration**: Google Gemini API (1.5 Flash/Pro)

- **Media Storage**: Spatie MediaLibrary (Ready for Google Cloud Storage)

- **Deployment**: Docker (FrankenPHP) ready for Google Cloud Run

## 🚀 Getting Started (Development)

1. Clone the repository.

2. Copy `.env.example` to `.env` and configure your database and Gemini API key.

3. Run the Docker container:

    ```bash
    docker-compose up -d
    ```

4. Install dependencies and run migrations:

    ```bash
    composer install
    php artisan migrate --seed
    ```

5. Access the app at `http://localhost:8000` and the admin panel at `http://localhost:8000/admin`.

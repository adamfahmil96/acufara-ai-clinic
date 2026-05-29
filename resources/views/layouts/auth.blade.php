<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login - Acufara AI Clinic')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#87A878">
    <link rel="apple-touch-icon" href="/pwa-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Acufara">

    <style>
        :root {
            --color-sage: #87A878;
            --color-sage-dark: #6B8F5B;
            --color-beige: #F5F0E8;
            --color-card: #eaf4f1;
        }
        body {
            background-color: white;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 px-6 antialiased text-neutral-900">

    <div class="mx-auto w-full max-w-md text-center">
        <a href="/" class="inline-block">
            <img src="{{ asset('images/acufara-header.svg') }}" alt="Acufara" class="h-10 mx-auto">
        </a>
        <p class="mt-2 text-sm text-neutral-500">Klinik Akupunktur & Kecantikan dengan Asisten AI</p>
    </div>

    <div class="mt-8 mx-auto w-full max-w-md">
        <div class="rounded-3xl bg-[var(--color-card)] p-8 sm:p-10">
            @yield('content')
        </div>
        
        <div class="mt-6 text-center">
            <a href="/" class="text-sm font-medium hover:underline" style="color: var(--color-sage-dark);">&larr; Kembali ke Laman Depan</a>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>

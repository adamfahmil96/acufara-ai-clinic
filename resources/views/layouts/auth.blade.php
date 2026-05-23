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
        <a href="/" class="text-xl font-bold tracking-tight text-neutral-900 hover:text-sage transition-colors">Acufara</a>
        <p class="mt-2 text-sm text-neutral-500">Klinik Akupunktur & Kecantikan dengan Asisten AI</p>
    </div>

    <div class="mt-8 mx-auto w-full max-w-md">
        <div class="rounded-3xl bg-[var(--color-card)] p-8 sm:p-10">
            @yield('content')
        </div>
    </div>

</body>
</html>

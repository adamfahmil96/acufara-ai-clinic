<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
@php
    $siteSettings = \App\Models\SiteSetting::pluck('setting_value', 'setting_key');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acufara AI Clinic')</title>
    <meta name="description" content="@yield('meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Acufara AI Clinic')">
    <meta property="og:description" content="@yield('meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.')">
    <meta property="og:image" content="@yield('meta_image', 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')">

    <!-- Fonts: Inter (sans) + Playfair Display (serif for headings) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-sage: #87A878;
            --color-sage-dark: #6B8F5B;
            --color-beige: #F5F0E8;
            --color-card: #eaf4f1;
            --color-teal: #5CA08E;
        }
        body {
            background-color: white;
            color: #1a1a1a;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        .bg-sage { background-color: var(--color-sage); }
        .text-sage { color: var(--color-sage); }
        .border-sage { border-color: var(--color-sage); }
        .bg-card { background-color: var(--color-card); }
        .hover-bg-sage:hover { background-color: var(--color-sage-dark); }

        /* Utilities */
        .shadow-soft { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); }
        .glass {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Button styles inspired by bagustechnologies */
        .btn-solid {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.75rem; font-size: 0.875rem; font-weight: 600;
            color: white; background: var(--color-sage);
            border-radius: 9999px; transition: all 0.2s;
            box-shadow: 0 4px 14px -3px rgba(135,168,120,0.4);
        }
        .btn-solid:hover { background: var(--color-sage-dark); transform: translateY(-1px); }
        .btn-outline {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.75rem; font-size: 0.875rem; font-weight: 600;
            color: #1a1a1a; background: transparent;
            border: 1px solid #d1d5db; border-radius: 9999px; transition: all 0.2s;
        }
        .btn-outline:hover { border-color: var(--color-sage); color: var(--color-sage); }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Global Toast Notification -->
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-6 right-6 z-[100] max-w-sm w-full glass shadow-soft rounded-2xl p-4 border-l-4 {{ session('error') ? 'border-red-500' : 'border-[#87A878]' }} flex items-start gap-3">
            <div class="flex-shrink-0">
                @if(session('error'))
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @else
                    <svg class="h-5 w-5 text-[#87A878]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                @endif
            </div>
            <p class="flex-1 text-sm font-medium text-neutral-900">{{ session('success') ?? session('error') }}</p>
            <button @click="show = false" class="text-neutral-400 hover:text-neutral-600 transition">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    @endif

    <!-- Navbar -->
    <header class="fixed inset-x-0 top-0 z-50 glass" x-data="{ open: false }">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-6 lg:px-0">
            <a href="{{ route('home') }}" class="inline-flex items-center" aria-label="Acufara">
                <span class="text-xl font-bold tracking-tight text-neutral-900">{{ $siteSettings->get('header.brand_name', 'Acufara') }}</span>
            </a>
            <nav class="hidden items-center gap-8 md:flex" aria-label="Primary">
                <a href="{{ route('home') }}#layanan" class="text-sm font-medium text-neutral-900 transition-colors hover:text-sage">Layanan</a>
                <a href="{{ route('home') }}#cara-booking" class="text-sm font-medium text-neutral-900 transition-colors hover:text-sage">Cara Booking</a>
                <a href="{{ route('home') }}#artikel" class="text-sm font-medium text-neutral-900 transition-colors hover:text-sage">Artikel</a>
            </nav>
            <div class="flex items-center gap-3">
                @auth
                    @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('branch_admin'))
                        <a href="{{ url('/admin') }}" class="hidden md:inline-flex text-sm font-medium text-neutral-700 hover:text-sage transition-colors">Dashboard</a>
                    @endif
                    <a href="{{ route('profile') }}" class="btn-solid text-xs sm:text-sm">Profil Saya</a>
                @else
                    <a href="{{ route('login') }}" class="btn-solid text-xs sm:text-sm">Login / Daftar</a>
                @endauth
                <!-- Mobile Menu Button -->
                <button @click="open = !open" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-neutral-900 md:hidden" aria-label="Buka menu">
                    <svg x-show="!open" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M3 6h14"/><path d="M3 10h14"/><path d="M3 14h14"/></svg>
                    <svg x-show="open" x-cloak width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M5 5l10 10"/><path d="M15 5L5 15"/></svg>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1" class="md:hidden glass border-t border-white/30 px-6 pb-4 pt-2 flex flex-col gap-3">
            <a href="{{ route('home') }}#layanan" @click="open=false" class="text-sm font-medium text-neutral-900 py-2 transition-colors hover:text-sage">Layanan</a>
            <a href="{{ route('home') }}#cara-booking" @click="open=false" class="text-sm font-medium text-neutral-900 py-2 transition-colors hover:text-sage">Cara Booking</a>
            <a href="{{ route('home') }}#artikel" @click="open=false" class="text-sm font-medium text-neutral-900 py-2 transition-colors hover:text-sage">Artikel</a>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-16"></div>

    <!-- Main Content -->
    <main class="flex flex-1 flex-col">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-neutral-200 mt-20">
        <div class="mx-auto max-w-5xl px-6 py-12 lg:px-0">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:order-1">
                    <p class="text-sm text-neutral-500">
                        &copy; {{ date('Y') }} {{ $siteSettings->get('header.brand_name', 'Acufara Clinic & Spa') }}. All rights reserved.
                    </p>
                    @if($siteSettings->get('footer.address'))
                        <p class="text-xs text-neutral-400 mt-1">{{ $siteSettings->get('footer.address') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-6 mt-6 md:mt-0 md:order-2">
                    @if($siteSettings->get('footer.instagram'))
                        <a href="{{ $siteSettings->get('footer.instagram') }}" target="_blank" class="text-neutral-400 hover:text-sage transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

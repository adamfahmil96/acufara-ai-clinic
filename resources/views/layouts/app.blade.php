<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
@php
    $siteSettings = \App\Models\SiteSetting::pluck('setting_value', 'setting_key');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acufara AI Clinic')</title>
    <meta name="description" content="@yield('meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.')"
    >
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Acufara AI Clinic')">
    <meta property="og:description" content="@yield('meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.')">
    <meta property="og:image" content="@yield('meta_image', 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-sage: #9cb4a1;
            --color-beige: #f5f5dc;
        }
        body {
            background-color: var(--color-beige);
            color: #333333;
        }
        .bg-sage { background-color: var(--color-sage); }
        .text-sage { color: var(--color-sage); }
        .border-sage { border-color: var(--color-sage); }
        .hover-bg-sage:hover { background-color: #8aa08f; }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-sage">
                        {{ $siteSettings->get('header.brand_name', 'Acufara') }}
                    </a>
                </div>
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('home') }}#layanan" class="text-gray-600 hover:text-sage font-medium">Layanan</a>
                    <a href="{{ route('home') }}#cara-booking" class="text-gray-600 hover:text-sage font-medium">Cara Booking</a>
                    <a href="{{ route('home') }}#artikel" class="text-gray-600 hover:text-sage font-medium">Artikel</a>
                </nav>
                <div class="flex items-center space-x-4">
                    @auth
                        @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('branch_admin'))
                            <a href="{{ url('/admin') }}" class="text-sm font-medium text-gray-700 hover:text-sage">Dashboard Admin</a>
                        @endif
                        <a href="{{ route('profile') }}" class="bg-sage hover-bg-sage text-white px-4 py-2 rounded-md text-sm font-medium transition">
                            Profil Saya
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sage border border-sage hover:bg-sage hover:text-white px-4 py-2 rounded-md text-sm font-medium transition">
                            Login / Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex justify-center md:justify-start space-x-6 md:order-2">
                    @if($siteSettings->get('footer.instagram'))
                        <a href="{{ $siteSettings->get('footer.instagram') }}" target="_blank" class="text-gray-500 hover:text-sage transition">
                            Instagram
                        </a>
                    @endif
                </div>
                <div class="mt-8 md:mt-0 md:order-1">
                    <p class="text-center md:text-left text-base text-gray-400">
                        &copy; {{ date('Y') }} {{ $siteSettings->get('header.brand_name', 'Acufara Clinic & Spa') }}. All rights reserved.
                    </p>
                    @if($siteSettings->get('footer.address'))
                        <p class="text-center md:text-left text-sm text-gray-400 mt-1">
                            {{ $siteSettings->get('footer.address') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

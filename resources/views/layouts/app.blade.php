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
            --color-sage: #87A878;
            --color-beige: #F5F0E8;
        }
        body {
            background-color: var(--color-beige);
            color: #333333;
            font-family: 'Inter', sans-serif; /* Cleaner modern font */
        }
        .bg-sage { background-color: var(--color-sage); }
        .text-sage { color: var(--color-sage); }
        .border-sage { border-color: var(--color-sage); }
        .hover-bg-sage:hover { background-color: #749566; }
        
        /* Soft UI / Glassmorphism utilities */
        .shadow-soft {
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        }
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">

    <!-- Global Toast Notification -->
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-4 right-4 z-[100] max-w-sm w-full bg-white glass shadow-soft rounded-2xl p-4 border-l-4 {{ session('error') ? 'border-red-500' : 'border-[#87A878]' }} flex items-start gap-3">
            <div class="flex-shrink-0">
                @if(session('error'))
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @else
                    <svg class="h-6 w-6 text-[#87A878]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </div>
            <div class="flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">
                    {{ session('success') ?? session('error') }}
                </p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-500 transition">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    @endif

    <!-- Navbar -->
    <header class="glass sticky top-0 z-50">
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

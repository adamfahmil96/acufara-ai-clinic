@extends('layouts.app')

@section('title', $settings->get('seo.meta_title', 'Acufara AI Clinic'))
@section('meta_description', $settings->get('seo.meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.'))

@section('content')
    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 pt-16 px-4 sm:px-6 lg:px-8">
                <main class="mt-10 mx-auto max-w-7xl sm:mt-12 md:mt-16 lg:mt-20 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">{{ $settings->get('hero.title', 'Akupunktur, Bekam, Baby Spa By Acufara') }}</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            {{ $settings->get('hero.subtitle', 'Perawatan holistik dengan alur booking yang mudah dan rekam medis digital.') }}
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="{{ route('booking.create') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-sage hover-bg-sage md:py-4 md:text-lg md:px-10 transition">
                                    {{ $settings->get('hero.cta_label', 'Booking Sekarang') }}
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="#layanan" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-sage bg-green-50 hover:bg-green-100 md:py-4 md:text-lg md:px-10 transition">
                                    Lihat Layanan
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
            <!-- Hero Image Placeholder (Acupuncture Theme) -->
            <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Acupuncture therapy">
        </div>
    </div>

    <!-- Layanan Section -->
    <div id="layanan" class="py-16 bg-[var(--color-beige)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base text-sage font-semibold tracking-wide uppercase">Layanan Kami</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Perawatan Terbaik Untuk Anda
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Kami menyediakan berbagai layanan kesehatan holistik dan kecantikan yang disesuaikan dengan kebutuhan Anda.
                </p>
            </div>

            <div class="mt-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($services as $service)
                        <div class="pt-6">
                            <div class="flow-root bg-white rounded-lg px-6 pb-8 shadow-sm hover:shadow-md transition duration-300 h-full border-t-4 border-sage">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center p-3 bg-sage rounded-md shadow-lg">
                                            <!-- SVG Icon Placeholder -->
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">{{ $service->name }}</h3>
                                    <p class="mt-2 text-base text-sage font-bold">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                                    <p class="mt-4 text-sm text-gray-500">
                                        {{ $service->description ?? 'Layanan unggulan dari Acufara Clinic yang ditangani oleh tenaga profesional.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Dummy Services if DB empty -->
                        @foreach(['Akupunktur Estetika', 'Bekam Medik', 'Pijat Refleksi'] as $dummyService)
                        <div class="pt-6">
                            <div class="flow-root bg-white rounded-lg px-6 pb-8 shadow-sm hover:shadow-md transition duration-300 h-full border-t-4 border-sage">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center p-3 bg-sage rounded-md shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">{{ $dummyService }}</h3>
                                    <p class="mt-2 text-base text-sage font-bold">Rp 150.000</p>
                                    <p class="mt-4 text-sm text-gray-500">
                                        Perawatan holistik untuk mengembalikan keseimbangan tubuh dan meningkatkan kesehatan secara alami.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cara Booking Section -->
    <div id="cara-booking" class="py-16 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base text-sage font-semibold tracking-wide uppercase">Mudah & Cepat</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    3 Langkah Booking Jadwal
                </p>
            </div>
            
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-between">
                    <!-- Step 1 -->
                    <div class="bg-white px-4 text-center w-1/3">
                        <span class="h-12 w-12 rounded-full bg-sage flex items-center justify-center mx-auto text-white font-bold text-xl ring-8 ring-white">
                            1
                        </span>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Daftar / Login</h3>
                        <p class="mt-2 text-sm text-gray-500">Cukup gunakan nomor WhatsApp Anda untuk masuk tanpa perlu password.</p>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-white px-4 text-center w-1/3">
                        <span class="h-12 w-12 rounded-full bg-sage flex items-center justify-center mx-auto text-white font-bold text-xl ring-8 ring-white">
                            2
                        </span>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Pilih Jadwal</h3>
                        <p class="mt-2 text-sm text-gray-500">Pilih layanan, cabang klinik, dan waktu kunjungan (atau *homecare*) sesuai keinginan Anda.</p>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-white px-4 text-center w-1/3">
                        <span class="h-12 w-12 rounded-full bg-sage flex items-center justify-center mx-auto text-white font-bold text-xl ring-8 ring-white">
                            3
                        </span>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Selesai</h3>
                        <p class="mt-2 text-sm text-gray-500">Datang ke klinik sesuai jadwal atau tunggu tim kami tiba di rumah Anda.</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-sage hover-bg-sage transition shadow-sm">
                    Mulai Booking
                </a>
            </div>
        </div>
    </div>

    <!-- Artikel Section -->
    <div id="artikel" class="bg-[var(--color-beige)] pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
        <div class="relative max-w-7xl mx-auto">
            <div class="text-center">
                <h2 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-4xl">Artikel & Tips Kesehatan</h2>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                    Kumpulan informasi bermanfaat dari tim ahli kami.
                </p>
            </div>
            <div class="mt-12 max-w-lg mx-auto grid gap-5 lg:grid-cols-3 lg:max-w-none">
                @forelse($articles as $article)
                    @php
                        $imageUrl = $article->getFirstMediaUrl('default') ?: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                    @endphp
                    <div class="flex flex-col rounded-lg shadow-sm hover:shadow-md transition overflow-hidden bg-white">
                        <div class="flex-shrink-0">
                            <img class="h-48 w-full object-cover" src="{{ $imageUrl }}" alt="{{ $article->title }}">
                        </div>
                        <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-sage">
                                    {{ $article->category ?? 'Edukasi' }}
                                </p>
                                <a href="#" class="block mt-2">
                                    <p class="text-xl font-semibold text-gray-900">
                                        {{ $article->title }}
                                    </p>
                                    <p class="mt-3 text-base text-gray-500 line-clamp-3">
                                        {{ strip_tags($article->content) }}
                                    </p>
                                </a>
                            </div>
                            <div class="mt-6 flex items-center">
                                <div class="ml-3">
                                    <div class="flex space-x-1 text-sm text-gray-500">
                                        <time datetime="{{ $article->created_at->format('Y-m-d') }}">
                                            {{ $article->created_at->format('d M Y') }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Dummy Articles if DB empty -->
                    @foreach(['Manfaat Akupunktur untuk Insomnia', 'Mengenal Terapi Bekam Estetika', 'Pentingnya Keseimbangan Yin dan Yang'] as $dummyTitle)
                    <div class="flex flex-col rounded-lg shadow-sm hover:shadow-md transition overflow-hidden bg-white">
                        <div class="flex-shrink-0">
                            <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="{{ $dummyTitle }}">
                        </div>
                        <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-sage">Artikel Kesehatan</p>
                                <a href="#" class="block mt-2">
                                    <p class="text-xl font-semibold text-gray-900">{{ $dummyTitle }}</p>
                                    <p class="mt-3 text-base text-gray-500">
                                        Membahas secara mendalam tentang manfaat dari terapi tradisional yang didukung oleh pendekatan modern dan AI.
                                    </p>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Layanan Akupunktur & Akupunktur Medik | Acufara')

@section('content')
<!-- Hero Section -->
<section class="relative bg-sage px-6 pt-32 pb-24 text-center sm:pt-40 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <span class="font-mono text-sm font-semibold uppercase tracking-widest text-white/80">Layanan Kami</span>
        <h1 class="mt-4 font-serif text-5xl font-bold tracking-tight text-white sm:text-7xl">
            Akupunktur & Akupunktur Medik
        </h1>
        <p class="mt-6 text-lg leading-8 text-white/90 max-w-2xl mx-auto">
            Metode pengobatan holistik untuk mengobati penyakit dan mengembalikan keseimbangan tubuh secara alami.
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="px-6 py-24 sm:py-32 lg:px-8 bg-white">
    <div class="mx-auto max-w-5xl">
        <!-- Definisi -->
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="rounded-3xl bg-[#eaf4f1] p-8 sm:p-10 shadow-soft">
                <h2 class="font-serif text-3xl text-neutral-900">Apa itu Akupunktur?</h2>
                <p class="mt-4 text-base leading-relaxed text-neutral-600">
                    Metode pengobatan dengan menusukkan titik akupuntur permukaan tubuh untuk mengobati suatu penyakit dan mengembalikan keseimbangan tubuh.
                </p>
            </div>
            
            <div class="rounded-3xl bg-[#F5F0E8] p-8 sm:p-10 shadow-soft">
                <h2 class="font-serif text-3xl text-neutral-900">Apa itu Akupunktur Medik?</h2>
                <p class="mt-4 text-base leading-relaxed text-neutral-600">
                    Ilmu akupunktur yang telah diintegrasikan ke dalam ilmu kedokteran modern sesuai dengan prinsip biomedik, uji klinis ilmiah, dan <em>evidence based medicine</em> dalam teori serta praktek kliniknya.
                </p>
            </div>
        </div>

        <!-- Indikasi -->
        <div class="mt-24">
            <div class="text-center mb-16">
                <h2 class="font-serif text-4xl text-neutral-900">Membantu Untuk Mengobati Keluhan:</h2>
                <div class="mt-4 h-1 w-24 bg-sage mx-auto rounded-full"></div>
            </div>

            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Estetika -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4 flex items-center gap-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                        Estetika
                    </h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Slimming</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Jerawat</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Menghilangkan kerutan</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Kantong mata</li>
                    </ul>
                </div>

                <!-- Pernafasan -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Pernafasan</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Sinus</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Batuk / Flu</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Dan lain-lain</li>
                    </ul>
                </div>

                <!-- Jantung -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Jantung & Pembuluh Darah</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Post Stroke</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Darah Tinggi</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Darah Rendah</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Jantung berdebar</li>
                    </ul>
                </div>

                <!-- Ginekologi -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Ginekologi</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Program hamil</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nyeri haid</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Haid tidak teratur</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Keputihan, dll</li>
                    </ul>
                </div>

                <!-- Pencernaan -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Pencernaan</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Maag & Diare</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Konstipasi</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nafsu Makan</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Perut Kembung & Ambien</li>
                    </ul>
                </div>

                <!-- Perkemihan -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Perkemihan</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Sulit Buang Air Kecil</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nyeri saat BAK</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Ngompol, dll</li>
                    </ul>
                </div>

                <!-- Nyeri -->
                <div>
                    <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Nyeri</h3>
                    <ul class="space-y-3 text-neutral-600">
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nyeri pinggang & lutut</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nyeri bahu & Kaku leher</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Kram</li>
                        <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Nyeri Kepala (Pusing, Migrain, Vertigo)</li>
                    </ul>
                </div>

                <!-- Metabolisme & Syaraf -->
                <div class="sm:col-span-2 lg:col-span-2 grid sm:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Metabolisme</h3>
                        <ul class="space-y-3 text-neutral-600">
                            <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Asam Urat</li>
                            <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Gula Darah</li>
                            <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Kolesterol</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-serif text-2xl text-sage-dark mb-4">Gang. Syaraf</h3>
                        <ul class="space-y-3 text-neutral-600">
                            <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Saraf Kejepit (HNP)</li>
                            <li class="flex items-start gap-2"><span class="text-sage mt-1">•</span> Bells Palsy, dll</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-sage py-20 text-center">
    <div class="mx-auto max-w-3xl px-6">
        <h2 class="font-serif text-4xl text-white tracking-tight">Siap Untuk Perawatan?</h2>
        <p class="mt-4 text-white/80 text-lg">Konsultasikan keluhan Anda dan buat jadwal terapi Akupunktur.</p>
        <div class="mt-10">
            <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-sage shadow-md transition hover:-translate-y-1">
                Booking Jadwal Sekarang
                <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

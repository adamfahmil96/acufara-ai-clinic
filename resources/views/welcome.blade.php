@extends('layouts.app')

@section('title', $settings->get('seo.meta_title', 'Acufara AI Clinic'))
@section('meta_description', $settings->get('seo.meta_description', 'Sistem manajemen klinik terpadu dengan integrasi AI Voice dan WhatsApp.'))

@section('content')
    <!-- Hero Section -->
    <section class="relative flex flex-1 flex-col items-center overflow-hidden px-6 pt-20 pb-24 sm:pt-28">
        <div class="mx-auto flex w-full max-w-5xl flex-col items-center text-center">
            <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Akupunktur · Bekam · Baby Spa</span>
            <h1 class="mt-5 max-w-4xl font-serif text-5xl leading-[1.05] tracking-tight text-neutral-900 sm:text-7xl">
                {{ $settings->get('hero.title', 'Akupunktur, Bekam, Baby Spa By Acufara') }}
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-neutral-600">
                {{ $settings->get('hero.subtitle', 'Perawatan holistik dengan alur booking yang mudah dan rekam medis digital.') }}
            </p>
            <div class="mt-10 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('booking.create') }}" class="btn-solid">
                    {{ $settings->get('hero.cta_label', 'Booking Sekarang') }}
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                </a>
                <a href="#layanan" class="btn-outline">Lihat Layanan</a>
            </div>

            <!-- Hero Image -->
            <div class="mt-16 w-full">
                <div class="relative aspect-video w-full overflow-hidden rounded-2xl shadow-[0_30px_80px_-24px_rgba(135,168,120,0.4)] ring-1 ring-black/5">
                    <img class="h-full w-full object-cover" src="https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80" alt="Acupuncture therapy at Acufara Clinic">
                </div>
            </div>
        </div>
    </section>

    <!-- Layanan Section -->
    <section id="layanan" class="px-6 py-24 sm:py-32">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mx-auto max-w-4xl text-center">
                <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Layanan Kami</span>
                <h2 class="mt-5 font-serif text-4xl leading-[1.06] tracking-tight text-black sm:text-[56px]">
                    Perawatan Terbaik Untuk Anda.
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-neutral-600 sm:text-lg">
                    Kami menyediakan berbagai layanan kesehatan holistik dan kecantikan yang disesuaikan dengan kebutuhan Anda.
                </p>
            </div>

            <ul class="mt-14 grid gap-5 sm:mt-20 lg:grid-cols-3 lg:gap-6">
                <!-- Layanan 1: Akupunktur -->
                <li class="group flex flex-col rounded-3xl bg-card p-8 transition-colors hover:bg-[#dceee7] sm:p-9">
                    <div>
                        <span class="inline-flex items-center justify-center p-3 bg-sage rounded-xl shadow-md">
                            <!-- Needle/Acupuncture Icon -->
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0-16l-3 3m3-3l3 3m-9 7h12" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-6 font-serif text-[22px] leading-snug tracking-tight text-neutral-900">Akupunktur Medik</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600 flex-1">
                        Integrasi ilmu kedokteran modern sesuai prinsip biomedik dan evidence based medicine untuk mengembalikan keseimbangan tubuh.
                    </p>
                    <a href="{{ route('layanan.akupunktur') }}" class="mt-5 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-sage transition-colors hover:text-sage-dark">
                        Selengkapnya
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-0.5"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                    </a>
                </li>
                <!-- Layanan 2: Bekam -->
                <li class="group flex flex-col rounded-3xl bg-card p-8 transition-colors hover:bg-[#dceee7] sm:p-9">
                    <div>
                        <span class="inline-flex items-center justify-center p-3 bg-sage rounded-xl shadow-md">
                            <!-- Cup Icon -->
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v1a5 5 0 01-5 5H8a5 5 0 01-5-5v-1a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-6 font-serif text-[22px] leading-snug tracking-tight text-neutral-900">Bekam / Cupping</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600 flex-1">
                        Metode mengeluarkan darah statis yang mengandung toksin untuk melancarkan peredaran darah dan mengatasi kelelahan.
                    </p>
                    <a href="{{ route('layanan.bekam') }}" class="mt-5 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-sage transition-colors hover:text-sage-dark">
                        Selengkapnya
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-0.5"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                    </a>
                </li>
                <!-- Layanan 3: Baby Spa -->
                <li class="group flex flex-col rounded-3xl bg-card p-8 transition-colors hover:bg-[#dceee7] sm:p-9">
                    <div>
                        <span class="inline-flex items-center justify-center p-3 bg-sage rounded-xl shadow-md">
                            <!-- Face/Smile Icon -->
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-6 font-serif text-[22px] leading-snug tracking-tight text-neutral-900">Baby Spa</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600 flex-1">
                        Perawatan pijat dan hydrotherapy untuk merangsang perkembangan motorik, relaksasi, dan kualitas tidur buah hati Anda.
                    </p>
                    <a href="{{ route('layanan.baby-spa') }}" class="mt-5 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-sage transition-colors hover:text-sage-dark">
                        Selengkapnya
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-0.5"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    <!-- Cara Booking Section -->
    <section id="cara-booking" class="px-6 py-24 sm:py-32 bg-[var(--color-beige)]">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mx-auto max-w-4xl text-center">
                <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Mudah & Cepat</span>
                <h2 class="mt-5 font-serif text-4xl leading-[1.06] tracking-tight text-black sm:text-[56px]">
                    3 Langkah Booking Jadwal.
                </h2>
            </div>

            <div class="mt-14 grid gap-6 sm:mt-20 lg:grid-cols-3 lg:gap-8">
                <!-- Step 1 -->
                <div class="relative flex flex-col items-center rounded-3xl bg-white p-8 text-center shadow-soft sm:p-10">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-sage text-white font-bold text-2xl shadow-md">1</span>
                    <h3 class="mt-6 font-serif text-xl leading-snug tracking-tight text-neutral-900">Daftar / Login</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600">Cukup gunakan nomor WhatsApp Anda untuk masuk tanpa perlu password.</p>
                </div>
                <!-- Step 2 -->
                <div class="relative flex flex-col items-center rounded-3xl bg-white p-8 text-center shadow-soft sm:p-10">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-sage text-white font-bold text-2xl shadow-md">2</span>
                    <h3 class="mt-6 font-serif text-xl leading-snug tracking-tight text-neutral-900">Pilih Jadwal</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600">Pilih layanan, cabang klinik, dan waktu kunjungan sesuai keinginan Anda.</p>
                </div>
                <!-- Step 3 -->
                <div class="relative flex flex-col items-center rounded-3xl bg-white p-8 text-center shadow-soft sm:p-10">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-sage text-white font-bold text-2xl shadow-md">3</span>
                    <h3 class="mt-6 font-serif text-xl leading-snug tracking-tight text-neutral-900">Selesai</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-neutral-600">Datang ke klinik sesuai jadwal atau tunggu tim kami tiba di rumah Anda.</p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <a href="{{ route('booking.create') }}" class="btn-solid">
                    Mulai Booking
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Artikel Section -->
    <section id="artikel" class="px-6 py-24 sm:py-32">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mx-auto max-w-4xl text-center">
                <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Blog & Edukasi</span>
                <h2 class="mt-5 font-serif text-4xl leading-[1.06] tracking-tight text-black sm:text-[56px]">
                    Artikel & Tips Kesehatan.
                </h2>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-neutral-600 sm:text-lg">
                    Kumpulan informasi bermanfaat dari tim ahli kami.
                </p>
            </div>

            <div class="mt-14 grid gap-6 sm:mt-20 lg:grid-cols-3 lg:gap-8">
                @forelse($articles as $article)
                    @php
                        $imageUrl = $article->getFirstMediaUrl('default') ?: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                    @endphp
                    <a href="{{ route('blog.show', $article->slug) }}" class="group flex flex-col overflow-hidden rounded-2xl bg-card transition-colors hover:bg-[#dceee7]">
                        <div class="aspect-[16/10] overflow-hidden">
                            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $imageUrl }}" alt="{{ $article->title }}">
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.15em] text-sage">{{ $article->category ?? 'Edukasi' }}</span>
                                <span class="text-neutral-300">·</span>
                                <time class="font-mono text-[10px] text-neutral-400" datetime="{{ $article->created_at->format('Y-m-d') }}">{{ $article->created_at->format('d M Y') }}</time>
                            </div>
                            <h3 class="mt-3 font-serif text-lg leading-snug tracking-tight text-neutral-900">{{ $article->title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-neutral-600 line-clamp-3">{{ Str::limit(strip_tags($article->content), 100) }}</p>
                        </div>
                    </a>
                @empty
                    @foreach(['Manfaat Akupunktur untuk Insomnia', 'Mengenal Terapi Bekam Estetika', 'Pentingnya Keseimbangan Yin dan Yang'] as $dummyTitle)
                    <a href="#" class="group flex flex-col overflow-hidden rounded-2xl bg-card transition-colors hover:bg-[#dceee7]">
                        <div class="aspect-[16/10] overflow-hidden">
                            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="{{ $dummyTitle }}">
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <span class="font-mono text-[10px] font-semibold uppercase tracking-[0.15em] text-sage">Artikel Kesehatan</span>
                            <h3 class="mt-3 font-serif text-lg leading-snug tracking-tight text-neutral-900">{{ $dummyTitle }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-neutral-600">
                                Membahas secara mendalam tentang manfaat dari terapi tradisional yang didukung oleh pendekatan modern dan AI.
                            </p>
                        </div>
                    </a>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="px-6 py-24 sm:py-32 bg-sage">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="font-serif text-4xl leading-[1.06] tracking-tight text-white sm:text-[56px]">
                Siap Untuk Perawatan Terbaik?
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-white/80 sm:text-lg">
                Jadwalkan kunjungan Anda sekarang dan rasakan pengalaman perawatan holistik yang berbeda.
            </p>
            <div class="mt-10 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3 text-sm font-semibold text-sage shadow-[0_4px_14px_-3px_rgba(0,0,0,0.15)] transition hover:bg-neutral-50 hover:-translate-y-0.5">
                    Booking Sekarang
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
                </a>
            </div>
        </div>
    </section>
@endsection

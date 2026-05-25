@extends('layouts.app')

@section('title', 'Layanan Bekam & Cupping | Acufara')

@section('content')
<!-- Hero Section -->
<section class="relative bg-sage px-6 pt-32 pb-24 text-center sm:pt-40 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <span class="font-mono text-sm font-semibold uppercase tracking-widest text-white/80">Layanan Kami</span>
        <h1 class="mt-4 font-serif text-5xl font-bold tracking-tight text-white sm:text-7xl">
            Bekam / Cupping
        </h1>
        <p class="mt-6 text-lg leading-8 text-white/90 max-w-2xl mx-auto">
            Detoksifikasi alami untuk melancarkan sirkulasi darah dan menyegarkan kembali energi tubuh Anda.
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="px-6 py-24 sm:py-32 lg:px-8 bg-white">
    <div class="mx-auto max-w-5xl">
        <!-- Definisi -->
        <div class="rounded-3xl bg-[#eaf4f1] p-8 sm:p-12 shadow-soft text-center mb-16">
            <h2 class="font-serif text-3xl text-neutral-900 sm:text-4xl">Apa itu Bekam / Cupping?</h2>
            <p class="mt-6 text-lg leading-relaxed text-neutral-600 max-w-3xl mx-auto">
                Metode pengobatan dengan cara mengeluarkan darah statis (kental) yang mengandung toksin dari dalam tubuh manusia.
            </p>
        </div>

        <!-- Manfaat Grid -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="font-serif text-4xl text-neutral-900">Beberapa Manfaat Bekam:</h2>
                <div class="mt-4 h-1 w-24 bg-sage mx-auto rounded-full"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Membuang racun, angin, kolesterol</p>
                </div>
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Mengatasi kelelahan</p>
                </div>
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Melancarkan peredaran darah & menurunkan hipertensi</p>
                </div>
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Meredakan nyeri otot</p>
                </div>
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Mengatasi demam</p>
                </div>
                <!-- Item -->
                <div class="flex items-center gap-4 rounded-2xl bg-[#F5F0E8] p-5 shadow-sm transition hover:bg-[#eaf4f1]">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sage/20 text-sage-dark">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <p class="font-semibold text-neutral-800">Mengurangi Anxiety (Kecemasan)</p>
                </div>
            </div>
        </div>

        <!-- Info Card (Endorfin) -->
        <div class="relative overflow-hidden rounded-3xl bg-neutral-900 p-8 sm:p-12 shadow-xl">
            <!-- Decorative circle -->
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-sage/20 blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sage text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    <h3 class="font-serif text-2xl text-white">Tahukah Anda?</h3>
                </div>
                
                <p class="text-lg leading-relaxed text-neutral-300">
                    Pada proses Cupping terjadi kerusakan <em>mast Cell</em> yang berakibat terstimulusnya beberapa zat yang salah satunya adalah <strong>endorfin</strong>. Endorfin akan muncul ketika tubuh mengalami stres ringan.
                </p>
                <p class="mt-4 text-lg leading-relaxed text-neutral-300">
                    Kerja endorfin mirip dengan <em>Morphin</em> sebagai antinyeri, menenangkan dan memberikan rasa rileks pada tubuh. Itulah mengapa banyak pasien merasa sangat segar setelah sesi bekam selesai.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-sage py-20 text-center">
    <div class="mx-auto max-w-3xl px-6">
        <h2 class="font-serif text-4xl text-white tracking-tight">Mulai Detoksifikasi Tubuh Anda</h2>
        <p class="mt-4 text-white/80 text-lg">Buat jadwal Bekam / Cupping bersama tenaga ahli kami sekarang.</p>
        <div class="mt-10">
            <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-sage shadow-md transition hover:-translate-y-1">
                Booking Jadwal Sekarang
                <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

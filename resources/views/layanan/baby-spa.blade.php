@extends('layouts.app')

@section('title', 'Layanan Baby Spa | Acufara')

@section('content')
<!-- Hero Section -->
<section class="relative bg-sage px-6 pt-32 pb-24 text-center sm:pt-40 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <span class="font-mono text-sm font-semibold uppercase tracking-widest text-white/80">Layanan Kami</span>
        <h1 class="mt-4 font-serif text-5xl font-bold tracking-tight text-white sm:text-7xl">
            Baby Spa
        </h1>
        <p class="mt-6 text-lg leading-8 text-white/90 max-w-2xl mx-auto">
            Perawatan pijat dan terapi air khusus untuk merangsang perkembangan motorik, relaksasi, dan kualitas tidur buah hati Anda.
        </p>
    </div>
</section>

<!-- Content Section -->
<section class="px-6 py-24 sm:py-32 lg:px-8 bg-white">
    <div class="mx-auto max-w-5xl">
        <!-- Definisi -->
        <div class="rounded-3xl bg-[#F5F0E8] p-8 sm:p-12 shadow-soft text-center mb-16">
            <h2 class="font-serif text-3xl text-neutral-900 sm:text-4xl">Apa itu Baby Spa?</h2>
            <p class="mt-6 text-lg leading-relaxed text-neutral-600 max-w-3xl mx-auto">
                Baby Spa adalah serangkaian perawatan untuk bayi yang meliputi pijat bayi (baby massage) dan terapi air (hydrotherapy). Perawatan ini dirancang khusus dengan sentuhan lembut dan aman untuk bayi, dipandu oleh terapis profesional kami.
            </p>
            <div class="mt-8">
                <a href="https://www.instagram.com/arababyspa/" target="_blank" class="inline-flex items-center gap-2 text-sage hover:text-sage-dark font-semibold">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    Lihat Galeri & Testimoni di Instagram @arababyspa
                </a>
            </div>
        </div>

        <!-- Manfaat Grid -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="font-serif text-4xl text-neutral-900">Manfaat Utama Baby Spa</h2>
                <div class="mt-4 h-1 w-24 bg-sage mx-auto rounded-full"></div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Item -->
                <div class="rounded-2xl bg-[#eaf4f1] p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sage text-white mb-4 shadow-md">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-neutral-900 mb-2">Relaksasi Otot</h3>
                    <p class="text-sm text-neutral-600 leading-relaxed">Membantu mengurangi ketegangan pada otot bayi setelah beraktivitas, membuat mereka lebih tenang.</p>
                </div>
                <!-- Item -->
                <div class="rounded-2xl bg-[#eaf4f1] p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sage text-white mb-4 shadow-md">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-neutral-900 mb-2">Meningkatkan Nafsu Makan</h3>
                    <p class="text-sm text-neutral-600 leading-relaxed">Pijatan yang tepat membantu sistem pencernaan lebih lancar sehingga asupan nutrisi bayi lebih optimal.</p>
                </div>
                <!-- Item -->
                <div class="rounded-2xl bg-[#eaf4f1] p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sage text-white mb-4 shadow-md">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-neutral-900 mb-2">Tidur Lebih Nyenyak</h3>
                    <p class="text-sm text-neutral-600 leading-relaxed">Hydrotherapy dan pijatan membuat bayi lebih rileks, sehingga kualitas tidur siang dan malam jauh lebih baik.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Call to Action -->
<section class="bg-sage py-20 text-center">
    <div class="mx-auto max-w-3xl px-6">
        <h2 class="font-serif text-4xl text-white tracking-tight">Berikan Perawatan Terbaik Untuk Si Kecil</h2>
        <p class="mt-4 text-white/80 text-lg">Jadwalkan kunjungan Baby Spa agar bayi Anda sehat dan ceria.</p>
        <div class="mt-10">
            <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-sage shadow-md transition hover:-translate-y-1">
                Booking Jadwal Sekarang
                <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10L10 4M10 4H5M10 4V9"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection

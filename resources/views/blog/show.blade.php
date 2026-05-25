@extends('layouts.app')

@section('title', $article->title . ' | ' . $settings->get('seo.meta_title', 'Acufara AI Clinic'))
@section('meta_description', Str::limit(strip_tags($article->content), 150))
@if($article->getFirstMediaUrl('default'))
    @section('meta_image', $article->getFirstMediaUrl('default'))
@endif

@section('content')
<article class="bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="mx-auto max-w-3xl text-base leading-7 text-neutral-700">
        <div class="flex items-center gap-4 text-sm mb-6">
            <span class="inline-flex items-center rounded-full bg-[#eaf4f1] px-3 py-1 font-mono text-xs font-semibold uppercase tracking-wider text-sage">
                {{ $article->category ?? 'Edukasi' }}
            </span>
            <time datetime="{{ $article->created_at->format('Y-m-d') }}" class="text-neutral-500">
                {{ $article->created_at->format('d M Y') }}
            </time>
        </div>

        <h1 class="mt-2 font-serif text-3xl font-bold tracking-tight text-neutral-900 sm:text-5xl leading-tight">
            {{ $article->title }}
        </h1>
        
        <div class="mt-10 aspect-[16/9] w-full overflow-hidden rounded-2xl bg-neutral-100 shadow-soft">
            <img src="{{ $article->getFirstMediaUrl('default') ?: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80' }}" alt="{{ $article->title }}" class="h-full w-full object-cover">
        </div>

        <div class="prose prose-lg prose-neutral mt-10 max-w-none prose-headings:font-serif prose-headings:text-neutral-900 prose-a:text-sage prose-a:no-underline hover:prose-a:text-sage-dark hover:prose-a:underline">
            {!! $article->content !!}
        </div>
        
        <div class="mt-16 border-t border-neutral-200 pt-8 flex items-center justify-between">
            <a href="{{ route('home') }}#artikel" class="text-sm font-semibold text-sage hover:text-sage-dark flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke Beranda
            </a>
            
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-neutral-500">Bagikan:</span>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank" class="text-neutral-400 hover:text-green-500">
                    <span class="sr-only">WhatsApp</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </a>
            </div>
        </div>
    </div>
</article>

<!-- Call to Action -->
<section class="bg-[#eaf4f1] py-16 text-center">
    <div class="mx-auto max-w-3xl px-6">
        <h2 class="font-serif text-3xl text-neutral-900 tracking-tight">Konsultasikan Keluhan Anda</h2>
        <p class="mt-4 text-neutral-600">Dapatkan layanan kesehatan profesional di Acufara Clinic. Buat jadwal kunjungan Anda dengan mudah.</p>
        <div class="mt-8">
            <a href="{{ route('booking.create') }}" class="btn-solid">Booking Sekarang</a>
        </div>
    </div>
</section>
@endsection

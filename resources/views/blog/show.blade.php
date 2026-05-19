@extends('layouts.app')

@section('title', $article->meta_title ?? $article->title . ' - Acufara Clinic')
@section('meta_description', $article->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($article->content), 150))
@section('meta_image', $article->getFirstMediaUrl('default') ?: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')

@section('content')
<div class="bg-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Header -->
        <div class="mb-8 text-center">
            <div class="text-sm font-medium text-sage mb-4">
                <a href="{{ route('blog.index') }}" class="hover:underline">Artikel</a> 
                <span class="mx-2">&bull;</span>
                <span>{{ $article->category ?? 'Edukasi' }}</span>
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight">
                {{ $article->title }}
            </h1>
            <p class="mt-4 text-gray-500 text-sm">
                Dipublikasikan pada {{ $article->created_at->format('d F Y') }}
            </p>
        </div>

        <!-- Featured Image -->
        <div class="mb-12 rounded-xl overflow-hidden shadow-sm">
            @php
                $imageUrl = $article->getFirstMediaUrl('default') ?: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $article->title }}" class="w-full h-auto object-cover max-h-[500px]">
        </div>

        <!-- Content -->
        <article class="prose prose-lg prose-sage mx-auto text-gray-700 leading-relaxed">
            {!! $article->content !!}
        </article>

        <!-- CTA Section -->
        <div class="mt-16 bg-[var(--color-beige)] rounded-xl p-8 text-center border border-sage/20">
            <h3 class="text-2xl font-bold text-gray-900">Tertarik mencoba perawatan kami?</h3>
            <p class="mt-2 text-gray-600 mb-6">Konsultasikan keluhan Anda dengan tim profesional Acufara Clinic.</p>
            <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-sage hover-bg-sage transition shadow-sm">
                Buat Jadwal Sekarang
            </a>
        </div>

    </div>
</div>
@endsection

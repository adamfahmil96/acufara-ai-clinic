@extends('layouts.app')

@section('title', 'Artikel & Blog - ' . ($settings->site_name ?? 'Acufara AI Clinic'))
@section('meta_description', 'Kumpulan artikel edukasi tentang kesehatan, akupunktur, dan kecantikan dari Acufara Clinic.')

@section('content')
<div class="bg-[var(--color-beige)] py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl tracking-tight">Artikel Edukasi</h1>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">Tingkatkan pemahaman Anda tentang kesehatan dan perawatan holistik bersama pakar kami.</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1">
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
                            <a href="{{ route('blog.show', $article->slug) }}" class="block mt-2 group">
                                <h2 class="text-xl font-semibold text-gray-900 group-hover:text-sage transition">
                                    {{ $article->title }}
                                </h2>
                                <p class="mt-3 text-base text-gray-500 line-clamp-3">
                                    {{ strip_tags($article->content) }}
                                </p>
                            </a>
                        </div>
                        <div class="mt-6 flex items-center text-sm text-gray-500">
                            <time datetime="{{ $article->created_at->format('Y-m-d') }}">
                                {{ $article->created_at->format('d M Y') }}
                            </time>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-500 text-lg">Belum ada artikel yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12 flex justify-center">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection

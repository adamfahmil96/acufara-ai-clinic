@extends('layouts.auth')

@section('title', 'Profil Pasien - Acufara AI Clinic')

@section('content')
    <div class="mb-6 text-center">
        <h3 class="text-xl font-bold text-gray-900">Profil Pasien</h3>
        <p class="text-sm text-gray-500 mt-2">Selamat datang, {{ auth()->user()->name }}!</p>
        <p class="text-xs text-gray-400 mt-1">Nomor WA: {{ auth()->user()->whatsapp_number }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 p-4 rounded-md text-center">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="space-y-4">
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h4 class="font-semibold text-gray-700">Status Anda</h4>
            <p class="text-sm text-gray-600 mt-1">Anda telah berhasil login ke dalam portal pasien. Fitur lengkap profil dan riwayat booking akan dikembangkan pada langkah berikutnya (Langkah 12).</p>
        </div>

        <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
            <a href="/" class="text-sm text-[#9cb4a1] hover:underline font-semibold">Halaman Utama</a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold bg-transparent border-none cursor-pointer">
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
@endsection

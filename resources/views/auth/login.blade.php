@extends('layouts.auth')

@section('title', 'Login Pasien - Acufara AI Clinic')

@section('content')
    <div class="mb-6 text-center">
        <h3 class="text-lg font-medium text-gray-900">Selamat Datang</h3>
        <p class="text-sm text-gray-500 mt-1">Masukkan nomor WhatsApp Anda untuk masuk atau mendaftar.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 p-4 rounded-md">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="space-y-6" action="{{ route('login.otp') }}" method="POST">
        @csrf

        <div>
            <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">
                Nomor WhatsApp
            </label>
            <div class="mt-1">
                <input id="whatsapp_number" name="whatsapp_number" type="text" autocomplete="tel" required
                       placeholder="Contoh: 08123456789"
                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
            </div>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9cb4a1]"
                    style="background-color: var(--color-sage);">
                Kirim Kode OTP
            </button>
        </div>
    </form>
@endsection

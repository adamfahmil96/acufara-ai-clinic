@extends('layouts.auth')

@section('title', 'Login Pasien - Acufara AI Clinic')

@section('content')
    <div class="mb-6 text-center">
        <h3 class="font-serif text-2xl tracking-tight text-neutral-900">Selamat Datang</h3>
        <p class="text-sm text-neutral-500 mt-2">Masukkan nomor WhatsApp Anda untuk masuk atau mendaftar.</p>
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
                       class="appearance-none block w-full px-4 py-3 border border-neutral-200 rounded-xl shadow-sm placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#87A878] focus:border-[#87A878] text-sm">
            </div>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-[#87A878] hover:bg-[#6B8F5B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#87A878] transition shadow-[0_4px_14px_-3px_rgba(135,168,120,0.4)]">
                Kirim Kode OTP
            </button>
        </div>
    </form>
@endsection

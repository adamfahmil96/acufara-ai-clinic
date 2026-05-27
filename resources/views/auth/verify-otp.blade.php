@extends('layouts.auth')

@section('title', 'Verifikasi OTP - Acufara AI Clinic')

@section('content')
    <div class="mb-6 text-center">
        <h3 class="text-lg font-medium text-gray-900">Verifikasi Kode OTP</h3>
        <p class="text-sm text-gray-500 mt-1">Kami telah mengirimkan kode OTP ke nomor <br> <span class="font-semibold text-gray-800">{{ $waNumber }}</span></p>
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-50 p-4 rounded-md">
            <p class="text-sm text-green-700">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 p-4 rounded-md">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="space-y-6" action="{{ route('login.verify.post') }}" method="POST">
        @csrf

        <div>
            <label for="otp" class="block text-sm font-medium text-gray-700 text-center mb-2">
                Masukkan 4 Digit Kode
            </label>
            <div class="mt-1 flex justify-center">
                <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" required
                       class="appearance-none block w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] text-center text-2xl tracking-widest font-mono">
            </div>
        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9cb4a1]"
                    style="background-color: var(--color-sage);">
                Verifikasi & Masuk
            </button>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-900">Ganti nomor WhatsApp?</a>
        </div>
    </form>
@endsection

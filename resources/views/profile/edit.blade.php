@extends('layouts.app')

@section('title', 'Pengaturan Profil - ' . ($settings->site_name ?? 'Acufara AI Clinic'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow px-6 py-8 sm:p-10 border border-gray-200">
        
        <div class="mb-8 border-b border-gray-200 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900">Pengaturan Profil</h2>
                <p class="mt-1 text-sm text-gray-500">Perbarui informasi personal Anda di bawah ini.</p>
            </div>
            <a href="{{ route('profile') }}" class="text-sm font-medium text-sage hover:text-green-700 transition">&larr; Kembali</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 p-4 rounded-md">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- WA Number (Readonly) -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp (Identitas Login)</label>
                <div class="mt-1 relative">
                    <input type="text" value="{{ $user->whatsapp_number }}" disabled class="appearance-none block w-full px-3 py-2 border border-gray-200 bg-gray-100 text-gray-500 rounded-md sm:text-sm cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Nomor ini digunakan untuk login. Hubungi admin jika Anda perlu mengubahnya.</p>
                </div>
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <div class="mt-1">
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email (Opsional)</label>
                <div class="mt-1">
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Tanggal Lahir -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <div class="mt-1">
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d')) }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <div class="mt-1">
                        <select id="gender" name="gender" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="male" {{ old('gender', $patient?->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $patient?->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Alamat -->
            <div>
                <label for="default_address" class="block text-sm font-medium text-gray-700">Alamat Lengkap Rumah</label>
                <p class="text-xs text-gray-500 mb-1">Penting jika Anda ingin memesan layanan Homecare.</p>
                <div class="mt-1">
                    <textarea id="default_address" name="default_address" rows="3" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">{{ old('default_address', $patient?->default_address) }}</textarea>
                </div>
            </div>

            <div class="pt-5 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-sage hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

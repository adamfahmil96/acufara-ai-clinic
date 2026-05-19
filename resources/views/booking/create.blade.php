@extends('layouts.app')

@section('title', 'Buat Jadwal - ' . ($settings->site_name ?? 'Acufara AI Clinic'))

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow px-6 py-8 sm:p-10">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 text-sage">Buat Jadwal Baru</h2>
            <p class="mt-2 text-sm text-gray-500">
                Silakan lengkapi formulir di bawah ini untuk mengatur jadwal kunjungan Anda.
            </p>
        </div>

        @if (session('error'))
            <div class="mb-4 bg-red-50 p-4 rounded-md">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('booking.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Pilihan Cabang -->
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Cabang Klinik / Lokasi</label>
                <div class="mt-1">
                    <select id="branch_id" name="branch_id" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->address ?? 'Alamat belum diatur' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('branch_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pilihan Layanan -->
            <div>
                <label for="service_id" class="block text-sm font-medium text-gray-700">Layanan</label>
                <div class="mt-1">
                    <select id="service_id" name="service_id" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('service_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal & Waktu -->
            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Tanggal & Waktu</label>
                <div class="mt-1">
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" required value="{{ old('scheduled_at') }}" min="{{ date('Y-m-d\TH:i') }}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">
                </div>
                @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keluhan / Catatan -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Keluhan Singkat / Catatan (Opsional)</label>
                <div class="mt-1">
                    <textarea id="notes" name="notes" rows="3" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#9cb4a1] focus:border-[#9cb4a1] sm:text-sm">{{ old('notes') }}</textarea>
                </div>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-sage hover-bg-sage text-white px-6 py-2 rounded-md text-sm font-medium transition shadow-sm">
                    Buat Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

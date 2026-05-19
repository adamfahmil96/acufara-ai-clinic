@extends('layouts.app')

@section('title', 'Profil Pasien - ' . ($settings->site_name ?? 'Acufara AI Clinic'))

@section('content')
<div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Header Profil -->
        <div class="bg-sage px-6 py-8 sm:p-10 text-white">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold">{{ $user->name }}</h2>
                    <p class="mt-2 text-green-50">WhatsApp: {{ $user->whatsapp_number }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sage bg-opacity-50 hover:bg-opacity-70 transition">
                        ⚙️ Pengaturan
                    </a>
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-sage bg-white hover:bg-gray-50 transition">
                        + Buat Jadwal
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 p-4 border-b border-green-200">
                <p class="text-sm text-green-700 font-medium text-center">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Riwayat Jadwal -->
        <div class="px-6 py-8 sm:p-10">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Riwayat Jadwal (Appointments)</h3>

            @if(count($appointments) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $appointment->service->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $appointment->branch->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match($appointment->status) {
                                                'scheduled' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                            $statusText = match($appointment->status) {
                                                'scheduled' => 'Terjadwal',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan',
                                                default => ucfirst($appointment->status),
                                            };
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">Anda belum memiliki riwayat jadwal perawatan.</p>
                    <div class="mt-4">
                        <a href="{{ route('booking.create') }}" class="text-sage hover:underline font-medium">Buat jadwal pertama Anda sekarang.</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer / Logout -->
        <div class="bg-gray-50 px-6 py-4 sm:px-10 border-t border-gray-200 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-sage font-medium">&larr; Kembali ke Beranda</a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold bg-transparent border-none cursor-pointer">
                    Keluar (Logout)
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

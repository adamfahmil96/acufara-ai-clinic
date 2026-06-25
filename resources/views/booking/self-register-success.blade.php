@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil - Acufara AI Clinic')

@section('content')
<section class="px-6 py-16 sm:py-24">
    <div class="mx-auto max-w-lg text-center">

        {{-- Icon Sukses --}}
        <div class="mx-auto w-20 h-20 rounded-full bg-[#eaf4f1] flex items-center justify-center mb-6">
            <svg class="h-10 w-10 text-[#87A878]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="font-serif text-3xl sm:text-4xl tracking-tight text-black mb-3">Pendaftaran Berhasil!</h1>
        <p class="text-base text-neutral-600 mb-8">
            Jadwal kunjungan Anda telah kami terima. Silakan tunggu konfirmasi dari pihak klinik.
        </p>

        {{-- Detail Booking --}}
        <div class="rounded-3xl bg-card p-8 text-left mb-8">
            <h2 class="text-sm font-bold text-[#4a7a42] uppercase tracking-wide mb-4">Detail Booking</h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama Pasien</span>
                    <span class="font-medium text-gray-800">{{ $appointment->patient->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">No. WhatsApp</span>
                    <span class="font-medium text-gray-800">{{ $appointment->patient->user->whatsapp_number ?? '-' }}</span>
                </div>
                <hr class="border-gray-200">
                <div class="flex justify-between">
                    <span class="text-gray-500">Layanan</span>
                    <span class="font-medium text-gray-800">{{ $appointment->service->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Cabang</span>
                    <span class="font-medium text-gray-800">{{ $appointment->branch->nama_cabang ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jadwal</span>
                    <span class="font-medium text-gray-800">
                        {{ $appointment->scheduled_at ? $appointment->scheduled_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tipe Kunjungan</span>
                    <span class="font-medium text-gray-800">
                        {{ $appointment->service_location_type === 'homecare' ? 'Homecare' : 'Klinik' }}
                    </span>
                </div>
                @if($appointment->complaint_summary)
                    <hr class="border-gray-200">
                    <div>
                        <span class="text-gray-500">Keluhan</span>
                        <p class="mt-1 text-gray-800">{{ $appointment->complaint_summary }}</p>
                    </div>
                @endif
                @if($appointment->ai_urgency)
                    <hr class="border-gray-200">
                    <div>
                        <span class="text-gray-500">Analisis AI</span>
                        <div class="mt-1 space-y-1">
                            <p class="text-gray-800">
                                <span class="font-medium">Urgensi:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    @if(str_contains(strtolower($appointment->ai_urgency ?? ''), 'tinggi')) bg-red-100 text-red-800
                                    @elseif(str_contains(strtolower($appointment->ai_urgency ?? ''), 'sedang')) bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif
                                ">{{ $appointment->ai_urgency }}</span>
                            </p>
                            @if($appointment->ai_recommendation)
                                <p class="text-sm text-gray-600">{{ $appointment->ai_recommendation }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Status --}}
        <div class="rounded-2xl border border-[#c8dfc0] bg-white/60 backdrop-blur-sm p-5 mb-8 text-left">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-yellow-400 animate-pulse"></div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Status: Menunggu Konfirmasi</p>
                    <p class="text-xs text-gray-500 mt-0.5">Pihak klinik akan menghubungi Anda via WhatsApp untuk konfirmasi.</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('home') }}" class="btn-solid">
                Kembali ke Beranda
            </a>
            @if($whatsapp = \App\Models\SiteSetting::where('setting_key', 'footer.whatsapp')->value('setting_value'))
                <a href="{{ $whatsapp }}" target="_blank" class="btn-outline">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Hubungi via WhatsApp
                </a>
            @endif
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Buat Jadwal - ' . ($settings->site_name ?? 'Acufara AI Clinic'))

@section('content')
<section class="px-6 py-16 sm:py-24">
    <div class="mx-auto max-w-2xl">
        <div class="text-center mb-10">
            <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Jadwal Kunjungan</span>
            <h1 class="mt-4 font-serif text-4xl leading-[1.06] tracking-tight text-black sm:text-5xl">Buat Jadwal Baru</h1>
            <p class="mt-3 text-base text-neutral-600">
                Silakan lengkapi formulir di bawah ini untuk mengatur jadwal kunjungan Anda.
            </p>
        </div>

    <div class="rounded-3xl bg-card p-8 sm:p-10">

        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 p-4 rounded-xl">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('booking.store') }}" method="POST" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            {{-- Pilihan Cabang --}}
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Cabang Klinik / Lokasi</label>
                <div class="mt-1">
                    <select id="branch_id" name="branch_id" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->nama_cabang }} ({{ $branch->alamat ?? 'Alamat belum diatur' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('branch_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilihan Layanan --}}
            <div>
                <label for="service_id" class="block text-sm font-medium text-gray-700">Layanan</label>
                <div class="mt-1">
                    <select id="service_id" name="service_id" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('service_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal & Waktu --}}
            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Tanggal &amp; Waktu</label>
                <div class="mt-1">
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" required
                        value="{{ old('scheduled_at') }}"
                        min="{{ date('Y-m-d\TH:i') }}"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                </div>
                @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipe Layanan (Klinik / Homecare) --}}
            <div x-data="{ locationType: '{{ old('service_location_type', 'clinic') }}' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kunjungan</label>
                <div class="flex gap-4 mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="service_location_type" value="clinic" x-model="locationType" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                        <span class="ml-2 text-sm text-gray-700">Kunjungan Klinik</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="service_location_type" value="homecare" x-model="locationType" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                        <span class="ml-2 text-sm text-gray-700">Homecare (Panggilan ke Rumah)</span>
                    </label>
                </div>
                @error('service_location_type')
                    <p class="mt-1 text-sm text-red-600 mb-4">{{ $message }}</p>
                @enderror

                {{-- Alamat Homecare --}}
                <div x-show="locationType === 'homecare'" x-transition class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <label for="address_at_time" class="block text-sm font-medium text-gray-700">Alamat Lengkap & Patokan Lokasi</label>
                    <p class="text-xs text-gray-500 mb-2">Karena ini layanan Homecare, mohon tuliskan alamat selengkap mungkin untuk memudahkan terapis.</p>
                    <div class="mt-1">
                        <textarea id="address_at_time" name="address_at_time" rows="3"
                            :required="locationType === 'homecare'"
                            placeholder="Contoh: Jl. Mawar No 10, RT 02 RW 01, Desa Sukamaju. Rumah cat putih pagar hitam, sebelah warung madura."
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm resize-none"
                        >{{ old('address_at_time') }}</textarea>
                    </div>
                    @error('address_at_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Keluhan & Analisis AI --}}
            <div x-data="triageAnalyzer()" x-init="init()">
                <label for="complaint_summary" class="block text-sm font-medium text-gray-700">
                    Keluhan Singkat <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <div class="mt-1">
                    <textarea
                        id="complaint_summary"
                        name="complaint_summary"
                        rows="3"
                        x-model="complaint"
                        placeholder="Contoh: Sakit kepala dan pusing sejak 3 hari, terasa berat di leher bagian belakang..."
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm resize-none"
                    >{{ old('complaint_summary') }}</textarea>
                </div>
                @error('complaint_summary')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Hidden Inputs untuk Hasil Triage agar tersimpan saat form disubmit --}}
                <input type="hidden" name="ai_urgency" :value="result?.urgency || ''">
                <input type="hidden" name="ai_recommendation" :value="result?.recommendation || ''">
                <input type="hidden" name="ai_notes" :value="result?.notes || ''">

                {{-- Tombol Analisis AI --}}
                <div class="mt-3 flex items-center gap-3">
                    <button
                        type="button"
                        @click="analyze()"
                        :disabled="loading || complaint.trim().length < 5"
                        :class="loading || complaint.trim().length < 5 ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md cursor-pointer'"
                        class="inline-flex items-center gap-2 bg-[#87A878] text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200 shadow-sm"
                    >
                        <span x-show="!loading">🔍</span>
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="loading ? 'Menganalisis...' : 'Analisis Keluhan dengan AI'">Analisis Keluhan dengan AI</span>
                    </button>
                    <p class="text-xs text-gray-400" x-show="complaint.trim().length < 5 && complaint.trim().length > 0">
                        Minimal 5 karakter.
                    </p>
                </div>

                {{-- Error State --}}
                <div x-show="error" x-transition class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-sm text-red-700" x-text="error"></p>
                </div>

                {{-- Card Hasil Analisis AI --}}
                <div x-show="result" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4">
                    <div class="rounded-2xl border border-[#c8dfc0] bg-white/60 backdrop-blur-sm p-5 shadow-soft">
                        {{-- Header --}}
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-lg">🤖</span>
                            <h4 class="text-sm font-bold text-[#4a7a42]">Hasil Analisis AI</h4>
                            <span class="ml-auto text-xs text-gray-400">Powered by Gemini</span>
                        </div>

                        {{-- Urgency Badge --}}
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Urgensi:</span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                :class="{
                                    'bg-green-100 text-green-800': result?.urgency?.toLowerCase().includes('rendah'),
                                    'bg-yellow-100 text-yellow-800': result?.urgency?.toLowerCase().includes('sedang'),
                                    'bg-red-100 text-red-800': result?.urgency?.toLowerCase().includes('tinggi')
                                }"
                                x-text="result?.urgency || '-'"
                            ></span>
                        </div>

                        {{-- Recommendation --}}
                        <div class="mb-3" x-show="result?.recommendation">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Rekomendasi:</p>
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="result?.recommendation"></p>
                        </div>

                        {{-- Notes --}}
                        <div x-show="result?.notes">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Catatan:</p>
                            <p class="text-sm text-gray-600 italic leading-relaxed" x-text="result?.notes"></p>
                        </div>

                        <p class="text-xs text-gray-400 mt-4 border-t border-[#c8dfc0] pt-3">
                            ℹ️ Hasil ini bersifat saran awal. Keputusan akhir tetap di tangan tenaga medis profesional.
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-end">
                <button type="submit" 
                        :disabled="submitting"
                        :class="submitting ? 'opacity-70 cursor-not-allowed' : 'hover:bg-[#749566] hover:shadow-lg cursor-pointer'"
                        class="inline-flex items-center gap-2 bg-[#87A878] text-white px-6 py-3 rounded-xl text-sm font-semibold transition-all duration-300 shadow-soft">
                    <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Memproses...' : 'Buat Jadwal'">Buat Jadwal</span>
                </button>
            </div>
        </form>
    </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('triageAnalyzer', () => ({
            complaint: '',
            loading: false,
            result: null,
            error: null,

            init() {
                // Sync model dengan nilai textarea awal (old input dari Laravel)
                const ta = document.getElementById('complaint_summary');
                if (ta && ta.value) this.complaint = ta.value;
            },

            async analyze() {
                if (this.complaint.trim().length < 5) return;

                this.loading = true;
                this.result  = null;
                this.error   = null;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch('{{ route('booking.triage') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ complaint: this.complaint }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Terjadi kesalahan pada server.');
                    }

                    this.result = data;
                } catch (e) {
                    this.error = '❌ ' + (e.message ?? 'Gagal menghubungi AI. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                }
            },
        }));
    });
</script>
@endpush
@endsection

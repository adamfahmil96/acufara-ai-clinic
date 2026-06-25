@extends('layouts.app')

@section('title', 'Daftar Mandiri - Acufara AI Clinic')

@section('content')
<section class="px-6 py-16 sm:py-24">
    <div class="mx-auto max-w-2xl">
        <div class="text-center mb-10">
            <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-sage">Pendaftaran Online</span>
            <h1 class="mt-4 font-serif text-4xl leading-[1.06] tracking-tight text-black sm:text-5xl">Daftar Mandiri</h1>
            <p class="mt-3 text-base text-neutral-600">
                Isi formulir berikut untuk membuat jadwal kunjungan tanpa perlu login.
            </p>
        </div>

        {{-- Error global --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-700">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="rounded-3xl bg-card p-8 sm:p-10"
             x-data="selfRegister()"
             x-init="init()">

            {{-- Step Indicator --}}
            <div class="flex items-center justify-center gap-2 mb-8">
                <template x-for="i in totalSteps" :key="i">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                             :class="step >= i ? 'bg-[#87A878] text-white' : 'bg-gray-200 text-gray-500'"
                             x-text="i"></div>
                        <div x-show="i < totalSteps" class="w-8 h-0.5" :class="step > i ? 'bg-[#87A878]' : 'bg-gray-200'"></div>
                    </div>
                </template>
            </div>

            {{-- ==================== STEP 1: Nomor WhatsApp ==================== --}}
            <div x-show="step === 1" x-transition>
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Nomor WhatsApp Anda</h2>
                <p class="text-sm text-gray-500 mb-6">Masukkan nomor WhatsApp yang aktif untuk melanjutkan.</p>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                    <div class="mt-1 flex gap-3">
                        <input type="text" id="whatsapp_number" x-model="form.whatsapp_number"
                            placeholder="08xxxxxxxxxx"
                            @keyup.enter="lookupWa()"
                            class="flex-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                        <button type="button" @click="lookupWa()"
                            :disabled="loading || form.whatsapp_number.length < 10"
                            :class="loading || form.whatsapp_number.length < 10 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#749566] cursor-pointer'"
                            class="inline-flex items-center gap-2 bg-[#87A878] text-white px-5 py-2 rounded-lg text-sm font-semibold transition-all shadow-sm">
                            <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="loading ? 'Mengecek...' : 'Cek Nomor'">Cek Nomor</span>
                        </button>
                    </div>
                    <p x-show="waError" x-text="waError" class="mt-2 text-sm text-red-600"></p>
                </div>

                {{-- Hasil Lookup: Pasien Ditemukan --}}
                <div x-show="patientFound" x-transition class="mt-6 rounded-2xl border border-[#c8dfc0] bg-white/60 backdrop-blur-sm p-5 shadow-soft">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">✅</span>
                        <h4 class="text-sm font-bold text-[#4a7a42]">Data Ditemukan</h4>
                    </div>
                    <div class="space-y-1.5 text-sm text-gray-700">
                        <p><span class="font-medium text-gray-500">Nama:</span> <span x-text="existingPatient.name"></span></p>
                        <p><span class="font-medium text-gray-500">WA:</span> <span x-text="existingPatient.whatsapp_number"></span></p>
                        <p x-show="existingPatient.date_of_birth"><span class="font-medium text-gray-500">Tgl Lahir:</span> <span x-text="existingPatient.date_of_birth"></span></p>
                        <p x-show="existingPatient.gender"><span class="font-medium text-gray-500">Gender:</span> <span x-text="existingPatient.gender === 'male' ? 'Laki-laki' : 'Perempuan'"></span></p>
                        <p x-show="existingPatient.default_address"><span class="font-medium text-gray-500">Alamat:</span> <span x-text="existingPatient.default_address"></span></p>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Anda terdaftar sebagai pasien. Silakan lanjut membuat jadwal.</p>
                </div>

                {{-- Hasil Lookup: Nomor Tidak Ditemukan --}}
                <div x-show="waNotFound" x-transition class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50/60 backdrop-blur-sm p-5 shadow-soft">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">ℹ️</span>
                        <h4 class="text-sm font-bold text-yellow-800">Nomor Belum Terdaftar</h4>
                    </div>
                    <p class="text-sm text-yellow-700">Nomor ini belum tercatat di sistem kami. Silakan lengkapi data diri pada langkah berikutnya untuk membuat akun baru.</p>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="button" @click="goToStep2()"
                        :disabled="!patientFound && !isNewPatient"
                        :class="!patientFound && !isNewPatient ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#749566] cursor-pointer'"
                        class="inline-flex items-center gap-2 bg-[#87A878] text-white px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-soft">
                        Lanjutkan
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ==================== STEP 2: Data Diri (Pasien Baru) ==================== --}}
            <div x-show="step === 2" x-transition>
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Lengkapi Data Diri</h2>
                <p class="text-sm text-gray-500 mb-6">Data ini diperlukan untuk pendaftaran pertama kali.</p>

                <div class="space-y-5">
                    <div>
                        <label for="reg_name" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="reg_name" x-model="form.name" required
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                    </div>
                    <div>
                        <label for="reg_dob" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" id="reg_dob" x-model="form.date_of_birth"
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="gender" value="male" x-model="form.gender" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="gender" value="female" x-model="form.gender" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label for="reg_address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="reg_address" x-model="form.default_address" rows="2"
                            placeholder="Alamat domisili saat ini..."
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm resize-none"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button type="button" @click="step = 1"
                        class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <button type="button" @click="goToStep3()"
                        :disabled="!form.name || !form.gender"
                        :class="!form.name || !form.gender ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#749566] cursor-pointer'"
                        class="inline-flex items-center gap-2 bg-[#87A878] text-white px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-soft">
                        Lanjutkan
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ==================== STEP 3: Booking ==================== --}}
            <div x-show="step === 3" x-transition>
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Buat Jadwal Kunjungan</h2>
                <p class="text-sm text-gray-500 mb-6">Pilih layanan, cabang, dan jadwal yang Anda inginkan.</p>

                <form action="{{ route('self-register.store') }}" method="POST" class="space-y-5" @submit="submitting = true">
                    @csrf
                    {{-- Hidden fields --}}
                    <input type="hidden" name="whatsapp_number" :value="form.whatsapp_number">
                    <input type="hidden" name="name" :value="form.name">
                    <input type="hidden" name="date_of_birth" :value="form.date_of_birth">
                    <input type="hidden" name="gender" :value="form.gender">
                    <input type="hidden" name="default_address" :value="form.default_address">
                    <input type="hidden" name="ai_urgency" :value="aiUrgency">
                    <input type="hidden" name="ai_recommendation" :value="aiRecommendation">
                    <input type="hidden" name="ai_notes" :value="aiNotes">

                    {{-- Cabang --}}
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Cabang Klinik</label>
                        <select id="branch_id" name="branch_id" required
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->nama_cabang }} ({{ $branch->alamat ?? 'Alamat belum diatur' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Layanan --}}
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-gray-700">Layanan</label>
                        <select id="service_id" name="service_id" required
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                            <option value="">-- Pilih Layanan --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} — Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal & Waktu --}}
                    <div>
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Tanggal &amp; Waktu</label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" required
                            value="{{ old('scheduled_at') }}"
                            min="{{ date('Y-m-d\TH:i') }}"
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm">
                    </div>

                    {{-- Tipe Kunjungan --}}
                    <div x-data="{ locationType: '{{ old('service_location_type', 'clinic') }}' }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kunjungan</label>
                        <div class="flex gap-4 mb-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="service_location_type" value="clinic" x-model="locationType" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Kunjungan Klinik</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="service_location_type" value="homecare" x-model="locationType" class="text-[#87A878] focus:ring-[#87A878] h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700">Homecare</span>
                            </label>
                        </div>

                        {{-- Alamat Homecare --}}
                        <div x-show="locationType === 'homecare'" x-transition class="mb-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label for="address_at_time" class="block text-sm font-medium text-gray-700">Alamat Lengkap & Patokan</label>
                            <p class="text-xs text-gray-500 mb-2">Tulis alamat selengkap mungkin untuk memudahkan terapis.</p>
                            <textarea id="address_at_time" name="address_at_time" rows="3"
                                :required="locationType === 'homecare'"
                                placeholder="Contoh: Jl. Mawar No 10, RT 02 RW 01, Desa Sukamaju. Rumah cat putih pagar hitam."
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm resize-none"
                            >{{ old('address_at_time') }}</textarea>
                        </div>
                    </div>

                    {{-- Keluhan & AI Triage --}}
                    <div x-data="triageAnalyzer()" x-init="init()">
                        <label for="complaint_summary" class="block text-sm font-medium text-gray-700">
                            Keluhan Singkat <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea id="complaint_summary" name="complaint_summary" rows="3"
                            x-model="complaint"
                            placeholder="Contoh: Sakit kepala dan pusing sejak 3 hari, terasa berat di leher bagian belakang..."
                            class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-[#87A878] focus:border-[#87A878] sm:text-sm resize-none"
                        >{{ old('complaint_summary') }}</textarea>

                        {{-- Tombol AI --}}
                        <div class="mt-3 flex items-center gap-3">
                            <button type="button" @click="analyze()"
                                :disabled="loading || complaint.trim().length < 5"
                                :class="loading || complaint.trim().length < 5 ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md cursor-pointer'"
                                class="inline-flex items-center gap-2 bg-[#87A878] text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200 shadow-sm">
                                <span x-show="!loading">🔍</span>
                                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="loading ? 'Menganalisis...' : 'Analisis Keluhan dengan AI'">Analisis Keluhan dengan AI</span>
                            </button>
                        </div>

                        {{-- Hasil AI --}}
                        <div x-show="result" x-transition class="mt-4">
                            <div class="rounded-2xl border border-[#c8dfc0] bg-white/60 backdrop-blur-sm p-5 shadow-soft">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-lg">🤖</span>
                                    <h4 class="text-sm font-bold text-[#4a7a42]">Hasil Analisis AI</h4>
                                    <span class="ml-auto text-xs text-gray-400">Powered by Gemini</span>
                                </div>
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Urgensi:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-green-100 text-green-800': result?.urgency?.toLowerCase().includes('rendah'),
                                            'bg-yellow-100 text-yellow-800': result?.urgency?.toLowerCase().includes('sedang'),
                                            'bg-red-100 text-red-800': result?.urgency?.toLowerCase().includes('tinggi')
                                        }"
                                        x-text="result?.urgency || '-'"></span>
                                </div>
                                <div class="mb-3" x-show="result?.recommendation">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Rekomendasi:</p>
                                    <p class="text-sm text-gray-700 leading-relaxed" x-text="result?.recommendation"></p>
                                </div>
                                <div x-show="result?.notes">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Catatan:</p>
                                    <p class="text-sm text-gray-600 italic leading-relaxed" x-text="result?.notes"></p>
                                </div>
                                <p class="text-xs text-gray-400 mt-3 border-t border-[#c8dfc0] pt-3">
                                    Hasil ini bersifat saran awal. Keputusan akhir tetap di tangan tenaga medis.
                                </p>
                            </div>
                        </div>
                        <div x-show="triageError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-xl p-3">
                            <p class="text-sm text-red-700" x-text="triageError"></p>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4 border-t border-gray-200 flex justify-between">
                        <button type="button" @click="step = isNewPatient ? 2 : 1"
                            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Kembali
                        </button>
                        <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-70 cursor-not-allowed' : 'hover:bg-[#749566] hover:shadow-lg cursor-pointer'"
                            class="inline-flex items-center gap-2 bg-[#87A878] text-white px-6 py-3 rounded-xl text-sm font-semibold transition-all duration-300 shadow-soft">
                            <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Memproses...' : 'Daftar Sekarang'">Daftar Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('selfRegister', () => ({
        step: 1,
        totalSteps: 3,
        loading: false,
        submitting: false,
        waError: '',
        waNotFound: false,
        patientFound: false,
        isNewPatient: false,
        existingPatient: {},
        triageResult: null,
        triageError: null,

        form: {
            whatsapp_number: '{{ old("whatsapp_number", session("old_whatsapp_number")) }}',
            name: '',
            date_of_birth: '',
            gender: '',
            default_address: '',
        },

        // AI Triage results (updated from child component)
        aiUrgency: '{{ old("ai_urgency") }}',
        aiRecommendation: '{{ old("ai_recommendation") }}',
        aiNotes: '{{ old("ai_notes") }}',

        init() {
            // Listen for triage results from child component
            window.addEventListener('triage-complete', (e) => {
                this.aiUrgency = e.detail?.urgency || '';
                this.aiRecommendation = e.detail?.recommendation || '';
                this.aiNotes = e.detail?.notes || '';
            });

            // Restore old input jika ada error validation
            @if(old('name'))
                this.form.name = '{{ old("name") }}';
                this.form.date_of_birth = '{{ old("date_of_birth") }}';
                this.form.gender = '{{ old("gender") }}';
                this.form.default_address = '{{ old("default_address") }}';
            @endif

            // Jika ada error validasi, kembali ke step yang sesuai
            @if($errors->any() && old('whatsapp_number'))
                // Cek apakah ini pasien baru (ada error di field name/gender)
                @if($errors->has('name') || $errors->has('gender'))
                    this.step = 2;
                    this.isNewPatient = true;
                @else
                    this.step = 3;
                    this.isNewPatient = true;
                @endif
            @endif
        },

        async lookupWa() {
            if (this.form.whatsapp_number.length < 10) return;

            this.loading = true;
            this.waError = '';
            this.waNotFound = false;
            this.patientFound = false;
            this.isNewPatient = false;
            this.existingPatient = {};

            try {
                const response = await fetch('{{ route("self-register.lookup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ whatsapp_number: this.form.whatsapp_number }),
                });

                const data = await response.json();

                if (response.status === 422) {
                    // Validation error
                    const errors = data.errors || {};
                    const firstError = Object.values(errors).flat()[0] || 'Nomor tidak valid.';
                    throw new Error(firstError);
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan.');
                }

                if (data.exists) {
                    this.patientFound = true;
                    this.existingPatient = data.patient;
                    this.waNotFound = false;
                } else {
                    this.isNewPatient = true;
                    this.waNotFound = true;
                }
            } catch (e) {
                this.waError = '❌ ' + (e.message || 'Gagal mengecek nomor. Coba lagi.');
            } finally {
                this.loading = false;
            }
        },

        goToStep2() {
            if (this.patientFound) {
                // Pasien existing → langsung ke step 3
                this.step = 3;
            } else if (this.isNewPatient) {
                this.step = 2;
            }
        },

        goToStep3() {
            if (!this.form.name || !this.form.gender) return;
            this.step = 3;
        },
    }));

    Alpine.data('triageAnalyzer', () => ({
        complaint: '',
        loading: false,
        result: null,
        error: null,

        init() {
            const ta = document.getElementById('complaint_summary');
            if (ta && ta.value) this.complaint = ta.value;
        },

        async analyze() {
            if (this.complaint.trim().length < 5) return;

            this.loading = true;
            this.result = null;
            this.error = null;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            try {
                const response = await fetch('{{ route("booking.triage") }}', {
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
                // Dispatch event to parent component
                window.dispatchEvent(new CustomEvent('triage-complete', { detail: data }));
            } catch (e) {
                this.error = '❌ ' + (e.message || 'Gagal menghubungi AI.');
            } finally {
                this.loading = false;
            }
        },
    }));
});
</script>
@endpush
@endsection

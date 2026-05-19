{{--
    AcuVoice Recorder Component
    Alpine component didefinisikan dalam @script/@endscript (Livewire v3 safe),
    lalu di-mount via x-data="acuvoiceRecorder()" agar tidak ada JS
    yang muncul sebagai teks di HTML attribute.
--}}

<div x-data="acuvoiceRecorder()" x-init="init()" class="flex flex-wrap items-center gap-3 py-1">

    {{-- Tombol Mulai / Stop Rekam --}}
    <button
        type="button"
        @click="toggle()"
        :disabled="!isSupported"
        :class="{
            'bg-red-600 hover:bg-red-700 ring-2 ring-red-400 ring-offset-1 animate-pulse': isRecording,
            'bg-green-700 hover:bg-green-800': !isRecording && isSupported,
            'bg-gray-500 cursor-not-allowed opacity-50': !isSupported
        }"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-150 focus:outline-none"
    >
        <span x-show="!isRecording">🎙️</span>
        <span x-show="isRecording">⏹️</span>
        <span x-text="isRecording ? 'Stop Rekam' : 'Mulai Rekam'">Mulai Rekam</span>
    </button>

    {{-- Indikator status --}}
    <p
        x-text="statusText"
        :class="isRecording ? 'text-red-400 font-medium' : 'text-gray-400'"
        class="text-sm transition-colors duration-200"
    ></p>

</div>

@script
<script>
    Alpine.data('acuvoiceRecorder', () => ({
        isRecording: false,
        isSupported: false,
        statusText: 'Siap merekam.',
        recognition: null,
        _accumulated: '',

        init() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) {
                this.statusText = '⚠️ Browser tidak mendukung Web Speech API. Gunakan Chrome / Edge.';
                return;
            }
            this.isSupported = true;

            const recog = new SR();
            recog.lang           = 'id-ID';
            recog.continuous     = true;
            recog.interimResults = true;

            recog.onstart = () => {
                this.statusText = '🔴 Sedang merekam... Bicara sekarang.';
            };

            recog.onresult = (event) => {
                let interim = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const seg = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        this._accumulated += seg + ' ';
                    } else {
                        interim += seg;
                    }
                }
                this._setTA(this._accumulated + interim);
            };

            recog.onend = () => {
                if (this.isRecording) {
                    try {
                        recog.start();
                    } catch {
                        this.isRecording = false;
                        this.statusText = 'Rekaman berhenti (timeout). Klik Mulai Rekam lagi.';
                    }
                } else {
                    this.statusText = '✅ Selesai. Klik "✨ Format dengan AI" untuk menganalisis.';
                }
            };

            recog.onerror = (e) => {
                if (e.error === 'no-speech') return;
                if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                    this.statusText = '⛔ Akses mikrofon ditolak. Izinkan mikrofon di browser.';
                } else {
                    this.statusText = '⚠️ Error: ' + e.error;
                }
                this.isRecording = false;
            };

            this.recognition = recog;
        },

        toggle() {
            if (!this.isSupported) return;
            this.isRecording ? this.stop() : this.start();
        },

        start() {
            this._accumulated = this._getTA()?.value ?? '';
            try {
                this.recognition.start();
                this.isRecording = true;
            } catch (e) {
                this.statusText = 'Gagal memulai. Refresh dan coba lagi.';
            }
        },

        stop() {
            this.isRecording = false;
            this.recognition.stop();
        },

        _getTA() {
            return document.querySelector('textarea[wire\\:model*="raw_transcript"]')
                || document.querySelector('textarea[id*="raw_transcript"]')
                || document.querySelector('textarea[name*="raw_transcript"]');
        },

        _setTA(text) {
            const el = this._getTA();
            if (!el) return;
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')?.set;
            setter ? setter.call(el, text) : (el.value = text);
            el.dispatchEvent(new Event('input',  { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }));
</script>
@endscript

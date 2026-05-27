/**
 * AcuVoice — Web Speech API Integration
 * Alpine.js component untuk rekam suara dan mengisi field raw_transcript
 * yang dikontrol oleh Filament/Livewire.
 *
 * Cara kerja:
 *  1. Admin klik "Mulai Rekam"
 *  2. SpeechRecognition (continuous) mentranskrip suara ke teks
 *  3. Hasil transkripsi dimasukkan ke textarea raw_transcript via event 'input'
 *     sehingga Livewire wire:model menangkap perubahannya
 *  4. Admin klik "Format dengan AI" (Filament server action) → GeminiService
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('acuvoice', () => ({
        isRecording: false,
        isSupported: false,
        recognition: null,
        statusText: 'Siap merekam.',
        _accumulatedFinal: '',

        init() {
            const SpeechRecognition =
                window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                this.statusText =
                    '⚠️ Browser tidak mendukung Web Speech API. Gunakan Chrome / Edge.';
                this.isSupported = false;
                return;
            }

            this.isSupported = true;

            const recog = new SpeechRecognition();
            recog.lang            = 'id-ID';
            recog.continuous      = true;
            recog.interimResults  = true;
            recog.maxAlternatives = 1;

            recog.onstart = () => {
                this.statusText = '🔴 Sedang merekam... Bicara sekarang.';
            };

            recog.onresult = (event) => {
                let interim = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const segment = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        this._accumulatedFinal += segment + ' ';
                    } else {
                        interim += segment;
                    }
                }

                // Perbarui textarea dengan teks final + interim sementara
                this._setTextareaValue(this._accumulatedFinal + interim);
            };

            recog.onend = () => {
                if (this.isRecording) {
                    // Restart otomatis (browser sering berhenti setelah hening sebentar)
                    try {
                        recog.start();
                    } catch {
                        this.isRecording = false;
                        this.statusText = 'Rekaman berhenti (timeout). Klik Mulai Rekam lagi.';
                    }
                } else {
                    this.statusText =
                        '✅ Rekaman selesai. Klik "✨ Format dengan AI" untuk menganalisis.';
                }
            };

            recog.onerror = (event) => {
                if (event.error === 'no-speech') {
                    // Bukan error fatal — hanya tidak ada suara
                    return;
                }
                console.error('[AcuVoice] error:', event.error);
                if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                    this.statusText =
                        '⛔ Akses mikrofon ditolak. Klik ikon kunci di address bar dan izinkan mikrofon.';
                    this.isRecording = false;
                } else {
                    this.statusText = `⚠️ Error rekam: ${event.error}`;
                    this.isRecording = false;
                }
            };

            this.recognition = recog;
        },

        toggleRecording() {
            if (!this.isSupported) return;
            this.isRecording ? this.stopRecording() : this.startRecording();
        },

        startRecording() {
            // Reset akumulasi teks saat rekam baru dimulai
            this._accumulatedFinal = this._currentTextareaValue();
            try {
                this.recognition.start();
                this.isRecording = true;
            } catch (e) {
                this.statusText = 'Gagal memulai rekaman. Refresh halaman dan coba lagi.';
                console.error('[AcuVoice] start error:', e);
            }
        },

        stopRecording() {
            this.isRecording = false;
            this.recognition.stop();
        },

        // ── Livewire textarea helpers ────────────────────────────────────────

        _getTextarea() {
            // Filament renders textarea dengan atribut wire:model atau id unik
            // Cari berdasarkan name yang berisi 'raw_transcript'
            return (
                document.querySelector('textarea[wire\\:model*="raw_transcript"]') ||
                document.querySelector('textarea[id*="raw_transcript"]') ||
                document.querySelector('textarea[name*="raw_transcript"]')
            );
        },

        _currentTextareaValue() {
            const el = this._getTextarea();
            return el ? el.value : '';
        },

        _setTextareaValue(text) {
            const el = this._getTextarea();
            if (!el) return;

            // Gunakan native setter agar React/Alpine/Livewire mendeteksi perubahan
            const nativeSetter = Object.getOwnPropertyDescriptor(
                HTMLTextAreaElement.prototype,
                'value'
            )?.set;

            if (nativeSetter) {
                nativeSetter.call(el, text);
            } else {
                el.value = text;
            }

            // Trigger event agar Livewire wire:model.live/debounce menangkap
            el.dispatchEvent(new Event('input',  { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }));
});

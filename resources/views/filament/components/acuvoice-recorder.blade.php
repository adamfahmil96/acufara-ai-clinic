{{--
    AcuVoice Recorder Component
    Modal dibuat secara dinamis via vanilla JS dan di-append langsung ke document.body,
    sehingga terbebas dari CSS stacking context Filament/Livewire.
--}}

<div x-data="acuvoiceRecorder()" x-init="init()" class="flex flex-wrap items-center gap-3 py-1">

    {{-- Tombol Mulai Rekam --}}
    <button
        type="button"
        @click="toggle()"
        :disabled="!isSupported"
        :class="{
            'opacity-50 cursor-not-allowed bg-gray-500': !isSupported
        }"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-150 focus:outline-none"
        :style="isSupported ? 'background: #87A878;' : ''"
        onmouseover="if(this.dataset.supported==='1') this.style.background='#6e8e62'"
        onmouseout="if(this.dataset.supported==='1') this.style.background='#87A878'"
        :data-supported="isSupported ? '1' : '0'"
    >
        <span>🎙️</span>
        <span>Mulai Rekam</span>
    </button>

    {{-- Indikator status --}}
    <p x-text="statusText" class="text-sm text-gray-500 transition-colors duration-200"></p>

</div>

@script
<script>
    Alpine.data('acuvoiceRecorder', () => ({
        isRecording: false,
        isSupported: false,
        statusText: 'Siap merekam.',
        recognition: null,

        // Akumulasi teks PERSISTEN antar-sesi rekaman (tidak di-reset saat start)
        _savedText: '',

        // Visualizer
        audioContext: null,
        audioStream: null,
        _rafId: null,

        // Referensi DOM elemen modal
        _modalEl: null,
        _barsContainer: null,
        _transcriptEl: null,

        init() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) {
                this.statusText = '⚠️ Browser tidak mendukung Web Speech API. Gunakan Chrome / Edge.';
                return;
            }
            this.isSupported = true;
            this._setupRecognition(SR);
        },

        _setupRecognition(SR) {
            const recog = new SR();
            recog.lang           = 'id-ID';
            recog.continuous     = true;
            recog.interimResults = true;

            let sessionAccumulated = ''; // akumulasi hanya untuk sesi ini

            recog.onstart = () => {
                this.statusText = '🔴 Merekam...';
            };

            recog.onresult = (event) => {
                let interim = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const seg = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        sessionAccumulated += seg + ' ';
                    } else {
                        interim += seg;
                    }
                }
                // Gabungkan teks tersimpan + sesi ini + interim
                const fullText = (this._savedText + sessionAccumulated + interim).trim();
                if (this._transcriptEl) {
                    this._transcriptEl.textContent = fullText || 'Silakan bicara...';
                    this._transcriptEl.parentElement.scrollTop = this._transcriptEl.parentElement.scrollHeight;
                }
                // Simpan sesi final ke savedText secara live
                recog._sessionAccumulated = sessionAccumulated;
            };

            recog.onend = () => {
                if (this.isRecording) {
                    try { recog.start(); } catch { this.stop(); }
                } else {
                    this.statusText = '✅ Selesai. Klik "✨ Format dengan AI" untuk menganalisis.';
                }
            };

            recog.onerror = (e) => {
                if (e.error === 'no-speech') return;
                this.statusText = e.error === 'not-allowed'
                    ? '⛔ Akses mikrofon ditolak. Izinkan di browser.'
                    : '⚠️ Error: ' + e.error;
                this.stop();
            };

            this.recognition = recog;
            // Expose setter agar bisa dibaca saat stop
            this._getSessionAccumulated = () => recog._sessionAccumulated || '';
            this._resetSessionAccumulated = () => { recog._sessionAccumulated = ''; };
        },

        toggle() {
            if (!this.isSupported) return;
            this.isRecording ? this.stop() : this.start();
        },

        start() {
            // Baca nilai textarea saat ini sebagai "savedText" agar hasil rekaman sebelumnya tidak hilang
            const currentVal = this._getTA()?.value?.trim() ?? '';
            this._savedText = currentVal ? currentVal + ' ' : '';
            this._resetSessionAccumulated();

            try {
                this.recognition.start();
                this.isRecording = true;
                this._showModal();
                this._initVisualizer();
            } catch (e) {
                this.statusText = 'Gagal memulai. Refresh dan coba lagi.';
            }
        },

        stop() {
            if (!this.isRecording) return;
            this.isRecording = false;
            this.recognition.stop();

            // Gabungkan teks tersimpan + sesi terakhir → simpan ke textarea
            const sessionText = this._getSessionAccumulated();
            const finalText = (this._savedText + sessionText).trim();
            this._savedText = finalText ? finalText + ' ' : '';
            this._setTA(finalText);

            // Hapus modal
            this._destroyModal();

            // Bersihkan audio stream
            if (this._rafId) { cancelAnimationFrame(this._rafId); this._rafId = null; }
            if (this.audioStream) { this.audioStream.getTracks().forEach(t => t.stop()); this.audioStream = null; }
            if (this.audioContext) { this.audioContext.close(); this.audioContext = null; }
        },

        // ─── Modal (vanilla DOM) ──────────────────────────────────────────────

        _showModal() {
            this._injectStyles();

            // Overlay
            const overlay = document.createElement('div');
            overlay.id = 'acuvoice-modal-overlay';
            overlay.style.cssText = `
                position: fixed; inset: 0;
                background: rgba(10, 20, 15, 0.75);
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
                z-index: 2147483647;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: acuFadeIn 0.25s ease forwards;
                font-family: ui-sans-serif, system-ui, sans-serif;
            `;

            // Card
            const card = document.createElement('div');
            card.style.cssText = `
                background: linear-gradient(160deg, #0f1f18 0%, #162618 100%);
                border: 1px solid #3a5c3a;
                border-radius: 1.5rem;
                padding: 2rem 2.5rem;
                max-width: 460px;
                width: 90%;
                text-align: center;
                box-shadow: 0 30px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(135,168,120,0.1);
                position: relative;
                animation: acuSlideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            `;

            // Live dot (top-right)
            const dotWrap = document.createElement('div');
            dotWrap.style.cssText = 'position:absolute;top:1.25rem;right:1.25rem;display:flex;align-items:center;gap:0.4rem;';
            dotWrap.innerHTML = `
                <span style="font-size:0.65rem;color:#87A878;letter-spacing:0.08em;font-weight:600;text-transform:uppercase;">LIVE</span>
                <span style="position:relative;display:flex;height:10px;width:10px;">
                    <span style="animation:acuPing 1.2s cubic-bezier(0,0,0.2,1) infinite;position:absolute;display:inline-flex;height:100%;width:100%;border-radius:50%;background:#87A878;opacity:0.5;"></span>
                    <span style="position:relative;display:inline-flex;border-radius:50%;height:10px;width:10px;background:#87A878;"></span>
                </span>
            `;
            card.appendChild(dotWrap);

            // Mic icon
            const micIcon = document.createElement('div');
            micIcon.style.cssText = 'font-size:2.5rem;margin-bottom:0.75rem;animation:acuPulseScale 2s ease-in-out infinite;';
            micIcon.textContent = '🎙️';
            card.appendChild(micIcon);

            // Title
            const title = document.createElement('h3');
            title.style.cssText = 'font-size:1.2rem;font-weight:700;color:#f0f4f1;margin:0 0 0.35rem 0;letter-spacing:-0.01em;';
            title.textContent = 'Mendengarkan Suara Anda...';
            card.appendChild(title);

            const subtitle = document.createElement('p');
            subtitle.style.cssText = 'font-size:0.75rem;color:#6b8f6b;margin:0 0 1.5rem 0;';
            subtitle.textContent = 'Bicara dengan jelas dalam Bahasa Indonesia. Hasil akan disimpan setelah selesai.';
            card.appendChild(subtitle);

            // Divider
            const divider = document.createElement('div');
            divider.style.cssText = 'height:1px;background:linear-gradient(90deg,transparent,#3a5c3a,transparent);margin-bottom:1.5rem;';
            card.appendChild(divider);

            // Sound bars container
            const barsWrap = document.createElement('div');
            barsWrap.style.cssText = 'display:flex;align-items:flex-end;justify-content:center;gap:5px;height:72px;margin-bottom:1.5rem;';
            for (let i = 0; i < 14; i++) {
                const bar = document.createElement('div');
                // Variasikan tinggi awal agar terlihat natural
                const initH = [15, 20, 35, 25, 40, 20, 15, 30, 25, 40, 20, 35, 20, 15][i];
                bar.style.cssText = `
                    width: 9px;
                    background: linear-gradient(to top, #87A878, #b5d4a8);
                    border-radius: 999px;
                    height: ${initH}%;
                    transition: height 0.08s ease;
                    box-shadow: 0 0 6px rgba(135,168,120,0.25);
                `;
                barsWrap.appendChild(bar);
            }
            this._barsContainer = barsWrap;
            card.appendChild(barsWrap);

            // Transcript preview
            const transcriptWrap = document.createElement('div');
            transcriptWrap.style.cssText = `
                background: rgba(0,0,0,0.35);
                border: 1px solid #2d4a2d;
                border-radius: 0.875rem;
                padding: 1rem 1.125rem;
                height: 6.5rem;
                overflow-y: auto;
                margin-bottom: 1.5rem;
                text-align: left;
                scrollbar-width: thin;
                scrollbar-color: #3a5c3a transparent;
            `;
            const transcriptP = document.createElement('p');
            transcriptP.style.cssText = 'color:#a8c8a8;font-style:italic;font-size:0.85rem;line-height:1.65;word-break:break-word;margin:0;';
            transcriptP.textContent = this._savedText.trim() || 'Silakan bicara...';
            this._transcriptEl = transcriptP;
            transcriptWrap.appendChild(transcriptP);
            card.appendChild(transcriptWrap);

            // Stop button
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = `
                background: linear-gradient(135deg, #87A878, #6e8e62);
                color: #fff;
                padding: 0.7rem 2.25rem;
                border-radius: 999px;
                font-weight: 700;
                font-size: 0.875rem;
                cursor: pointer;
                border: none;
                box-shadow: 0 4px 20px rgba(135,168,120,0.35);
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                letter-spacing: 0.01em;
            `;
            btn.innerHTML = '⏹️ &nbsp;Selesai &amp; Simpan';
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = 'translateY(-1px)';
                btn.style.boxShadow = '0 6px 24px rgba(135,168,120,0.5)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translateY(0)';
                btn.style.boxShadow = '0 4px 20px rgba(135,168,120,0.35)';
            });
            btn.addEventListener('click', () => this.stop());
            card.appendChild(btn);

            overlay.appendChild(card);
            document.body.appendChild(overlay);
            this._modalEl = overlay;
        },

        _destroyModal() {
            this._modalEl?.remove();
            this._modalEl = null;
            this._barsContainer = null;
            this._transcriptEl = null;
        },

        _injectStyles() {
            if (document.getElementById('acuvoice-styles')) return;
            const style = document.createElement('style');
            style.id = 'acuvoice-styles';
            style.textContent = `
                @keyframes acuFadeIn {
                    from { opacity: 0; }
                    to   { opacity: 1; }
                }
                @keyframes acuSlideUp {
                    from { transform: translateY(24px) scale(0.97); opacity: 0; }
                    to   { transform: translateY(0)     scale(1);    opacity: 1; }
                }
                @keyframes acuPing {
                    75%, 100% { transform: scale(2.2); opacity: 0; }
                }
                @keyframes acuPulseScale {
                    0%, 100% { transform: scale(1); }
                    50%      { transform: scale(1.08); }
                }
            `;
            document.head.appendChild(style);
        },

        // ─── Audio Visualizer ─────────────────────────────────────────────────

        async _initVisualizer() {
            try {
                this.audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const analyser = this.audioContext.createAnalyser();
                const source = this.audioContext.createMediaStreamSource(this.audioStream);
                source.connect(analyser);
                analyser.fftSize = 64;

                const dataArray = new Uint8Array(analyser.frequencyBinCount);

                const draw = () => {
                    if (!this.isRecording || !this._barsContainer) return;
                    this._rafId = requestAnimationFrame(draw);
                    analyser.getByteFrequencyData(dataArray);

                    const bars = this._barsContainer.children;
                    for (let i = 0; i < bars.length; i++) {
                        const raw = dataArray[i + 1] || 0;
                        bars[i].style.height = Math.max(8, (raw / 255) * 95) + '%';
                    }
                };
                draw();

            } catch {
                // Fallback: animasi simulasi organik
                let t = 0;
                const simulate = () => {
                    if (!this.isRecording || !this._barsContainer) return;
                    this._rafId = requestAnimationFrame(simulate);
                    t += 0.12;
                    const bars = this._barsContainer.children;
                    for (let i = 0; i < bars.length; i++) {
                        const h = 15 + Math.abs(Math.sin(t + i * 0.5)) * 70 + Math.random() * 10;
                        bars[i].style.height = Math.min(95, h) + '%';
                    }
                };
                simulate();
            }
        },

        // ─── Textarea helpers ─────────────────────────────────────────────────

        _getTA() {
            // Coba berbagai selector — Livewire v3 bisa menggunakan wire:model.live atau wire:model.live.debounce
            return document.querySelector('textarea[wire\\:model*="raw_transcript"]')
                || document.querySelector('textarea[wire\\:model\\.live*="raw_transcript"]')
                || document.querySelector('textarea[id*="raw_transcript"]')
                || document.querySelector('textarea[name*="raw_transcript"]');
        },

        _setTA(text) {
            const el = this._getTA();
            if (!el) {
                console.warn('[AcuVoice] Textarea raw_transcript tidak ditemukan.');
                return;
            }
            // Langsung set value
            el.value = text;
            // Dispatch input dan change agar Livewire v3 mendeteksi perubahan
            el.dispatchEvent(new Event('input',  { bubbles: true, cancelable: true }));
            el.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
        },
    }));
</script>
@endscript

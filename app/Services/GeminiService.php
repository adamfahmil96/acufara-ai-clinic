<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model  = config('services.gemini.model', 'gemini-2.5-flash');
    }

    // -------------------------------------------------------------------------
    // AcuVoice: Format raw transcript → structured SOAP Note
    // -------------------------------------------------------------------------

    /**
     * Kirim raw transcript ke Gemini dan dapatkan field SOAP terstruktur.
     *
     * @param  string $rawTranscript  Transkripsi mentah dari Web Speech API
     * @return array{subjective: string, objective: string, assessment: string, plan: string}
     */
    public function formatSoapNote(string $rawTranscript): array
    {
        $prompt = <<<PROMPT
Kamu adalah asisten medis yang membantu terapis akupunktur, bekam, dan baby spa.
Tugas kamu adalah mengubah transkripsi percakapan/catatan mentah berikut menjadi catatan rekam medis terstruktur dalam format SOAP standar.

Kembalikan HANYA objek JSON valid dengan 4 kunci berikut (tanpa teks lain, tanpa markdown):
{
  "subjective":  "Keluhan utama dan riwayat yang disampaikan pasien (kalimat lengkap, Bahasa Indonesia).",
  "objective":   "Temuan pemeriksaan fisik dan observasi terapis (kalimat lengkap, Bahasa Indonesia).",
  "assessment":  "Diagnosa kerja / kesimpulan klinis terapis (kalimat lengkap, Bahasa Indonesia).",
  "plan":        "Rencana tindakan lanjut, terapi yang diberikan, dan jadwal kunjungan berikutnya (kalimat lengkap, Bahasa Indonesia)."
}

Jika informasi untuk suatu kunci tidak ada dalam transkripsi, isi dengan string kosong "".

Transkripsi:
---
{$rawTranscript}
---
PROMPT;

        try {
            $response = $this->generateContent($prompt);

            // Parse JSON dari response Gemini
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Bersihkan markdown code block jika ada
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/i', '', $text);
            $text = trim($text);

            $data = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
                Log::error('[GeminiService] Failed to parse SOAP JSON', [
                    'raw_response' => $text,
                    'json_error'   => json_last_error_msg(),
                ]);

                return $this->emptysoap('Gagal mem-parsing respons AI. Silakan coba lagi.');
            }

            return [
                'subjective' => $data['subjective'] ?? '',
                'objective'  => $data['objective']  ?? '',
                'assessment' => $data['assessment'] ?? '',
                'plan'       => $data['plan']        ?? '',
            ];

        } catch (\Throwable $e) {
            Log::error('[GeminiService] formatSoapNote error', [
                'message' => $e->getMessage(),
            ]);

            return $this->emptysoap('Terjadi kesalahan saat menghubungi AI: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Smart Triage: Analyze complaint + location (Langkah 14 — stub)
    // -------------------------------------------------------------------------

    /**
     * Analisis keluhan pasien dan rekomendasikan urgensi + rute kunjungan.
     *
     * @param  string $complaint  Keluhan pasien
     * @param  array  $location   ['lat' => float, 'lng' => float, 'address' => string]
     * @return array{urgency: string, recommendation: string, notes: string}
     */
    public function analyzeComplaint(string $complaint, array $location = []): array
    {
        $locationStr = ! empty($location['address'])
            ? "Alamat pasien: {$location['address']}."
            : 'Lokasi tidak tersedia.';

        $prompt = <<<PROMPT
Kamu adalah asisten triage medis untuk klinik akupunktur, bekam, dan baby spa.
Analisis keluhan pasien berikut dan berikan rekomendasi layanan.

{$locationStr}
Keluhan: {$complaint}

Kembalikan HANYA objek JSON valid (tanpa teks lain, tanpa markdown):
{
  "urgency":        "Tingkat urgensi: rendah / sedang / tinggi",
  "recommendation": "Rekomendasi layanan dan waktu kunjungan yang tepat.",
  "notes":          "Catatan tambahan untuk terapis (jika ada)."
}
PROMPT;

        try {
            $response = $this->generateContent($prompt);
            $text     = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $text     = preg_replace('/```json\s*/i', '', $text);
            $text     = preg_replace('/```\s*/i', '', $text);
            $text     = trim($text);

            $data = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
                return ['urgency' => '', 'recommendation' => '', 'notes' => 'Gagal mem-parsing respons AI.'];
            }

            return [
                'urgency'        => $data['urgency']        ?? '',
                'recommendation' => $data['recommendation'] ?? '',
                'notes'          => $data['notes']          ?? '',
            ];

        } catch (\Throwable $e) {
            Log::error('[GeminiService] analyzeComplaint error', ['message' => $e->getMessage()]);

            return ['urgency' => '', 'recommendation' => '', 'notes' => 'Error: ' . $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Panggil Gemini generateContent API.
     */
    protected function generateContent(string $prompt): array
    {
        $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(30)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature'     => 0.2,
                    'maxOutputTokens' => 2048,
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Gemini API error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Helper untuk return SOAP kosong saat error.
     */
    protected function emptysoap(string $errorMsg = ''): array
    {
        return [
            'subjective' => '',
            'objective'  => '',
            'assessment' => $errorMsg,
            'plan'       => '',
        ];
    }

    /**
     * Optimasi rute perjalanan homecare menggunakan Gemini.
     * Mengembalikan Markdown berisi saran rute dari AI.
     */
    public function optimizeRoute($appointments, string $branchAddress): string
    {
        $appointmentDetails = [];
        
        foreach ($appointments as $idx => $apt) {
            $patientName = $apt->patient->user->name ?? 'Pasien ' . ($idx + 1);
            $time = \Carbon\Carbon::parse($apt->scheduled_at)->format('H:i');
            $address = $apt->address_at_time ?? 'Alamat tidak diketahui';
            $appointmentDetails[] = "- [{$time}] {$patientName} - Alamat: {$address}";
        }

        $appointmentsList = implode("\n", $appointmentDetails);

        $prompt = <<<EOT
Kamu adalah sistem asisten logistik untuk sebuah klinik yang melayani panggilan ke rumah (homecare).
Tugasmu adalah menganalisis dan menyusun rute perjalanan yang paling efisien berdasarkan waktu kunjungan dan perkiraan lokasi dari teks alamat.

Titik Keberangkatan (Cabang Klinik):
{$branchAddress}

Daftar Jadwal Pasien Hari Ini:
{$appointmentsList}

INSTRUKSI:
1. Analisis teks alamat dan patokan lokasi dari masing-masing pasien.
2. Pertimbangkan jam jadwal kunjungan (pastikan logis dengan urutan).
3. Berikan saran rute perjalanan (Urutan Kunjungan) dari titik keberangkatan, ke pasien 1, pasien 2, dst.
4. Jelaskan secara singkat mengapa rute tersebut efisien (misalnya "karena Pasien A dan B berada di area yang searah", jika informasinya ada).
5. Tuliskan dalam bahasa Indonesia dengan format Markdown yang rapi (gunakan list/bullet points).
6. Jangan sertakan disclaimer yang berlebihan, fokus pada rute dan jadwalnya.
EOT;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/' . $this->model . ':generateContent?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2, // Rendah agar logis dan tidak berhalusinasi terlalu jauh
                'maxOutputTokens' => 1024,
            ]
        ]);

        if ($response->failed()) {
            Log::error('Gemini API Error (optimizeRoute): ' . $response->body());
            throw new \Exception('Gagal menghubungi Gemini API untuk optimasi rute.');
        }

        $responseData = $response->json();
        $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $text ?: 'Maaf, AI gagal memproses rekomendasi rute.';
    }
}

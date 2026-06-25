<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelfRegisterController extends Controller
{
    public function index()
    {
        $services = Service::whereRaw('CAST("is_active" AS boolean) = true')->get();
        $branches = Branch::whereRaw('CAST("is_active" AS boolean) = true')->get();

        return view('booking.self-register', compact('services', 'branches'));
    }

    /**
     * AJAX: Cek apakah nomor WA sudah terdaftar.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'whatsapp_number' => 'required|string|min:10|max:20',
        ]);

        $input = $request->input('whatsapp_number');
        $candidates = $this->getWaCandidates($input);

        $user = User::whereIn('whatsapp_number', $candidates)
            ->with('patient')
            ->first();

        if ($user && $user->patient) {
            return response()->json([
                'exists' => true,
                'patient' => [
                    'name' => $user->name,
                    'whatsapp_number' => $user->whatsapp_number,
                    'date_of_birth' => $user->patient->date_of_birth?->format('Y-m-d'),
                    'gender' => $user->patient->gender,
                    'default_address' => $user->patient->default_address,
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Proses booking mandiri.
     */
    public function store(Request $request, WhatsAppNotificationService $waService)
    {
        $request->validate([
            'whatsapp_number'     => 'required|string|min:10|max:20',
            // Data diri (wajib jika pasien baru)
            'name'                => 'nullable|string|max:255',
            'date_of_birth'       => 'nullable|date|before:today',
            'gender'              => 'nullable|in:male,female',
            'default_address'     => 'nullable|string|max:500',
            // Booking
            'service_id'          => 'required|exists:services,id',
            'branch_id'           => 'required|exists:branches,id',
            'scheduled_at'        => 'required|date|after:now',
            'service_location_type' => 'required|in:clinic,homecare',
            'address_at_time'     => 'nullable|required_if:service_location_type,homecare|string|max:500',
            'complaint_summary'   => 'nullable|string|max:1000',
            'ai_urgency'          => 'nullable|string|max:50',
            'ai_recommendation'   => 'nullable|string',
            'ai_notes'            => 'nullable|string',
        ]);

        $waNumber = $this->normalizeWaNumber($request->input('whatsapp_number'));
        $candidates = $this->getWaCandidates($request->input('whatsapp_number'));

        // Cek duplikat: nomor yang sama sudah booking di hari yang sama
        $today = now()->toDateString();
        $duplicateCheck = Appointment::whereHas('patient.user', function ($q) use ($candidates) {
            $q->whereIn('whatsapp_number', $candidates);
        })->whereDate('created_at', $today)->exists();

        if ($duplicateCheck) {
            return back()
                ->withInput()
                ->withErrors([
                    'whatsapp_number' => 'Anda sudah melakukan pendaftaran hari ini. Silakan hubungi klinik jika ingin mengubah jadwal.',
                ]);
        }

        // Validasi data diri untuk pasien baru
        $user = User::whereIn('whatsapp_number', $candidates)->first();
        if (!$user) {
            $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
            ]);
        }

        try {
            DB::beginTransaction();

            // Find atau buat User + Patient
            if (!$user) {
                $user = User::create([
                    'name' => $request->input('name'),
                    'whatsapp_number' => $waNumber,
                ]);
                $user->assignRole('patient');

                Patient::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $request->input('date_of_birth'),
                    'gender' => $request->input('gender'),
                    'default_address' => $request->input('default_address'),
                ]);
            } elseif (!$user->patient) {
                Patient::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $request->input('date_of_birth'),
                    'gender' => $request->input('gender'),
                    'default_address' => $request->input('default_address'),
                ]);
            }

            $patient = $user->patient;

            // Buat Appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'service_id' => $request->input('service_id'),
                'branch_id' => $request->input('branch_id'),
                'scheduled_at' => $request->input('scheduled_at'),
                'complaint_summary' => $request->input('complaint_summary'),
                'ai_urgency' => $request->input('ai_urgency'),
                'ai_recommendation' => $request->input('ai_recommendation'),
                'ai_notes' => $request->input('ai_notes'),
                'status' => Appointment::STATUS_SCHEDULED,
                'service_location_type' => $request->input('service_location_type'),
                'address_at_time' => $request->input('service_location_type') === 'homecare'
                    ? $request->input('address_at_time')
                    : null,
                'final_price' => Service::find($request->input('service_id'))->base_price ?? 0,
                'source' => Appointment::SOURCE_SELF_REGISTER,
            ]);

            DB::commit();

            // Kirim notifikasi WA ke Acufara (di luar transaksi)
            try {
                $waService->notifyNewBooking($appointment);
            } catch (\Throwable $e) {
                Log::error('[SELF REGISTER] Gagal kirim WA notification: ' . $e->getMessage());
            }

            return redirect()
                ->route('self-register.success', ['appointment' => $appointment->id])
                ->with('success', 'Pendaftaran Anda berhasil! Silakan tunggu konfirmasi dari pihak klinik.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SELF REGISTER] Gagal menyimpan booking: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['whatsapp_number' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Halaman sukses setelah booking.
     */
    public function success(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'branch', 'service']);

        return view('booking.self-register-success', compact('appointment'));
    }

    /**
     * Normalisasi nomor WA: hapus spasi, strip, dll. Pastikan format 62xxx.
     */
    private function normalizeWaNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    private function getWaCandidates(string $input): array
    {
        $clean = preg_replace('/[^0-9]/', '', $input);
        $normalized = $this->normalizeWaNumber($input);

        return array_unique([
            $input,
            $clean,
            $normalized,
            '0' . substr($normalized, 2),
        ]);
    }
}

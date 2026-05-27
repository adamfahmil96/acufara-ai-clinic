<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Service;
use App\Models\Branch;
use App\Models\Appointment;
use App\Models\SiteSetting;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create()
    {
        $settings = SiteSetting::first();
        $services = Service::all();
        $branches = Branch::all();

        return view('booking.create', compact('services', 'branches', 'settings'));
    }

    /**
     * Analisis keluhan pasien menggunakan Gemini AI.
     * Endpoint: POST /triage
     */
    public function triage(Request $request): JsonResponse
    {
        $request->validate([
            'complaint' => 'required|string|min:5|max:1000',
        ]);

        try {
            /** @var GeminiService $gemini */
            $gemini = app(GeminiService::class);
            $result = $gemini->analyzeComplaint($request->complaint);

            return response()->json([
                'success'        => true,
                'urgency'        => $result['urgency']        ?? '',
                'recommendation' => $result['recommendation'] ?? '',
                'notes'          => $result['notes']          ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id'        => 'required|exists:services,id',
            'branch_id'         => 'required|exists:branches,id',
            'scheduled_at'      => 'required|date|after:now',
            'complaint_summary' => 'nullable|string|max:1000',
            'ai_urgency'        => 'nullable|string|max:50',
            'ai_recommendation' => 'nullable|string',
            'ai_notes'          => 'nullable|string',
            'service_location_type' => 'required|in:clinic,homecare',
            'address_at_time'   => 'nullable|required_if:service_location_type,homecare|string|max:500',
        ]);

        $patient = Auth::user()->patient;

        if (!$patient) {
            return back()->with('error', 'Data pasien tidak ditemukan.');
        }

        Appointment::create([
            'patient_id'        => $patient->id,
            'service_id'        => $request->service_id,
            'branch_id'         => $request->branch_id,
            'scheduled_at'      => $request->scheduled_at,
            'complaint_summary' => $request->complaint_summary,
            'ai_urgency'        => $request->ai_urgency,
            'ai_recommendation' => $request->ai_recommendation,
            'ai_notes'          => $request->ai_notes,
            'status'            => Appointment::STATUS_SCHEDULED,
            'service_location_type' => $request->service_location_type,
            'address_at_time'   => $request->service_location_type === 'homecare' ? $request->address_at_time : null,
            'final_price'       => Service::find($request->service_id)->base_price ?? 0,
        ]);

        return redirect()->route('profile')->with('success', 'Jadwal Anda berhasil dibuat! Silakan tunggu konfirmasi dari pihak klinik.');
    }
}

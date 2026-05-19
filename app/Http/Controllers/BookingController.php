<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Branch;
use App\Models\Appointment;
use App\Models\SiteSetting;
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

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'branch_id' => 'required|exists:branches,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $patient = Auth::user()->patient;

        if (!$patient) {
            return back()->with('error', 'Data pasien tidak ditemukan.');
        }

        Appointment::create([
            'patient_id' => $patient->id,
            'service_id' => $request->service_id,
            'branch_id' => $request->branch_id,
            'scheduled_at' => $request->scheduled_at,
            'notes' => $request->notes,
            'status' => 'scheduled',
            'payment_status' => 'unpaid',
            'total_price' => Service::find($request->service_id)->price ?? 0,
        ]);

        return redirect()->route('profile')->with('success', 'Jadwal Anda berhasil dibuat! Silakan tunggu konfirmasi dari pihak klinik.');
    }
}

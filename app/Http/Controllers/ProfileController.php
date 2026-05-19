<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SiteSetting;

class ProfileController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        $user = Auth::user();
        $patient = $user->patient;

        // Ambil riwayat appointment pasien, diurutkan dari yang terbaru
        $appointments = [];
        if ($patient) {
            $appointments = $patient->appointments()->with(['service', 'branch'])->latest('scheduled_at')->get();
        }

        return view('profile', compact('user', 'patient', 'appointments', 'settings'));
    }

    public function edit()
    {
        $settings = SiteSetting::first();
        $user = Auth::user();
        $patient = $user->patient;

        return view('profile.edit', compact('user', 'patient', 'settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $patient = $user->patient;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'default_address' => 'nullable|string|max:1000',
        ]);

        // Update tabel users
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update tabel patients
        if ($patient) {
            $patient->update([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'default_address' => $request->default_address,
            ]);
        }

        return redirect()->route('profile')->with('success', 'Profil Anda berhasil diperbarui!');
    }
}

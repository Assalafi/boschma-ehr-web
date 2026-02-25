<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VitalSign;
use App\Models\Encounter;

class VitalSignController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $encounters = Encounter::with(['patient'])
            ->where('facility_id', $user->facility_id)
            ->where('status', 'pending')
            ->whereDoesntHave('vitalSigns')
            ->latest()
            ->get();

        return view('nurse.vital-signs.create', compact('encounters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'temperature' => 'nullable|numeric|between:30,45',
            'pulse_rate' => 'nullable|integer|between:30,200',
            'respiratory_rate' => 'nullable|integer|between:5,60',
            'blood_pressure_systolic' => 'nullable|integer|between:50,250',
            'blood_pressure_diastolic' => 'nullable|integer|between:30,150',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'weight' => 'nullable|numeric|between:1,500',
            'height' => 'nullable|numeric|between:30,250',
            'notes' => 'nullable|string'
        ]);

        VitalSign::create([
            'encounter_id' => $request->encounter_id,
            'recorded_by' => Auth::id(),
            'temperature' => $request->temperature,
            'pulse_rate' => $request->pulse_rate,
            'respiratory_rate' => $request->respiratory_rate,
            'blood_pressure_systolic' => $request->blood_pressure_systolic,
            'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
            'oxygen_saturation' => $request->oxygen_saturation,
            'weight' => $request->weight,
            'height' => $request->height,
            'notes' => $request->notes,
            'recorded_at' => now()
        ]);

        return redirect()->route('nurse.dashboard')
            ->with('success', 'Vital signs recorded successfully.');
    }

    public function show(VitalSign $vitalSign)
    {
        $vitalSign->load(['encounter.patient', 'recordedBy']);
        return view('nurse.vital-signs.show', compact('vitalSign'));
    }
}

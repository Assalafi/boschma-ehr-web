<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Models\Beneficiary;
use App\Models\Encounter;

class PatientController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $patients = collect();
        
        if ($query) {
            $patients = Beneficiary::where('fullname', 'like', "%{$query}%")
                ->orWhere('boschma_no', 'like', "%{$query}%")
                ->orWhere('nin', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->take(20)
                ->get();
        }

        return view('receptionist.patients.search', compact('patients', 'query'));
    }

    public function create()
    {
        return view('receptionist.patients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'program_id' => 'required|exists:programs,id',
            'complaint' => 'required|string'
        ]);

        $user = Auth::user();
        
        $encounter = Encounter::create([
            'beneficiary_id' => $request->beneficiary_id,
            'facility_id' => $user->facility_id,
            'program_id' => $request->program_id,
            'checked_in_by' => $user->id,
            'chief_complaint' => $request->complaint,
            'status' => 'pending',
            'checked_in_at' => now()
        ]);

        return redirect()->route('receptionist.dashboard')
            ->with('success', 'Patient checked in successfully. Encounter ID: ' . $encounter->id);
    }

    public function show(Patient $patient)
    {
        $patient->load(['beneficiary', 'encounters.consultations']);
        return view('receptionist.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('receptionist.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'phone' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $patient->update($request->only(['phone', 'address']));

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }
}

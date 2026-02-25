<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClinicalConsultation;
use App\Models\Encounter;

class ConsultationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $consultations = ClinicalConsultation::with(['encounter.patient', 'doctor'])
            ->whereHas('encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            })
            ->latest()
            ->paginate(15);

        return view('doctor.consultations.index', compact('consultations'));
    }

    public function create(Encounter $encounter)
    {
        $encounter->load(['patient', 'vitalSigns']);
        return view('doctor.consultations.create', compact('encounter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'presenting_complaint' => 'required|string',
            'history_of_present_illness' => 'nullable|string',
            'examination_findings' => 'nullable|string',
            'provisional_diagnosis' => 'required|string',
            'treatment_plan' => 'nullable|string'
        ]);

        $consultation = ClinicalConsultation::create([
            'encounter_id' => $request->encounter_id,
            'doctor_id' => Auth::id(),
            'presenting_complaint' => $request->presenting_complaint,
            'history_of_present_illness' => $request->history_of_present_illness,
            'examination_findings' => $request->examination_findings,
            'provisional_diagnosis' => $request->provisional_diagnosis,
            'treatment_plan' => $request->treatment_plan,
            'status' => 'in_progress'
        ]);

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation started successfully.');
    }

    public function show(ClinicalConsultation $consultation)
    {
        $consultation->load(['encounter.patient', 'encounter.vitalSigns', 'diagnoses', 'prescriptions', 'investigations']);
        return view('doctor.consultations.show', compact('consultation'));
    }

    public function edit(ClinicalConsultation $consultation)
    {
        $consultation->load(['encounter.patient']);
        return view('doctor.consultations.edit', compact('consultation'));
    }

    public function update(Request $request, ClinicalConsultation $consultation)
    {
        $request->validate([
            'presenting_complaint' => 'required|string',
            'examination_findings' => 'nullable|string',
            'provisional_diagnosis' => 'required|string',
            'treatment_plan' => 'nullable|string',
            'status' => 'nullable|in:in_progress,completed'
        ]);

        $consultation->update($request->only([
            'presenting_complaint',
            'examination_findings', 
            'provisional_diagnosis',
            'treatment_plan',
            'status'
        ]));

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation updated successfully.');
    }
}

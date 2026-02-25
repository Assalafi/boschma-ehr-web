<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Investigation;
use App\Models\Encounter;

class InvestigationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $investigations = Investigation::with(['encounter.patient', 'consultation'])
            ->whereHas('encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            })
            ->latest()
            ->paginate(15);

        return view('investigations.index', compact('investigations'));
    }

    public function create(Encounter $encounter)
    {
        $encounter->load(['patient']);
        return view('investigations.create', compact('encounter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'consultation_id' => 'nullable|exists:clinical_consultations,id',
            'investigation_type' => 'required|string',
            'investigation_name' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        Investigation::create([
            'encounter_id' => $request->encounter_id,
            'consultation_id' => $request->consultation_id,
            'investigation_type' => $request->investigation_type,
            'investigation_name' => $request->investigation_name,
            'notes' => $request->notes,
            'ordered_by' => Auth::id(),
            'status' => 'pending'
        ]);

        return redirect()->route('investigations.index')
            ->with('success', 'Investigation ordered successfully.');
    }

    public function show(Investigation $investigation)
    {
        $investigation->load(['encounter.patient', 'consultation']);
        return view('investigations.show', compact('investigation'));
    }

    public function storeResults(Request $request, Investigation $investigation)
    {
        $request->validate([
            'result' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $investigation->update([
            'result' => $request->result,
            'notes' => $request->notes,
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id()
        ]);

        return redirect()->route('investigations.show', $investigation)
            ->with('success', 'Results recorded successfully.');
    }
}

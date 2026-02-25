<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prescription;

class PrescriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $prescriptions = Prescription::with(['consultation.encounter.patient', 'items.drug'])
            ->whereHas('consultation.encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            })
            ->latest()
            ->paginate(15);

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['consultation.encounter.patient', 'items.drug', 'items.dispensations']);
        return view('prescriptions.show', compact('prescription'));
    }

    public function dispense(Request $request, Prescription $prescription)
    {
        $prescription->update(['status' => 'dispensed']);
        return redirect()->back()->with('success', 'Prescription marked as dispensed.');
    }
}

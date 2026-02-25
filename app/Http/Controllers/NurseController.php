<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Encounter;
use App\Models\VitalSign;
use App\Models\PrescriptionItem;
use App\Models\DrugAdministration;

class NurseController extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        
        // Pending triage (registered but no vitals)
        $pendingTriage = Encounter::where('facility_id', $facilityId)
            ->where('status', Encounter::STATUS_REGISTERED)
            ->whereDoesntHave('vitalSigns')
            ->count();
            
        // Completed triage today
        $completedToday = VitalSign::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereDate('created_at', today())
            ->count();
            
        // Critical patients today (Red priority)
        $criticalToday = VitalSign::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('overall_priority', 'Red')
            ->whereDate('created_at', today())
            ->count();
            
        // Pending drug administrations
        $pendingAdministrations = PrescriptionItem::whereHas('prescription.consultation.encounter', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })->where('dispensing_status', PrescriptionItem::STATUS_DISPENSED)
          ->count();
          
        // Recent triage activity (last 10)
        $recentTriage = VitalSign::with(['encounter.patient.beneficiary', 'takenBy'])
            ->whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->latest()
            ->take(10)
            ->get();
            
        // Priority distribution for today
        $priorityStats = VitalSign::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereDate('created_at', today())
            ->selectRaw('overall_priority, count(*) as count')
            ->groupBy('overall_priority')
            ->pluck('count', 'overall_priority')
            ->toArray();

        return view('nurse.dashboard', compact(
            'pendingTriage',
            'completedToday',
            'criticalToday',
            'pendingAdministrations',
            'recentTriage',
            'priorityStats'
        ));
    }

    /**
     * Triage - List patients awaiting triage (no vitals yet)
     */
    public function triageIndex()
    {
        $user = Auth::user();
        $encounters = Encounter::with(['patient.beneficiary', 'program', 'vitalSigns'])
            ->where('facility_id', $user->facility_id)
            ->where('status', Encounter::STATUS_REGISTERED)
            ->whereDoesntHave('vitalSigns')
            ->latest()
            ->paginate(15);

        return view('nurse.triage.index', compact('encounters'));
    }

    /**
     * Triage - Create form (record vitals + complete triage)
     */
    public function triageCreate(Encounter $encounter)
    {
        $encounter->load(['patient.beneficiary', 'program']);
        return view('nurse.triage.create', compact('encounter'));
    }

    /**
     * Triage - Store vitals and complete triage
     */
    public function triageStore(Request $request, Encounter $encounter)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:30|max:45',
            'blood_pressure_systolic' => 'required|integer|min:50|max:250',
            'blood_pressure_diastolic' => 'required|integer|min:30|max:150',
            'pulse_rate' => 'required|integer|min:30|max:200',
            'respiration_rate' => 'required|integer|min:5|max:60',
            'spo2' => 'required|integer|min:50|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:30|max:250',
            'overall_priority' => 'required|in:Red,Yellow,Green',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Create vital signs record
        VitalSign::create([
            'encounter_id' => $encounter->id,
            'taken_by' => Auth::id(),
            'temperature' => $request->temperature,
            'blood_pressure_systolic' => $request->blood_pressure_systolic,
            'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
            'pulse_rate' => $request->pulse_rate,
            'respiration_rate' => $request->respiration_rate,
            'spo2' => $request->spo2,
            'weight' => $request->weight,
            'height' => $request->height,
            'overall_priority' => $request->overall_priority,
        ]);

        // Update encounter status to Triaged
        $encounter->update([
            'status' => Encounter::STATUS_TRIAGED,
        ]);

        // Log the triage action
        \App\Models\EncounterAction::create([
            'encounter_id' => $encounter->id,
            'user_id' => Auth::id(),
            'action_type' => \App\Enums\ActionType::TRIAGE,
            'description' => 'Patient triaged with ' . $request->overall_priority . ' priority by ' . Auth::user()->name,
            'action_time' => now(),
        ]);

        $message = 'Patient triaged successfully.';
        if ($request->overall_priority === 'Red') {
            $message = 'Patient triaged as CRITICAL (Red). Doctor notified for urgent attention.';
        }

        return redirect()->route('nurse.triage.index')
            ->with('success', $message);
    }

    /**
     * Triage - View completed triage details
     */
    public function triageShow(Encounter $encounter)
    {
        $encounter->load(['patient.beneficiary', 'vitalSigns.takenBy', 'program']);
        return view('nurse.triage.show', compact('encounter'));
    }

    /**
     * Triage History - List all completed triage records
     */
    public function triageHistory(Request $request)
    {
        $user = Auth::user();
        $query = VitalSign::with(['encounter.patient.beneficiary', 'encounter.program', 'takenBy'])
            ->whereHas('encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            });
            
        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('overall_priority', $request->priority);
        }
        
        // Search by patient name or BOSCHMA
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('encounter.patient.beneficiary', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('boschma_no', 'like', "%{$search}%");
            });
        }
        
        $vitalSigns = $query->latest()->paginate(20);
        
        return view('nurse.triage.history', compact('vitalSigns'));
    }

    /**
     * Triage - Edit form
     */
    public function triageEdit(Encounter $encounter)
    {
        $encounter->load(['patient.beneficiary', 'vitalSigns', 'program']);
        $vitalSign = $encounter->vitalSigns->first();
        
        if (!$vitalSign) {
            return redirect()->route('nurse.triage.create', $encounter)
                ->with('error', 'No vital signs recorded. Please complete triage first.');
        }
        
        return view('nurse.triage.edit', compact('encounter', 'vitalSign'));
    }

    /**
     * Triage - Update vitals
     */
    public function triageUpdate(Request $request, Encounter $encounter)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:30|max:45',
            'blood_pressure_systolic' => 'required|integer|min:50|max:250',
            'blood_pressure_diastolic' => 'required|integer|min:30|max:150',
            'pulse_rate' => 'required|integer|min:30|max:200',
            'respiration_rate' => 'required|integer|min:5|max:60',
            'spo2' => 'required|integer|min:50|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:30|max:250',
            'overall_priority' => 'required|in:Red,Yellow,Green',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vitalSign = $encounter->vitalSigns->first();
        
        if (!$vitalSign) {
            return redirect()->route('nurse.triage.index')
                ->with('error', 'Vital signs record not found.');
        }

        $vitalSign->update([
            'temperature' => $request->temperature,
            'blood_pressure_systolic' => $request->blood_pressure_systolic,
            'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
            'pulse_rate' => $request->pulse_rate,
            'respiration_rate' => $request->respiration_rate,
            'spo2' => $request->spo2,
            'weight' => $request->weight,
            'height' => $request->height,
            'overall_priority' => $request->overall_priority,
        ]);

        // Log the update action
        \App\Models\EncounterAction::create([
            'encounter_id' => $encounter->id,
            'user_id' => Auth::id(),
            'action_type' => \App\Enums\ActionType::TRIAGE,
            'description' => 'Vital signs updated by ' . Auth::user()->name,
            'action_time' => now(),
        ]);

        return redirect()->route('nurse.triage.show', $encounter)
            ->with('success', 'Vital signs updated successfully.');
    }

    /**
     * Triage Report
     */
    public function triageReport(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->format('Y-m-d'));
        
        $query = VitalSign::with(['encounter.patient.beneficiary', 'takenBy'])
            ->whereHas('encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Summary stats
        $totalTriage = $query->count();
        $priorityBreakdown = (clone $query)->selectRaw('overall_priority, count(*) as count')
            ->groupBy('overall_priority')
            ->pluck('count', 'overall_priority')
            ->toArray();
            
        // Daily breakdown
        $dailyStats = (clone $query)->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
            
        // By nurse
        $byNurse = (clone $query)->selectRaw('taken_by, count(*) as count')
            ->groupBy('taken_by')
            ->with('takenBy')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->takenBy->name ?? 'Unknown' => $item->count];
            })
            ->toArray();
        
        // Detailed records for export
        $records = $query->latest()->get();
        
        return view('nurse.reports.triage', compact(
            'startDate', 
            'endDate', 
            'totalTriage', 
            'priorityBreakdown', 
            'dailyStats',
            'byNurse',
            'records'
        ));
    }

    public function drugAdministrationIndex()
    {
        $user = Auth::user();
        $items = PrescriptionItem::with(['prescription.consultation.encounter.patient', 'drug', 'administrations'])
            ->whereHas('prescription.consultation.encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            })
            ->where('dispensing_status', PrescriptionItem::STATUS_DISPENSED)
            ->latest()
            ->paginate(15);

        return view('nurse.drug-administration.index', compact('items'));
    }

    public function administerDrug(PrescriptionItem $prescriptionItem)
    {
        $prescriptionItem->load(['prescription.consultation.encounter.patient', 'drug', 'administrations']);
        return view('nurse.drug-administration.administer', compact('prescriptionItem'));
    }

    public function storeAdministration(Request $request, PrescriptionItem $prescriptionItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        DrugAdministration::create([
            'prescription_item_id' => $prescriptionItem->id,
            'administering_officer_id' => Auth::id(),
            'dose_given' => $request->quantity,
            'administration_date_time' => now(),
            'notes' => $request->notes
        ]);

        return redirect()->route('nurse.drug-administration.index')
            ->with('success', 'Drug administration recorded successfully.');
    }
}

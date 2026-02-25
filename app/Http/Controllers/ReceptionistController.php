<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Beneficiary;
use App\Models\Program;
use App\Models\EncounterAction;
use App\Enums\ActionType;
use App\Enums\EncounterStatus;
use Carbon\Carbon;

class ReceptionistController extends Controller
{
    protected function getFacilityId()
    {
        return Auth::user()->facility_id;
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $facilityId = $this->getFacilityId();
        $today = Carbon::today();
        
        // Today's statistics
        $todayEncounters = Encounter::where('facility_id', $facilityId)
            ->whereDate('created_at', $today)
            ->count();
            
        $pendingEncounters = Encounter::where('facility_id', $facilityId)
            ->where('status', Encounter::STATUS_REGISTERED)
            ->count();
        
        $awaitingTriage = Encounter::where('facility_id', $facilityId)
            ->where('status', Encounter::STATUS_REGISTERED)
            ->count();
            
        $completedToday = Encounter::where('facility_id', $facilityId)
            ->whereDate('created_at', $today)
            ->where('status', Encounter::STATUS_COMPLETED)
            ->count();
        
        // Weekly statistics
        $weekStart = Carbon::now()->startOfWeek();
        $weeklyEncounters = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $weekStart)
            ->count();
        
        // Monthly statistics
        $monthStart = Carbon::now()->startOfMonth();
        $monthlyEncounters = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $monthStart)
            ->count();
            
        // Referral patients awaiting processing
        $pendingReferrals = Encounter::where('facility_id', $facilityId)
            ->where('mode_of_entry', 'Referral')
            ->whereIn('status', [Encounter::STATUS_PENDING, Encounter::STATUS_REGISTERED])
            ->count();
            
        // Recent encounters (last 10)
        $recentEncounters = Encounter::with(['patient.beneficiary', 'program'])
            ->where('facility_id', $facilityId)
            ->latest()
            ->take(10)
            ->get();
        
        // Today's encounter queue
        $encounterQueue = Encounter::with(['patient.beneficiary', 'program'])
            ->where('facility_id', $facilityId)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('receptionist.dashboard', compact(
            'todayEncounters',
            'pendingEncounters',
            'awaitingTriage',
            'completedToday',
            'weeklyEncounters',
            'monthlyEncounters',
            'pendingReferrals',
            'recentEncounters',
            'encounterQueue'
        ));
    }

    /**
     * Search for beneficiaries belonging to this facility
     */
    public function searchBeneficiary(Request $request)
    {
        $facilityId = $this->getFacilityId();
        $query = $request->get('q');
        
        $beneficiaries = collect();
        
        if ($query && strlen($query) >= 2) {
            $beneficiaries = Beneficiary::where('facility_id', $facilityId)
                ->where(function($q) use ($query) {
                    $q->where('fullname', 'like', "%{$query}%")
                      ->orWhere('boschma_no', 'like', "%{$query}%")
                      ->orWhere('nin', 'like', "%{$query}%")
                      ->orWhere('phone_no', 'like', "%{$query}%");
                })
                ->with(['patient', 'program'])
                ->take(20)
                ->get();
        }
        
        if ($request->ajax()) {
            return response()->json($beneficiaries);
        }
        
        return view('receptionist.beneficiaries.search', compact('beneficiaries', 'query'));
    }

    /**
     * Show beneficiary details for check-in
     */
    public function showBeneficiary(Beneficiary $beneficiary)
    {
        $facilityId = $this->getFacilityId();
        
        // Ensure beneficiary belongs to this facility
        if ($beneficiary->facility_id !== $facilityId) {
            abort(403, 'This beneficiary does not belong to your facility.');
        }
        
        $beneficiary->load(['patient', 'program', 'spouse', 'children']);
        
        // Get recent encounters and check for ongoing encounter
        $recentEncounters = [];
        $ongoingEncounter = null;
        
        if ($beneficiary->patient) {
            $recentEncounters = Encounter::where('patient_id', $beneficiary->patient->id)
                ->with(['facility', 'program'])
                ->latest()
                ->take(5)
                ->get();
            
            // Check for ongoing (not completed/cancelled) encounter
            $ongoingEncounter = Encounter::where('patient_id', $beneficiary->patient->id)
                ->whereNotIn('status', [Encounter::STATUS_COMPLETED, Encounter::STATUS_CANCELLED])
                ->with(['facility', 'program'])
                ->latest()
                ->first();
        }
        
        $programs = Program::all();
        
        return view('receptionist.beneficiaries.show', compact('beneficiary', 'recentEncounters', 'ongoingEncounter', 'programs'));
    }

    /**
     * Check-in a beneficiary and create an encounter
     */
    public function checkIn(Request $request, Beneficiary $beneficiary)
    {
        $facilityId = $this->getFacilityId();
        
        // Ensure beneficiary belongs to this facility
        if ($beneficiary->facility_id !== $facilityId) {
            abort(403, 'This beneficiary does not belong to your facility.');
        }
        
        $request->validate([
            'nature_of_visit' => 'required|string',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        try {
            // Get or create patient record for this beneficiary
            $patient = $beneficiary->patient;
            $isNewPatient = false;
            
            if (!$patient) {
                $isNewPatient = true;
                
                // Generate unique file number
                do {
                    $fileNumber = 'PAT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                } while (Patient::where('file_number', $fileNumber)->exists());
                
                $patient = Patient::create([
                    'file_number' => $fileNumber,
                    'enrollee_number' => $beneficiary->boschma_no,
                    'enrollee_type' => 'beneficiary',
                ]);
                $beneficiary->update(['patient_id' => $patient->id]);
            }
            
            // Create encounter
            $encounter = Encounter::create([
                'patient_id' => $patient->id,
                'facility_id' => $facilityId,
                'program_id' => $beneficiary->program_id, // Use beneficiary's program
                'nature_of_visit' => $request->nature_of_visit,
                'mode_of_entry' => 'Walk-in', // Default mode of entry
                'reason_for_visit' => $request->chief_complaint,
                'status' => Encounter::STATUS_REGISTERED,
                'officer_in_charge_id' => Auth::id(),
                'visit_date' => $request->visit_date ?? now(),
            ]);
            
            // Log the registration action
            EncounterAction::create([
                'encounter_id' => $encounter->id,
                'user_id' => Auth::id(),
                'action_type' => ActionType::REGISTRATION,
                'description' => 'Patient checked in by ' . Auth::user()->name,
                'action_time' => now(),
            ]);
            
            DB::commit();
            
            $successMessage = $beneficiary->fullname . ' checked in successfully.';
            if ($isNewPatient) {
                $successMessage .= ' New patient file ' . $patient->file_number . ' created.';
            }
            
            return redirect()->route('receptionist.encounters.show', $encounter)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to check in patient: ' . $e->getMessage());
        }
    }

    /**
     * List all encounters for today
     */
    public function encounters(Request $request)
    {
        $facilityId = $this->getFacilityId();
        $date = $request->get('date', today()->format('Y-m-d'));
        $status = $request->get('status');
        
        $query = Encounter::with(['patient.beneficiary', 'program', 'officerInCharge'])
            ->where('facility_id', $facilityId)
            ->whereDate('created_at', $date);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $encounters = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $statuses = [
            Encounter::STATUS_REGISTERED,
            Encounter::STATUS_TRIAGED,
            Encounter::STATUS_IN_CONSULTATION,
            Encounter::STATUS_AWAITING_LAB,
            Encounter::STATUS_AWAITING_PHARMACY,
            Encounter::STATUS_COMPLETED,
            Encounter::STATUS_CANCELLED,
        ];
        
        return view('receptionist.encounters.index', compact('encounters', 'date', 'status', 'statuses'));
    }

    /**
     * Show single encounter details
     */
    public function showEncounter(Encounter $encounter)
    {
        $facilityId = $this->getFacilityId();
        
        if ($encounter->facility_id !== $facilityId) {
            abort(403, 'This encounter does not belong to your facility.');
        }
        
        $encounter->load([
            'patient.beneficiary',
            'program',
            'facility',
            'officerInCharge',
            'vitalSigns',
            'actions.user',
        ]);
        
        return view('receptionist.encounters.show', compact('encounter'));
    }

    /**
     * Forward encounter to nurse (triage stage)
     */
    public function forwardToNurse(Request $request, Encounter $encounter)
    {
        $facilityId = $this->getFacilityId();
        
        if ($encounter->facility_id !== $facilityId) {
            abort(403, 'This encounter does not belong to your facility.');
        }
        
        if ($encounter->status !== Encounter::STATUS_REGISTERED) {
            return back()->with('error', 'Only registered encounters can be forwarded to nurse.');
        }
        
        $encounter->update([
            'status' => Encounter::STATUS_REGISTERED,
        ]);
        
        EncounterAction::create([
            'encounter_id' => $encounter->id,
            'user_id' => Auth::id(),
            'action_type' => ActionType::REGISTRATION,
            'description' => 'Encounter forwarded to triage by ' . Auth::user()->name,
            'action_time' => now(),
        ]);
        
        return redirect()->route('receptionist.encounters.index')
            ->with('success', 'Patient forwarded to nurse for triage.');
    }

    /**
     * Patient/Encounter history
     */
    public function history(Request $request)
    {
        $facilityId = $this->getFacilityId();
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = Encounter::with(['patient.beneficiary', 'program'])
            ->where('facility_id', $facilityId);
        
        if ($search) {
            $query->whereHas('patient.beneficiary', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('boschma_no', 'like', "%{$search}%");
            });
        }
        
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $encounters = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('receptionist.history.index', compact('encounters', 'search', 'dateFrom', 'dateTo'));
    }

    /**
     * Referrals management
     */
    public function referrals(Request $request)
    {
        $facilityId = $this->getFacilityId();
        $status = $request->get('status', 'pending');
        
        $query = Encounter::with(['patient.beneficiary', 'program'])
            ->where('facility_id', $facilityId)
            ->where('mode_of_entry', 'Referral');
        
        if ($status === 'pending') {
            $query->whereIn('status', [Encounter::STATUS_PENDING, Encounter::STATUS_REGISTERED]);
        } elseif ($status === 'processed') {
            $query->whereNotIn('status', [Encounter::STATUS_PENDING, Encounter::STATUS_REGISTERED]);
        }
        
        $referrals = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('receptionist.referrals.index', compact('referrals', 'status'));
    }

    /**
     * Reports overview
     */
    public function reports(Request $request)
    {
        $facilityId = $this->getFacilityId();
        $period = $request->get('period', 'today');
        
        $dateFrom = match($period) {
            'today' => today(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => today(),
        };
        
        // Encounter statistics
        $totalEncounters = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $dateFrom)
            ->count();
        
        $encountersByStatus = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $dateFrom)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        $encountersByNature = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $dateFrom)
            ->select('nature_of_visit', DB::raw('count(*) as count'))
            ->groupBy('nature_of_visit')
            ->get()
            ->pluck('count', 'nature_of_visit');
        
        $encountersByEntry = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $dateFrom)
            ->select('mode_of_entry', DB::raw('count(*) as count'))
            ->groupBy('mode_of_entry')
            ->get()
            ->pluck('count', 'mode_of_entry');
        
        // Daily breakdown for charts
        $dailyEncounters = Encounter::where('facility_id', $facilityId)
            ->where('created_at', '>=', $dateFrom)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        
        return view('receptionist.reports.index', compact(
            'period',
            'totalEncounters',
            'encountersByStatus',
            'encountersByNature',
            'encountersByEntry',
            'dailyEncounters'
        ));
    }

    /**
     * Cancel an encounter
     */
    public function cancelEncounter(Request $request, Encounter $encounter)
    {
        $facilityId = $this->getFacilityId();
        
        if ($encounter->facility_id !== $facilityId) {
            abort(403, 'This encounter does not belong to your facility.');
        }
        
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);
        
        $encounter->update([
            'status' => Encounter::STATUS_CANCELLED,
            'cancellation_reason' => $request->cancellation_reason,
        ]);
        
        EncounterAction::create([
            'encounter_id' => $encounter->id,
            'user_id' => Auth::id(),
            'action_type' => ActionType::REGISTRATION,
            'description' => 'Encounter cancelled: ' . $request->cancellation_reason,
            'action_time' => now(),
        ]);
        
        return redirect()->route('receptionist.encounters.index')
            ->with('success', 'Encounter cancelled successfully.');
    }
}

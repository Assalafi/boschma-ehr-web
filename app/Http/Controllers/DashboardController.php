<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Encounter;
use App\Models\Beneficiary;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard - redirects based on role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }
        
        // CHECK FOR DUAL ROLE FIRST
        if ($user->isNurse() && $user->isReceptionist()) {
            return $this->dualNurseReceptionistDashboard($user);
        }
        
        if ($user->isNurse()) {
            return redirect()->route('nurse.dashboard');
        }
        
        if ($user->isPharmacist()) {
            return redirect()->route('pharmacy.dashboard');
        }
        
        if ($user->isLabTechnician()) {
            return redirect()->route('laboratory.dashboard');
        }
        
        if ($user->isReceptionist()) {
            return redirect()->route('receptionist.dashboard');
        }

        if ($user->isRadiologist()) {
            return redirect()->route('radiologist.dashboard');
        }
        
        // Default dashboard view
        return view('dashboard.index', [
            'user' => $user,
        ]);
    }

    private function dualNurseReceptionistDashboard($user)
    {
        $facilityId = $user->facility_id;
        $today = \Carbon\Carbon::today();
        
        // ================= RECEPTIONIST STATS =================
        $todayEncounters = Encounter::where('facility_id', $facilityId)->whereDate('created_at', $today)->count();
        $awaitingTriage = Encounter::where('facility_id', $facilityId)->where('status', Encounter::STATUS_REGISTERED)->count();
        $pendingReferrals = \Illuminate\Support\Facades\DB::table('service_referrals')
            ->where('to_facility_id', $facilityId)->where('status', 'pending')->count();
        
        $encounterQueue = Encounter::with(['patient.beneficiary', 'program'])
            ->where('facility_id', $facilityId)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // ================= NURSE STATS =================
        $pendingTriage = $awaitingTriage; // Same as awaiting triage for receptionist
        
        $completedTodayTriage = \App\Models\VitalSign::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })->whereDate('created_at', $today)->count();
            
        $criticalToday = \App\Models\VitalSign::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })->where('overall_priority', 'Red')->whereDate('created_at', $today)->count();
            
        $nurseWardIds = $user->wards()->pluck('wards.id')->toArray();
        $currentAdmissions = \App\Models\Admission::whereHas('encounter', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })->where('is_active', true)
        ->when(!empty($nurseWardIds), function($q) use ($nurseWardIds) {
            $q->whereIn('ward_id', $nurseWardIds);
        })->count();
        
        $recentTriage = \App\Models\VitalSign::with(['encounter.patient.beneficiary', 'takenBy'])
            ->whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })->latest()->take(5)->get();

        return view('dashboard.dual_nurse_receptionist', compact(
            'todayEncounters', 'awaitingTriage', 'pendingReferrals', 'encounterQueue',
            'pendingTriage', 'completedTodayTriage', 'criticalToday', 'currentAdmissions', 'recentTriage'
        ));
    }
}

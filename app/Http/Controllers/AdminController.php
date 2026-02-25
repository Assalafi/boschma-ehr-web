<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Encounter;
use App\Models\Beneficiary;
use App\Models\User;
use App\Models\Facility;

class AdminController extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        
        $totalEncounters = Encounter::where('facility_id', $facilityId)->count();
        $todayEncounters = Encounter::where('facility_id', $facilityId)
            ->whereDate('created_at', today())
            ->count();
        $totalBeneficiaries = Beneficiary::where('facility_id', $facilityId)->count();
        $totalStaff = User::where('facility_id', $facilityId)->count();
        
        $recentEncounters = Encounter::with(['patient', 'program'])
            ->where('facility_id', $facilityId)
            ->latest()
            ->take(10)
            ->get();
            
        $monthlyStats = Encounter::where('facility_id', $facilityId)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'totalEncounters',
            'todayEncounters',
            'totalBeneficiaries',
            'totalStaff',
            'recentEncounters',
            'monthlyStats'
        ));
    }
}

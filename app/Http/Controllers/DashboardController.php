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
        
        // Default dashboard view
        return view('dashboard.index', [
            'user' => $user,
        ]);
    }
}

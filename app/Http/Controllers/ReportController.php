<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\Investigation;
use App\Models\Drug;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function encounters(Request $request)
    {
        $user = Auth::user();
        $query = Encounter::with(['patient', 'program', 'facility'])
            ->where('facility_id', $user->facility_id);

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $encounters = $query->latest()->paginate(20);
        return view('admin.reports.encounters', compact('encounters'));
    }

    public function prescriptions(Request $request)
    {
        $user = Auth::user();
        $query = Prescription::with(['consultation.encounter.patient', 'items.drug'])
            ->whereHas('consultation.encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            });

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $prescriptions = $query->latest()->paginate(20);
        return view('admin.reports.prescriptions', compact('prescriptions'));
    }

    public function investigations(Request $request)
    {
        $user = Auth::user();
        $query = Investigation::with(['encounter.patient'])
            ->whereHas('encounter', function($q) use ($user) {
                $q->where('facility_id', $user->facility_id);
            });

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $investigations = $query->latest()->paginate(20);
        return view('admin.reports.investigations', compact('investigations'));
    }

    public function stock()
    {
        $user = Auth::user();
        $drugs = Drug::with(['stocks' => function($q) use ($user) {
            $q->where('facility_id', $user->facility_id)->where('status', 'active');
        }])
        ->where('facility_id', $user->facility_id)
        ->get();

        return view('admin.reports.stock', compact('drugs'));
    }
}

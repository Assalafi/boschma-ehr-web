@extends('layouts.app')

@section('title', 'Dual Role Dashboard (Reception & Nurse)')

@section('content')
<style>
:root{--bs-primary:#016634;--bs-primary-rgb:1,102,52;--bs-link-color:#016634;--bs-link-hover-color:#01552b}
.page-header{background:linear-gradient(135deg,#01552b,#016634);border-radius:16px;padding:20px 28px;color:#fff;margin-bottom:24px}
.page-header h3{font-weight:700;letter-spacing:-.3px;color:#fff}
.page-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.page-header .breadcrumb-item.active{color:#fff}
.page-header .breadcrumb{font-size:12px}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
.section-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.section-title .material-symbols-outlined { color: #016634; }
</style>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h3 class="mb-0">Dual Role Dashboard</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item active" aria-current="page">Receptionist & Nurse</li>
        </ol>
    </nav>
</div>

<!-- ======================= RECEPTIONIST OVERVIEW ======================= -->
<h5 class="section-title"><span class="material-symbols-outlined">desk</span> Receptionist Overview</h5>
<div class="row">
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-primary bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Today's Encounters</span>
                        <h4 class="mb-0 fw-semibold">{{ $todayEncounters ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-primary fs-1">calendar_today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-warning bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Awaiting Triage</span>
                        <h4 class="mb-0 fw-semibold">{{ $awaitingTriage ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-warning fs-1">pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-info bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Pending Referrals</span>
                        <h4 class="mb-0 fw-semibold">{{ $pendingReferrals ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-info fs-1">swap_horiz</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions for Receptionist -->
    <div class="col-xxl-3 col-sm-6">
        <a href="{{ route('receptionist.beneficiaries.search') }}" class="btn btn-outline-primary w-100 py-3 mb-2" style="height: calc(50% - 4px); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span class="material-symbols-outlined">search</span> Search Beneficiary
        </a>
        <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-primary w-100 py-3 mt-2" style="height: calc(50% - 4px); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span class="material-symbols-outlined">list_alt</span> Today's Queue
        </a>
    </div>
</div>


<!-- ======================= NURSE OVERVIEW ======================= -->
<h5 class="section-title mt-2"><span class="material-symbols-outlined">medical_services</span> Nurse Station Overview</h5>
<div class="row">
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-warning bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Pending Triage</span>
                        <h4 class="mb-0 fw-semibold">{{ $pendingTriage ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-warning fs-1">hourglass_empty</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-success bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Vitals Taken Today</span>
                        <h4 class="mb-0 fw-semibold">{{ $completedTodayTriage ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-success fs-1">monitor_heart</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-danger bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Critical Today</span>
                        <h4 class="mb-0 fw-semibold">{{ $criticalToday ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-danger fs-1">warning</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions for Nurse -->
    <div class="col-xxl-3 col-sm-6">
        <a href="{{ route('nurse.triage.index') }}" class="btn btn-warning text-dark w-100 py-3 mb-2 border-0" style="height: calc(50% - 4px); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span class="material-symbols-outlined">monitor_heart</span> Start Triage
        </a>
        <a href="{{ route('nurse.admitted.index') }}" class="btn btn-info text-dark w-100 py-3 mt-2 border-0" style="height: calc(50% - 4px); display: flex; align-items: center; justify-content: center; gap: 8px;">
            <span class="material-symbols-outlined">local_hospital</span> Admitted Patients ({{ $currentAdmissions ?? 0 }})
        </a>
    </div>
</div>

<!-- ======================= ACTIVE LISTS ======================= -->
<div class="row">
    <!-- Today's Encounters (Receptionist View) -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-3 mb-4 h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                <h5 class="mb-0 fw-semibold">Reception: Today's Queue</h5>
                <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(collect($encounterQueue ?? [])->take(5) as $encounter)
                            <tr>
                                <td>{{ $encounter->created_at->format('H:i') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $encounter->patient->enrollee_number ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @php
                                        $color = match($encounter->status) {
                                            'Registered' => 'info',
                                            'Triaged' => 'warning',
                                            'Completed' => 'success',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $encounter->status }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('receptionist.encounters.show', $encounter) }}" class="btn btn-sm btn-light">
                                        <span class="material-symbols-outlined fs-6">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No encounters today.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Triage (Nurse View) -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-3 mb-4 h-100">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Nurse: Recent Triage Activity</h5>
                <a href="{{ route('nurse.triage.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Priority</th>
                                <th>Vitals</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTriage ?? [] as $vital)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $vital->encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }}">
                                        {{ $vital->overall_priority }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <span class="text-muted">BP:</span> {{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }}<br>
                                        <span class="text-muted">SpO2:</span> {{ $vital->spo2 }}%
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nurse.triage.show', $vital->encounter) }}" class="btn btn-sm btn-outline-primary">
                                        <span class="material-symbols-outlined fs-6">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No recent triage activity.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

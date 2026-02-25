@extends('layouts.app')

@section('title', 'Reception Dashboard')

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
</style>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h3 class="mb-0">Reception Dashboard</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item active" aria-current="page">Reception</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
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
        <div class="card bg-success bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Completed Today</span>
                        <h4 class="mb-0 fw-semibold">{{ $completedToday ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-success fs-1">check_circle</span>
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
</div>

<!-- Period Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="wh-54 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <span class="material-symbols-outlined text-primary">date_range</span>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-semibold">{{ $weeklyEncounters ?? 0 }}</h5>
                        <span class="text-muted fs-13">This Week</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="wh-54 bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <span class="material-symbols-outlined text-success">calendar_month</span>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-semibold">{{ $monthlyEncounters ?? 0 }}</h5>
                        <span class="text-muted fs-13">This Month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                <h5 class="mb-0 fw-semibold">Quick Actions</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('receptionist.beneficiaries.search') }}" class="btn btn-outline-primary w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">search</span>
                            Search Beneficiary
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-outline-info w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">list_alt</span>
                            Today's Queue
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('receptionist.referrals') }}" class="btn btn-outline-warning w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">swap_horiz</span>
                            Referrals
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('receptionist.history') }}" class="btn btn-outline-secondary w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">history</span>
                            Patient History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Queue -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                <h5 class="mb-0 fw-semibold">Today's Encounter Queue</h5>
                <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>BOSCHMA ID</th>
                                <th>Nature of Visit</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($encounterQueue ?? [] as $encounter)
                            <tr>
                                <td>{{ $encounter->created_at->format('H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="wh-40 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="material-symbols-outlined text-primary fs-6">person</span>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $encounter->patient->beneficiary->fullname ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $encounter->patient->beneficiary->boschma_no ?? 'N/A' }}</span></td>
                                <td>{{ $encounter->nature_of_visit }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'Registered' => 'info',
                                            'Triaged' => 'warning',
                                            'In Consultation' => 'primary',
                                            'Awaiting Lab' => 'secondary',
                                            'Awaiting Pharmacy' => 'dark',
                                            'Completed' => 'success',
                                            'Cancelled' => 'danger',
                                        ];
                                        $color = $statusColors[$encounter->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $encounter->status }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                            <span class="material-symbols-outlined fs-6">more_vert</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('receptionist.encounters.show', $encounter) }}">
                                                    <span class="material-symbols-outlined me-2 fs-6">visibility</span> View Details
                                                </a>
                                            </li>
                                            @if($encounter->status === 'Registered')
                                            <li>
                                                <form action="{{ route('receptionist.encounters.forward-nurse', $encounter) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <span class="material-symbols-outlined me-2 fs-6">send</span> Forward to Nurse
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $encounter->id }}">
                                                    <span class="material-symbols-outlined me-2 fs-6">cancel</span> Cancel Encounter
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <span class="material-symbols-outlined fs-1 d-block mb-2">inbox</span>
                                    No encounters today. <a href="{{ route('receptionist.beneficiaries.search') }}">Check in a patient</a>
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

<!-- Reports Link -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3 bg-gradient" style="background: linear-gradient(135deg, #01552b, #016634);">
            <div class="card-body p-4 text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-white">Reception Reports</h5>
                        <p class="mb-0 opacity-75">View detailed encounter statistics and analytics</p>
                    </div>
                    <a href="{{ route('receptionist.reports') }}" class="btn btn-light">
                        <span class="material-symbols-outlined me-1">analytics</span> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modals -->
@foreach($encounterQueue ?? [] as $encounter)
@if($encounter->status === 'Registered')
<div class="modal fade" id="cancelModal{{ $encounter->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('receptionist.encounters.cancel', $encounter) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Encounter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this encounter for <strong>{{ $encounter->patient->beneficiary->fullname ?? 'this patient' }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Encounter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection

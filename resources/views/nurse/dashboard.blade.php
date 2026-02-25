@extends('layouts.app')

@section('title', 'Nurse Dashboard')

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
    <h3 class="mb-0">Nurse Station</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item active" aria-current="page">Nurse Station</li>
        </ol>
    </nav>
</div>

<!-- Stats Cards -->
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
    
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-info bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Drug Admin</span>
                        <h4 class="mb-0 fw-semibold">{{ $pendingAdministrations ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-info fs-1">medication</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Quick Actions</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('nurse.triage.index') }}" class="btn btn-warning w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">monitor_heart</span>
                            Triage Queue
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('nurse.triage.history') }}" class="btn btn-outline-secondary w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">history</span>
                            Triage History
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('nurse.drug-administration.index') }}" class="btn btn-outline-success w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">medication</span>
                            Drug Admin
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('nurse.triage.report') }}" class="btn btn-outline-primary w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">summarize</span>
                            Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Priority Distribution Today -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Today's Priority Distribution</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $greenCount = $priorityStats['Green'] ?? 0;
                    $yellowCount = $priorityStats['Yellow'] ?? 0;
                    $redCount = $priorityStats['Red'] ?? 0;
                    $total = $greenCount + $yellowCount + $redCount;
                @endphp
                
                @if($total > 0)
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-success fw-medium">Green (Normal)</span>
                            <span class="fw-semibold">{{ $greenCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $total > 0 ? ($greenCount / $total * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-warning fw-medium">Yellow (Urgent)</span>
                            <span class="fw-semibold">{{ $yellowCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $total > 0 ? ($yellowCount / $total * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-danger fw-medium">Red (Critical)</span>
                            <span class="fw-semibold">{{ $redCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: {{ $total > 0 ? ($redCount / $total * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-muted" style="font-size: 48px;">insights</span>
                    <p class="text-muted mt-2 mb-0">No triage data for today</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Triage Activity -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Recent Triage Activity</h5>
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
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTriage ?? [] as $vital)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="wh-35 bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="material-symbols-outlined fs-6">person</span>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $vital->encounter->patient->beneficiary->fullname ?? 'N/A' }}</span>
                                            <br><small class="text-muted">{{ $vital->encounter->patient->beneficiary->boschma_no ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }}">
                                        {{ $vital->overall_priority }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <span class="text-muted">T:</span> {{ $vital->temperature }}°C |
                                        <span class="text-muted">BP:</span> {{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }} |
                                        <span class="text-muted">SpO2:</span> {{ $vital->spo2 }}%
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $vital->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nurse.triage.show', $vital->encounter) }}" class="btn btn-sm btn-outline-primary">
                                        <span class="material-symbols-outlined fs-6">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <span class="material-symbols-outlined text-muted" style="font-size: 48px;">inbox</span>
                                    <p class="text-muted mt-2 mb-0">No recent triage activity</p>
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

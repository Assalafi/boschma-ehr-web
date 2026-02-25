@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Admin Dashboard</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg-primary bg-opacity-10 border-0 rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="d-block mb-1 fs-13">Total Encounters</span>
                        <h4 class="mb-0 fw-semibold">{{ number_format($totalEncounters ?? 0) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-primary fs-1">medical_services</span>
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
                        <span class="d-block mb-1 fs-13">Today's Encounters</span>
                        <h4 class="mb-0 fw-semibold">{{ $todayEncounters ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-success fs-1">calendar_today</span>
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
                        <span class="d-block mb-1 fs-13">Total Beneficiaries</span>
                        <h4 class="mb-0 fw-semibold">{{ number_format($totalBeneficiaries ?? 0) }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-info fs-1">people</span>
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
                        <span class="d-block mb-1 fs-13">Staff Members</span>
                        <h4 class="mb-0 fw-semibold">{{ $totalStaff ?? 0 }}</h4>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-warning fs-1">badge</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                <h5 class="mb-0 fw-semibold">Quick Actions</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('admin.beneficiaries.index') }}" class="btn btn-outline-primary w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">people</span>
                            Beneficiaries
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-success w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">manage_accounts</span>
                            User Management
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-info w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">location_city</span>
                            Facilities
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-warning w-100 py-3">
                            <span class="material-symbols-outlined d-block mb-2 fs-1">assessment</span>
                            Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
                <h5 class="mb-0 fw-semibold">Recent Encounters</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEncounters ?? [] as $encounter)
                            <tr>
                                <td>{{ $encounter->patient->fullname ?? 'N/A' }}</td>
                                <td>{{ $encounter->program->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $encounter->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($encounter->status) }}
                                    </span>
                                </td>
                                <td>{{ $encounter->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent encounters</td>
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

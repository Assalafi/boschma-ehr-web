@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Reports</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
    </nav>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-primary fs-1">medical_services</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Encounter Report</h5>
                        <small class="text-muted">Patient visits and consultations</small>
                    </div>
                </div>
                <p class="text-muted small">Generate reports on patient encounters, including visit types, outcomes, and trends.</p>
                <a href="{{ route('admin.reports.encounters') }}" class="btn btn-outline-primary w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-success fs-1">medication</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Prescription Report</h5>
                        <small class="text-muted">Drug prescriptions and dispenses</small>
                    </div>
                </div>
                <p class="text-muted small">Analyze prescription patterns, drug usage, and dispense statistics.</p>
                <a href="{{ route('admin.reports.prescriptions') }}" class="btn btn-outline-success w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-info fs-1">science</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Laboratory Report</h5>
                        <small class="text-muted">Lab tests and investigations</small>
                    </div>
                </div>
                <p class="text-muted small">Review laboratory test volumes, turnaround times, and result distributions.</p>
                <a href="{{ route('admin.reports.investigations') }}" class="btn btn-outline-info w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-warning fs-1">inventory_2</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Stock Report</h5>
                        <small class="text-muted">Drug inventory and stock levels</small>
                    </div>
                </div>
                <p class="text-muted small">Monitor drug stock levels, expiry dates, and reorder requirements.</p>
                <a href="{{ route('admin.reports.stock') }}" class="btn btn-outline-warning w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-danger bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-danger fs-1">people</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Beneficiary Report</h5>
                        <small class="text-muted">Enrollment and demographics</small>
                    </div>
                </div>
                <p class="text-muted small">View beneficiary enrollment statistics, demographics, and utilization.</p>
                <a href="{{ route('admin.reports.beneficiaries') }}" class="btn btn-outline-danger w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-lg bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-secondary fs-1">receipt_long</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-0">Claims Report</h5>
                        <small class="text-muted">Facility claims and billing</small>
                    </div>
                </div>
                <p class="text-muted small">Track facility claims, billing status, and payment reconciliation.</p>
                <a href="{{ route('admin.reports.claims') }}" class="btn btn-outline-secondary w-100">
                    <span class="material-symbols-outlined me-1">analytics</span> Generate Report
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

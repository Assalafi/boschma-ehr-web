@extends('layouts.app')

@section('title', 'Triage Report')

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
    <h3 class="mb-0">Triage Report</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item active" aria-current="page">Triage Report</li>
        </ol>
    </nav>
</div>

<!-- Date Filter -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('nurse.triage.report') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined align-middle">search</span> Generate Report
                </button>
            </div>
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-outline-success" onclick="window.print()">
                    <span class="material-symbols-outlined align-middle">print</span> Print
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-primary bg-opacity-10">
            <div class="card-body p-4 text-center">
                <h2 class="mb-1 fw-bold text-primary">{{ $totalTriage }}</h2>
                <small class="text-muted">Total Triaged</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-success bg-opacity-10">
            <div class="card-body p-4 text-center">
                <h2 class="mb-1 fw-bold text-success">{{ $priorityBreakdown['Green'] ?? 0 }}</h2>
                <small class="text-muted">Green (Normal)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-warning bg-opacity-10">
            <div class="card-body p-4 text-center">
                <h2 class="mb-1 fw-bold text-warning">{{ $priorityBreakdown['Yellow'] ?? 0 }}</h2>
                <small class="text-muted">Yellow (Urgent)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-danger bg-opacity-10">
            <div class="card-body p-4 text-center">
                <h2 class="mb-1 fw-bold text-danger">{{ $priorityBreakdown['Red'] ?? 0 }}</h2>
                <small class="text-muted">Red (Critical)</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Daily Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">calendar_month</span>
                    Daily Breakdown
                </h5>
            </div>
            <div class="card-body p-4">
                @if(count($dailyStats) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyStats as $date => $count)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                                <td class="text-end"><span class="badge bg-primary">{{ $count }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-muted" style="font-size: 48px;">event_busy</span>
                    <p class="text-muted mt-2 mb-0">No data for selected period</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- By Nurse -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">group</span>
                    By Nurse
                </h5>
            </div>
            <div class="card-body p-4">
                @if(count($byNurse) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nurse</th>
                                <th class="text-end">Patients Triaged</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byNurse as $nurse => $count)
                            <tr>
                                <td>{{ $nurse }}</td>
                                <td class="text-end"><span class="badge bg-secondary">{{ $count }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-muted" style="font-size: 48px;">person_off</span>
                    <p class="text-muted mt-2 mb-0">No nurse data available</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Detailed Records -->
<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-semibold">
            <span class="material-symbols-outlined me-2 align-middle">list</span>
            Detailed Records ({{ $records->count() }})
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>BOSCHMA</th>
                        <th>Priority</th>
                        <th>Temp</th>
                        <th>BP</th>
                        <th>Pulse</th>
                        <th>SpO2</th>
                        <th>Nurse</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $vital)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $vital->encounter->patient->enrollee_name ?? 'N/A' }}</td>
                        <td><small>{{ $vital->encounter->patient->enrollee_number ?? 'N/A' }}</small></td>
                        <td>
                            <span class="badge bg-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }}">
                                {{ $vital->overall_priority }}
                            </span>
                        </td>
                        <td>{{ $vital->temperature }}°C</td>
                        <td>{{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }}</td>
                        <td>{{ $vital->pulse_rate }}</td>
                        <td>{{ $vital->spo2 }}%</td>
                        <td><small>{{ $vital->takenBy->name ?? 'N/A' }}</small></td>
                        <td><small>{{ $vital->created_at->format('d M, H:i') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <span class="material-symbols-outlined text-muted" style="font-size: 64px;">search_off</span>
                            <h5 class="mt-3">No records found</h5>
                            <p class="text-muted">Try selecting a different date range</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .sidebar, .navbar, form, .btn, .breadcrumb {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
}
</style>
@endpush

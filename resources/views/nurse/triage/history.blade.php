@extends('layouts.app')

@section('title', 'Triage History')

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
    <h3 class="mb-0">Triage History</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item active" aria-current="page">Triage History</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('nurse.triage.history') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Patient</label>
                <input type="text" name="search" class="form-control" placeholder="Name or BOSCHMA ID" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    <option value="Red" {{ request('priority') == 'Red' ? 'selected' : '' }}>Red (Critical)</option>
                    <option value="Yellow" {{ request('priority') == 'Yellow' ? 'selected' : '' }}>Yellow (Urgent)</option>
                    <option value="Green" {{ request('priority') == 'Green' ? 'selected' : '' }}>Green (Normal)</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2">
                    <span class="material-symbols-outlined align-middle">search</span> Filter
                </button>
                <a href="{{ route('nurse.triage.history') }}" class="btn btn-outline-secondary">
                    <span class="material-symbols-outlined align-middle">refresh</span> Reset
                </a>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('nurse.triage.report') }}" class="btn btn-outline-primary">
                    <span class="material-symbols-outlined align-middle">summarize</span> Report
                </a>
            </div>
        </form>
    </div>
</div>

<!-- History Table -->
<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">
            <span class="material-symbols-outlined me-2 align-middle">history</span>
            All Triage Records
        </h5>
        <span class="badge bg-secondary">{{ $vitalSigns->total() }} Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>Priority</th>
                        <th>Temperature</th>
                        <th>BP</th>
                        <th>Pulse</th>
                        <th>SpO2</th>
                        <th>Nurse</th>
                        <th>Date/Time</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vitalSigns as $vital)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }}-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <span class="material-symbols-outlined text-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }} fs-6">person</span>
                                </div>
                                <span class="fw-medium">{{ $vital->encounter->patient->beneficiary->fullname ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $vital->encounter->patient->beneficiary->boschma_no ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge bg-{{ $vital->overall_priority == 'Red' ? 'danger' : ($vital->overall_priority == 'Yellow' ? 'warning' : 'success') }}">
                                {{ $vital->overall_priority }}
                            </span>
                        </td>
                        <td>{{ $vital->temperature }}°C</td>
                        <td>{{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }}</td>
                        <td>{{ $vital->pulse_rate }} bpm</td>
                        <td>{{ $vital->spo2 }}%</td>
                        <td><small class="text-muted">{{ $vital->takenBy->name ?? 'N/A' }}</small></td>
                        <td><small>{{ $vital->created_at->format('d M Y, H:i') }}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('nurse.triage.show', $vital->encounter) }}" class="btn btn-outline-primary" title="View">
                                    <span class="material-symbols-outlined fs-6">visibility</span>
                                </a>
                                <a href="{{ route('nurse.triage.edit', $vital->encounter) }}" class="btn btn-outline-warning" title="Edit">
                                    <span class="material-symbols-outlined fs-6">edit</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <span class="material-symbols-outlined text-muted" style="font-size: 64px;">search_off</span>
                            <h5 class="mt-3">No triage records found</h5>
                            <p class="text-muted">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($vitalSigns->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $vitalSigns->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

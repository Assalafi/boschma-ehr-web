@extends('layouts.app')

@section('title', 'Triage Details')

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
    <h3 class="mb-0">Triage Details</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.triage.history') }}">Triage History</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </nav>
</div>

@php
    $vitalSign = $encounter->vitalSigns->first();
@endphp

<div class="row">
    <div class="col-lg-4">
        <!-- Patient Info Card -->
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="text-center">
                    <div class="wh-80 bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden">
                        @if($encounter->patient->beneficiary->photo ?? false)
                            <img src="{{ asset('storage/' . $encounter->patient->beneficiary->photo) }}" class="rounded-circle wh-80 object-fit-cover" alt="">
                        @else
                            <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">person</span>
                        @endif
                    </div>
                    <h5 class="mb-1 text-white">{{ $encounter->patient->beneficiary->fullname ?? 'N/A' }}</h5>
                    <span class="badge bg-light text-dark">{{ $encounter->patient->beneficiary->boschma_no ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Gender</span>
                        <span class="fw-medium">{{ $encounter->patient->beneficiary->gender ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Age</span>
                        <span class="fw-medium">{{ $encounter->patient->beneficiary->date_of_birth ? \Carbon\Carbon::parse($encounter->patient->beneficiary->date_of_birth)->age . ' years' : 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Nature of Visit</span>
                        <span class="fw-medium">{{ $encounter->nature_of_visit }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Program</span>
                        <span class="fw-medium">{{ $encounter->program->name ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Encounter Status</span>
                        <span class="badge bg-info">{{ $encounter->status }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Priority Card -->
        @if($vitalSign)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-{{ $vitalSign->overall_priority == 'Red' ? 'danger' : ($vitalSign->overall_priority == 'Yellow' ? 'warning' : 'success') }} text-{{ $vitalSign->overall_priority == 'Yellow' ? 'dark' : 'white' }} p-4">
                <h6 class="mb-0 fw-semibold d-flex align-items-center justify-content-center">
                    <span class="material-symbols-outlined me-2">flag</span>
                    {{ $vitalSign->overall_priority }} Priority
                </h6>
            </div>
            <div class="card-body p-4 text-center">
                <small class="text-muted">
                    Triaged by {{ $vitalSign->takenBy->name ?? 'Unknown' }}<br>
                    {{ $vitalSign->created_at->format('d M Y, H:i') }}
                </small>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <!-- Vital Signs Details -->
        @if($vitalSign)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">monitor_heart</span>
                    Vital Signs
                </h5>
                <a href="{{ route('nurse.triage.edit', $encounter) }}" class="btn btn-sm btn-warning">
                    <span class="material-symbols-outlined align-middle">edit</span> Edit
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-danger mb-2" style="font-size: 32px;">thermostat</span>
                            <h4 class="mb-1">{{ $vitalSign->temperature }}°C</h4>
                            <small class="text-muted">Temperature</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-primary mb-2" style="font-size: 32px;">favorite</span>
                            <h4 class="mb-1">{{ $vitalSign->blood_pressure_systolic }}/{{ $vitalSign->blood_pressure_diastolic }}</h4>
                            <small class="text-muted">Blood Pressure (mmHg)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-success mb-2" style="font-size: 32px;">cardiology</span>
                            <h4 class="mb-1">{{ $vitalSign->pulse_rate }}</h4>
                            <small class="text-muted">Pulse Rate (bpm)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-info mb-2" style="font-size: 32px;">air</span>
                            <h4 class="mb-1">{{ $vitalSign->respiration_rate }}</h4>
                            <small class="text-muted">Respiration (/min)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-warning mb-2" style="font-size: 32px;">bloodtype</span>
                            <h4 class="mb-1">{{ $vitalSign->spo2 }}%</h4>
                            <small class="text-muted">SpO2</small>
                        </div>
                    </div>
                    @if($vitalSign->weight && $vitalSign->height)
                    @php
                        $heightM = $vitalSign->height / 100;
                        $bmi = $vitalSign->weight / ($heightM * $heightM);
                    @endphp
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 32px;">calculate</span>
                            <h4 class="mb-1">{{ number_format($bmi, 1) }}</h4>
                            <small class="text-muted">BMI</small>
                        </div>
                    </div>
                    @endif
                </div>
                
                @if($vitalSign->weight || $vitalSign->height)
                <hr class="my-4">
                <div class="row">
                    @if($vitalSign->weight)
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Weight:</strong> {{ $vitalSign->weight }} kg</p>
                    </div>
                    @endif
                    @if($vitalSign->height)
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Height:</strong> {{ $vitalSign->height }} cm</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <span class="material-symbols-outlined align-middle me-2">warning</span>
            No vital signs recorded for this encounter.
        </div>
        @endif

        <!-- Chief Complaint -->
        @if($encounter->reason_for_visit)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">description</span>
                    Chief Complaint
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0">{{ $encounter->reason_for_visit }}</p>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('nurse.triage.history') }}" class="btn btn-outline-secondary">
                <span class="material-symbols-outlined align-middle me-1">arrow_back</span>
                Back to History
            </a>
            <a href="{{ route('nurse.triage.edit', $encounter) }}" class="btn btn-warning">
                <span class="material-symbols-outlined align-middle me-1">edit</span>
                Edit Vital Signs
            </a>
        </div>
    </div>
</div>
@endsection

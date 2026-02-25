@extends('layouts.app')

@section('title', 'Record Vital Signs')

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
    <h3 class="mb-0">Record Vital Signs</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.vital-signs.index') }}">Vital Signs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Record</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="text-center">
                    <div class="wh-80 bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden">
                        @if($encounter->patient->enrollee_photo ?? false)
                            <img src="{{ $encounter->patient->enrollee_photo }}" class="rounded-circle wh-80 object-fit-cover" alt="">
                        @else
                            <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">person</span>
                        @endif
                    </div>
                    <h5 class="mb-1 text-white">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</h5>
                    <span class="badge bg-light text-dark">{{ $encounter->patient->enrollee_number ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Gender</span>
                        <span class="fw-medium">{{ $encounter->patient->enrollee_gender ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Age</span>
                        <span class="fw-medium">{{ $encounter->patient->enrollee_dob ? \Carbon\Carbon::parse($encounter->patient->enrollee_dob)->age . ' years' : 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Nature of Visit</span>
                        <span class="fw-medium">{{ $encounter->nature_of_visit }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Program</span>
                        <span class="fw-medium">{{ $encounter->program->name ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">monitor_heart</span>
                    Vital Signs Entry
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('nurse.vital-signs.store', $encounter) }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Temperature (°C) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="temperature" class="form-control @error('temperature') is-invalid @enderror" 
                                   value="{{ old('temperature') }}" placeholder="36.5" required>
                            @error('temperature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">SpO2 (%) <span class="text-danger">*</span></label>
                            <input type="number" name="spo2" class="form-control @error('spo2') is-invalid @enderror" 
                                   value="{{ old('spo2') }}" placeholder="98" required>
                            @error('spo2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Blood Pressure Systolic (mmHg) <span class="text-danger">*</span></label>
                            <input type="number" name="blood_pressure_systolic" class="form-control @error('blood_pressure_systolic') is-invalid @enderror" 
                                   value="{{ old('blood_pressure_systolic') }}" placeholder="120" required>
                            @error('blood_pressure_systolic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Blood Pressure Diastolic (mmHg) <span class="text-danger">*</span></label>
                            <input type="number" name="blood_pressure_diastolic" class="form-control @error('blood_pressure_diastolic') is-invalid @enderror" 
                                   value="{{ old('blood_pressure_diastolic') }}" placeholder="80" required>
                            @error('blood_pressure_diastolic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Pulse Rate (bpm) <span class="text-danger">*</span></label>
                            <input type="number" name="pulse_rate" class="form-control @error('pulse_rate') is-invalid @enderror" 
                                   value="{{ old('pulse_rate') }}" placeholder="72" required>
                            @error('pulse_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Respiration Rate (/min) <span class="text-danger">*</span></label>
                            <input type="number" name="respiration_rate" class="form-control @error('respiration_rate') is-invalid @enderror" 
                                   value="{{ old('respiration_rate') }}" placeholder="16" required>
                            @error('respiration_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" class="form-control @error('weight') is-invalid @enderror" 
                                   value="{{ old('weight') }}" placeholder="70.5">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Height (cm)</label>
                            <input type="number" step="0.1" name="height" class="form-control @error('height') is-invalid @enderror" 
                                   value="{{ old('height') }}" placeholder="170">
                            @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('nurse.vital-signs.index') }}" class="btn btn-outline-secondary">
                            <span class="material-symbols-outlined align-middle me-1">arrow_back</span>
                            Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined align-middle me-1">save</span>
                            Save Vital Signs
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

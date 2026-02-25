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
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse</a></li>
            <li class="breadcrumb-item"><a href="{{ route('triage.index') }}">Triage</a></li>
            <li class="breadcrumb-item active" aria-current="page">Vital Signs</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h5 class="mb-0 fw-semibold">Patient Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <span class="material-symbols-outlined text-muted" style="font-size: 40px;">person</span>
                    </div>
                    <h5 class="mb-1">{{ $encounter->patient_name }}</h5>
                    <span class="badge bg-primary">{{ $encounter->patient_boschma_no }}</span>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Type</span>
                        <span class="badge bg-success">{{ ucfirst($encounter->patient_type) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Visit Nature</span>
                        <strong>{{ $encounter->nature_of_visit }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Program</span>
                        <strong>{{ $encounter->program?->name ?? 'N/A' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Check-in Time</span>
                        <strong>{{ $encounter->visit_date->format('H:i') }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Vital Signs</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('vital-signs.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="encounter_id" value="{{ $encounter->id }}">
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Temperature (°C) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="temperature" class="form-control @error('temperature') is-invalid @enderror" placeholder="36.5" value="{{ old('temperature') }}" required>
                            @error('temperature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Pulse Rate (bpm) <span class="text-danger">*</span></label>
                            <input type="number" name="pulse_rate" class="form-control @error('pulse_rate') is-invalid @enderror" placeholder="72" value="{{ old('pulse_rate') }}" required>
                            @error('pulse_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Blood Pressure Systolic (mmHg) <span class="text-danger">*</span></label>
                            <input type="number" name="blood_pressure_systolic" class="form-control @error('blood_pressure_systolic') is-invalid @enderror" placeholder="120" value="{{ old('blood_pressure_systolic') }}" required>
                            @error('blood_pressure_systolic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Blood Pressure Diastolic (mmHg) <span class="text-danger">*</span></label>
                            <input type="number" name="blood_pressure_diastolic" class="form-control @error('blood_pressure_diastolic') is-invalid @enderror" placeholder="80" value="{{ old('blood_pressure_diastolic') }}" required>
                            @error('blood_pressure_diastolic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Respiratory Rate (breaths/min)</label>
                            <input type="number" name="respiratory_rate" class="form-control" placeholder="16" value="{{ old('respiratory_rate') }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Oxygen Saturation (%)</label>
                            <input type="number" step="0.1" name="oxygen_saturation" class="form-control" placeholder="98" value="{{ old('oxygen_saturation') }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="weight" class="form-control @error('weight') is-invalid @enderror" placeholder="70" value="{{ old('weight') }}" required>
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Height (cm)</label>
                            <input type="number" step="0.1" name="height" class="form-control" placeholder="170" value="{{ old('height') }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">BMI</label>
                            <input type="text" name="bmi" class="form-control" id="bmi" readonly placeholder="Calculated">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Blood Sugar (mg/dL)</label>
                            <input type="number" step="0.1" name="blood_sugar" class="form-control" placeholder="100" value="{{ old('blood_sugar') }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="overall_priority" class="form-select @error('overall_priority') is-invalid @enderror" required>
                                <option value="">Select Priority</option>
                                <option value="1" {{ old('overall_priority') == '1' ? 'selected' : '' }}>1 - Immediate (Red)</option>
                                <option value="2" {{ old('overall_priority') == '2' ? 'selected' : '' }}>2 - Urgent (Orange)</option>
                                <option value="3" {{ old('overall_priority') == '3' ? 'selected' : '' }}>3 - Less Urgent (Yellow)</option>
                                <option value="4" {{ old('overall_priority') == '4' ? 'selected' : '' }}>4 - Non-Urgent (Green)</option>
                                <option value="5" {{ old('overall_priority') == '5' ? 'selected' : '' }}>5 - Minor (Blue)</option>
                            </select>
                            @error('overall_priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Chief Complaint</label>
                            <textarea name="chief_complaint" class="form-control" rows="2" placeholder="Patient's main complaint...">{{ old('chief_complaint') }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional observations...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined me-1">save</span> Save & Send to Doctor
                        </button>
                        <a href="{{ route('triage.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.querySelector('input[name="weight"]');
    const heightInput = document.querySelector('input[name="height"]');
    const bmiInput = document.getElementById('bmi');
    
    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const heightCm = parseFloat(heightInput.value);
        
        if (weight && heightCm) {
            const heightM = heightCm / 100;
            const bmi = (weight / (heightM * heightM)).toFixed(1);
            bmiInput.value = bmi;
        } else {
            bmiInput.value = '';
        }
    }
    
    weightInput.addEventListener('input', calculateBMI);
    heightInput.addEventListener('input', calculateBMI);
});
</script>
@endpush
@endsection

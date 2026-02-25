@extends('layouts.app')

@section('title', 'Triage Patient')

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
    <h3 class="mb-0">Triage Patient</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.triage.index') }}">Triage Queue</a></li>
            <li class="breadcrumb-item active" aria-current="page">Record Vitals</li>
        </ol>
    </nav>
</div>

<!-- Critical Alert Banner (hidden by default) -->
<div id="criticalAlert" class="alert alert-danger d-none mb-4">
    <div class="d-flex align-items-center">
        <span class="material-symbols-outlined me-2 fs-3">warning</span>
        <div>
            <strong>Critical Parameter Detected!</strong>
            <p class="mb-0">Patient will be flagged as RED priority and doctor will be notified for urgent review.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Patient Info Card -->
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="text-center">
                    <div class="wh-80 bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden">
                        @if($encounter->patient->enrollee_photo ?? false)
                            <img src="{{ asset('storage/' . $encounter->patient->enrollee_photo) }}" class="rounded-circle wh-80 object-fit-cover" alt="">
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

        <!-- Overall Priority Card -->
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-secondary text-white p-4" id="priorityHeader">
                <h6 class="mb-0 fw-semibold d-flex align-items-center">
                    <span class="material-symbols-outlined me-2">flag</span>
                    Overall Priority: <span id="overallPriorityText" class="ms-2">--</span>
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-0 small">Priority is automatically calculated based on vital signs values entered.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">monitor_heart</span>
                    Vital Signs & Triage
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('nurse.triage.store', $encounter) }}" method="POST" id="triageForm">
                    @csrf
                    
                    <div class="row g-4">
                        <!-- Temperature -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Temperature (°C) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">thermostat</span></span>
                                <input type="number" step="0.1" name="temperature" id="temperature" 
                                       class="form-control @error('temperature') is-invalid @enderror" 
                                       value="{{ old('temperature') }}" placeholder="36.5" required>
                                <span class="input-group-text">°C</span>
                            </div>
                            <div id="temperatureStatus" class="mt-2"></div>
                            @error('temperature')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- SpO2 -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">SpO2 (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">bloodtype</span></span>
                                <input type="number" name="spo2" id="spo2" 
                                       class="form-control @error('spo2') is-invalid @enderror" 
                                       value="{{ old('spo2') }}" placeholder="98" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div id="spo2Status" class="mt-2"></div>
                            @error('spo2')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Blood Pressure Systolic -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Blood Pressure Systolic (mmHg) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">trending_up</span></span>
                                <input type="number" name="blood_pressure_systolic" id="bpSystolic" 
                                       class="form-control @error('blood_pressure_systolic') is-invalid @enderror" 
                                       value="{{ old('blood_pressure_systolic') }}" placeholder="120" required>
                                <span class="input-group-text">mmHg</span>
                            </div>
                            @error('blood_pressure_systolic')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Blood Pressure Diastolic -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Blood Pressure Diastolic (mmHg) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">trending_down</span></span>
                                <input type="number" name="blood_pressure_diastolic" id="bpDiastolic" 
                                       class="form-control @error('blood_pressure_diastolic') is-invalid @enderror" 
                                       value="{{ old('blood_pressure_diastolic') }}" placeholder="80" required>
                                <span class="input-group-text">mmHg</span>
                            </div>
                            <div id="bpStatus" class="mt-2"></div>
                            @error('blood_pressure_diastolic')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pulse Rate -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Pulse Rate (bpm) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">favorite</span></span>
                                <input type="number" name="pulse_rate" id="pulseRate" 
                                       class="form-control @error('pulse_rate') is-invalid @enderror" 
                                       value="{{ old('pulse_rate') }}" placeholder="72" required>
                                <span class="input-group-text">bpm</span>
                            </div>
                            <div id="pulseStatus" class="mt-2"></div>
                            @error('pulse_rate')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Respiration Rate -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Respiration Rate (/min) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">air</span></span>
                                <input type="number" name="respiration_rate" id="respirationRate" 
                                       class="form-control @error('respiration_rate') is-invalid @enderror" 
                                       value="{{ old('respiration_rate') }}" placeholder="16" required>
                                <span class="input-group-text">/min</span>
                            </div>
                            <div id="respirationStatus" class="mt-2"></div>
                            @error('respiration_rate')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Weight -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Weight (kg)</label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">monitor_weight</span></span>
                                <input type="number" step="0.1" name="weight" id="weight" 
                                       class="form-control @error('weight') is-invalid @enderror" 
                                       value="{{ old('weight') }}" placeholder="70.5">
                                <span class="input-group-text">kg</span>
                            </div>
                            @error('weight')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Height -->
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Height (cm)</label>
                            <div class="input-group">
                                <span class="input-group-text"><span class="material-symbols-outlined fs-6">height</span></span>
                                <input type="number" step="0.1" name="height" id="height" 
                                       class="form-control @error('height') is-invalid @enderror" 
                                       value="{{ old('height') }}" placeholder="170">
                                <span class="input-group-text">cm</span>
                            </div>
                            @error('height')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- BMI Display -->
                        <div class="col-12">
                            <div class="alert alert-light d-flex align-items-center" id="bmiDisplay" style="display: none !important;">
                                <span class="material-symbols-outlined me-2">calculate</span>
                                <span>BMI: <strong id="bmiValue">--</strong> <span id="bmiCategory" class="badge ms-2">--</span></span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label fw-medium">Clinical Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Any additional observations...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Hidden field for calculated priority -->
                    <input type="hidden" name="overall_priority" id="overallPriority" value="Green">

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('nurse.triage.index') }}" class="btn btn-outline-secondary">
                            <span class="material-symbols-outlined align-middle me-1">arrow_back</span>
                            Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined align-middle me-1">check</span>
                            Complete Triage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fields = {
        temperature: document.getElementById('temperature'),
        spo2: document.getElementById('spo2'),
        bpSystolic: document.getElementById('bpSystolic'),
        bpDiastolic: document.getElementById('bpDiastolic'),
        pulseRate: document.getElementById('pulseRate'),
        respirationRate: document.getElementById('respirationRate'),
        weight: document.getElementById('weight'),
        height: document.getElementById('height')
    };

    const statusElements = {
        temperature: document.getElementById('temperatureStatus'),
        spo2: document.getElementById('spo2Status'),
        bp: document.getElementById('bpStatus'),
        pulse: document.getElementById('pulseStatus'),
        respiration: document.getElementById('respirationStatus')
    };

    let statuses = {
        temperature: null,
        spo2: null,
        bp: null,
        pulse: null,
        respiration: null
    };

    // Status badge generator
    function createStatusBadge(label, color) {
        return `<span class="badge" style="background-color: ${color}15; color: ${color}; border: 1px solid ${color}30;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${color}; margin-right: 6px;"></span>
            ${label}
        </span>`;
    }

    // Temperature: Normal: 36–37.5 / Moderate: 37.6–38.5 / Critical: ≥38.6 or <35
    function getTemperatureStatus(temp) {
        if (temp >= 36.0 && temp <= 37.5) {
            return { label: 'Normal', color: '#0d6efd', priority: 'Normal' };
        } else if (temp >= 37.6 && temp <= 38.5) {
            return { label: 'Moderate', color: '#fd7e14', priority: 'Warning' };
        } else if (temp >= 38.6 || temp < 35.0) {
            return { label: 'Critical', color: '#dc3545', priority: 'Critical' };
        } else {
            return { label: 'Low', color: '#0d6efd', priority: 'Normal' };
        }
    }

    // SpO2: Normal: 95–100 / Moderate: 90–94 / Critical: <90
    function getSpO2Status(spo2) {
        if (spo2 >= 95 && spo2 <= 100) {
            return { label: 'Normal', color: '#0d6efd', priority: 'Normal' };
        } else if (spo2 >= 90 && spo2 <= 94) {
            return { label: 'Moderate', color: '#fd7e14', priority: 'Warning' };
        } else if (spo2 < 90) {
            return { label: 'Critical', color: '#dc3545', priority: 'Critical' };
        }
        return { label: 'Invalid', color: '#6c757d', priority: 'Normal' };
    }

    // BP: Normal: <120/80 / Moderate: 120-139/80-89 / Critical: ≥140/90 or <90/60
    function getBPStatus(systolic, diastolic) {
        if (systolic < 90 || diastolic < 60) {
            return { label: 'Low - Critical', color: '#dc3545', priority: 'Critical' };
        } else if (systolic < 120 && diastolic < 80) {
            return { label: 'Normal', color: '#0d6efd', priority: 'Normal' };
        } else if ((systolic >= 120 && systolic <= 139) || (diastolic >= 80 && diastolic <= 89)) {
            return { label: 'Elevated', color: '#fd7e14', priority: 'Warning' };
        } else if (systolic >= 140 || diastolic >= 90) {
            return { label: 'High - Critical', color: '#dc3545', priority: 'Critical' };
        }
        return { label: 'Unknown', color: '#6c757d', priority: 'Normal' };
    }

    // Pulse: Normal: 60–100 / Moderate: 50-59 or 101-120 / Critical: <50 or >120
    function getPulseStatus(pulse) {
        if (pulse >= 60 && pulse <= 100) {
            return { label: 'Normal', color: '#0d6efd', priority: 'Normal' };
        } else if ((pulse >= 50 && pulse <= 59) || (pulse >= 101 && pulse <= 120)) {
            return { label: 'Moderate', color: '#fd7e14', priority: 'Warning' };
        } else if (pulse < 50 || pulse > 120) {
            return { label: 'Critical', color: '#dc3545', priority: 'Critical' };
        }
        return { label: 'Invalid', color: '#6c757d', priority: 'Normal' };
    }

    // Respiration: Normal: 12–20 / Moderate: 10-11 or 21-25 / Critical: <10 or >25
    function getRespirationStatus(resp) {
        if (resp >= 12 && resp <= 20) {
            return { label: 'Normal', color: '#0d6efd', priority: 'Normal' };
        } else if ((resp >= 10 && resp <= 11) || (resp >= 21 && resp <= 25)) {
            return { label: 'Moderate', color: '#fd7e14', priority: 'Warning' };
        } else if (resp < 10 || resp > 25) {
            return { label: 'Critical', color: '#dc3545', priority: 'Critical' };
        }
        return { label: 'Invalid', color: '#6c757d', priority: 'Normal' };
    }

    // Calculate BMI
    function calculateBMI() {
        const weight = parseFloat(fields.weight.value);
        const height = parseFloat(fields.height.value);
        
        if (weight > 0 && height > 0) {
            const heightM = height / 100;
            const bmi = weight / (heightM * heightM);
            document.getElementById('bmiValue').textContent = bmi.toFixed(1);
            
            let category, color;
            if (bmi < 18.5) { category = 'Underweight'; color = 'bg-info'; }
            else if (bmi < 25) { category = 'Normal'; color = 'bg-success'; }
            else if (bmi < 30) { category = 'Overweight'; color = 'bg-warning'; }
            else { category = 'Obese'; color = 'bg-danger'; }
            
            document.getElementById('bmiCategory').textContent = category;
            document.getElementById('bmiCategory').className = 'badge ms-2 ' + color;
            document.getElementById('bmiDisplay').style.display = 'flex !important';
            document.getElementById('bmiDisplay').classList.remove('d-none');
        }
    }

    // Update overall priority
    function updateOverallPriority() {
        let hasCritical = false;
        let hasWarning = false;

        Object.values(statuses).forEach(status => {
            if (status) {
                if (status.priority === 'Critical') hasCritical = true;
                if (status.priority === 'Warning') hasWarning = true;
            }
        });

        const priorityHeader = document.getElementById('priorityHeader');
        const priorityText = document.getElementById('overallPriorityText');
        const priorityInput = document.getElementById('overallPriority');
        const criticalAlert = document.getElementById('criticalAlert');

        if (hasCritical) {
            priorityHeader.className = 'card-header bg-danger text-white p-4';
            priorityText.textContent = 'RED (Critical)';
            priorityInput.value = 'Red';
            criticalAlert.classList.remove('d-none');
        } else if (hasWarning) {
            priorityHeader.className = 'card-header bg-warning text-dark p-4';
            priorityText.textContent = 'YELLOW (Urgent)';
            priorityInput.value = 'Yellow';
            criticalAlert.classList.add('d-none');
        } else {
            priorityHeader.className = 'card-header bg-success text-white p-4';
            priorityText.textContent = 'GREEN (Non-urgent)';
            priorityInput.value = 'Green';
            criticalAlert.classList.add('d-none');
        }
    }

    // Event listeners
    fields.temperature.addEventListener('input', function() {
        const val = parseFloat(this.value);
        if (val) {
            statuses.temperature = getTemperatureStatus(val);
            statusElements.temperature.innerHTML = createStatusBadge(statuses.temperature.label, statuses.temperature.color);
        } else {
            statuses.temperature = null;
            statusElements.temperature.innerHTML = '';
        }
        updateOverallPriority();
    });

    fields.spo2.addEventListener('input', function() {
        const val = parseInt(this.value);
        if (val) {
            statuses.spo2 = getSpO2Status(val);
            statusElements.spo2.innerHTML = createStatusBadge(statuses.spo2.label, statuses.spo2.color);
        } else {
            statuses.spo2 = null;
            statusElements.spo2.innerHTML = '';
        }
        updateOverallPriority();
    });

    function updateBPStatus() {
        const systolic = parseInt(fields.bpSystolic.value);
        const diastolic = parseInt(fields.bpDiastolic.value);
        if (systolic && diastolic) {
            statuses.bp = getBPStatus(systolic, diastolic);
            statusElements.bp.innerHTML = createStatusBadge(statuses.bp.label, statuses.bp.color);
        } else {
            statuses.bp = null;
            statusElements.bp.innerHTML = '';
        }
        updateOverallPriority();
    }

    fields.bpSystolic.addEventListener('input', updateBPStatus);
    fields.bpDiastolic.addEventListener('input', updateBPStatus);

    fields.pulseRate.addEventListener('input', function() {
        const val = parseInt(this.value);
        if (val) {
            statuses.pulse = getPulseStatus(val);
            statusElements.pulse.innerHTML = createStatusBadge(statuses.pulse.label, statuses.pulse.color);
        } else {
            statuses.pulse = null;
            statusElements.pulse.innerHTML = '';
        }
        updateOverallPriority();
    });

    fields.respirationRate.addEventListener('input', function() {
        const val = parseInt(this.value);
        if (val) {
            statuses.respiration = getRespirationStatus(val);
            statusElements.respiration.innerHTML = createStatusBadge(statuses.respiration.label, statuses.respiration.color);
        } else {
            statuses.respiration = null;
            statusElements.respiration.innerHTML = '';
        }
        updateOverallPriority();
    });

    fields.weight.addEventListener('input', calculateBMI);
    fields.height.addEventListener('input', calculateBMI);
});
</script>
@endpush

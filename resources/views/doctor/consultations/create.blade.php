@extends('layouts.app')

@section('title', 'Clinical Consultation')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Clinical Consultation</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Doctor</a></li>
            <li class="breadcrumb-item"><a href="{{ route('doctor.queue') }}">Queue</a></li>
            <li class="breadcrumb-item active" aria-current="page">Consultation</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-3">
                <h6 class="mb-0 fw-semibold">Patient Information</h6>
            </div>
            <div class="card-body p-3">
                <div class="text-center mb-3">
                    <h6 class="mb-1">{{ $encounter->patient_name }}</h6>
                    <span class="badge bg-primary">{{ $encounter->patient_boschma_no }}</span>
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Type</span>
                        <span class="badge bg-success">{{ ucfirst($encounter->patient_type) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Nature</span>
                        <strong>{{ $encounter->nature_of_visit }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Program</span>
                        <strong>{{ $encounter->program?->name ?? 'N/A' }}</strong>
                    </li>
                </ul>
            </div>
        </div>
        
        @if($vitals = $encounter->vitalSigns->last())
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-info text-white p-3">
                <h6 class="mb-0 fw-semibold">Vital Signs</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">BP</small>
                            <strong>{{ $vitals->blood_pressure_systolic }}/{{ $vitals->blood_pressure_diastolic }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Temp</small>
                            <strong>{{ $vitals->temperature }}°C</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Pulse</small>
                            <strong>{{ $vitals->pulse_rate }} bpm</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Weight</small>
                            <strong>{{ $vitals->weight }} kg</strong>
                        </div>
                    </div>
                    @if($vitals->chief_complaint)
                    <div class="col-12 mt-2">
                        <div class="alert alert-warning mb-0 p-2 small">
                            <strong>Chief Complaint:</strong> {{ $vitals->chief_complaint }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-8">
        <form action="{{ route('consultations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="encounter_id" value="{{ $encounter->id }}">
            
            <div class="card border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-semibold">Clinical Notes</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Presenting Complaint <span class="text-danger">*</span></label>
                            <textarea name="presenting_complaint" class="form-control @error('presenting_complaint') is-invalid @enderror" rows="3" required>{{ old('presenting_complaint') }}</textarea>
                            @error('presenting_complaint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">History of Presenting Illness</label>
                            <textarea name="history_of_presenting_illness" class="form-control" rows="3">{{ old('history_of_presenting_illness') }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Examination Findings <span class="text-danger">*</span></label>
                            <textarea name="examination_findings" class="form-control @error('examination_findings') is-invalid @enderror" rows="3" required>{{ old('examination_findings') }}</textarea>
                            @error('examination_findings')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Assessment / Diagnosis <span class="text-danger">*</span></label>
                            <textarea name="assessment" class="form-control @error('assessment') is-invalid @enderror" rows="2" required>{{ old('assessment') }}</textarea>
                            @error('assessment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Treatment Plan <span class="text-danger">*</span></label>
                            <textarea name="treatment_plan" class="form-control @error('treatment_plan') is-invalid @enderror" rows="3" required>{{ old('treatment_plan') }}</textarea>
                            @error('treatment_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Prescription</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPrescriptionBtn">
                        <span class="material-symbols-outlined">add</span> Add Drug
                    </button>
                </div>
                <div class="card-body p-4" id="prescriptionContainer">
                    <p class="text-muted small mb-0" id="noPrescriptionText">No drugs prescribed yet. Click "Add Drug" to prescribe.</p>
                </div>
            </div>
            
            <div class="card border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Investigations</h6>
                    <button type="button" class="btn btn-outline-info btn-sm" id="addInvestigationBtn">
                        <span class="material-symbols-outlined">add</span> Add Investigation
                    </button>
                </div>
                <div class="card-body p-4" id="investigationContainer">
                    <p class="text-muted small mb-0" id="noInvestigationText">No investigations ordered yet.</p>
                </div>
            </div>
            
            <div class="card border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="mb-0 fw-semibold">Follow-up</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Outcome</label>
                            <select name="outcome" class="form-select">
                                <option value="treated">Treated & Discharged</option>
                                <option value="referred">Referred</option>
                                <option value="admitted">Admitted</option>
                                <option value="follow_up">Follow-up Required</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <span class="material-symbols-outlined me-1">save</span> Complete Consultation
                </button>
                <a href="{{ route('doctor.queue') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let prescriptionCount = 0;
let investigationCount = 0;

document.getElementById('addPrescriptionBtn').addEventListener('click', function() {
    document.getElementById('noPrescriptionText').style.display = 'none';
    const container = document.getElementById('prescriptionContainer');
    const html = `
        <div class="prescription-item border rounded p-3 mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Drug</label>
                    <select name="prescriptions[${prescriptionCount}][drug_id]" class="form-select" required>
                        <option value="">Select Drug</option>
                        @foreach($drugs ?? [] as $drug)
                        <option value="{{ $drug->id }}">{{ $drug->name }} ({{ $drug->strength }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dosage</label>
                    <input type="text" name="prescriptions[${prescriptionCount}][dosage]" class="form-control" placeholder="e.g., 500mg">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Frequency</label>
                    <select name="prescriptions[${prescriptionCount}][frequency]" class="form-select">
                        <option value="OD">OD (Once daily)</option>
                        <option value="BD">BD (Twice daily)</option>
                        <option value="TDS">TDS (3x daily)</option>
                        <option value="QID">QID (4x daily)</option>
                        <option value="PRN">PRN (As needed)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Duration</label>
                    <input type="text" name="prescriptions[${prescriptionCount}][duration]" class="form-control" placeholder="e.g., 5 days">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.prescription-item').remove()">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    prescriptionCount++;
});

document.getElementById('addInvestigationBtn').addEventListener('click', function() {
    document.getElementById('noInvestigationText').style.display = 'none';
    const container = document.getElementById('investigationContainer');
    const html = `
        <div class="investigation-item border rounded p-3 mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Investigation</label>
                    <select name="investigations[${investigationCount}][test_id]" class="form-select" required>
                        <option value="">Select Test</option>
                        @foreach($labTests ?? [] as $test)
                        <option value="{{ $test->id }}">{{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Clinical Indication</label>
                    <input type="text" name="investigations[${investigationCount}][indication]" class="form-control" placeholder="Reason for test">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.investigation-item').remove()">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    investigationCount++;
});
</script>
@endpush
@endsection

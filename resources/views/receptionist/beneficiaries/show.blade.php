@extends('layouts.app')

@section('title', 'Beneficiary Check-in')

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
    <h3 class="mb-0">Beneficiary Check-in</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.beneficiaries.search') }}">Search</a></li>
            <li class="breadcrumb-item active" aria-current="page">Check-in</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Beneficiary Info -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="text-center">
                    <div class="wh-80 bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden">
                        @if($beneficiary->photo)
                            <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $beneficiary->photo }}" class="rounded-circle wh-80 object-fit-cover" alt="">
                        @else
                            <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">person</span>
                        @endif
                    </div>
                    <h5 class="mb-1 text-white">{{ $beneficiary->fullname }}</h5>
                    <span class="badge bg-light text-dark">{{ $beneficiary->boschma_no }}</span>
                    @if($beneficiary->patient)
                        <br><span class="badge bg-white bg-opacity-25 text-white">File: {{ $beneficiary->patient->file_number }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Gender</span>
                        <span class="fw-medium">{{ $beneficiary->gender }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Age</span>
                        <span class="fw-medium">{{ $beneficiary->date_of_birth ? \Carbon\Carbon::parse($beneficiary->date_of_birth)->age . ' years' : 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">NIN</span>
                        <span class="fw-medium">{{ $beneficiary->nin ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Phone</span>
                        <span class="fw-medium">{{ $beneficiary->phone_no ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Program</span>
                        <span class="fw-medium">{{ $beneficiary->program->name ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Status</span>
                        @if($beneficiary->status === 'Active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">{{ $beneficiary->status }}</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>

        <!-- Dependants -->
        @if($beneficiary->spouse || ($beneficiary->children && $beneficiary->children->count() > 0))
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="mb-0 fw-semibold">Dependants</h6>
            </div>
            <div class="card-body p-4">
                @if($beneficiary->spouse)
                <div class="d-flex align-items-center mb-3">
                    <div class="wh-40 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                        <span class="material-symbols-outlined text-info fs-6">person</span>
                    </div>
                    <div>
                        <span class="fw-medium">{{ $beneficiary->spouse->name ?? 'Spouse' }}</span>
                        <br><small class="text-muted">Spouse</small>
                    </div>
                </div>
                @endif
                @foreach($beneficiary->children ?? [] as $child)
                <div class="d-flex align-items-center mb-3">
                    <div class="wh-40 bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                        <span class="material-symbols-outlined text-warning fs-6">child_care</span>
                    </div>
                    <div>
                        <span class="fw-medium">{{ $child->name ?? 'Child' }}</span>
                        <br><small class="text-muted">Child</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Ongoing Encounter Warning -->
        @if($ongoingEncounter)
        <div class="card border-0 rounded-3 mb-4 border-danger">
            <div class="card-header bg-danger text-white p-4">
                <h6 class="mb-0 fw-semibold d-flex align-items-center">
                    <span class="material-symbols-outlined me-2">warning</span>
                    Ongoing Encounter Detected
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-danger mb-3">
                    <strong>This patient has an active encounter that is not yet completed.</strong>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-warning text-dark">{{ $ongoingEncounter->status }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Nature of Visit</span>
                        <span class="fw-medium">{{ $ongoingEncounter->nature_of_visit }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Date</span>
                        <span class="fw-medium">{{ $ongoingEncounter->created_at->format('M d, Y H:i') }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Facility</span>
                        <span class="fw-medium">{{ $ongoingEncounter->facility->name ?? 'N/A' }}</span>
                    </li>
                </ul>
                <div class="mt-3">
                    <a href="{{ route('receptionist.encounters.show', $ongoingEncounter) }}" class="btn btn-outline-danger">
                        <span class="material-symbols-outlined align-middle me-1">visibility</span>
                        View Ongoing Encounter
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Registration Status -->
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="mb-0 fw-semibold">Registration Status</h6>
            </div>
            <div class="card-body p-4">
                @if($beneficiary->patient)
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <span class="material-symbols-outlined me-2">check_circle</span>
                        <div>
                            <strong>Patient Record Exists</strong>
                            <br><small class="mb-0">File No: {{ $beneficiary->patient->file_number }}</small>
                        </div>
                    </div>
                    @if(count($recentEncounters) > 0)
                    <div class="mt-3">
                        <span class="fw-medium">{{ count($recentEncounters) }} recent encounter(s)</span>
                    </div>
                    @endif
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-outlined me-2">person_add</span>
                        <div>
                            <strong>New Patient Registration</strong>
                            <br><small class="mb-0">Patient record will be created during check-in</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Encounters -->
        @if(count($recentEncounters) > 0)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="mb-0 fw-semibold">Recent Encounters</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($recentEncounters as $enc)
                    <li class="list-group-item px-4 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">{{ $enc->nature_of_visit }}</span>
                                <br><small class="text-muted">{{ $enc->created_at->format('M d, Y') }} at {{ $enc->facility->name ?? 'N/A' }}</small>
                            </div>
                            <span class="badge bg-{{ $enc->status === 'Completed' ? 'success' : 'secondary' }}">{{ $enc->status }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <!-- Check-in Form -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">login</span>
                    Create New Encounter
                </h5>
            </div>
            <div class="card-body p-4">
                @if($ongoingEncounter)
                <div class="alert alert-danger d-flex align-items-start mb-4">
                    <span class="material-symbols-outlined me-2 mt-1">error</span>
                    <div>
                        <strong>Warning: Patient has an ongoing encounter!</strong>
                        <p class="mb-2">Current status: <span class="badge bg-warning text-dark">{{ $ongoingEncounter->status }}</span></p>
                        <p class="mb-0 small">Creating a new encounter is not recommended. Please complete or cancel the existing encounter first, or proceed only if this is a different visit type (e.g., Emergency).</p>
                    </div>
                </div>
                @endif
                
                <form action="{{ route('receptionist.beneficiaries.checkin', $beneficiary) }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nature of Visit <span class="text-danger">*</span></label>
                            <select name="nature_of_visit" class="form-select @error('nature_of_visit') is-invalid @enderror" required>
                                <option value="">Select nature of visit...</option>
                                <option value="New Visit" {{ old('nature_of_visit') == 'New Visit' ? 'selected' : '' }}>New Visit</option>
                                <option value="Follow-up" {{ old('nature_of_visit') == 'Follow-up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="Emergency" {{ old('nature_of_visit') == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="Routine Check" {{ old('nature_of_visit') == 'Routine Check' ? 'selected' : '' }}>Routine Check</option>
                                <option value="Antenatal" {{ old('nature_of_visit') == 'Antenatal' ? 'selected' : '' }}>Antenatal</option>
                                <option value="Postnatal" {{ old('nature_of_visit') == 'Postnatal' ? 'selected' : '' }}>Postnatal</option>
                                <option value="Immunization" {{ old('nature_of_visit') == 'Immunization' ? 'selected' : '' }}>Immunization</option>
                                <option value="Lab Only" {{ old('nature_of_visit') == 'Lab Only' ? 'selected' : '' }}>Lab Only</option>
                                <option value="Pharmacy Only" {{ old('nature_of_visit') == 'Pharmacy Only' ? 'selected' : '' }}>Pharmacy Only</option>
                            </select>
                            @error('nature_of_visit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Date of Visit <span class="text-danger">*</span></label>
                            <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror" 
                                   value="{{ old('visit_date', now()->format('Y-m-d')) }}" required>
                            @error('visit_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="col-12">
                            <label class="form-label fw-medium">Chief Complaint</label>
                            <textarea name="chief_complaint" class="form-control @error('chief_complaint') is-invalid @enderror" 
                                      rows="4" placeholder="Enter patient's chief complaint or reason for visit...">{{ old('chief_complaint') }}</textarea>
                            @error('chief_complaint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Brief description of patient's main concern (optional)</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('receptionist.beneficiaries.search') }}" class="btn btn-light">
                            <span class="material-symbols-outlined me-1">arrow_back</span> Back to Search
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span class="material-symbols-outlined me-1">check_circle</span> Complete Check-in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Patient Check-In')

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
    <h3 class="mb-0">Patient Check-In</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Receptionist</a></li>
            <li class="breadcrumb-item active" aria-current="page">Check-In</li>
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
                    <div class="avatar avatar-xl bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        @if($patient->photo)
                            <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/'$patient->photo) }}" alt="Patient" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <span class="material-symbols-outlined text-muted" style="font-size: 48px;">person</span>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ $patient->fullname ?? $patient->name }}</h5>
                    <span class="badge bg-primary">{{ $patient->boschma_no ?? $patient->enrollee_number }}</span>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Type</span>
                        <span class="badge bg-{{ $patient->enrollee_type == 'beneficiary' ? 'success' : ($patient->enrollee_type == 'spouse' ? 'info' : 'warning') }}">
                            {{ ucfirst($patient->enrollee_type ?? 'Unknown') }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Gender</span>
                        <strong>{{ $patient->gender ?? 'N/A' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Date of Birth</span>
                        <strong>{{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'N/A' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Phone</span>
                        <strong>{{ $patient->phone ?? 'N/A' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Facility</span>
                        <strong>{{ $patient->facility?->name ?? 'N/A' }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Create Encounter</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('encounters.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program <span class="text-danger">*</span></label>
                            <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                <option value="">Select Program</option>
                                @foreach($programs ?? [] as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Nature of Visit <span class="text-danger">*</span></label>
                            <select name="nature_of_visit" class="form-select @error('nature_of_visit') is-invalid @enderror" required>
                                <option value="">Select Nature</option>
                                <option value="New" {{ old('nature_of_visit') == 'New' ? 'selected' : '' }}>New Visit</option>
                                <option value="Follow-up" {{ old('nature_of_visit') == 'Follow-up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="Emergency" {{ old('nature_of_visit') == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="Referral" {{ old('nature_of_visit') == 'Referral' ? 'selected' : '' }}>Referral</option>
                            </select>
                            @error('nature_of_visit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Mode of Entry <span class="text-danger">*</span></label>
                            <select name="mode_of_entry" class="form-select @error('mode_of_entry') is-invalid @enderror" required>
                                <option value="">Select Mode</option>
                                <option value="Walk-in" {{ old('mode_of_entry') == 'Walk-in' ? 'selected' : '' }}>Walk-in</option>
                                <option value="Ambulance" {{ old('mode_of_entry') == 'Ambulance' ? 'selected' : '' }}>Ambulance</option>
                                <option value="Referral" {{ old('mode_of_entry') == 'Referral' ? 'selected' : '' }}>Referral</option>
                            </select>
                            @error('mode_of_entry')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Visit Date</label>
                            <input type="datetime-local" name="visit_date" class="form-control" value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Reason for Visit</label>
                            <textarea name="reason_for_visit" class="form-control" rows="3" placeholder="Brief description of the reason for visit...">{{ old('reason_for_visit') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined me-1">how_to_reg</span> Complete Check-In
                        </button>
                        <a href="{{ route('patients.search') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        
        @if(isset($recentEncounters) && $recentEncounters->count() > 0)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Recent Visits</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Program</th>
                                <th>Nature</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEncounters as $encounter)
                            <tr>
                                <td>{{ $encounter->visit_date->format('d M Y H:i') }}</td>
                                <td>{{ $encounter->program?->name ?? 'N/A' }}</td>
                                <td>{{ $encounter->nature_of_visit }}</td>
                                <td>
                                    <span class="badge bg-{{ $encounter->status == 'Completed' ? 'success' : 'warning' }}">
                                        {{ $encounter->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Encounter Details')

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
    <h3 class="mb-0">Encounter Details</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.encounters.index') }}">Encounters</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Patient Info -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="text-center">
                    <div class="wh-80 bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 overflow-hidden">
                        @if($encounter->patient->enrollee_photo)
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
                        <span class="fw-medium">
                            @if($encounter->patient->enrollee_dob)
                                {{ \Carbon\Carbon::parse($encounter->patient->enrollee_dob)->age }} years
                            @else
                                N/A
                            @endif
                        </span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Phone</span>
                        <span class="fw-medium">{{ $encounter->patient->enrollee_phone ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Program</span>
                        <span class="fw-medium">{{ $encounter->program->name ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Actions Timeline -->
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="mb-0 fw-semibold">Activity Timeline</h6>
            </div>
            <div class="card-body p-4">
                @forelse($encounter->actions ?? [] as $action)
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="wh-32 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                            <span class="material-symbols-outlined text-primary fs-6">schedule</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <span class="fw-medium">{{ $action->action_type->value ?? $action->action_type }}</span>
                        <br><small class="text-muted">{{ $action->user->name ?? 'System' }} • {{ $action->action_time->diffForHumans() }}</small>
                        @if($action->description)
                            <br><small class="text-secondary">{{ $action->description }}</small>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No activity recorded yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Encounter Details -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Encounter Information</h5>
                @php
                    $statusColors = [
                        'Registered' => 'info',
                        'Triaged' => 'warning',
                        'In Consultation' => 'primary',
                        'Awaiting Lab' => 'secondary',
                        'Awaiting Pharmacy' => 'dark',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                    ];
                    $color = $statusColors[$encounter->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $color }} px-3 py-2">{{ $encounter->status }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Encounter Date</label>
                        <p class="fw-medium mb-0">{{ $encounter->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Nature of Visit</label>
                        <p class="fw-medium mb-0">{{ $encounter->nature_of_visit }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Mode of Entry</label>
                        <p class="fw-medium mb-0">{{ $encounter->mode_of_entry }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Registered By</label>
                        <p class="fw-medium mb-0">{{ $encounter->officerInCharge->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Chief Complaint</label>
                        <p class="fw-medium mb-0">{{ $encounter->reason_for_visit ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vital Signs (if available) -->
        @if($encounter->vitalSigns && $encounter->vitalSigns->count() > 0)
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Vital Signs</h5>
            </div>
            <div class="card-body p-4">
                @foreach($encounter->vitalSigns as $vitals)
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-danger mb-2">favorite</span>
                            <h5 class="mb-0">{{ $vitals->blood_pressure ?? 'N/A' }}</h5>
                            <small class="text-muted">Blood Pressure</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-warning mb-2">thermostat</span>
                            <h5 class="mb-0">{{ $vitals->temperature ?? 'N/A' }}°C</h5>
                            <small class="text-muted">Temperature</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-info mb-2">monitor_heart</span>
                            <h5 class="mb-0">{{ $vitals->pulse_rate ?? 'N/A' }}</h5>
                            <small class="text-muted">Pulse Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <span class="material-symbols-outlined text-primary mb-2">scale</span>
                            <h5 class="mb-0">{{ $vitals->weight ?? 'N/A' }} kg</h5>
                            <small class="text-muted">Weight</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-light">
                        <span class="material-symbols-outlined me-1">arrow_back</span> Back to List
                    </a>
                    <div>
                        @if($encounter->status === 'Registered')
                        <form action="{{ route('receptionist.encounters.forward-nurse', $encounter) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <span class="material-symbols-outlined me-1">send</span> Forward to Nurse
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <span class="material-symbols-outlined me-1">cancel</span> Cancel Encounter
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
@if($encounter->status === 'Registered')
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('receptionist.encounters.cancel', $encounter) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Encounter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this encounter?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Encounter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

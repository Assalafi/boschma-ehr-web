@extends('layouts.app')

@section('title', 'Administer Drug')

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
    <h3 class="mb-0">Administer Drug</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.drug-administration.index') }}">Drug Administration</a></li>
            <li class="breadcrumb-item active" aria-current="page">Administer</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h6 class="mb-0 fw-semibold">Patient Information</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Name</span>
                        <span class="fw-medium">{{ $prescriptionItem->prescription->consultation->encounter->patient->enrollee_name ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">BOSCHMA No</span>
                        <span class="fw-medium">{{ $prescriptionItem->prescription->consultation->encounter->patient->enrollee_number ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Gender</span>
                        <span class="fw-medium">{{ $prescriptionItem->prescription->consultation->encounter->patient->enrollee_gender ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-success text-white p-4">
                <h6 class="mb-0 fw-semibold">Drug Information</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Drug Name</span>
                        <span class="fw-medium">{{ $prescriptionItem->drug->name ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Form</span>
                        <span class="fw-medium">{{ $prescriptionItem->drug->form ?? 'N/A' }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Dosage</span>
                        <span class="fw-medium">{{ $prescriptionItem->dosage }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Frequency</span>
                        <span class="fw-medium">{{ $prescriptionItem->frequency }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Duration</span>
                        <span class="fw-medium">{{ $prescriptionItem->duration }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-info text-white p-4">
                <h6 class="mb-0 fw-semibold">Administration Progress</h6>
            </div>
            <div class="card-body p-4">
                @php
                    $totalAdministered = $prescriptionItem->total_administered ?? 0;
                    $remaining = $prescriptionItem->quantity - $totalAdministered;
                    $progress = $prescriptionItem->quantity > 0 ? ($totalAdministered / $prescriptionItem->quantity) * 100 : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Progress</span>
                        <span>{{ $totalAdministered }} / {{ $prescriptionItem->quantity }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Prescribed Qty</span>
                        <span class="fw-medium">{{ $prescriptionItem->quantity }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Administered</span>
                        <span class="badge bg-success">{{ $totalAdministered }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Remaining</span>
                        <span class="badge bg-{{ $remaining > 0 ? 'warning' : 'success' }}">{{ $remaining }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">
                    <span class="material-symbols-outlined me-2 align-middle">medication</span>
                    Record Administration
                </h5>
            </div>
            <div class="card-body p-4">
                @if($remaining > 0)
                <form action="{{ route('nurse.drug-administration.store', $prescriptionItem) }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Quantity to Administer <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity', 1) }}" min="1" max="{{ $remaining }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum: {{ $remaining }}</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Any observations or notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('nurse.drug-administration.index') }}" class="btn btn-outline-secondary">
                            <span class="material-symbols-outlined align-middle me-1">arrow_back</span>
                            Back
                        </a>
                        <button type="submit" class="btn btn-success">
                            <span class="material-symbols-outlined align-middle me-1">check</span>
                            Record Administration
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-success">
                    <span class="material-symbols-outlined align-middle me-2">check_circle</span>
                    All doses have been administered for this prescription item.
                </div>
                <a href="{{ route('nurse.drug-administration.index') }}" class="btn btn-outline-primary">
                    <span class="material-symbols-outlined align-middle me-1">arrow_back</span>
                    Back to List
                </a>
                @endif
            </div>
        </div>

        <!-- Administration History -->
        @if($prescriptionItem->administrations->count() > 0)
        <div class="card border-0 rounded-3">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Administration History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Time</th>
                                <th>Dose Given</th>
                                <th>Administered By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptionItem->administrations as $admin)
                            <tr>
                                <td>{{ $admin->administration_date_time->format('M d, Y H:i') }}</td>
                                <td><span class="badge bg-success">{{ $admin->dose_given }}</span></td>
                                <td>{{ $admin->administeringOfficer->name ?? 'N/A' }}</td>
                                <td>{{ $admin->notes ?? '-' }}</td>
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

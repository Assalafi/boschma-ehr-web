@extends('layouts.app')

@section('title', 'Patient Search')

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
    <h3 class="mb-0">Patient Search</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Receptionist</a></li>
            <li class="breadcrumb-item active" aria-current="page">Patient Search</li>
        </ol>
    </nav>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('patients.search') }}" method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><span class="material-symbols-outlined">search</span></span>
                    <input type="text" class="form-control" name="q" placeholder="Search by BOSCHMA ID, Name, Phone Number, or File Number..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="beneficiary" {{ request('type') == 'beneficiary' ? 'selected' : '' }}>Beneficiary</option>
                    <option value="spouse" {{ request('type') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                    <option value="child" {{ request('type') == 'child' ? 'selected' : '' }}>Child</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <span class="material-symbols-outlined me-1">search</span> Search
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($patients) && $patients->count() > 0)
<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-semibold">Search Results ({{ $patients->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>BOSCHMA ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Facility</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                    <tr>
                        <td><span class="badge bg-primary">{{ $patient->boschma_no ?? $patient->enrollee_number }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-outlined text-muted">person</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $patient->fullname ?? $patient->name }}</h6>
                                    <small class="text-muted">{{ $patient->file_number ?? 'No file number' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $patient->enrollee_type == 'beneficiary' ? 'success' : ($patient->enrollee_type == 'spouse' ? 'info' : 'warning') }}">
                                {{ ucfirst($patient->enrollee_type ?? 'Unknown') }}
                            </span>
                        </td>
                        <td>{{ $patient->gender ?? 'N/A' }}</td>
                        <td>{{ $patient->phone ?? 'N/A' }}</td>
                        <td>{{ $patient->facility?->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-primary" title="View">
                                    <span class="material-symbols-outlined fs-6">visibility</span>
                                </a>
                                <a href="{{ route('patients.checkin', $patient->id) }}" class="btn btn-outline-success" title="Check In">
                                    <span class="material-symbols-outlined fs-6">how_to_reg</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($patients->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $patients->withQueryString()->links() }}
    </div>
    @endif
</div>
@elseif(request('q'))
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-5 text-center">
        <span class="material-symbols-outlined text-muted" style="font-size: 64px;">search_off</span>
        <h5 class="mt-3">No patients found</h5>
        <p class="text-muted">No patients match your search criteria "{{ request('q') }}"</p>
    </div>
</div>
@else
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-5 text-center">
        <span class="material-symbols-outlined text-muted" style="font-size: 64px;">person_search</span>
        <h5 class="mt-3">Search for a patient</h5>
        <p class="text-muted">Enter a BOSCHMA ID, name, phone number, or file number to find a patient</p>
    </div>
</div>
@endif
@endsection

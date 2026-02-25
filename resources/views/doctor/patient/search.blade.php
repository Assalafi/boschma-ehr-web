@extends('layouts.app')

@section('title', 'Patient Search')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Patient Records</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" class="text-decoration-none">Doctor</a></li>
            <li class="breadcrumb-item active">Patient Search</li>
        </ol>
    </nav>
</div>

{{-- Search Bar --}}
<div class="card border-0 rounded-3 mb-4 shadow-sm">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('doctor.patients') }}" id="searchForm">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0">
                    <span class="material-symbols-outlined text-muted">search</span>
                </span>
                <input type="text" name="q" class="form-control border-start-0 ps-0" 
                    placeholder="Search by name, BOSCHMA ID, file number, phone or NIN..."
                    value="{{ $query }}" autocomplete="off" id="searchInput">
                @if($query)
                <a href="{{ route('doctor.patients') }}" class="btn btn-outline-secondary">
                    <span class="material-symbols-outlined align-middle">close</span>
                </a>
                @endif
                <button class="btn btn-primary px-4" type="submit">Search</button>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
@if($query)
    @if($patients->isEmpty())
        <div class="card border-0 rounded-3 shadow-sm">
            <div class="card-body text-center py-5">
                <span class="material-symbols-outlined text-muted" style="font-size:56px">person_search</span>
                <p class="text-muted mt-3 mb-1">No patients found for <strong>"{{ $query }}"</strong></p>
                <small class="text-muted">Try searching by name, ID, phone or NIN</small>
            </div>
        </div>
    @else
        <p class="text-muted small mb-3">{{ $patients->count() }} result(s) for "<strong>{{ $query }}</strong>"</p>
        <div class="row g-3">
            @foreach($patients as $p)
                @php
                    $info = $p->enrollee;
                    $lastEncounter = $p->encounters()->latest('visit_date')->first();
                @endphp
                <div class="col-xl-4 col-md-6">
                    <a href="{{ route('doctor.patient.dashboard', $p) }}" class="text-decoration-none">
                        <div class="card border-0 rounded-3 shadow-sm h-100 patient-card">
                            <div class="card-body p-3 d-flex gap-3 align-items-start">
                                <div class="flex-shrink-0">
                                    @if($info?->photo)
                                        <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $info->photo }}" class="rounded-circle object-fit-cover" width="52" height="52" alt="">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                                            <span class="material-symbols-outlined text-primary">person</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-dark">{{ $info?->fullname ?? 'Unknown' }}</div>
                                    <div class="text-muted small">{{ $p->enrollee_number }} &bull; {{ ucfirst($p->enrollee_type) }}</div>
                                    <div class="text-muted small">
                                        @if($info?->gender) {{ $info->gender }} &bull; @endif
                                        @if($info?->date_of_birth) Age {{ \Carbon\Carbon::parse($info->date_of_birth)->age }} @endif
                                    </div>
                                    @if($lastEncounter)
                                    <div class="mt-1">
                                        <span class="badge bg-{{ $lastEncounter->status === 'Completed' ? 'success' : 'warning text-dark' }} bg-opacity-10 text-{{ $lastEncounter->status === 'Completed' ? 'success' : 'warning' }} border border-{{ $lastEncounter->status === 'Completed' ? 'success' : 'warning' }}" style="font-size:11px">
                                            Last visit: {{ $lastEncounter->visit_date?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                <span class="material-symbols-outlined text-muted">chevron_right</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@else
    <div class="card border-0 rounded-3 shadow-sm">
        <div class="card-body text-center py-5">
            <span class="material-symbols-outlined text-primary" style="font-size:64px;opacity:.3">manage_search</span>
            <p class="text-muted mt-3 mb-1 fs-5">Search for a Patient</p>
            <small class="text-muted">Enter a name, BOSCHMA ID, file number, phone or NIN above</small>
        </div>
    </div>
@endif

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.patient-card { transition: all .15s ease; border: 1px solid var(--doc-border) !important; border-radius: 14px !important; }
.patient-card:hover { border-color: var(--doc-primary) !important; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(10,110,94,.12) !important; }
.card { border-radius: 14px !important; border-color: var(--doc-border) !important; }
.input-group-lg .form-control { border-radius: 0 !important; font-size: 14px; }
.input-group-lg .input-group-text { border-radius: 14px 0 0 14px !important; }
.input-group-lg .btn { border-radius: 0 14px 14px 0 !important; background: var(--doc-primary); border-color: var(--doc-primary); font-weight: 600; }
.input-group-lg .btn:hover { background: var(--doc-primary-dark); border-color: var(--doc-primary-dark); }
.badge { border-radius: 20px; font-weight: 600; }
</style>

@endsection

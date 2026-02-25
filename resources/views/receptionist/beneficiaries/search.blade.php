@extends('layouts.app')

@section('title', 'Search Beneficiary')

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
    <h3 class="mb-0">Search Beneficiary</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search</li>
        </ol>
    </nav>
</div>

<!-- Search Card -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('receptionist.beneficiaries.search') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Search Beneficiary</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                        <input type="text" name="q" class="form-control" 
                               placeholder="Enter name, BOSCHMA ID, NIN, or phone number..." 
                               value="{{ $query ?? '' }}" autofocus>
                    </div>
                    <small class="text-muted">Only beneficiaries registered at your facility will be shown</small>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <span class="material-symbols-outlined me-2">search</span> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
@if(isset($query) && $query)
<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-semibold">
            Search Results 
            <span class="badge bg-primary ms-2">{{ count($beneficiaries) }} found</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Beneficiary</th>
                        <th>BOSCHMA ID</th>
                        <th>NIN</th>
                        <th>Phone</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beneficiaries as $beneficiary)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($beneficiary->photo)
                                        <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $beneficiary->photo }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-primary fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $beneficiary->name }}</span>
                                    <br><small class="text-muted">{{ $beneficiary->gender }} • {{ $beneficiary->dob ? \Carbon\Carbon::parse($beneficiary->dob)->age . ' yrs' : 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark fw-medium">{{ $beneficiary->boschma_no }}</span></td>
                        <td>{{ $beneficiary->nin ?? 'N/A' }}</td>
                        <td>{{ $beneficiary->phone ?? 'N/A' }}</td>
                        <td>{{ $beneficiary->program->name ?? 'N/A' }}</td>
                        <td>
                            @if($beneficiary->status === 'Active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">{{ $beneficiary->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('receptionist.beneficiaries.show', [$beneficiary->type, $beneficiary->id]) }}" class="btn btn-sm btn-primary">
                                <span class="material-symbols-outlined fs-6">login</span> Register
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined fs-1 d-block mb-2">search_off</span>
                            No beneficiaries found matching "{{ $query }}"
                            <br><small>Make sure the beneficiary is registered at your facility</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<!-- Initial State -->
<div class="card border-0 rounded-3">
    <div class="card-body p-5 text-center">
        <span class="material-symbols-outlined text-muted" style="font-size: 5rem;">person_search</span>
        <h5 class="mt-3 mb-2">Search for a Beneficiary</h5>
        <p class="text-muted mb-0">Enter the beneficiary's name, BOSCHMA ID, NIN, or phone number to begin check-in</p>
    </div>
</div>
@endif
@endsection

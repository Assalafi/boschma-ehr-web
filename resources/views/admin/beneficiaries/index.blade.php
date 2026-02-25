@extends('layouts.app')

@section('title', 'Beneficiary Management')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Beneficiary Management</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Beneficiaries</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-primary bg-opacity-10">
            <div class="card-body p-3 text-center">
                <h3 class="mb-0 fw-bold text-primary">{{ $totalBeneficiaries ?? 0 }}</h3>
                <small class="text-muted">Total Beneficiaries</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-success bg-opacity-10">
            <div class="card-body p-3 text-center">
                <h3 class="mb-0 fw-bold text-success">{{ $activeBeneficiaries ?? 0 }}</h3>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-info bg-opacity-10">
            <div class="card-body p-3 text-center">
                <h3 class="mb-0 fw-bold text-info">{{ $totalSpouses ?? 0 }}</h3>
                <small class="text-muted">Spouses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-warning bg-opacity-10">
            <div class="card-body p-3 text-center">
                <h3 class="mb-0 fw-bold text-warning">{{ $totalChildren ?? 0 }}</h3>
                <small class="text-muted">Children</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
        <h5 class="mb-0 fw-semibold">All Beneficiaries</h5>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.beneficiaries.index') }}" class="btn btn-{{ !request('status') ? 'primary' : 'outline-primary' }}">All</a>
            <a href="{{ route('admin.beneficiaries.index', ['status' => 'active']) }}" class="btn btn-{{ request('status') == 'active' ? 'success' : 'outline-success' }}">Active</a>
            <a href="{{ route('admin.beneficiaries.index', ['status' => 'pending']) }}" class="btn btn-{{ request('status') == 'pending' ? 'warning' : 'outline-warning' }}">Pending</a>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.beneficiaries.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by BOSCHMA ID, name, NIN, or phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="facility" class="form-select">
                    <option value="">All Facilities</option>
                    @foreach($facilities ?? [] as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="program" class="form-select">
                    <option value="">All Programs</option>
                    @foreach($programs ?? [] as $program)
                    <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>BOSCHMA ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Facility</th>
                        <th>Dependants</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beneficiaries ?? [] as $beneficiary)
                    <tr>
                        <td><span class="badge bg-primary">{{ $beneficiary->boschma_no }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    @if($beneficiary->photo)
                                    <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $beneficiary->photo }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="avatar avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-outlined text-muted">person</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $beneficiary->fullname }}</h6>
                                    <small class="text-muted">{{ $beneficiary->gender }} | {{ $beneficiary->date_of_birth?->age ?? 'N/A' }} yrs</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $beneficiary->phone ?? 'N/A' }}</td>
                        <td>{{ $beneficiary->facility?->name ?? 'N/A' }}</td>
                        <td>
                            @if($beneficiary->spouse)
                            <span class="badge bg-info">1 Spouse</span>
                            @endif
                            @if($beneficiary->children_count ?? $beneficiary->children->count())
                            <span class="badge bg-warning">{{ $beneficiary->children_count ?? $beneficiary->children->count() }} Children</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $beneficiary->status == 'Active' ? 'success' : ($beneficiary->status == 'Pending' ? 'warning' : 'secondary') }}">
                                {{ $beneficiary->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.beneficiaries.show', $beneficiary->id) }}" class="btn btn-outline-primary" title="View">
                                    <span class="material-symbols-outlined fs-6">visibility</span>
                                </a>
                                <a href="{{ route('admin.beneficiaries.edit', $beneficiary->id) }}" class="btn btn-outline-warning" title="Edit">
                                    <span class="material-symbols-outlined fs-6">edit</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No beneficiaries found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($beneficiaries) && $beneficiaries->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $beneficiaries->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

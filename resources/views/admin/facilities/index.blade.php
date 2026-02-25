@extends('layouts.app')

@section('title', 'Facility Management')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="mb-0">Facility Management</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Facilities</li>
        </ol>
    </nav>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
        <h5 class="mb-0 fw-semibold">All Facilities</h5>
        <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary btn-sm">
            <span class="material-symbols-outlined me-1">add_business</span> Add Facility
        </a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.facilities.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Primary" {{ request('type') == 'Primary' ? 'selected' : '' }}>Primary</option>
                    <option value="Secondary" {{ request('type') == 'Secondary' ? 'selected' : '' }}>Secondary</option>
                    <option value="Tertiary" {{ request('type') == 'Tertiary' ? 'selected' : '' }}>Tertiary</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Facility Name</th>
                        <th>Type</th>
                        <th>LGA</th>
                        <th>Beneficiaries</th>
                        <th>Staff</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilities ?? [] as $facility)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $facility->code ?? 'N/A' }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-outlined text-primary">location_city</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $facility->name }}</h6>
                                    <small class="text-muted">{{ $facility->address ?? 'No address' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $facility->type == 'Primary' ? 'info' : ($facility->type == 'Secondary' ? 'warning' : 'danger') }}">
                                {{ $facility->type ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $facility->lga?->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-primary">{{ $facility->beneficiaries_count ?? $facility->beneficiaries->count() }}</span></td>
                        <td><span class="badge bg-info">{{ $facility->users_count ?? $facility->users->count() }}</span></td>
                        <td>
                            <span class="badge bg-{{ $facility->is_active ? 'success' : 'danger' }}">
                                {{ $facility->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.facilities.show', $facility->id) }}" class="btn btn-outline-primary" title="View">
                                    <span class="material-symbols-outlined fs-6">visibility</span>
                                </a>
                                <a href="{{ route('admin.facilities.edit', $facility->id) }}" class="btn btn-outline-warning" title="Edit">
                                    <span class="material-symbols-outlined fs-6">edit</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No facilities found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($facilities) && $facilities->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $facilities->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

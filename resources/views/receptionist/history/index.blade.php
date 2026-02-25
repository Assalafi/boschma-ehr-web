@extends('layouts.app')

@section('title', 'Encounter History')

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
    <h3 class="mb-0">Encounter History</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item active" aria-current="page">History</li>
        </ol>
    </nav>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-semibold">Search & Filter</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('receptionist.history') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search Patient</label>
                <div class="input-group">
                    <span class="input-group-text"><span class="material-symbols-outlined fs-6">search</span></span>
                    <input type="text" name="search" class="form-control" placeholder="Name or BOSCHMA ID..." value="{{ $search ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <span class="material-symbols-outlined me-1">filter_alt</span> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Encounter Records</h5>
        <span class="badge bg-primary">{{ $encounters->total() ?? 0 }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>Nature of Visit</th>
                        <th>Mode of Entry</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encounters as $encounter)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $encounter->created_at->format('M d, Y') }}</span>
                            <br><small class="text-muted">{{ $encounter->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->enrollee_photo)
                                        <img src="{{ $encounter->patient->enrollee_photo }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-primary fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $encounter->patient->enrollee_gender ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $encounter->patient->enrollee_number ?? 'N/A' }}</span></td>
                        <td>{{ $encounter->nature_of_visit }}</td>
                        <td>{{ $encounter->mode_of_entry }}</td>
                        <td>
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
                            <span class="badge bg-{{ $color }}">{{ $encounter->status }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('receptionist.encounters.show', $encounter) }}" class="btn btn-sm btn-outline-primary">
                                <span class="material-symbols-outlined fs-6">visibility</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined fs-1 d-block mb-2">history</span>
                            No encounter history found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($encounters->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $encounters->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

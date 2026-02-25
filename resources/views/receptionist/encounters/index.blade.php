@extends('layouts.app')

@section('title', 'Encounters')

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
    <h3 class="mb-0">Encounters</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item active" aria-current="page">Encounters</li>
        </ol>
    </nav>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
        <h5 class="mb-0 fw-semibold">Encounter List</h5>
        <a href="{{ route('receptionist.beneficiaries.search') }}" class="btn btn-primary btn-sm">
            <span class="material-symbols-outlined me-1">add</span> New Check-In
        </a>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('receptionist.encounters.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date ?? today()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach($statuses ?? [] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <span class="material-symbols-outlined me-1">filter_alt</span> Filter
                </button>
                <a href="{{ route('receptionist.encounters.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>Program</th>
                        <th>Nature of Visit</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encounters ?? [] as $encounter)
                    <tr>
                        <td>{{ $encounter->created_at->format('H:i') }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->beneficiary->photo)
                                        <img src="{{ asset('storage/' . $encounter->patient->beneficiary->photo) }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-primary fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $encounter->patient->beneficiary->fullname ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $encounter->patient->beneficiary->gender ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $encounter->patient->beneficiary->boschma_no ?? 'N/A' }}</span></td>
                        <td>{{ $encounter->program->name ?? 'N/A' }}</td>
                        <td>{{ $encounter->nature_of_visit }}</td>
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
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                    <span class="material-symbols-outlined fs-6">more_vert</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('receptionist.encounters.show', $encounter) }}">
                                            <span class="material-symbols-outlined me-2 fs-6">visibility</span> View Details
                                        </a>
                                    </li>
                                    @if($encounter->status === 'Registered')
                                    <li>
                                        <form action="{{ route('receptionist.encounters.forward-nurse', $encounter) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <span class="material-symbols-outlined me-2 fs-6">send</span> Forward to Nurse
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $encounter->id }}">
                                            <span class="material-symbols-outlined me-2 fs-6">cancel</span> Cancel
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined fs-1 d-block mb-2">inbox</span>
                            No encounters found for this date
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($encounters) && $encounters->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $encounters->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Cancel Modals -->
@foreach($encounters ?? [] as $encounter)
@if($encounter->status === 'Registered')
<div class="modal fade" id="cancelModal{{ $encounter->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('receptionist.encounters.cancel', $encounter) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Encounter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Cancel encounter for <strong>{{ $encounter->patient->beneficiary->fullname ?? 'this patient' }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
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
@endforeach
@endsection

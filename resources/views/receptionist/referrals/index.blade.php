@extends('layouts.app')

@section('title', 'Referrals')

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
    <h3 class="mb-0">Referral Patients</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item active" aria-current="page">Referrals</li>
        </ol>
    </nav>
</div>

<!-- Status Tabs -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ ($status ?? 'pending') === 'pending' ? 'active' : '' }}" href="{{ route('receptionist.referrals', ['status' => 'pending']) }}">
                    <span class="material-symbols-outlined me-1 fs-6">pending</span> Pending
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($status ?? '') === 'processed' ? 'active' : '' }}" href="{{ route('receptionist.referrals', ['status' => 'processed']) }}">
                    <span class="material-symbols-outlined me-1 fs-6">check_circle</span> Processed
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($status ?? '') === 'rejected' ? 'active' : '' }}" href="{{ route('receptionist.referrals', ['status' => 'rejected']) }}">
                    <span class="material-symbols-outlined me-1 fs-6">cancel</span> Rejected
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($status ?? '') === 'all' ? 'active' : '' }}" href="{{ route('receptionist.referrals', ['status' => 'all']) }}">
                    <span class="material-symbols-outlined me-1 fs-6">list</span> All
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($status ?? '') === 'outgoing' ? 'active' : '' }}" href="{{ route('receptionist.referrals', ['status' => 'outgoing']) }}">
                    <span class="material-symbols-outlined me-1 fs-6">outbound</span> Outgoing
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                @if(($status ?? 'pending') === 'pending')
                    Pending Referrals
                @elseif($status === 'processed')
                    Processed Referrals
                @elseif($status === 'rejected')
                    Rejected Referrals
                @elseif($status === 'outgoing')
                    Outgoing Referrals
                @else
                    All Referrals
                @endif
            </h5>
            <span class="badge bg-primary d-lg-none">{{ $referrals->total() ?? 0 }} records</span>
        </div>
        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
            <span class="badge bg-primary d-none d-lg-inline-flex me-2">{{ $referrals->total() ?? 0 }} records</span>
            
            <form action="{{ route('receptionist.referrals') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2 mb-0 w-100">
                <input type="hidden" name="status" value="{{ $status ?? 'pending' }}">
                <select name="program" class="form-select form-select-sm" style="min-width: 150px; width: auto;">
                    <option value="">All Programs</option>
                    @isset($programs)
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ ($program_id ?? '') == $prog->id ? 'selected' : '' }}>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
                <div class="input-group input-group-sm w-100" style="min-width: 280px; max-width: 450px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, ID..." value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary d-flex align-items-center" type="submit">
                        <span class="material-symbols-outlined fs-6">search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>{{ ($status ?? 'pending') === 'outgoing' ? 'Referred To' : 'Referred From' }}</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $referral)
                    @php
                        $patient = $referral->encounter->patient ?? null;
                        $isRejected = $referral->status === 'rejected' || ($referral->approval_status ?? '') === 'rejected';
                    @endphp
                    <tr class="{{ $isRejected ? 'table-danger-subtle' : '' }}">
                        <td>
                            <span class="fw-medium">{{ $referral->created_at ? \Carbon\Carbon::parse($referral->created_at)->format('M d, Y') : 'N/A' }}</span>
                            <br><small class="text-muted">{{ $referral->created_at ? \Carbon\Carbon::parse($referral->created_at)->format('H:i') : '' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden flex-shrink-0">
                                    @if($patient && $patient->enrollee_photo)
                                        <img src="{{ $patient->enrollee_photo }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-info fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $patient->enrollee_gender ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $patient->enrollee_number ?? 'N/A' }}</span></td>
                        <td>
                            @if(($status ?? 'pending') === 'outgoing')
                                <span class="fw-medium">{{ $referral->toFacility->name ?? 'Unknown' }}</span>
                            @else
                                <span class="fw-medium">{{ $referral->fromFacility->name ?? 'Unknown' }}</span>
                            @endif
                        </td>
                        <td style="min-width: 220px; max-width: 320px;">
                            <div class="mb-1">
                                <small class="text-secondary d-block">
                                    <strong>Referral:</strong> {{ Str::limit($referral->reason, 80) }}
                                </small>
                            </div>
                            @if($isRejected)
                                <div class="p-2 rounded bg-danger-subtle border border-danger-subtle text-danger" style="font-size: 12px; line-height: 1.35;">
                                    <div class="fw-bold d-flex align-items-center gap-1 mb-1">
                                        <span class="material-symbols-outlined" style="font-size: 15px;">cancel</span>
                                        <span>Reason for Rejection:</span>
                                    </div>
                                    <div class="text-dark fw-medium text-break">
                                        {{ $referral->rejection_reason ?: 'No reason specified' }}
                                    </div>
                                    @if($referral->rejected_at)
                                        <div class="text-muted mt-1" style="font-size: 11px;">
                                            Rejected {{ \Carbon\Carbon::parse($referral->rejected_at)->format('M d, Y H:i') }}
                                            @if($referral->rejectedByUser)
                                                • by {{ $referral->rejectedByUser->name }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($isRejected)
                                <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Rejected
                                </span>
                                @if($referral->rejection_reason)
                                    <div class="text-danger small mt-1" style="font-size: 11px; max-width: 170px;">
                                        <span class="fw-semibold">Reason:</span> {{ Str::limit($referral->rejection_reason, 40) }}
                                    </div>
                                @endif
                            @else
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                    $approvalColors = [
                                        'pending' => 'secondary',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                    $statusColor = $statusColors[$referral->status] ?? 'secondary';
                                    $approvalColor = $approvalColors[$referral->approval_status ?? 'pending'] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($referral->status) }}</span>
                                @if($referral->status === 'pending')
                                    <span class="badge bg-{{ $approvalColor }} ms-1">{{ ucfirst($referral->approval_status ?? 'pending') }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center" style="min-width: 130px;">
                            @if($isRejected)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-block mb-1 py-1">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">cancel</span> Rejected
                                </span>
                                <button type="button" 
                                    class="btn btn-sm btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center mb-1"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewRejectionModal"
                                    data-name="{{ $patient->enrollee_name ?? 'N/A' }}"
                                    data-boschma="{{ $patient->enrollee_number ?? 'N/A' }}"
                                    data-facility="{{ (($status ?? 'pending') === 'outgoing' ? ($referral->toFacility->name ?? 'Receiving Facility') : ($referral->fromFacility->name ?? 'Referring Facility')) }}"
                                    data-reason="{{ $referral->reason ?? 'N/A' }}"
                                    data-rejection-reason="{{ $referral->rejection_reason ?: 'No rejection reason specified' }}"
                                    data-rejected-at="{{ $referral->rejected_at ? \Carbon\Carbon::parse($referral->rejected_at)->format('M d, Y H:i') : '' }}"
                                    data-rejected-by="{{ $referral->rejectedByUser->name ?? '' }}">
                                    <span class="material-symbols-outlined me-1" style="font-size:16px">info</span> View Reason
                                </button>
                            @elseif(($status ?? 'pending') !== 'outgoing' && $referral->status === 'pending' && $referral->approval_status === 'approved')
                                <button type="button" 
                                    class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center mb-1"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#registerReferralModal"
                                    data-action="{{ route('receptionist.referrals.register', $referral) }}"
                                    data-name="{{ $patient->enrollee_name ?? 'N/A' }}"
                                    data-boschma="{{ $patient->enrollee_number ?? 'N/A' }}"
                                    data-facility="{{ $referral->fromFacility->name ?? 'Referring Facility' }}"
                                    data-reason="{{ $referral->reason ?? 'General Referral' }}"
                                    data-date="{{ $referral->created_at ? \Carbon\Carbon::parse($referral->created_at)->format('M d, Y H:i') : '' }}">
                                    <span class="material-symbols-outlined me-1 fs-6 align-middle">person_add</span> Register
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center mb-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectReferralModal"
                                    data-action="{{ route('receptionist.referrals.reject', $referral) }}"
                                    data-name="{{ $patient->enrollee_name ?? 'N/A' }}"
                                    data-boschma="{{ $patient->enrollee_number ?? 'N/A' }}"
                                    data-facility="{{ $referral->fromFacility->name ?? 'Referring Facility' }}">
                                    <span class="material-symbols-outlined me-1 fs-6 align-middle">close</span> Reject
                                </button>
                            @elseif($referral->status === 'pending')
                                <span class="badge bg-secondary-subtle text-secondary d-block mb-1">Awaiting {{ $referral->approval_status === 'approved' ? 'Registration' : 'Approval' }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success d-block mb-1">Registered</span>
                            @endif
                            
                            @if(($referral->approval_status === 'approved' || $referral->status !== 'pending') && !$isRejected)
                            <a href="{{ route('doctor.consultation.referral-pdf', $referral->id) }}" class="btn btn-sm btn-success w-100 d-inline-flex align-items-center justify-content-center mt-1" title="Download Referral Slip (PDF)" target="_blank">
                                <span class="material-symbols-outlined align-middle" style="font-size:16px">download</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined fs-1 d-block mb-2">swap_horiz</span>
                            No referral patients found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($referrals->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $referrals->withQueryString()->links() }}
    </div>
    @endif
</div>

@include('receptionist.referrals._register_modal')
@include('receptionist.referrals._reject_modal')
@include('receptionist.referrals._view_rejection_modal')
@endsection

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
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $referral->created_at ? \Carbon\Carbon::parse($referral->created_at)->format('M d, Y') : 'N/A' }}</span>
                            <br><small class="text-muted">{{ $referral->created_at ? \Carbon\Carbon::parse($referral->created_at)->format('H:i') : '' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
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
                        <td style="max-width:200px"><small>{{ Str::limit($referral->reason, 80) }}</small></td>
                        <td>
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
                        </td>
                        <td class="text-center">
                            @if(($status ?? 'pending') !== 'outgoing' && $referral->status === 'pending' && $referral->approval_status === 'approved')
                            <form action="{{ route('receptionist.referrals.register', $referral) }}" method="POST" class="d-block mb-1" onsubmit="return confirm('Register this referred patient and create an encounter?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <span class="material-symbols-outlined me-1 fs-6 align-middle">person_add</span> Register
                                </button>
                            </form>
                            @elseif($referral->status === 'pending' && $referral->approval_status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger d-block mb-1">Rejected</span>
                            @elseif($referral->status === 'pending')
                                <span class="badge bg-secondary-subtle text-secondary d-block mb-1">Awaiting {{ $referral->approval_status === 'approved' ? 'Registration' : 'Approval' }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success d-block mb-1">Registered</span>
                            @endif
                            
                            @if($referral->approval_status === 'approved' || $referral->status !== 'pending')
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
@endsection

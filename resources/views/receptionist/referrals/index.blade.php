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
        </ul>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">
            @if(($status ?? 'pending') === 'pending')
                Pending Referrals
            @elseif($status === 'processed')
                Processed Referrals
            @else
                All Referrals
            @endif
        </h5>
        <span class="badge bg-primary">{{ $referrals->total() ?? 0 }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>Referred From</th>
                        <th>Nature of Visit</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $encounter)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $encounter->created_at->format('M d, Y') }}</span>
                            <br><small class="text-muted">{{ $encounter->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->enrollee_photo)
                                        <img src="{{ asset('storage/' . $encounter->patient->enrollee_photo) }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-info fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $encounter->patient->enrollee_gender ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $encounter->patient->enrollee_number ?? 'N/A' }}</span></td>
                        <td>External Referral</td>
                        <td>{{ $encounter->nature_of_visit }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'Pending' => 'warning',
                                    'Registered' => 'info',
                                    'Triaged' => 'primary',
                                    'In Consultation' => 'primary',
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
                                    @if(in_array($encounter->status, ['Pending', 'Registered']))
                                    <li>
                                        <form action="{{ route('receptionist.encounters.forward-nurse', $encounter) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <span class="material-symbols-outlined me-2 fs-6">send</span> Forward to Nurse
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
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

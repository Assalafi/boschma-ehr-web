@extends('layouts.app')

@section('title', 'Drug Administration')

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
    <h3 class="mb-0">Drug Administration</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item active" aria-current="page">Drug Administration</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 rounded-3">
    <div class="card-header bg-white border-bottom p-4">
        <h5 class="mb-0 fw-semibold">
            <span class="material-symbols-outlined me-2 align-middle">medication</span>
            Pending Drug Administrations
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Patient</th>
                        <th>Drug</th>
                        <th>Dosage</th>
                        <th>Quantity</th>
                        <th>Administered</th>
                        <th>Remaining</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    <span class="material-symbols-outlined text-success fs-6">person</span>
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $item->prescription->consultation->encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $item->prescription->consultation->encounter->patient->enrollee_number ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $item->drug->name ?? 'N/A' }}</span>
                            <br><small class="text-muted">{{ $item->drug->form ?? '' }}</small>
                        </td>
                        <td>{{ $item->dosage }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            <span class="badge bg-info">{{ $item->total_administered ?? 0 }}</span>
                        </td>
                        <td>
                            @php
                                $remaining = $item->quantity - ($item->total_administered ?? 0);
                            @endphp
                            <span class="badge bg-{{ $remaining > 0 ? 'warning' : 'success' }}">{{ $remaining }}</span>
                        </td>
                        <td class="text-center">
                            @if($remaining > 0)
                            <a href="{{ route('nurse.drug-administration.show', $item) }}" class="btn btn-sm btn-success">
                                <span class="material-symbols-outlined align-middle">add</span>
                                Administer
                            </a>
                            @else
                            <span class="badge bg-success">Completed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <span class="material-symbols-outlined fs-1 text-muted">check_circle</span>
                            <p class="mb-0 mt-2 text-muted">No pending drug administrations</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection

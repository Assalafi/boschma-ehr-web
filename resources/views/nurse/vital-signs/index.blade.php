@extends('layouts.app')

@section('title', 'Vital Signs - Pending Patients')

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
    <h3 class="mb-0">Vital Signs</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse Station</a></li>
            <li class="breadcrumb-item active" aria-current="page">Vital Signs</li>
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
            <span class="material-symbols-outlined me-2 align-middle">monitor_heart</span>
            Patients Awaiting Vital Signs
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>BOSCHMA No</th>
                        <th>Nature of Visit</th>
                        <th>Program</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encounters as $encounter)
                    <tr>
                        <td>{{ $encounter->created_at->format('H:i') }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->enrollee_photo ?? false)
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
                        <td>{{ $encounter->nature_of_visit }}</td>
                        <td>{{ $encounter->program->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <a href="{{ route('nurse.vital-signs.create', $encounter) }}" class="btn btn-sm btn-info">
                                <span class="material-symbols-outlined align-middle">add</span>
                                Record Vitals
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <span class="material-symbols-outlined fs-1 text-muted">check_circle</span>
                            <p class="mb-0 mt-2 text-muted">No patients awaiting vital signs</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($encounters->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $encounters->links() }}
    </div>
    @endif
</div>
@endsection

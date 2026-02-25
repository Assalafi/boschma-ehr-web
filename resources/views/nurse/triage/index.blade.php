@extends('layouts.app')

@section('title', 'Triage Queue')

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
    <h3 class="mb-0">Triage Queue</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Nurse</a></li>
            <li class="breadcrumb-item active" aria-current="page">Triage</li>
        </ol>
    </nav>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
        <h5 class="mb-0 fw-semibold">Patients Awaiting Triage</h5>
        <span class="badge bg-warning">{{ $encounters->count() ?? 0 }} Waiting</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Queue #</th>
                        <th>Patient</th>
                        <th>BOSCHMA ID</th>
                        <th>Program</th>
                        <th>Check-in Time</th>
                        <th>Wait Time</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encounters ?? [] as $index => $encounter)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->enrollee_photo ?? false)
                                        <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $encounter->patient->enrollee_photo }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-warning fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    <br><small class="text-muted">{{ $encounter->nature_of_visit }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $encounter->patient->enrollee_number ?? 'N/A' }}</span></td>
                        <td>{{ $encounter->program->name ?? 'N/A' }}</td>
                        <td>{{ $encounter->created_at->format('H:i') }}</td>
                        <td>
                            @php
                                $waitTime = $encounter->created_at->diffForHumans(null, true);
                            @endphp
                            <span class="text-{{ $encounter->created_at->diffInMinutes() > 30 ? 'danger' : 'muted' }}">
                                {{ $waitTime }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('nurse.triage.create', $encounter) }}" class="btn btn-warning btn-sm">
                                <span class="material-symbols-outlined align-middle">monitor_heart</span> Start Triage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <span class="material-symbols-outlined text-success" style="font-size: 64px;">check_circle</span>
                            <h5 class="mt-3">No patients waiting</h5>
                            <p class="text-muted">All patients have been triaged</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

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
.form-select{background-color:#fff!important;height:50px;font-size:15px;border:1px solid #D5D9E2;border-radius:7px;color:#3A4252!important;padding:12px 2.5rem 12px 16px}
.form-select:focus{border-color:#016634;box-shadow:0 0 0 .25rem rgba(1,102,52,.15)}
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

<!-- Search & Filters -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('nurse.triage.index') }}" method="GET" id="triageSearchForm" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-medium">Search Patient</label>
                <div class="position-relative">
                    <input type="text" 
                           id="triageSearchInput" 
                           name="search" 
                           class="form-control" 
                           style="padding-left: 45px;"
                           placeholder="Search by Name, BOSCHMA ID, File No, Phone..." 
                           value="{{ request('search') }}"
                           autocomplete="off">
                    <span class="material-symbols-outlined position-absolute top-50 translate-middle-y text-muted" style="left: 14px; font-size: 22px;">search</span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Program</label>
                <select name="program_id" class="form-select bg-white" onchange="document.getElementById('triageSearchForm').submit()">
                    <option value="">All Programs</option>
                    @foreach($programs ?? [] as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center" style="height: 50px;">
                    <span class="material-symbols-outlined align-middle me-1">search</span> Search
                </button>
                @if(request()->hasAny(['search', 'program_id']))
                    <a href="{{ route('nurse.triage.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="height: 50px;">
                        <span class="material-symbols-outlined align-middle me-1">close</span> Clear
                    </a>
                @endif
                <a href="{{ route('nurse.triage.history') }}" class="btn btn-outline-primary ms-auto d-inline-flex align-items-center justify-content-center" style="height: 50px;">
                    <span class="material-symbols-outlined align-middle me-1">history</span> History
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-4">
        <div>
            <h5 class="mb-0 fw-semibold">Patients Awaiting Triage</h5>
            @if(request('search'))
                <small class="text-muted">Filtered by: "<strong>{{ request('search') }}</strong>"</small>
            @endif
        </div>
        <span class="badge bg-warning" id="waitingCountBadge">{{ $encounters->total() }} Waiting</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="triageTable">
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
                    <tr data-patient-row>
                        <td><span class="badge bg-secondary">{{ $encounters->firstItem() ? ($encounters->firstItem() + $index) : ($index + 1) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="wh-40 bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                                    @if($encounter->patient->enrollee_photo ?? false)
                                        <img src="{{ $encounter->patient->enrollee_photo }}" class="rounded-circle wh-40 object-fit-cover" alt="">
                                    @else
                                        <span class="material-symbols-outlined text-warning fs-6">person</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</span>
                                    @if($encounter->vitalSigns->count() > 0)
                                        <span class="badge bg-info ms-1" style="font-size:9px">Re-triage</span>
                                    @endif
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
                    <tr id="emptyRow">
                        <td colspan="7" class="text-center py-5">
                            @if(request()->hasAny(['search', 'program_id']))
                                <span class="material-symbols-outlined text-muted" style="font-size: 64px;">person_search</span>
                                <h5 class="mt-3">No matching patients found</h5>
                                <p class="text-muted">No patients awaiting triage matched your search criteria.</p>
                                <a href="{{ route('nurse.triage.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <span class="material-symbols-outlined align-middle me-1">refresh</span> Clear Filter
                                </a>
                            @else
                                <span class="material-symbols-outlined text-success" style="font-size: 64px;">check_circle</span>
                                <h5 class="mt-3">No patients waiting</h5>
                                <p class="text-muted">All patients have been triaged</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($encounters->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $encounters->firstItem() }} to {{ $encounters->lastItem() }} of {{ $encounters->total() }} patients
        </div>
        <div>
            {{ $encounters->links() }}
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('triageSearchInput');
    const tableBody = document.querySelector('#triageTable tbody');
    const badge = document.getElementById('waitingCountBadge');
    if (!searchInput || !tableBody) return;

    // Instant client-side filtering while typing
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr[data-patient-row]');
        if (!rows.length) return;

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (!query || text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (badge) {
            badge.textContent = (query ? visibleCount : '{{ $encounters->total() }}') + ' Waiting';
        }
    });
});
</script>
@endsection


@extends('layouts.app')

@section('title', 'Reception Reports')

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
    <h3 class="mb-0">Reception Reports</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}">Reception</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
    </nav>
</div>

<!-- Period Filter -->
<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('receptionist.reports') }}" method="GET" class="d-flex gap-2">
            <select name="period" class="form-select w-auto" onchange="this.form.submit()">
                <option value="today" {{ ($period ?? 'today') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ ($period ?? '') === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ ($period ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ ($period ?? '') === 'year' ? 'selected' : '' }}>This Year</option>
            </select>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-primary text-white">
            <div class="card-body p-4 text-center">
                <span class="material-symbols-outlined mb-2" style="font-size: 2.5rem;">calendar_month</span>
                <h2 class="mb-1">{{ $totalEncounters ?? 0 }}</h2>
                <span>Total Encounters</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-success text-white">
            <div class="card-body p-4 text-center">
                <span class="material-symbols-outlined mb-2" style="font-size: 2.5rem;">check_circle</span>
                <h2 class="mb-1">{{ $encountersByStatus['Completed'] ?? 0 }}</h2>
                <span>Completed</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-warning text-white">
            <div class="card-body p-4 text-center">
                <span class="material-symbols-outlined mb-2" style="font-size: 2.5rem;">pending</span>
                <h2 class="mb-1">{{ ($encountersByStatus['Registered'] ?? 0) + ($encountersByStatus['Triaged'] ?? 0) }}</h2>
                <span>In Progress</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 bg-danger text-white">
            <div class="card-body p-4 text-center">
                <span class="material-symbols-outlined mb-2" style="font-size: 2.5rem;">cancel</span>
                <h2 class="mb-1">{{ $encountersByStatus['Cancelled'] ?? 0 }}</h2>
                <span>Cancelled</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- By Status -->
    <div class="col-md-6">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Encounters by Status</h5>
            </div>
            <div class="card-body p-4">
                @forelse($encountersByStatus ?? [] as $status => $count)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
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
                            $color = $statusColors[$status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }} me-2">{{ $status }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-3" style="width: 150px; height: 8px;">
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $totalEncounters > 0 ? ($count / $totalEncounters * 100) : 0 }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ $count }}</span>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted mb-0">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- By Nature of Visit -->
    <div class="col-md-6">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Encounters by Nature of Visit</h5>
            </div>
            <div class="card-body p-4">
                @forelse($encountersByNature ?? [] as $nature => $count)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-dark">{{ $nature }}</span>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-3" style="width: 150px; height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $totalEncounters > 0 ? ($count / $totalEncounters * 100) : 0 }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ $count }}</span>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted mb-0">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- By Mode of Entry -->
    <div class="col-md-6">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Encounters by Mode of Entry</h5>
            </div>
            <div class="card-body p-4">
                @forelse($encountersByEntry ?? [] as $entry => $count)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        @php
                            $entryIcons = [
                                'Walk-in' => 'directions_walk',
                                'Appointment' => 'event',
                                'Referral' => 'swap_horiz',
                                'Emergency' => 'emergency',
                            ];
                            $icon = $entryIcons[$entry] ?? 'login';
                        @endphp
                        <span class="material-symbols-outlined text-muted me-2">{{ $icon }}</span>
                        <span class="text-dark">{{ $entry }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-3" style="width: 150px; height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ $totalEncounters > 0 ? ($count / $totalEncounters * 100) : 0 }}%"></div>
                        </div>
                        <span class="fw-semibold">{{ $count }}</span>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted mb-0">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Daily Trend -->
    <div class="col-md-6">
        <div class="card border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="mb-0 fw-semibold">Daily Encounters</h5>
            </div>
            <div class="card-body p-4">
                @if(count($dailyEncounters ?? []) > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyEncounters as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</td>
                                <td class="text-end fw-semibold">{{ $day->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted mb-0">No data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 rounded-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Need More Details?</h5>
                        <p class="text-muted mb-0">View full encounter history with advanced filtering options</p>
                    </div>
                    <a href="{{ route('receptionist.history') }}" class="btn btn-primary">
                        <span class="material-symbols-outlined me-1">history</span> View History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

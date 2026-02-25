@extends('layouts.app')
@section('title', 'Dispensation Queue')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.pharm-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.pharm-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--pharm-border); display: flex; align-items: center; gap: 8px; }
.pharm-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.pharm-badge-teal { background: var(--pharm-primary-light); color: var(--pharm-primary-dark); }
.pharm-badge-amber { background: #fef3c7; color: #92400e; }
.pharm-badge-green { background: #dcfce7; color: #166534; }
.pharm-badge-blue { background: #dbeafe; color: #1e40af; }
.pharm-badge-gray { background: #f1f5f9; color: #475569; }
.stat-mini { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); padding: 18px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.stat-mini-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px; }
.stat-mini-value { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-mini-label { font-size: 11px; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: .3px; margin-top: 4px; }
.pharm-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.pharm-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--pharm-border); white-space: nowrap; }
.pharm-table tbody td { padding: 14px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pharm-table tbody tr { transition: background .1s; }
.pharm-table tbody tr:hover { background: #f8fafb; }
.pharm-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.pharm-btn-primary { background: var(--pharm-primary); color: #fff; }
.pharm-btn-primary:hover { background: var(--pharm-primary-dark); color: #fff; }
.pharm-btn-outline { background: transparent; border: 1.5px solid var(--pharm-border); color: #64748b; }
.pharm-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.pharm-btn-amber { background: #f59e0b; color: #fff; }
.pharm-btn-amber:hover { background: #d97706; color: #fff; }
.pharm-search { display: flex; gap: 8px; align-items: center; }
.pharm-search input { padding: 7px 14px; border: 1.5px solid var(--pharm-border); border-radius: 8px; font-size: 13px; min-width: 240px; transition: border-color .2s; }
.pharm-search input:focus { border-color: var(--pharm-primary); outline: none; box-shadow: 0 0 0 3px rgba(1,102,52,.1); }
.patient-cell { display: flex; align-items: center; gap: 12px; }
.patient-avatar { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.patient-name { font-weight: 600; color: #1e293b; font-size: 13px; }
.patient-id { font-size: 11px; color: #94a3b8; }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state .material-symbols-outlined { font-size: 56px; color: #059669; opacity: .6; }
.empty-state h5 { font-weight: 700; color: #1e293b; margin-top: 12px; }
.empty-state p { color: #94a3b8; font-size: 13px; }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Queue</li>
      </ol>
    </nav>
    <h4 class="mb-0">Dispensation Queue</h4>
  </div>
  <a href="{{ route('pharmacy.dashboard') }}" class="pharm-btn pharm-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#fef3c7">
        <span class="material-symbols-outlined" style="font-size:22px;color:#d97706">pending_actions</span>
      </div>
      <div class="stat-mini-value">{{ $pendingCount }}</div>
      <div class="stat-mini-label">Pending</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#dcfce7">
        <span class="material-symbols-outlined" style="font-size:22px;color:#059669">check_circle</span>
      </div>
      <div class="stat-mini-value">{{ $dispensedToday }}</div>
      <div class="stat-mini-label">Dispensed Today</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#dbeafe">
        <span class="material-symbols-outlined" style="font-size:22px;color:#2563eb">schedule</span>
      </div>
      <div class="stat-mini-value" style="font-size:24px">{{ now()->format('H:i') }}</div>
      <div class="stat-mini-label">Current Time</div>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="pharm-card">
  <div class="pharm-card-header" style="justify-content:space-between;color:#1e293b;flex-wrap:wrap;gap:12px">
    <div class="d-flex align-items-center gap-2">
      <span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">queue</span>
      Prescriptions Awaiting Dispensation
    </div>
    <form method="GET" action="{{ route('pharmacy.queue') }}" class="pharm-search">
      <input type="text" name="search" placeholder="Search file no / enrollee no…" value="{{ request('search') }}">
      <button type="submit" class="pharm-btn pharm-btn-primary" style="padding:7px 14px">
        <span class="material-symbols-outlined" style="font-size:15px">search</span>
      </button>
      @if(request('search'))
        <a href="{{ route('pharmacy.queue') }}" class="pharm-btn pharm-btn-outline" style="padding:7px 12px">Clear</a>
      @endif
    </form>
  </div>
  <div style="overflow-x:auto">
    <table class="pharm-table">
      <thead>
        <tr>
          <th style="padding-left:20px;width:50px">#</th>
          <th>Patient</th>
          <th>Rx No.</th>
          <th>Prescribed By</th>
          <th style="text-align:center">Items</th>
          <th>Visit Date</th>
          <th>Rx Date</th>
          <th style="text-align:center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($prescriptions as $index => $rx)
        @php
          $patient   = $rx->consultation?->encounter?->patient;
          $enrollee  = $patient?->enrolleeDetails;
          $patName   = $enrollee?->fullname ?? ($enrollee?->name ?? 'Unknown');
          $boschmaNo = $patient?->enrollee_number ?? '—';
          $visitDate = $rx->consultation?->encounter?->visit_date;
        @endphp
        <tr>
          <td style="padding-left:20px">
            <span class="pharm-badge pharm-badge-gray">{{ $prescriptions->firstItem() + $index }}</span>
          </td>
          <td>
            <div class="patient-cell">
              <div class="patient-avatar" style="background:#fef3c7">
                <span class="material-symbols-outlined" style="font-size:18px;color:#d97706">person</span>
              </div>
              <div>
                <div class="patient-name">{{ $patName }}</div>
                <div class="patient-id">{{ $boschmaNo }}</div>
              </div>
            </div>
          </td>
          <td><code style="font-size:11px;background:#f1f5f9;padding:2px 8px;border-radius:4px">{{ $rx->prescription_number ?? '—' }}</code></td>
          <td style="color:#64748b;font-size:12px">{{ $rx->prescribedBy?->name ?? '—' }}</td>
          <td style="text-align:center">
            <span class="pharm-badge pharm-badge-amber">{{ $rx->items->count() }} item{{ $rx->items->count() > 1 ? 's' : '' }}</span>
          </td>
          <td style="color:#64748b;font-size:12px">{{ $visitDate ? $visitDate->format('d M Y H:i') : '—' }}</td>
          <td style="color:#64748b;font-size:12px">{{ $rx->prescription_date ? \Carbon\Carbon::parse($rx->prescription_date)->format('d M Y') : $rx->created_at->format('d M Y') }}</td>
          <td style="text-align:center">
            <a href="{{ route('pharmacy.prescription.show', $rx->id) }}" class="pharm-btn pharm-btn-amber">
              <span class="material-symbols-outlined" style="font-size:15px">medication</span> Dispense
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <span class="material-symbols-outlined">check_circle</span>
              <h5>All clear!</h5>
              <p>No pending prescriptions at the moment.</p>
              <a href="{{ route('pharmacy.history') }}" class="pharm-btn pharm-btn-outline" style="margin:0 auto">
                <span class="material-symbols-outlined" style="font-size:14px">history</span> View History
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($prescriptions->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--pharm-border);display:flex;justify-content:flex-end">
    {{ $prescriptions->withQueryString()->links() }}
  </div>
  @endif
</div>

</div>
@endsection

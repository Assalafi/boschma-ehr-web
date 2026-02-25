@extends('layouts.app')
@section('title', 'Consultation History')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.filter-bar { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 16px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.filter-bar input, .filter-bar select { padding: 7px 12px; border: 1.5px solid var(--doc-border); border-radius: 8px; font-size: 13px; }
.filter-bar input:focus, .filter-bar select:focus { border-color: var(--doc-primary); outline: none; box-shadow: 0 0 0 3px rgba(10,110,94,.1); }
.filter-bar label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 4px; display: block; }
.hist-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.hist-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--doc-border); white-space: nowrap; }
.hist-table tbody td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.hist-table tbody tr:hover { background: #f8fafb; }
</style>

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item active" style="color:#fff">History</li>
      </ol>
    </nav>
    <h4 class="mb-0">Consultation History</h4>
  </div>
  <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

<div class="filter-bar">
  <form action="{{ route('doctor.consultation.history') }}" method="GET" class="d-flex align-items-end gap-3 flex-wrap">
    <div><label>Start Date</label><input type="date" name="start_date" value="{{ request('start_date') }}"></div>
    <div><label>End Date</label><input type="date" name="end_date" value="{{ request('end_date') }}"></div>
    <div><label>Status</label>
      <select name="status" style="min-width:140px">
        <option value="">All</option>
        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
      </select>
    </div>
    <div><label>Search</label><input type="text" name="search" placeholder="Name or ID…" value="{{ request('search') }}" style="min-width:180px"></div>
    <div class="d-flex gap-2">
      <button type="submit" class="doc-btn doc-btn-primary"><span class="material-symbols-outlined" style="font-size:14px">search</span> Filter</button>
      <a href="{{ route('doctor.consultation.history') }}" class="doc-btn doc-btn-outline">Reset</a>
    </div>
  </form>
</div>

<div class="doc-card">
  <div class="doc-card-header">
    <span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">history</span>
    Consultations
    <span class="doc-badge doc-badge-gray ms-1">{{ $consultations->total() }}</span>
  </div>
  <div style="overflow-x:auto">
    <table class="hist-table">
      <thead>
        <tr>
          <th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Outcome</th><th>Status</th><th style="text-align:center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($consultations as $consultation)
        <tr>
          <td>
            <div style="font-weight:600;color:#1e293b">{{ $consultation->created_at->format('d M Y') }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ $consultation->created_at->format('H:i') }}</div>
          </td>
          <td>
            <div style="font-weight:600;color:#1e293b;font-size:13px">{{ $consultation->encounter->patient->beneficiary->fullname ?? 'N/A' }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ $consultation->encounter->patient->beneficiary->boschma_no ?? '' }}</div>
          </td>
          <td style="font-size:12px;color:#64748b">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</td>
          <td>
            @if($consultation->diagnoses->isNotEmpty())
            <span style="font-size:12px">{{ Str::limit($consultation->diagnoses->first()->icdCode->description ?? 'N/A', 30) }}</span>
            @if($consultation->diagnoses->count() > 1)
            <span class="doc-badge doc-badge-gray">+{{ $consultation->diagnoses->count() - 1 }}</span>
            @endif
            @else <span style="color:#94a3b8;font-size:12px">--</span> @endif
          </td>
          <td style="font-size:12px;color:#64748b">{{ $consultation->encounter->outcome ?? '--' }}</td>
          <td>
            @php $sc = match($consultation->status) { 'Completed' => 'green', 'In Progress' => 'amber', default => 'gray' }; @endphp
            <span class="doc-badge doc-badge-{{ $sc }}">{{ $consultation->status }}</span>
          </td>
          <td style="text-align:center">
            <a href="{{ route('doctor.consultation.show', $consultation) }}" class="doc-btn doc-btn-outline" style="padding:5px 10px">
              <span class="material-symbols-outlined" style="font-size:14px">visibility</span>
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:48px 20px">
            <span class="material-symbols-outlined" style="font-size:52px;color:#94a3b8;opacity:.4">search_off</span>
            <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No consultations found</h5>
            <p style="color:#94a3b8;font-size:13px">Try adjusting your filters</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($consultations->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--doc-border);display:flex;justify-content:flex-end">
    {{ $consultations->withQueryString()->links() }}
  </div>
  @endif
</div>

</div>
@endsection

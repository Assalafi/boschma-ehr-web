@extends('layouts.app')
@section('title', 'Doctor Reports')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.stat-mini { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 18px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.stat-mini-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px; }
.stat-mini-value { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-mini-label { font-size: 11px; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: .3px; margin-top: 4px; }
.rpt-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.rpt-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--doc-border); }
.rpt-table tbody td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.rpt-table tbody tr:hover { background: #f8fafb; }
.filter-bar { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 16px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.filter-bar input, .filter-bar select { padding: 7px 12px; border: 1.5px solid var(--doc-border); border-radius: 8px; font-size: 13px; }
.filter-bar input:focus, .filter-bar select:focus { border-color: var(--doc-primary); outline: none; box-shadow: 0 0 0 3px rgba(10,110,94,.1); }
.filter-bar label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 4px; display: block; }
</style>

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Reports</li>
      </ol>
    </nav>
    <h4 class="mb-0">Consultation Reports</h4>
  </div>
  <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

{{-- Filter --}}
<div class="filter-bar">
  <form action="{{ route('doctor.reports') }}" method="GET" class="d-flex align-items-end gap-3 flex-wrap">
    <div><label>Start Date</label><input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"></div>
    <div><label>End Date</label><input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"></div>
    <button type="submit" class="doc-btn doc-btn-primary"><span class="material-symbols-outlined" style="font-size:14px">filter_list</span> Generate</button>
  </form>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="font-size:22px;color:#2563eb">event_note</span></div>
      <div class="stat-mini-value">{{ $totalConsultations }}</div>
      <div class="stat-mini-label">Total Consultations</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#dcfce7"><span class="material-symbols-outlined" style="font-size:22px;color:#059669">trending_up</span></div>
      <div class="stat-mini-value">{{ $byOutcome['Improved'] ?? 0 }}</div>
      <div class="stat-mini-label">Improved</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#fef3c7"><span class="material-symbols-outlined" style="font-size:22px;color:#d97706">event_upcoming</span></div>
      <div class="stat-mini-value">{{ $byOutcome['Follow-up'] ?? 0 }}</div>
      <div class="stat-mini-label">Follow-up</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-mini">
      <div class="stat-mini-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="font-size:22px;color:#2563eb">share</span></div>
      <div class="stat-mini-value">{{ $byOutcome['Refer'] ?? 0 }}</div>
      <div class="stat-mini-label">Referred</div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- By Doctor --}}
  <div class="col-md-6">
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">group</span> By Doctor</div>
      <div style="overflow-x:auto">
        <table class="rpt-table">
          <thead><tr><th>Doctor</th><th style="text-align:right">Count</th><th style="text-align:right">%</th></tr></thead>
          <tbody>
            @forelse($byDoctor as $doctor)
            <tr>
              <td style="font-weight:600;color:#1e293b">Dr. {{ $doctor->name }}</td>
              <td style="text-align:right">{{ $doctor->consultation_count }}</td>
              <td style="text-align:right;color:#64748b">{{ $totalConsultations > 0 ? round($doctor->consultation_count / $totalConsultations * 100, 1) : 0 }}%</td>
            </tr>
            @empty <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px">No data</td></tr> @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  {{-- Top Diagnoses --}}
  <div class="col-md-6">
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">diagnosis</span> Top 10 Diagnoses</div>
      <div style="overflow-x:auto">
        <table class="rpt-table">
          <thead><tr><th>Diagnosis</th><th style="text-align:right">Count</th></tr></thead>
          <tbody>
            @forelse($topDiagnoses as $diagnosis)
            <tr>
              <td>
                <span style="display:inline-flex;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569;margin-right:6px">{{ $diagnosis->code }}</span>
                <span style="font-size:12px">{{ Str::limit($diagnosis->description, 35) }}</span>
              </td>
              <td style="text-align:right;font-weight:700">{{ $diagnosis->count }}</td>
            </tr>
            @empty <tr><td colspan="2" style="text-align:center;color:#94a3b8;padding:20px">No data</td></tr> @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Daily Breakdown --}}
<div class="doc-card">
  <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">calendar_month</span> Daily Breakdown</div>
  <div style="overflow-x:auto">
    <table class="rpt-table">
      <thead><tr><th>Date</th><th style="text-align:right">Consultations</th><th>Trend</th></tr></thead>
      <tbody>
        @php $prevCount = 0; @endphp
        @forelse($dailyBreakdown as $day)
        <tr>
          <td style="font-weight:500">{{ \Carbon\Carbon::parse($day->date)->format('D, d M Y') }}</td>
          <td style="text-align:right;font-weight:700">{{ $day->count }}</td>
          <td>
            @if($day->count > $prevCount)
            <span style="color:#059669"><span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle">trending_up</span></span>
            @elseif($day->count < $prevCount)
            <span style="color:#dc2626"><span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle">trending_down</span></span>
            @else
            <span style="color:#94a3b8"><span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle">trending_flat</span></span>
            @endif
          </td>
        </tr>
        @php $prevCount = $day->count; @endphp
        @empty <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px">No data for this period</td></tr> @endforelse
      </tbody>
    </table>
  </div>
</div>

</div>
@endsection

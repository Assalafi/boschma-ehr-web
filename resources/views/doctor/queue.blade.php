@extends('layouts.app')
@section('title', 'Patient Queue')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-teal { background: var(--doc-primary-light); color: var(--doc-primary-dark); }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.filter-bar { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 16px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.filter-bar select, .filter-bar input { padding: 7px 12px; border: 1.5px solid var(--doc-border); border-radius: 8px; font-size: 13px; }
.filter-bar select:focus, .filter-bar input:focus { border-color: var(--doc-primary); outline: none; box-shadow: 0 0 0 3px rgba(10,110,94,.1); }
.queue-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--doc-border); padding: 0 4px; overflow-x: auto; }
.queue-tab { padding: 12px 18px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; border: none; background: none; white-space: nowrap; display: flex; align-items: center; gap: 6px; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .15s; }
.queue-tab:hover { color: var(--doc-primary); }
.queue-tab.active { color: var(--doc-primary); font-weight: 700; border-bottom-color: var(--doc-primary); }
.queue-tab .tab-count { padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; }
</style>

<div class="doc-page">

{{-- Header --}}
<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Patient Queue</li>
      </ol>
    </nav>
    <h4 class="mb-0">Patient Queue</h4>
  </div>
  <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

{{-- Filter --}}
<div class="filter-bar">
  <form action="{{ route('doctor.queue') }}" method="GET" class="d-flex align-items-end gap-3 flex-wrap">
    <div>
      <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;display:block">Priority</label>
      <select name="priority" style="min-width:160px">
        <option value="">All Priorities</option>
        <option value="Red" {{ request('priority') == 'Red' ? 'selected' : '' }}>Critical</option>
        <option value="Yellow" {{ request('priority') == 'Yellow' ? 'selected' : '' }}>Urgent</option>
        <option value="Green" {{ request('priority') == 'Green' ? 'selected' : '' }}>Normal</option>
      </select>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="doc-btn doc-btn-primary"><span class="material-symbols-outlined" style="font-size:14px">filter_list</span> Filter</button>
      <a href="{{ route('doctor.queue') }}" class="doc-btn doc-btn-outline">Reset</a>
    </div>
    <div class="ms-auto d-flex gap-2 flex-wrap">
      <span class="doc-badge doc-badge-red">{{ $triaged->where(fn($e) => $e->vitalSigns->first()?->overall_priority == 'Red')->count() }} Critical</span>
      <span class="doc-badge doc-badge-amber">{{ $triaged->where(fn($e) => $e->vitalSigns->first()?->overall_priority == 'Yellow')->count() }} Urgent</span>
      <span class="doc-badge doc-badge-green">{{ $triaged->where(fn($e) => $e->vitalSigns->first()?->overall_priority == 'Green')->count() }} Normal</span>
    </div>
  </form>
</div>

{{-- Tabbed Card --}}
<div class="doc-card">
  <div class="queue-tabs" role="tablist">
    <button class="queue-tab active" data-bs-toggle="tab" data-bs-target="#triaged" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">people</span> Awaiting
      <span class="tab-count" style="background:#fee2e2;color:#dc2626">{{ $triaged->count() }}</span>
    </button>
    <button class="queue-tab" data-bs-toggle="tab" data-bs-target="#inConsultation" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">medical_services</span> In Consultation
      <span class="tab-count" style="background:#dbeafe;color:#2563eb">{{ $inConsultation->count() }}</span>
    </button>
    <button class="queue-tab" data-bs-toggle="tab" data-bs-target="#awaitingLab" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">science</span> Lab Results
      <span class="tab-count" style="background:#fef3c7;color:#d97706">{{ $awaitingLab->count() }}</span>
    </button>
    <button class="queue-tab" data-bs-toggle="tab" data-bs-target="#awaitingPharmacy" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">medication</span> Pharmacy
      <span class="tab-count" style="background:#f1f5f9;color:#475569">{{ $awaitingPharmacy->count() }}</span>
    </button>
    <button class="queue-tab" data-bs-toggle="tab" data-bs-target="#completedToday" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Completed
      <span class="tab-count" style="background:#dcfce7;color:#059669">{{ $completedToday->count() }}</span>
    </button>
  </div>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="triaged">@include('doctor.queue._triaged_table', ['encounters' => $triaged])</div>
    <div class="tab-pane fade" id="inConsultation">@include('doctor.queue._consultation_table', ['encounters' => $inConsultation])</div>
    <div class="tab-pane fade" id="awaitingLab">@include('doctor.queue._lab_table', ['encounters' => $awaitingLab])</div>
    <div class="tab-pane fade" id="awaitingPharmacy">@include('doctor.queue._pharmacy_table', ['encounters' => $awaitingPharmacy])</div>
    <div class="tab-pane fade" id="completedToday">@include('doctor.queue._completed_table', ['encounters' => $completedToday])</div>
  </div>
</div>

</div>
@endsection

@extends('layouts.app')
@section('title', 'Patient Queue')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;color:#fff }
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.queue-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--doc-border); padding: 0 4px; overflow-x: auto; }
.queue-tab { padding: 12px 18px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; border: none; background: none; white-space: nowrap; display: flex; align-items: center; gap: 6px; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .15s; }
.queue-tab:hover { color: var(--doc-primary); }
.queue-tab.active { color: var(--doc-primary); font-weight: 700; border-bottom-color: var(--doc-primary); }
.queue-tab .tab-count { padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; }
.tab-toolbar { display:flex; align-items:center; gap:10px; padding:12px 20px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; background:#fafcfb; }
.tab-search-wrap { position:relative; min-width:200px; max-width:320px; flex-shrink:0; }
.tab-search-wrap .material-symbols-outlined { position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:18px; color:#94a3b8; pointer-events:none; }
.tab-search-input { width:100%; padding:7px 12px 7px 34px; border:1.5px solid var(--doc-border); border-radius:8px; font-size:13px; background:#fff; }
.tab-search-input:focus { border-color:var(--doc-primary); outline:none; box-shadow:0 0 0 3px rgba(1,102,52,.1); }
.tab-filter { padding:6px 10px; border:1.5px solid var(--doc-border); border-radius:7px; font-size:12px; color:#475569; background:#fff; cursor:pointer; }
.tab-filter:focus { border-color:var(--doc-primary); outline:none; }
.tab-info { font-size:12px; color:#64748b; white-space:nowrap; }
.tab-body { min-height:200px; position:relative; }
.tab-body.loading { opacity:.5; pointer-events:none; }
.tab-body.loading::after { content:''; position:absolute; top:50%; left:50%; width:28px; height:28px; margin:-14px; border:3px solid var(--doc-border); border-top-color:var(--doc-primary); border-radius:50%; animation:spin .6s linear infinite; }
@keyframes spin { to { transform:rotate(360deg) } }
.tab-pagination { display:flex; align-items:center; justify-content:center; gap:4px; padding:12px 20px; border-top:1px solid #f1f5f9; }
.tab-page-btn { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border:1.5px solid var(--doc-border); border-radius:6px; font-size:12px; font-weight:600; color:#475569; background:#fff; cursor:pointer; transition:all .15s; }
.tab-page-btn:hover { border-color:#cbd5e1; background:#f8fafc; }
.tab-page-btn.active { background:var(--doc-primary); border-color:var(--doc-primary); color:#fff; }
.tab-page-btn:disabled { opacity:.4; cursor:not-allowed; }
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

{{-- Tabbed Card --}}
<div class="doc-card">
  <div class="queue-tabs" role="tablist">
    <button class="queue-tab active" data-tab="triaged" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">people</span> Awaiting
      <span class="tab-count" data-count="triaged" style="background:#fee2e2;color:#dc2626">{{ $counts['triaged'] }}</span>
    </button>
    <button class="queue-tab" data-tab="inConsultation" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">medical_services</span> In Consultation
      <span class="tab-count" data-count="inConsultation" style="background:#dbeafe;color:#2563eb">{{ $counts['inConsultation'] }}</span>
    </button>
    <button class="queue-tab" data-tab="awaitingLab" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">science</span> Lab Results
      <span class="tab-count" data-count="awaitingLab" style="background:#fef3c7;color:#d97706">{{ $counts['awaitingLab'] }}</span>
    </button>
    <button class="queue-tab" data-tab="awaitingPharmacy" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">medication</span> Pharmacy
      <span class="tab-count" data-count="awaitingPharmacy" style="background:#f1f5f9;color:#475569">{{ $counts['awaitingPharmacy'] }}</span>
    </button>
    <button class="queue-tab" data-tab="completedToday" type="button">
      <span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Completed
      <span class="tab-count" data-count="completedToday" style="background:#dcfce7;color:#059669">{{ $counts['completedToday'] }}</span>
    </button>
  </div>

  {{-- TRIAGED filters --}}
  <div class="tab-toolbar" data-toolbar="triaged">
    <div class="tab-search-wrap">
      <span class="material-symbols-outlined">search</span>
      <input type="text" class="tab-search-input" data-filter="search" placeholder="Search patient name or ID...">
    </div>
    <select class="tab-filter" data-filter="priority"><option value="">All Priorities</option><option value="Red">Critical</option><option value="Yellow">Urgent</option><option value="Green">Normal</option></select>
    <select class="tab-filter" data-filter="program"><option value="">All Programs</option>@foreach($programs as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="per_page"><option value="15">15 / page</option><option value="25">25</option><option value="50">50</option></select>
    <span class="tab-info" data-info="triaged"></span>
  </div>

  {{-- IN CONSULTATION filters --}}
  <div class="tab-toolbar" data-toolbar="inConsultation" style="display:none">
    <div class="tab-search-wrap">
      <span class="material-symbols-outlined">search</span>
      <input type="text" class="tab-search-input" data-filter="search" placeholder="Search patient, ID, or doctor...">
    </div>
    <select class="tab-filter" data-filter="program"><option value="">All Programs</option>@foreach($programs as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="per_page"><option value="15">15 / page</option><option value="25">25</option><option value="50">50</option></select>
    <span class="tab-info" data-info="inConsultation"></span>
  </div>

  {{-- LAB filters --}}
  <div class="tab-toolbar" data-toolbar="awaitingLab" style="display:none">
    <div class="tab-search-wrap">
      <span class="material-symbols-outlined">search</span>
      <input type="text" class="tab-search-input" data-filter="search" placeholder="Search patient, ID, or order...">
    </div>
    <select class="tab-filter" data-filter="program"><option value="">All Programs</option>@foreach($programs as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="per_page"><option value="15">15 / page</option><option value="25">25</option><option value="50">50</option></select>
    <span class="tab-info" data-info="awaitingLab"></span>
  </div>

  {{-- PHARMACY filters --}}
  <div class="tab-toolbar" data-toolbar="awaitingPharmacy" style="display:none">
    <div class="tab-search-wrap">
      <span class="material-symbols-outlined">search</span>
      <input type="text" class="tab-search-input" data-filter="search" placeholder="Search patient, ID, or drug...">
    </div>
    <select class="tab-filter" data-filter="program"><option value="">All Programs</option>@foreach($programs as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="per_page"><option value="15">15 / page</option><option value="25">25</option><option value="50">50</option></select>
    <span class="tab-info" data-info="awaitingPharmacy"></span>
  </div>

  {{-- COMPLETED filters --}}
  <div class="tab-toolbar" data-toolbar="completedToday" style="display:none">
    <div class="tab-search-wrap">
      <span class="material-symbols-outlined">search</span>
      <input type="text" class="tab-search-input" data-filter="search" placeholder="Search patient, ID, or outcome...">
    </div>
    <label style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px">From <input type="date" class="tab-filter" data-filter="date_from" value="{{ date('Y-m-d') }}"></label>
    <label style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:4px">To <input type="date" class="tab-filter" data-filter="date_to" value="{{ date('Y-m-d') }}"></label>
    <select class="tab-filter" data-filter="doctor"><option value="">All Doctors</option>@foreach($doctors as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="program"><option value="">All Programs</option>@foreach($programs as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
    <select class="tab-filter" data-filter="per_page"><option value="15">15 / page</option><option value="25">25</option><option value="50">50</option></select>
    <span class="tab-info" data-info="completedToday"></span>
  </div>

  {{-- Tab body + pagination --}}
  <div class="tab-body" id="tabBody"></div>
  <div class="tab-pagination" id="tabPagination"></div>
</div>

</div>
<script>
var QUEUE_URL = "{{ route('doctor.queue.tab') }}";
</script>
<script src="{{ asset('js/queue-tabs.js') }}"></script>
@endsection

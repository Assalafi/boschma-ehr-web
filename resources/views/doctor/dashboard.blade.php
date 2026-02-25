@extends('layouts.app')
@section('title', 'Doctor Dashboard')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.stat-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: 22px; display: flex; align-items: center; gap: 16px; transition: transform .15s, box-shadow .15s; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon .material-symbols-outlined { font-size: 26px; }
.stat-label { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .4px; }
.stat-value { font-size: 26px; font-weight: 800; color: #1e293b; line-height: 1; }
.action-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 20px; text-align: center; text-decoration: none !important; color: #1e293b !important; transition: all .15s; display: flex; flex-direction: column; align-items: center; gap: 10px; height: 100%; }
.action-card:hover { border-color: var(--doc-primary); box-shadow: 0 4px 12px rgba(10,110,94,.12); transform: translateY(-2px); }
.action-card .action-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.action-card .action-icon .material-symbols-outlined { font-size: 26px; }
.action-card .action-label { font-weight: 600; font-size: 13px; }
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; justify-content: space-between; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-teal { background: var(--doc-primary-light); color: var(--doc-primary-dark); }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.doc-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--doc-border); white-space: nowrap; }
.doc-table tbody td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.doc-table tbody tr { transition: background .1s; }
.doc-table tbody tr:hover { background: #f8fafb; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.doc-btn-danger { background: #dc2626; color: #fff; }
.doc-btn-danger:hover { background: #b91c1c; color: #fff; }
.patient-cell { display: flex; align-items: center; gap: 12px; }
.patient-avatar { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.patient-avatar img { width: 100%; height: 100%; object-fit: cover; }
.patient-name { font-weight: 600; color: #1e293b; font-size: 13px; }
.patient-id { font-size: 11px; color: #94a3b8; }
.section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.section-title .material-symbols-outlined { font-size: 18px; color: var(--doc-primary); }
.empty-state { text-align: center; padding: 48px 20px; }
.empty-state .material-symbols-outlined { font-size: 52px; color: #059669; opacity: .5; }
.empty-state h5 { font-weight: 700; color: #1e293b; margin-top: 10px; font-size: 15px; }
.empty-state p { color: #94a3b8; font-size: 13px; }
.priority-bar { height: 6px; border-radius: 3px; overflow: hidden; background: #f1f5f9; }
.priority-bar-fill { height: 100%; border-radius: 3px; transition: width .3s; }
.recent-item { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; transition: background .1s; }
.recent-item:last-child { border-bottom: none; }
.recent-item:hover { background: #fafcfc; }
</style>

<div class="doc-page">

{{-- Header --}}
<div class="doc-header">
  <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
    <ol class="breadcrumb mb-0" style="font-size:12px">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,.7)">Home</a></li>
      <li class="breadcrumb-item active" style="color:#fff">Doctor Station</li>
    </ol>
  </nav>
  <h4 class="mb-1">Doctor Station</h4>
  <p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:0">
    <span class="material-symbols-outlined align-middle me-1" style="font-size:15px">calendar_today</span>{{ now()->format('l, d M Y') }}
    <span class="mx-2" style="opacity:.3">|</span>
    <span class="material-symbols-outlined align-middle me-1" style="font-size:15px">person</span>Dr. {{ auth()->user()->name ?? 'Doctor' }}
  </p>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fee2e2"><span class="material-symbols-outlined" style="color:#dc2626">hourglass_empty</span></div>
      <div>
        <div class="stat-label">Waiting Queue</div>
        <div class="stat-value">{{ $pendingConsultations ?? 0 }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fef3c7"><span class="material-symbols-outlined" style="color:#d97706">pending</span></div>
      <div>
        <div class="stat-label">In Progress</div>
        <div class="stat-value">{{ $inProgressCount ?? 0 }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dcfce7"><span class="material-symbols-outlined" style="color:#059669">check_circle</span></div>
      <div>
        <div class="stat-label">Completed Today</div>
        <div class="stat-value">{{ $completedToday ?? 0 }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="color:#2563eb">stethoscope</span></div>
      <div>
        <div class="stat-label">My Today</div>
        <div class="stat-value">{{ $myConsultationsToday ?? 0 }}</div>
      </div>
    </div>
  </div>
</div>

{{-- Quick Actions --}}
<div class="section-title"><span class="material-symbols-outlined">bolt</span> Quick Actions</div>
<div class="row g-3 mb-4">
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('doctor.queue') }}" class="action-card">
      <div class="action-icon" style="background:#fee2e2"><span class="material-symbols-outlined" style="color:#dc2626">queue</span></div>
      <div class="action-label">Patient Queue</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('doctor.consultation.history') }}" class="action-card">
      <div class="action-icon" style="background:#f1f5f9"><span class="material-symbols-outlined" style="color:#64748b">history</span></div>
      <div class="action-label">Consultation History</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('doctor.patients') }}" class="action-card">
      <div class="action-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="color:#2563eb">manage_search</span></div>
      <div class="action-label">Patient Records</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('doctor.reports') }}" class="action-card">
      <div class="action-icon" style="background:#dcfce7"><span class="material-symbols-outlined" style="color:#059669">summarize</span></div>
      <div class="action-label">Reports</div>
    </a>
  </div>
</div>

<div class="row g-3">
  {{-- Patient Queue --}}
  <div class="col-lg-8">
    <div class="doc-card">
      <div class="doc-card-header" style="color:#1e293b">
        <div class="d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="font-size:16px;color:#dc2626">queue</span> Patient Queue
        </div>
        <a href="{{ route('doctor.queue') }}" class="doc-btn doc-btn-outline" style="font-size:11px;padding:4px 12px">View All</a>
      </div>
      <div style="overflow-x:auto">
        <table class="doc-table">
          <thead>
            <tr>
              <th>Priority</th>
              <th>Patient</th>
              <th>Complaint</th>
              <th>Vitals</th>
              <th>Wait</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($patientQueue ?? [] as $encounter)
            @php
              $vitalSign = $encounter->vitalSigns->first();
              $priority = $vitalSign?->overall_priority ?? 'Green';
              $pc = [
                'Red'=>'red','Yellow'=>'amber','Green'=>'green',
                'High'=>'red','high'=>'red',
                'Critical'=>'red','critical'=>'critical',
                'Urgent'=>'amber','urgent'=>'urgent',
                'Normal'=>'green','normal'=>'normal'
            ];
            @endphp
            <tr>
              <td><span class="doc-badge doc-badge-{{ $pc[$priority] ?? 'green' }}">{{ $priority }}</span></td>
              <td>
                <div class="patient-cell">
                  <div class="patient-avatar" style="background:{{ $priority == 'Red' ? '#fee2e2' : ($priority == 'Yellow' ? '#fef3c7' : 'var(--doc-primary-light)') }}">
                    @if($encounter->patient->enrollee_photo ?? false)
                      <img src="{{ $encounter->patient->enrollee_photo }}" alt="">
                    @else
                      <span class="material-symbols-outlined" style="font-size:18px;color:{{ $priority == 'Red' ? '#dc2626' : ($priority == 'Yellow' ? '#d97706' : 'var(--doc-primary)') }}">person</span>
                    @endif
                  </div>
                  <div>
                    <div class="patient-name">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</div>
                    <div class="patient-id">{{ $encounter->patient->enrollee_number ?? '' }}</div>
                  </div>
                </div>
              </td>
              <td style="color:#475569;font-size:12px;max-width:160px">{{ Str::limit($encounter->reason_for_visit, 40) }}</td>
              <td style="font-size:11px;color:#64748b">
                @if($vitalSign)
                  T:{{ $vitalSign->temperature }}° | BP:{{ $vitalSign->blood_pressure_systolic }}/{{ $vitalSign->blood_pressure_diastolic }} | P:{{ $vitalSign->pulse_rate }}
                @else <span class="text-muted">--</span> @endif
              </td>
              <td>
                <span class="doc-badge {{ $encounter->created_at->diffInMinutes() > 30 ? 'doc-badge-red' : 'doc-badge-gray' }}" style="font-size:10px">
                  {{ $encounter->created_at->diffForHumans(null, true) }}
                </span>
              </td>
              <td style="text-align:center">
                <a href="{{ route('doctor.consultation.start', $encounter) }}" class="doc-btn doc-btn-primary" style="padding:5px 12px">
                  <span class="material-symbols-outlined" style="font-size:14px">play_arrow</span> Start
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <span class="material-symbols-outlined">check_circle</span>
                  <h5>No patients waiting</h5>
                  <p>All patients have been attended to</p>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div class="col-lg-4">
    {{-- Priority Distribution --}}
    <div class="doc-card">
      <div class="doc-card-header" style="color:#1e293b">
        <div class="d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">insights</span> Priority Distribution
        </div>
      </div>
      <div style="padding:18px 20px">
        @php
          $greenCount = $priorityStats['Green'] ?? 0;
          $yellowCount = $priorityStats['Yellow'] ?? 0;
          $redCount = $priorityStats['Red'] ?? 0;
          $total = $greenCount + $yellowCount + $redCount;
        @endphp
        @if($total > 0)
        <div class="d-flex flex-column gap-3">
          <div>
            <div class="d-flex justify-content-between mb-1" style="font-size:12px">
              <span style="color:#dc2626;font-weight:600">Red (Critical)</span>
              <span class="fw-bold">{{ $redCount }}</span>
            </div>
            <div class="priority-bar"><div class="priority-bar-fill" style="width:{{ $total > 0 ? ($redCount/$total*100) : 0 }}%;background:#dc2626"></div></div>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1" style="font-size:12px">
              <span style="color:#d97706;font-weight:600">Yellow (Urgent)</span>
              <span class="fw-bold">{{ $yellowCount }}</span>
            </div>
            <div class="priority-bar"><div class="priority-bar-fill" style="width:{{ $total > 0 ? ($yellowCount/$total*100) : 0 }}%;background:#d97706"></div></div>
          </div>
          <div>
            <div class="d-flex justify-content-between mb-1" style="font-size:12px">
              <span style="color:#059669;font-weight:600">Green (Normal)</span>
              <span class="fw-bold">{{ $greenCount }}</span>
            </div>
            <div class="priority-bar"><div class="priority-bar-fill" style="width:{{ $total > 0 ? ($greenCount/$total*100) : 0 }}%;background:#059669"></div></div>
          </div>
        </div>
        @else
        <div class="text-center py-3">
          <span class="material-symbols-outlined" style="font-size:40px;color:#94a3b8;opacity:.4">insights</span>
          <p class="text-muted mt-2 mb-0" style="font-size:12px">No patient data for today</p>
        </div>
        @endif
      </div>
    </div>

    {{-- Recent Consultations --}}
    <div class="doc-card">
      <div class="doc-card-header" style="color:#1e293b">
        <div class="d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">history</span> Recent Consultations
        </div>
      </div>
      <div>
        @forelse($recentConsultations ?? [] as $consultation)
        <div class="recent-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-weight:600;font-size:13px;color:#1e293b">{{ $consultation->encounter->patient->enrollee_name ?? 'N/A' }}</div>
              <div style="font-size:11px;color:#94a3b8;margin-top:2px">{{ $consultation->diagnoses->first()?->icdCode?->description ?? 'No diagnosis' }}</div>
            </div>
            <div class="text-end">
              <span class="doc-badge {{ $consultation->status == 'Completed' ? 'doc-badge-green' : 'doc-badge-amber' }}">{{ $consultation->status }}</span>
              <div style="font-size:10px;color:#94a3b8;margin-top:3px">{{ $consultation->created_at->diffForHumans() }}</div>
            </div>
          </div>
        </div>
        @empty
        <div class="text-center py-4" style="font-size:12px;color:#94a3b8">No recent consultations</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

</div>
@endsection

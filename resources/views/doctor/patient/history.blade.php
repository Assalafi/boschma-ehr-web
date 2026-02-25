@extends('layouts.app')
@section('title', 'Patient History')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-teal { background: var(--doc-primary-light); color: var(--doc-primary-dark); }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row .info-label { color: #94a3b8; }
.info-row .info-value { font-weight: 600; color: #1e293b; }
.timeline-item { padding: 20px; border-bottom: 1px solid #f1f5f9; transition: background .1s; }
.timeline-item:last-child { border-bottom: none; }
.timeline-item:hover { background: #fafcfc; }
.timeline-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
.vitals-bar { background: #f8fafc; border-radius: 8px; padding: 8px 14px; font-size: 12px; color: #475569; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.consult-block { border-left: 3px solid var(--doc-primary); padding-left: 14px; margin-bottom: 10px; }
</style>

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item"><a href="{{ route('doctor.patients') }}" style="color:rgba(255,255,255,.7)">Patients</a></li>
        <li class="breadcrumb-item active" style="color:#fff">History</li>
      </ol>
    </nav>
    <h4 class="mb-0">Patient Medical History</h4>
  </div>
  <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

<div class="row g-3">
  {{-- Patient Info --}}
  <div class="col-lg-4">
    <div style="position:sticky;top:80px">
      <div class="doc-card">
        <div style="background:var(--doc-primary);color:#fff;padding:24px;text-align:center">
          <div style="width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;overflow:hidden">
            @if($patient->enrollee_photo ?? false)
              <img src="{{ asset('storage/' . $patient->enrollee_photo) }}" style="width:100%;height:100%;object-fit:cover" alt="">
            @else
              <span class="material-symbols-outlined" style="font-size:30px">person</span>
            @endif
          </div>
          <h5 style="margin:0 0 6px;color:#fff;font-weight:700;font-size:16px">{{ $patient->enrollee_name ?? 'N/A' }}</h5>
          <span style="display:inline-block;padding:3px 12px;border-radius:20px;background:rgba(255,255,255,.2);font-size:12px">{{ $patient->enrollee_number ?? '' }}</span>
        </div>
        <div style="padding:16px 20px">
          <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ $patient->enrollee_gender ?? 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Age</span><span class="info-value">{{ $patient->enrollee_dob ? \Carbon\Carbon::parse($patient->enrollee_dob)->age . ' years' : 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Phone</span><span class="info-value">{{ $patient->enrollee_phone ?? 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Encounters</span><span class="doc-badge doc-badge-teal">{{ $encounters->count() }}</span></div>
        </div>
      </div>

      @if($patient->programs->isNotEmpty())
      <div class="doc-card">
        <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">badge</span> Programs</div>
        <div style="padding:14px 20px;display:flex;flex-wrap:wrap;gap:6px">
          @foreach($patient->programs as $program)
          <span class="doc-badge doc-badge-blue">{{ $program->name }}</span>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Timeline --}}
  <div class="col-lg-8">
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">timeline</span> Encounter Timeline</div>
      <div>
        @forelse($encounters as $encounter)
        <div class="timeline-item">
          <div class="timeline-header">
            <div>
              @php $sc = match($encounter->status) { 'Completed' => 'green', 'In Progress' => 'amber', default => 'gray' }; @endphp
              <span class="doc-badge doc-badge-{{ $sc }}" style="margin-right:8px">{{ $encounter->status }}</span>
              <strong style="font-size:14px;color:#1e293b">{{ $encounter->visit_date?->format('d M Y') }}</strong>
              <div style="font-size:12px;color:#94a3b8;margin-top:2px">{{ $encounter->nature_of_visit }} | {{ $encounter->program->name ?? 'N/A' }}</div>
            </div>
            <span style="font-size:11px;color:#94a3b8">{{ $encounter->created_at->diffForHumans() }}</span>
          </div>

          @if($encounter->reason_for_visit)
          <div style="margin-bottom:8px;font-size:13px"><strong style="color:#64748b">Complaint:</strong> <span style="color:#334155">{{ $encounter->reason_for_visit }}</span></div>
          @endif

          @if($encounter->vitalSigns->isNotEmpty())
          @php $vs = $encounter->vitalSigns->first(); @endphp
          <div class="vitals-bar">
            <span class="material-symbols-outlined" style="font-size:14px;color:var(--doc-primary)">monitor_heart</span>
            <span>T:{{ $vs->temperature }}°C</span>
            <span style="color:#ccc">|</span>
            <span>BP:{{ $vs->blood_pressure_systolic }}/{{ $vs->blood_pressure_diastolic }}</span>
            <span style="color:#ccc">|</span>
            <span>P:{{ $vs->pulse_rate }}</span>
            <span style="color:#ccc">|</span>
            <span>SpO2:{{ $vs->spo2 }}%</span>
            @php $pc2 = ['Red'=>'red','Yellow'=>'amber','Green'=>'green']; @endphp
            <span class="doc-badge doc-badge-{{ $pc2[$vs->overall_priority] ?? 'gray' }}" style="margin-left:auto">{{ $vs->overall_priority }}</span>
          </div>
          @endif

          @foreach($encounter->consultations as $consultation)
          <div class="consult-block">
            <div style="font-size:12px;color:#94a3b8;margin-bottom:4px">Consultation by Dr. {{ $consultation->doctor->name ?? 'N/A' }}</div>
            @if($consultation->presenting_complaints)
            <div style="font-size:12px;margin-bottom:4px"><strong style="color:#64748b">Complaints:</strong> {{ Str::limit($consultation->presenting_complaints, 100) }}</div>
            @endif
            @if($consultation->diagnoses->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:4px">
              @foreach($consultation->diagnoses as $diagnosis)
              <span class="doc-badge {{ $diagnosis->diagnosis_type == 'Confirmed' ? 'doc-badge-green' : 'doc-badge-amber' }}">{{ $diagnosis->icdCode->code ?? '' }}</span>
              @endforeach
            </div>
            @endif
            @if($consultation->prescriptions->flatMap->items->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:4px">
              <span class="material-symbols-outlined" style="font-size:13px;color:#94a3b8;margin-top:2px">medication</span>
              @foreach($consultation->prescriptions->flatMap->items->take(3) as $item)
              <span class="doc-badge doc-badge-gray">{{ $item->drug->name ?? 'Unknown' }}</span>
              @endforeach
              @if($consultation->prescriptions->flatMap->items->count() > 3)
              <span class="doc-badge doc-badge-gray">+{{ $consultation->prescriptions->flatMap->items->count() - 3 }}</span>
              @endif
            </div>
            @endif
          </div>
          @endforeach

          @if($encounter->outcome)
          <div style="font-size:12px;color:#64748b;margin-top:6px"><strong>Outcome:</strong> {{ $encounter->outcome }}</div>
          @endif
        </div>
        @empty
        <div style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#94a3b8;opacity:.4">history</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Previous Encounters</h5>
          <p style="color:#94a3b8;font-size:13px">This patient has no recorded encounters</p>
        </div>
        @endforelse
      </div>
    </div>

    <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline"><span class="material-symbols-outlined" style="font-size:14px">arrow_back</span> Back to Dashboard</a>
  </div>
</div>

</div>
@endsection

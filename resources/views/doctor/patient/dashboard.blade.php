@extends('layouts.app')
@section('title', ($patient->beneficiary?->fullname ?? 'Patient') . ' — Dashboard')
@section('content')
@php
    $info = $patient->beneficiary;
    $age  = $info?->date_of_birth ? \Carbon\Carbon::parse($info->date_of_birth)->age : null;
    $latestVital = $allVitals->first();
    $photo = $info?->photo ? asset('storage/'.$info->photo) : null;
    $openEnc = $encounters->whereNotIn('status',['Completed','Cancelled'])->first();
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h3 class="mb-0">Patient Dashboard</h3>
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none"><span class="material-symbols-outlined">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('doctor.patients') }}" class="text-decoration-none">Patients</a></li>
            <li class="breadcrumb-item active">{{ $info?->fullname ?? $patient->enrollee_number }}</li>
        </ol>
    </nav>
</div>
<div class="card border-0 rounded-3 shadow-sm mb-4">
  <div class="card-body p-0">
    <div class="d-flex flex-wrap align-items-stretch">
      <div class="d-flex align-items-center gap-3 p-4 border-end" style="min-width:260px">
        @if($photo)
          <img src="{{ $photo }}" class="rounded-circle object-fit-cover border border-3 border-primary" width="70" height="70" alt="">
        @else
          <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center border border-3 border-primary" style="width:70px;height:70px">
            <span class="material-symbols-outlined text-primary" style="font-size:32px">person</span>
          </div>
        @endif
        <div>
          <h5 class="mb-1 fw-bold">{{ $info?->fullname ?? 'Unknown' }}</h5>
          <div class="mb-1">
            <span class="badge bg-primary bg-opacity-10 text-primary me-1">{{ $patient->enrollee_number }}</span>
            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $patient->file_number }}</span>
          </div>
          <div class="text-muted small">{{ $info?->gender }}@if($age) &bull; {{ $age }} yrs @endif</div>
          @if($info?->phone_no)<div class="text-muted small"><span class="material-symbols-outlined align-middle" style="font-size:13px">call</span> {{ $info->phone_no }}</div>@endif
        </div>
      </div>
      @php $tiles=[['event_note','Total Visits',$stats['total_visits'],'primary'],['calendar_today','Last Visit',$stats['last_visit']?\Carbon\Carbon::parse($stats['last_visit'])->format('d M Y'):'—','info'],['medication','Active Rx',$stats['active_rx'],$stats['active_rx']>0?'warning':'secondary'],['biotech','Pending Lab',$stats['pending_lab'],$stats['pending_lab']>0?'danger':'secondary'],['diagnosis','Diagnoses',$stats['total_diagnoses'],'success'],['event_upcoming','Follow-up',$stats['follow_up']?\Carbon\Carbon::parse($stats['follow_up'])->format('d M Y'):'—','secondary']]; @endphp
      <div class="d-flex flex-wrap flex-grow-1">
      @foreach($tiles as [$icon,$label,$val,$c])
        <div class="d-flex flex-column align-items-center justify-content-center text-center px-3 py-3 border-end" style="flex:1;min-width:90px">
          <span class="material-symbols-outlined text-{{ $c }} mb-1" style="font-size:20px">{{ $icon }}</span>
          <div class="fw-bold lh-1 mb-1" style="font-size:16px">{{ $val }}</div>
          <div class="text-muted" style="font-size:10px">{{ $label }}</div>
        </div>
      @endforeach
      </div>
      <div class="d-flex flex-column justify-content-center gap-2 px-3">
        @if($openEnc)
        <a href="{{ route('doctor.consultation.start',$openEnc) }}" class="btn btn-primary btn-sm text-nowrap">
          <span class="material-symbols-outlined align-middle me-1" style="font-size:14px">{{ $activeConsultations->isNotEmpty()?'play_arrow':'add' }}</span>{{ $activeConsultations->isNotEmpty()?'Continue':'Consult' }}</a>
        @endif
        <a href="{{ route('doctor.patients') }}" class="btn btn-outline-secondary btn-sm"><span class="material-symbols-outlined align-middle" style="font-size:14px">arrow_back</span></a>
      </div>
    </div>
  </div>
</div>
<ul class="nav nav-tabs" id="pTabs">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#t-overview"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">dashboard</span>Overview</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t-consult"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">stethoscope</span>Consultations <span class="badge bg-primary ms-1">{{ $allConsultations->count() }}</span></a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t-lab"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">biotech</span>Lab @if($stats['pending_lab'])<span class="badge bg-danger ms-1">{{ $stats['pending_lab'] }}</span>@endif</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t-pharmacy"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">medication</span>Pharmacy @if($stats['active_rx'])<span class="badge bg-warning text-dark ms-1">{{ $stats['active_rx'] }}</span>@endif</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t-vitals"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">monitor_heart</span>Vitals <span class="badge bg-secondary ms-1">{{ $allVitals->count() }}</span></a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#t-encounters"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">event_note</span>Encounters <span class="badge bg-secondary ms-1">{{ $encounters->count() }}</span></a></li>
</ul>
<div class="tab-content border border-top-0 rounded-bottom-3 bg-white shadow-sm p-4">
{{-- OVERVIEW --}}
<div class="tab-pane fade show active" id="t-overview">
  <div class="row g-4">
    <div class="col-lg-4">
      <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:11px">Latest Vitals @if($latestVital)<span class="fw-normal ms-1 text-lowercase" style="font-size:10px">— {{ $latestVital->created_at->diffForHumans() }}</span>@endif</h6>
      @if($latestVital)
      @php $vRows=[ ['Temp','thermostat','danger',($latestVital->temperature??'—').'°C'], ['BP','favorite','danger',($latestVital->blood_pressure_systolic??'—').'/'.($latestVital->blood_pressure_diastolic??'—').' mmHg'], ['Pulse','monitor_heart','primary',($latestVital->pulse_rate??'—').' bpm'], ['SpO₂','air','info',($latestVital->spo2??'—').'%'], ['Resp','pulmonology','success',($latestVital->respiration_rate??'—').' /min'], ['Weight','scale','secondary',($latestVital->weight??'—').' kg'], ['Height','height','secondary',($latestVital->height??'—').' cm'], ['BMI','person','warning',$latestVital->bmi??'—'] ]; @endphp
      <div class="row g-2">
        @foreach($vRows as [$lbl,$ico,$c,$v])
        <div class="col-6"><div class="bg-light rounded-3 p-2 d-flex align-items-center gap-2">
          <span class="material-symbols-outlined text-{{ $c }}" style="font-size:18px">{{ $ico }}</span>
          <div><div class="fw-semibold lh-1" style="font-size:13px">{{ $v }}</div><div class="text-muted" style="font-size:10px">{{ $lbl }}</div></div>
        </div></div>
        @endforeach
      </div>
      @else
      <div class="text-center text-muted py-4"><span class="material-symbols-outlined" style="font-size:40px;opacity:.3">monitor_heart</span><p class="small mt-2 mb-0">No vitals recorded</p></div>
      @endif
    </div>
    <div class="col-lg-4">
      <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:11px">Diagnoses</h6>
      @forelse($allDiagnoses->sortByDesc('created_at')->take(8) as $dx)
      <div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
        <span class="badge bg-{{ $dx->diagnosis_type==='Confirmed'?'danger':'warning text-dark' }} mt-1" style="font-size:10px;min-width:68px">{{ $dx->diagnosis_type??'Provisional' }}</span>
        <div>
          <div style="font-size:13px">{{ $dx->icdCode?->description ?? $dx->diagnosis_name ?? 'Unknown' }}</div>
          @if($dx->icdCode?->code)<div class="text-muted" style="font-size:11px">{{ $dx->icdCode->code }}</div>@endif
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-4"><span class="material-symbols-outlined" style="font-size:40px;opacity:.3">diagnosis</span><p class="small mt-2 mb-0">No diagnoses</p></div>
      @endforelse
    </div>
    <div class="col-lg-4">
      <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:11px">Visit Timeline</h6>
      @forelse($encounters->take(7) as $enc)
      <div class="d-flex gap-2 mb-3">
        <div class="d-flex flex-column align-items-center">
          <div class="rounded-circle bg-primary" style="width:8px;height:8px;margin-top:4px;flex-shrink:0"></div>
          <div style="width:1px;flex-grow:1;background:#dee2e6"></div>
        </div>
        <div class="pb-1">
          <div class="fw-medium" style="font-size:12px">{{ $enc->nature_of_visit ?? 'Visit' }}</div>
          <div class="text-muted" style="font-size:11px">{{ $enc->visit_date?->format('d M Y') }}</div>
          @php $sc=['Completed'=>'success','Cancelled'=>'danger','In Consultation'=>'primary','Triaged'=>'warning']; @endphp
          <span class="badge bg-{{ $sc[$enc->status]??'secondary' }} bg-opacity-10 text-{{ $sc[$enc->status]??'secondary' }}" style="font-size:10px">{{ $enc->status }}</span>
        </div>
      </div>
      @empty<p class="text-muted small">No visits yet</p>@endforelse
    </div>
  </div>
  @if($activePrescriptions->isNotEmpty())
  <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0 d-flex gap-2 align-items-center">
    <span class="material-symbols-outlined">medication</span>
    <div><strong>{{ $activePrescriptions->count() }} active prescription(s) pending dispensing</strong>
    <div class="small">{{ $activePrescriptions->pluck('prescription_number')->join(', ') }}</div></div>
  </div>
  @endif
  @if($pendingOrders->isNotEmpty())
  <div class="alert alert-danger border-0 rounded-3 mt-2 mb-0 d-flex gap-2 align-items-center">
    <span class="material-symbols-outlined">biotech</span>
    <div><strong>{{ $pendingOrders->count() }} pending lab/service order(s)</strong></div>
  </div>
  @endif
</div>
{{-- CONSULTATIONS --}}
<div class="tab-pane fade" id="t-consult">
  <ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#c-active">Active <span class="badge bg-primary ms-1">{{ $activeConsultations->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#c-history">History <span class="badge bg-secondary ms-1">{{ $pastConsultations->count() }}</span></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="c-active">
      @forelse($activeConsultations as $c)
      <div class="card border-0 bg-light rounded-3 mb-3">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">{{ $c->created_at->format('d M Y') }} <span class="badge bg-primary ms-1">In Progress</span></div>
            <div class="text-muted small">Dr. {{ $c->doctor?->name ?? 'N/A' }}</div>
            @if($c->presenting_complaints)<div class="small mt-1">{{ Str::limit($c->presenting_complaints,80) }}</div>@endif
          </div>
          <div class="d-flex gap-2">
            @if($c->encounter)<a href="{{ route('doctor.consultation.start',$c->encounter) }}" class="btn btn-primary btn-sm"><span class="material-symbols-outlined align-middle me-1" style="font-size:14px">play_arrow</span>Continue</a>@endif
            <a href="{{ route('doctor.consultation.show',$c) }}" class="btn btn-outline-secondary btn-sm"><span class="material-symbols-outlined align-middle" style="font-size:14px">visibility</span></a>
          </div>
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">stethoscope</span><p class="mt-2 mb-0 small">No active consultations</p></div>
      @endforelse
    </div>
    <div class="tab-pane fade" id="c-history">
      @forelse($pastConsultations as $c)
      <div class="d-flex justify-content-between align-items-start py-3 border-bottom flex-wrap gap-2">
        <div>
          <div class="fw-medium">{{ $c->created_at->format('d M Y') }} <span class="badge bg-success bg-opacity-10 text-success ms-1">Completed</span></div>
          <div class="text-muted small">Dr. {{ $c->doctor?->name ?? 'N/A' }}</div>
          @if($c->diagnoses->isNotEmpty())<div class="small mt-1 text-muted">{{ $c->diagnoses->map(fn($d)=>$d->icdCode?->description??($d->diagnosis_name??''))->filter()->join(' · ') }}</div>@endif
          @if($c->prescriptions->isNotEmpty())<div class="text-muted small"><span class="material-symbols-outlined align-middle" style="font-size:12px">medication</span> {{ $c->prescriptions->count() }} prescription(s)</div>@endif
        </div>
        <a href="{{ route('doctor.consultation.show',$c) }}" class="btn btn-outline-primary btn-sm"><span class="material-symbols-outlined align-middle" style="font-size:14px">visibility</span> View</a>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">history</span><p class="mt-2 mb-0 small">No consultation history</p></div>
      @endforelse
    </div>
  </div>
</div>
{{-- LAB --}}
<div class="tab-pane fade" id="t-lab">
  <ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#l-pending">Pending <span class="badge bg-danger ms-1">{{ $pendingOrders->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#l-completed">Completed <span class="badge bg-secondary ms-1">{{ $completedOrders->count() }}</span></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="l-pending">
      @forelse($pendingOrders as $order)
      <div class="card border-0 bg-light rounded-3 mb-3">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">
              <span class="material-symbols-outlined align-middle text-warning me-1" style="font-size:15px">science</span>
              {{ $order->order_number ?? 'Order #'.substr($order->id,0,8) }}
            </div>
            <div class="text-muted small">{{ $order->created_at?->format('d M Y') }}</div>
          </div>
          @foreach($order->items as $item)
          @php
            $sc2=['pending'=>'warning','in_progress'=>'info','completed'=>'success'];
            $bc=$sc2[$item->status]??'secondary';
            $res=$item->latestResult;
          @endphp
          <div class="border-top py-2">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size:13px">{{ $item->serviceItem?->name ?? 'Service' }}</div>
                @if($item->serviceItem?->serviceType?->name)
                <div class="text-muted" style="font-size:11px">{{ $item->serviceItem->serviceType->name }}</div>
                @endif
              </div>
              <span class="badge bg-{{ $bc }} bg-opacity-15 text-{{ $bc }}" style="font-size:11px;white-space:nowrap">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span>
            </div>
            @if($res)
            <div class="mt-1 p-2 bg-white rounded-2 border-start border-3 border-success" style="font-size:12px">
              @if($res->result_value)<div><strong>Result:</strong> {{ $res->result_value }} @if($res->reference_range)<span class="text-muted">(Ref: {{ $res->reference_range }})</span>@endif</div>@endif
              @if($res->remark)<div><strong>Remark:</strong> <span class="badge bg-{{ in_array($res->remark,['Normal','Negative'])?'success':'danger' }} bg-opacity-10 text-{{ in_array($res->remark,['Normal','Negative'])?'success':'danger' }}">{{ $res->remark }}</span></div>@endif
              @if($res->result_note)<div class="text-muted">{{ Str::limit($res->result_note,120) }}</div>@endif
            </div>
            @endif
          </div>
          @endforeach
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">biotech</span><p class="mt-2 mb-0 small">No pending lab orders</p></div>
      @endforelse
    </div>
    <div class="tab-pane fade" id="l-completed">
      @forelse($completedOrders as $order)
      <div class="card border-0 bg-light rounded-3 mb-3">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">
              <span class="material-symbols-outlined align-middle text-success me-1" style="font-size:15px">verified</span>
              {{ $order->order_number ?? 'Order #'.substr($order->id,0,8) }}
            </div>
            <div class="text-muted small">{{ $order->updated_at?->format('d M Y') }}</div>
          </div>
          @foreach($order->items as $item)
          @php $res=$item->latestResult; @endphp
          <div class="border-top py-2">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size:13px">{{ $item->serviceItem?->name ?? 'Service' }}</div>
                @if($item->serviceItem?->serviceType?->name)
                <div class="text-muted" style="font-size:11px">{{ $item->serviceItem->serviceType->name }}</div>
                @endif
              </div>
              <span class="badge bg-success bg-opacity-10 text-success" style="font-size:11px">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span>
            </div>
            @if($res)
            <div class="mt-1 p-2 bg-white rounded-2 border-start border-3 border-success" style="font-size:12px">
              @if($res->result_value)<div><strong>Result:</strong> {{ $res->result_value }} @if($res->reference_range)<span class="text-muted">(Ref: {{ $res->reference_range }})</span>@endif</div>@endif
              @if($res->remark)
              @php $rc2=in_array($res->remark,['Normal','Negative'])?'success':'danger'; @endphp
              <div><strong>Remark:</strong> <span class="badge bg-{{ $rc2 }} bg-opacity-10 text-{{ $rc2 }}">{{ $res->remark }}</span></div>
              @endif
              @if($res->result_note)<div class="text-muted">{{ Str::limit($res->result_note,120) }}</div>@endif
              @if($res->result_document_url)
              <div class="mt-1"><a href="{{ Storage::url($res->result_document_url) }}" target="_blank" class="text-primary" style="font-size:11px"><span class="material-symbols-outlined align-middle" style="font-size:12px">open_in_new</span> View Document</a></div>
              @endif
              <div class="text-muted mt-1" style="font-size:10px">By {{ $res->reportedBy?->name ?? '—' }} &bull; {{ $res->reported_at?->format('d M Y H:i') }}</div>
            </div>
            @else
            <div class="text-muted mt-1" style="font-size:11px"><span class="material-symbols-outlined align-middle" style="font-size:11px">pending</span> Awaiting result entry</div>
            @endif
          </div>
          @endforeach
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">check_circle</span><p class="mt-2 mb-0 small">No completed lab orders</p></div>
      @endforelse
    </div>
  </div>
</div>
{{-- PHARMACY --}}
<div class="tab-pane fade" id="t-pharmacy">
  <ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#p-active">Active <span class="badge bg-warning text-dark ms-1">{{ $activePrescriptions->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#p-dispensed">Dispensed <span class="badge bg-secondary ms-1">{{ $dispensedPrescriptions->count() }}</span></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade show active" id="p-active">
      @forelse($activePrescriptions as $rx)
      <div class="card border-0 bg-light rounded-3 mb-3">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">{{ $rx->prescription_number }}
              @if($rx->status === 'Partially Dispensed') <span class="badge bg-warning text-dark ms-1">Partial</span>
              @else <span class="badge bg-danger bg-opacity-10 text-danger ms-1">Pending</span>@endif
            </div>
            <div class="text-muted small">{{ ($rx->prescription_date ?? $rx->created_at)?->format('d M Y') }}</div>
          </div>
          @foreach($rx->items as $item)
          <div class="d-flex justify-content-between border-top py-1 align-items-center">
            <div>
              <span style="font-size:13px">{{ $item->drug?->name ?? 'Drug' }}</span>
              @if($item->dosage)<span class="text-muted small ms-2">{{ $item->dosage }}</span>@endif
              @if($item->frequency)<span class="text-muted small ms-1">— {{ $item->frequency }}</span>@endif
            </div>
            <span class="badge bg-{{ ($item->dispensing_status??'')===('Dispensed')?'success':'secondary' }} bg-opacity-10 text-{{ ($item->dispensing_status??'')===('Dispensed')?'success':'secondary' }}" style="font-size:11px">{{ $item->dispensing_status ?? 'Pending' }}</span>
          </div>
          @endforeach
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">medication</span><p class="mt-2 mb-0 small">No active prescriptions</p></div>
      @endforelse
    </div>
    <div class="tab-pane fade" id="p-dispensed">
      @forelse($dispensedPrescriptions as $rx)
      <div class="d-flex justify-content-between align-items-center py-3 border-bottom flex-wrap gap-2">
        <div>
          <div class="fw-medium">{{ $rx->prescription_number }} <span class="badge bg-success bg-opacity-10 text-success ms-1">Dispensed</span></div>
          <div class="text-muted small">{{ $rx->items->count() }} item(s) &bull; {{ $rx->updated_at?->format('d M Y') }}</div>
          @if($rx->items->isNotEmpty())<div class="small text-muted">{{ $rx->items->map(fn($i)=>$i->drug?->name)->filter()->join(', ') }}</div>@endif
        </div>
      </div>
      @empty
      <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">check_circle</span><p class="mt-2 mb-0 small">No dispensed prescriptions</p></div>
      @endforelse
    </div>
  </div>
</div>
{{-- VITALS --}}
<div class="tab-pane fade" id="t-vitals">
  @if($allVitals->isEmpty())
  <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">monitor_heart</span><p class="mt-2 mb-0">No vital signs recorded</p></div>
  @else
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light"><tr>
        <th>Date</th><th>Temp&nbsp;(°C)</th><th>BP&nbsp;(mmHg)</th><th>Pulse&nbsp;(bpm)</th><th>SpO₂&nbsp;(%)</th><th>Resp&nbsp;(/min)</th><th>Wt&nbsp;(kg)</th><th>Ht&nbsp;(cm)</th><th>BMI</th><th>Priority</th>
      </tr></thead>
      <tbody>
        @foreach($allVitals as $v)
        @php 
        $pc=[
            'Red'=>'danger','Yellow'=>'warning','Green'=>'success',
            'High'=>'danger','high'=>'danger',
            'Critical'=>'danger','critical'=>'danger',
            'Urgent'=>'warning','urgent'=>'warning',
            'Normal'=>'success','normal'=>'success'
        ]; 
        @endphp
        <tr>
          <td class="text-nowrap"><strong>{{ $v->created_at->format('d M Y') }}</strong><div class="text-muted" style="font-size:11px">{{ $v->created_at->format('H:i') }}</div></td>
          <td>{{ $v->temperature ?? '—' }}</td>
          <td>{{ ($v->blood_pressure_systolic??'—') }}/{{ ($v->blood_pressure_diastolic??'—') }}</td>
          <td>{{ $v->pulse_rate ?? '—' }}</td>
          <td>{{ $v->spo2 ?? '—' }}</td>
          <td>{{ $v->respiration_rate ?? '—' }}</td>
          <td>{{ $v->weight ?? '—' }}</td>
          <td>{{ $v->height ?? '—' }}</td>
          <td>{{ $v->bmi ?? '—' }}</td>
          <td>@if($v->overall_priority)<span class="badge bg-{{ $pc[$v->overall_priority]??'secondary' }}">{{ $v->overall_priority }}</span>@else —@endif</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- ENCOUNTERS --}}
<div class="tab-pane fade" id="t-encounters">
  @forelse($encounters as $enc)
  @php $sc=['Completed'=>'success','Cancelled'=>'danger','In Consultation'=>'primary','Triaged'=>'warning','Awaiting Lab'=>'info','Awaiting Pharmacy'=>'warning']; @endphp
  <div class="card border-0 rounded-3 mb-3" style="border-left:4px solid var(--bs-{{ $sc[$enc->status]??'secondary' }}) !important">
    <div class="card-body p-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <div class="fw-semibold">{{ $enc->visit_date?->format('d M Y') ?? 'N/A' }}
          <span class="badge bg-{{ $sc[$enc->status]??'secondary' }} bg-opacity-10 text-{{ $sc[$enc->status]??'secondary' }} ms-1">{{ $enc->status }}</span>
        </div>
        <div class="text-muted small">{{ $enc->nature_of_visit ?? 'Visit' }} &bull; {{ $enc->program?->name ?? 'N/A' }}</div>
        @if($enc->consultations->isNotEmpty())
        <div class="small text-muted mt-1">
          <span class="me-2"><span class="material-symbols-outlined align-middle" style="font-size:12px">stethoscope</span> {{ $enc->consultations->count() }} consultation(s)</span>
          <span class="me-2"><span class="material-symbols-outlined align-middle" style="font-size:12px">diagnosis</span> {{ $enc->consultations->flatMap->diagnoses->count() }} diagnosis(es)</span>
          <span><span class="material-symbols-outlined align-middle" style="font-size:12px">medication</span> {{ $enc->consultations->flatMap->prescriptions->count() }} Rx</span>
        </div>
        @endif
      </div>
      <div class="d-flex gap-2">
        @if(!in_array($enc->status,['Completed','Cancelled']))
        <a href="{{ route('doctor.consultation.start',$enc) }}" class="btn btn-primary btn-sm"><span class="material-symbols-outlined align-middle" style="font-size:14px">play_arrow</span></a>
        @elseif($enc->consultations->first())
        <a href="{{ route('doctor.consultation.show',$enc->consultations->first()) }}" class="btn btn-outline-success btn-sm"><span class="material-symbols-outlined align-middle" style="font-size:14px">visibility</span></a>
        @endif
      </div>
    </div>
  </div>
  @empty
  <div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size:48px;opacity:.3">event_note</span><p class="mt-2 mb-0">No encounters recorded</p></div>
  @endforelse
</div>

</div>{{-- /tab-content --}}

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.nav-tabs { border-bottom: 2px solid var(--doc-border); }
.nav-tabs .nav-link { color:#64748b; border-radius:10px 10px 0 0; border:none; padding:10px 16px; font-size:13px; font-weight:500; transition:all .15s; border-bottom:2px solid transparent; margin-bottom:-2px; }
.nav-tabs .nav-link:hover { color:var(--doc-primary); background:transparent; }
.nav-tabs .nav-link.active { font-weight:700; color:var(--doc-primary); border-bottom-color:var(--doc-primary); background:transparent; }
.nav-pills .nav-link { font-size:12px; border-radius:8px; padding:6px 14px; color:#64748b; font-weight:500; }
.nav-pills .nav-link.active { background:var(--doc-primary); color:#fff; font-weight:600; }
.tab-content { border:1px solid var(--doc-border); border-top:none; border-radius:0 0 14px 14px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); padding:20px; }
.card { border-radius:14px !important; border-color:var(--doc-border) !important; }
.badge { border-radius:20px; font-weight:600; }
</style>
@endsection

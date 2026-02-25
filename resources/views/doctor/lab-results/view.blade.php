@extends('layouts.app')
@section('title', 'Lab Results')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.doc-card-header { padding: 14px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.doc-btn-success { background: #059669; color: #fff; }
.doc-btn-success:hover { background: #047857; color: #fff; }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row .info-label { color: #94a3b8; }
.info-row .info-value { font-weight: 600; color: #1e293b; }
.result-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); overflow: hidden; margin-bottom: 16px; }
.result-header { padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--doc-border); }
.result-body { padding: 0; }
.result-entry { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
.result-entry:last-child { border-bottom: none; }
.result-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.result-detail { background: #f8fafc; border-radius: 10px; padding: 12px 16px; border-left: 3px solid #059669; margin-top: 8px; }
.vital-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
.vital-tile { background: #f8fafc; border-radius: 8px; padding: 8px 12px; text-align: center; }
.vital-tile .vital-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; }
.vital-tile .vital-value { font-weight: 700; color: #1e293b; font-size: 14px; }
</style>

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.queue') }}" style="color:rgba(255,255,255,.7)">Queue</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Lab Results</li>
      </ol>
    </nav>
    <h4 class="mb-0">Lab Results</h4>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('doctor.consultation.start', $order->encounter) }}" class="doc-btn doc-btn-success">
      <span class="material-symbols-outlined" style="font-size:15px">play_arrow</span> Continue Consultation
    </a>
    <a href="{{ route('doctor.queue') }}#lab-tab" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
      <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Queue
    </a>
  </div>
</div>

<div class="row g-3">
  {{-- Sidebar --}}
  <div class="col-lg-4">
    <div style="position:sticky;top:80px">
      <div class="doc-card">
        <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">person</span> Patient Information</div>
        <div style="padding:16px 20px">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
            <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--doc-primary-light)">
              @if($order->encounter->patient->enrollee_photo ?? false)
                <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/' . $order->encounter->patient->enrollee_photo }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:24px;color:var(--doc-primary)">person</span>
              @endif
            </div>
            <div>
              <div style="font-weight:700;font-size:15px;color:#1e293b">{{ $order->encounter->patient->enrollee_name ?? 'N/A' }}</div>
              <div style="font-size:12px;color:#94a3b8">{{ $order->encounter->patient->enrollee_number ?? '' }}</div>
            </div>
          </div>
          <div class="info-row"><span class="info-label">Age/Gender</span><span class="info-value">{{ $order->encounter->patient->age }}y, {{ $order->encounter->patient->gender }}</span></div>
          <div class="info-row"><span class="info-label">Program</span><span class="info-value">{{ $order->encounter->program->name ?? 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Order No.</span><span class="doc-badge doc-badge-blue">{{ $order->order_number }}</span></div>
          <div class="info-row"><span class="info-label">Ordered By</span><span class="info-value">{{ $order->orderedBy?->name ?? 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $order->created_at->format('d M Y H:i') }}</span></div>
        </div>
      </div>

      @if($order->encounter->vitalSigns->isNotEmpty())
      @php $vitals = $order->encounter->vitalSigns->last(); @endphp
      <div class="doc-card">
        <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:#0284c7">monitor_heart</span> Vital Signs</div>
        <div style="padding:14px 20px">
          <div class="vital-grid">
            @if($vitals->blood_pressure_systolic && $vitals->blood_pressure_diastolic)
            <div class="vital-tile"><div class="vital-label">BP</div><div class="vital-value">{{ $vitals->blood_pressure_systolic }}/{{ $vitals->blood_pressure_diastolic }}</div></div>
            @endif
            @if($vitals->heart_rate)<div class="vital-tile"><div class="vital-label">HR</div><div class="vital-value">{{ $vitals->heart_rate }}</div></div>@endif
            @if($vitals->temperature)<div class="vital-tile"><div class="vital-label">Temp</div><div class="vital-value">{{ $vitals->temperature }}°</div></div>@endif
            @if($vitals->oxygen_saturation)<div class="vital-tile"><div class="vital-label">SpO2</div><div class="vital-value">{{ $vitals->oxygen_saturation }}%</div></div>@endif
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Results --}}
  <div class="col-lg-8">
    @foreach($order->items as $item)
    <div class="result-card">
      <div class="result-header">
        <div style="font-weight:700;font-size:14px;color:#1e293b">{{ $item->serviceItem->name }}</div>
        @php $sc = match($item->status) { 'completed' => 'green', 'in_progress' => 'amber', default => 'gray' }; @endphp
        <span class="doc-badge doc-badge-{{ $sc }}">{{ ucfirst($item->status) }}</span>
      </div>

      @if($item->serviceResults->isNotEmpty())
      <div class="result-body">
        @foreach($item->serviceResults as $result)
        <div class="result-entry">
          <div class="result-meta">
            <span class="doc-badge doc-badge-green">Result #{{ $loop->iteration }}</span>
            <span style="font-size:11px;color:#94a3b8">{{ $result->reported_at?->format('d M Y H:i') }} — {{ $result->reportedBy?->name ?? '—' }}</span>
          </div>

          @if($result->result_value)
          <div style="margin-bottom:6px">
            <span style="color:#64748b;font-size:12px">Result:</span>
            <strong style="color:#1e293b;margin-left:4px">{{ $result->result_value }}</strong>
            @if($result->unit)<span style="color:#94a3b8;font-size:12px;margin-left:4px">{{ $result->unit }}</span>@endif
            @if($result->reference_range)<span style="color:#94a3b8;font-size:11px;margin-left:8px">(Ref: {{ $result->reference_range }})</span>@endif
          </div>
          @endif

          @if($result->result_status)
          <div style="margin-bottom:6px">
            <span style="color:#64748b;font-size:12px">Status:</span>
            @php $rsc = match($result->result_status) { 'normal' => 'green', 'abnormal' => 'amber', default => 'red' }; @endphp
            <span class="doc-badge doc-badge-{{ $rsc }}" style="margin-left:4px">{{ ucfirst($result->result_status) }}</span>
          </div>
          @endif

          @if($result->remark)
          <div style="margin-bottom:6px">
            <span style="color:#64748b;font-size:12px">Remark:</span>
            @php $rmk = in_array($result->remark, ['Normal','Negative']) ? 'green' : 'red'; @endphp
            <span class="doc-badge doc-badge-{{ $rmk }}" style="margin-left:4px">{{ $result->remark }}</span>
          </div>
          @endif

          @if($result->result_note)
          <div class="result-detail">
            <div style="font-size:11px;color:#64748b;font-weight:600;margin-bottom:4px">INTERPRETATION</div>
            <div style="font-size:13px;color:#334155;line-height:1.5">{{ $result->result_note }}</div>
          </div>
          @endif

          @if($result->result_document_url)
          <div style="margin-top:10px">
            @php $documents = json_decode($result->result_document_url, true) ?: [$result->result_document_url]; @endphp
            @foreach($documents as $index => $document)
            <a href="{{ Storage::url($document) }}" target="_blank" class="doc-btn doc-btn-outline" style="padding:5px 12px;margin-right:4px">
              <span class="material-symbols-outlined" style="font-size:14px">open_in_new</span> Document {{ count($documents) > 1 ? $index + 1 : '' }}
            </a>
            @endforeach
          </div>
          @endif
        </div>
        @endforeach
      </div>
      @else
      <div style="text-align:center;padding:40px 20px;color:#94a3b8">
        <span class="material-symbols-outlined" style="font-size:44px;opacity:.4">hourglass_empty</span>
        <p style="margin:8px 0 0;font-size:13px">Results not yet available</p>
      </div>
      @endif
    </div>
    @endforeach
  </div>
</div>

</div>
@endsection

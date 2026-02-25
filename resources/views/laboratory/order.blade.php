@extends('layouts.app')
@section('title','Lab Order')
@section('content')
<style>
:root{--lab:#016634;--lab-dk:#01552b;--lab-lt:#e6f5ed;--lb:#e2e8f0;--bs-primary:#016634;--bs-primary-rgb:1,102,52}
.lab-page{font-size:14px}
.lab-header{background:linear-gradient(135deg,var(--lab-dk),var(--lab));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.lab-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.lab-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.lab-header .breadcrumb-item.active{color:#fff}
.lab-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;margin-bottom:20px}
.lab-card-header{padding:14px 20px;font-weight:600;font-size:13px;border-bottom:1px solid var(--lb);display:flex;align-items:center;gap:8px;color:#1e293b}
.lab-card-header .material-symbols-outlined{font-size:18px;color:var(--lab)}
.lab-card-body{padding:16px 20px}
.lab-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.lab-badge-amber{background:#fef3c7;color:#92400e}.lab-badge-blue{background:#dbeafe;color:#1e40af}
.lab-badge-green{background:#dcfce7;color:#166534}.lab-badge-gray{background:#f1f5f9;color:#475569}
.lab-badge-red{background:#fee2e2;color:#991b1b}
.lab-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-btn-primary{background:var(--lab);color:#fff}.lab-btn-primary:hover{background:var(--lab-dk);color:#fff}
.lab-btn-outline{background:#fff;color:var(--lab);border:1.5px solid var(--lab)}.lab-btn-outline:hover{background:var(--lab-lt);color:var(--lab)}
.lab-btn-success{background:#10b981;color:#fff}.lab-btn-success:hover{background:#059669;color:#fff}
.lab-btn-amber{background:#fef3c7;color:#92400e;border:1.5px solid #fcd34d}.lab-btn-amber:hover{background:#fde68a}
.lab-btn-blue{background:#dbeafe;color:#1e40af;border:1.5px solid #93c5fd}.lab-btn-blue:hover{background:#bfdbfe}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
.info-row:last-child{border-bottom:none}
.info-label{color:#64748b;font-weight:500}
.info-value{color:#1e293b;font-weight:600;text-align:right}
.vitals-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.vital-box{background:#f8fafc;border-radius:10px;padding:10px 8px;text-align:center}
.vital-box .v-val{font-size:14px;font-weight:700;color:#1e293b}
.vital-box .v-lbl{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.3px}
.result-entry{padding:16px 20px;border-bottom:1px solid #f1f5f9}
.result-entry:last-child{border-bottom:none}
.form-control,.form-select{border:1.5px solid var(--lb);border-radius:10px;font-size:13px;transition:border-color .15s}
.form-control:focus,.form-select:focus{border-color:var(--lab);box-shadow:0 0 0 3px rgba(1,102,52,.1)}
.form-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:4px}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
@php
$patient=$item->serviceOrder?->encounter?->patient;
$info=$patient?->beneficiary;
$name=$info?->fullname??$info?->name??'Unknown';
$enc=$item->serviceOrder?->encounter;
$sc=['pending'=>'amber','in_progress'=>'blue','completed'=>'green'];
$badge=$sc[$item->status]??'gray';
@endphp
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Lab Order — {{ $item->serviceItem?->name ?? 'Test' }}</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratory.queue') }}">Queue</a></li>
            <li class="breadcrumb-item active">Order</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" style="background:#dcfce7;color:#166534;border-left:4px solid #10b981!important">
    <span class="material-symbols-outlined align-middle me-2" style="font-size:18px">check_circle</span>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger border-0 rounded-3 mb-4" style="background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444!important">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<div class="row g-4">
<div class="col-lg-4">
    <div class="lab-card">
        <div class="lab-card-header"><span class="material-symbols-outlined">person</span> Patient Information</div>
        <div class="lab-card-body">
            <div class="info-row"><span class="info-label">Name</span><span class="info-value">{{ $name }}</span></div>
            <div class="info-row"><span class="info-label">File No.</span><span class="info-value">{{ $patient?->file_number ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Enrollee</span><span class="info-value">{{ $patient?->enrollee_number ?? '—' }}</span></div>
            @if($info?->gender)
            <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ $info->gender }}</span></div>
            @endif
            @if($info?->date_of_birth)
            <div class="info-row"><span class="info-label">Age</span><span class="info-value">{{ \Carbon\Carbon::parse($info->date_of_birth)->age }} yrs</span></div>
            @endif
            @if($info?->phone_no)
            <div class="info-row"><span class="info-label">Phone</span><span class="info-value">{{ $info->phone_no }}</span></div>
            @endif
        </div>
    </div>

    @if($enc)
    <div class="lab-card">
        <div class="lab-card-header"><span class="material-symbols-outlined">event_note</span> Encounter</div>
        <div class="lab-card-body">
            <div class="info-row"><span class="info-label">Visit</span><span class="info-value">{{ $enc->visit_date?->format('d M Y') }}</span></div>
            <div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="lab-badge lab-badge-gray">{{ $enc->status }}</span></span></div>
            <div class="info-row"><span class="info-label">Program</span><span class="info-value">{{ $enc->program?->name??'—' }}</span></div>
            @if($enc->consultations->isNotEmpty())
            <div class="info-row"><span class="info-label">Doctor</span><span class="info-value">{{ $enc->consultations->first()?->doctor?->name??'—' }}</span></div>
            @if($enc->consultations->first()?->diagnoses->isNotEmpty())
            <div class="info-row"><span class="info-label">Diagnosis</span><span class="info-value" style="max-width:60%;text-align:right">{{ $enc->consultations->first()->diagnoses->pluck('diagnosis_name')->filter()->join(', ') }}</span></div>
            @endif
            @endif
        </div>
    </div>

    @if($enc->vitalSigns->isNotEmpty())
    @php $v=$enc->vitalSigns->sortByDesc('created_at')->first(); @endphp
    <div class="lab-card">
        <div class="lab-card-header"><span class="material-symbols-outlined">monitor_heart</span> Latest Vitals</div>
        <div class="lab-card-body">
            <div class="vitals-grid">
                @foreach([['Temp',($v->temperature??'—').'°C'],['BP',($v->blood_pressure_systolic??'—').'/'.($v->blood_pressure_diastolic??'—')],['Pulse',($v->pulse_rate??'—').' bpm'],['SpO2',($v->spo2??'—').'%'],['Wt',($v->weight??'—').' kg'],['BMI',$v->bmi??'—']] as [$lbl,$val])
                <div class="vital-box"><div class="v-val">{{ $val }}</div><div class="v-lbl">{{ $lbl }}</div></div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

<div class="col-lg-8">
    <div class="lab-card">
        <div class="lab-card-header d-flex justify-content-between w-100">
            <div class="d-flex align-items-center gap-2"><span class="material-symbols-outlined">science</span> {{ $item->serviceItem?->name??'Lab Test' }}</div>
            <span class="lab-badge lab-badge-{{ $badge }}">
                <span class="material-symbols-outlined" style="font-size:12px">{{ ['pending'=>'schedule','in_progress'=>'labs','completed'=>'check_circle'][$item->status] ?? 'circle' }}</span>
                {{ ucfirst(str_replace('_',' ',$item->status)) }}
            </span>
        </div>
        <div class="lab-card-body">
            <div class="row g-3 mb-3">
                <div class="col-6"><div class="info-label" style="font-size:11px;margin-bottom:2px">Category</div><div class="fw-semibold" style="font-size:13px">{{ $item->serviceItem?->serviceType?->name??'—' }}</div></div>
                <div class="col-6"><div class="info-label" style="font-size:11px;margin-bottom:2px">Ordered By</div><div class="fw-semibold" style="font-size:13px">{{ $item->serviceOrder?->orderedBy?->name??'—' }}</div></div>
                <div class="col-6"><div class="info-label" style="font-size:11px;margin-bottom:2px">Order Date</div><div class="fw-semibold" style="font-size:13px">{{ $item->created_at->format('d M Y H:i') }}</div></div>
                @if($item->authorization_code)
                <div class="col-6"><div class="info-label" style="font-size:11px;margin-bottom:2px">Auth Code</div><div class="fw-semibold font-monospace" style="font-size:13px">{{ $item->authorization_code }}</div></div>
                @endif
                @if($item->serviceItem?->description)
                <div class="col-12"><div class="info-label" style="font-size:11px;margin-bottom:2px">Description</div><div style="font-size:13px;color:#475569">{{ $item->serviceItem->description }}</div></div>
                @endif
            </div>
            <div class="d-flex gap-2 pt-3" style="border-top:1px solid #f1f5f9">
                @if($item->status==='pending')
                <form method="POST" action="{{ route('laboratory.order.status',$item) }}">@csrf<input type="hidden" name="status" value="in_progress">
                    <button class="lab-btn lab-btn-blue"><span class="material-symbols-outlined" style="font-size:15px">labs</span> Mark In Progress</button>
                </form>
                @elseif($item->status==='in_progress')
                <form method="POST" action="{{ route('laboratory.order.status',$item) }}">@csrf<input type="hidden" name="status" value="pending">
                    <button class="lab-btn lab-btn-amber"><span class="material-symbols-outlined" style="font-size:15px">undo</span> Revert Pending</button>
                </form>
                @endif
                <a href="{{ route('laboratory.queue') }}" class="lab-btn lab-btn-outline ms-auto">Back to Queue</a>
            </div>
        </div>
    </div>

    @if($item->serviceResults->isNotEmpty())
    <div class="lab-card">
        <div class="lab-card-header" style="color:#166534"><span class="material-symbols-outlined" style="color:#10b981">verified</span> Recorded Results</div>
        @foreach($item->serviceResults as $res)
        <div class="result-entry">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="lab-badge lab-badge-green"><span class="material-symbols-outlined" style="font-size:12px">check_circle</span> Result #{{ $loop->iteration }}</span>
                <span class="text-muted" style="font-size:11px">{{ $res->reported_at?->format('d M Y H:i') }} — {{ $res->reportedBy?->name??'—' }}</span>
            </div>
            @if($res->result_value)
            <div class="mb-1" style="font-size:13px"><span class="info-label">Value:</span> <strong style="color:#1e293b">{{ $res->result_value }}</strong> @if($res->reference_range)<span class="text-muted" style="font-size:11px">(Ref: {{ $res->reference_range }})</span>@endif</div>
            @endif
            @if($res->remark)
            <div class="mb-1" style="font-size:13px"><span class="info-label">Remark:</span> <span class="lab-badge lab-badge-{{ in_array($res->remark,['Normal','Negative'])?'green':'red' }}">{{ $res->remark }}</span></div>
            @endif
            @if($res->result_note)<div class="text-muted" style="font-size:12px;margin-top:4px">{{ $res->result_note }}</div>@endif
            @if($res->result_document_url)
            <div class="mt-2 d-flex gap-1 flex-wrap">
                @php $documents = json_decode($res->result_document_url, true) ?: [$res->result_document_url]; @endphp
                @foreach($documents as $index => $document)
                <a href="{{ Storage::url($document) }}" target="_blank" class="lab-btn lab-btn-outline" style="font-size:11px;padding:4px 10px">
                    <span class="material-symbols-outlined" style="font-size:14px">open_in_new</span> Document {{ count($documents) > 1 ? $index + 1 : '' }}
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="lab-card">
        <div class="lab-card-header"><span class="material-symbols-outlined">add_circle</span> Record New Result</div>
        <div class="lab-card-body">
            <form method="POST" action="{{ route('laboratory.order.result',$item) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Result Value</label>
                        <input type="text" name="result_value" class="form-control @error('result_value') is-invalid @enderror" value="{{ old('result_value') }}" placeholder="e.g. 5.2 mmol/L">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Range</label>
                        <input type="text" name="reference_range" class="form-control" value="{{ old('reference_range') }}" placeholder="e.g. 3.9–5.5 mmol/L">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remark</label>
                        <select name="remark" class="form-select">
                            <option value="">— Select —</option>
                            @foreach(['Normal','Abnormal','Critical','Positive','Negative','Borderline'] as $r)
                            <option value="{{ $r }}" {{ old('remark')===$r?'selected':'' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Result Documents <span class="text-muted fw-normal">(PDF/Image, max 5MB per file)</span></label>
                        <div id="fileUploadContainer" style="border:1.5px dashed var(--lb);border-radius:12px;padding:16px;background:#f8fafc">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span style="font-size:12px;color:#64748b">Upload result documents</span>
                                <button type="button" class="lab-btn lab-btn-outline" style="font-size:11px;padding:4px 10px" onclick="addFileInput()">
                                    <span class="material-symbols-outlined" style="font-size:14px">add</span> Add File
                                </button>
                            </div>
                            <div id="fileInputs">
                                <div class="file-input-group mb-2">
                                    <input type="file" name="result_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile(this)">
                                    <div class="file-preview mt-2" style="display:none">
                                        <div class="d-flex align-items-center gap-2 p-2 bg-white rounded-3 border">
                                            <span class="material-symbols-outlined" style="color:var(--lab);font-size:18px">description</span>
                                            <span class="file-name" style="font-size:12px;color:#1e293b"></span>
                                            <button type="button" class="lab-btn ms-auto" style="background:#fee2e2;color:#991b1b;font-size:11px;padding:3px 8px" onclick="removeFileInput(this)">
                                                <span class="material-symbols-outlined" style="font-size:14px">close</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Clinical Notes / Interpretation</label>
                        <textarea name="result_note" rows="4" class="form-control @error('result_note') is-invalid @enderror" placeholder="Detailed findings, interpretation, recommendations…">{{ old('result_note') }}</textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="lab-btn lab-btn-success">
                            <span class="material-symbols-outlined" style="font-size:16px">save</span> Save Result & Complete
                        </button>
                        <a href="{{ route('laboratory.queue') }}" class="lab-btn lab-btn-outline">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

</div>

<script>
function addFileInput(){
    const c=document.getElementById('fileInputs'),d=document.createElement('div');
    d.className='file-input-group mb-2';
    d.innerHTML=`<input type="file" name="result_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" onchange="previewFile(this)"><div class="file-preview mt-2" style="display:none"><div class="d-flex align-items-center gap-2 p-2 bg-white rounded-3 border"><span class="material-symbols-outlined" style="color:var(--lab);font-size:18px">description</span><span class="file-name" style="font-size:12px;color:#1e293b"></span><button type="button" class="lab-btn ms-auto" style="background:#fee2e2;color:#991b1b;font-size:11px;padding:3px 8px" onclick="removeFileInput(this)"><span class="material-symbols-outlined" style="font-size:14px">close</span></button></div></div>`;
    c.appendChild(d);
}
function removeFileInput(b){b.closest('.file-input-group').remove()}
function previewFile(i){
    const p=i.nextElementSibling,n=p.querySelector('.file-name');
    if(i.files&&i.files[0]){const f=i.files[0];n.textContent=f.name;const ic=p.querySelector('.material-symbols-outlined');ic.textContent=f.type==='application/pdf'?'picture_as_pdf':f.type.startsWith('image/')?'image':'description';p.style.display='block'}else{p.style.display='none'}
}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('input[type="file"]').forEach(i=>{if(i.files&&i.files[0])previewFile(i)})});
</script>
@endsection

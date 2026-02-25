@extends('layouts.app')

@section('title', 'Enter Lab Result')

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
.lab-card-header.bg-accent{background:linear-gradient(135deg,var(--lab-dk),var(--lab));color:#fff}
.lab-card-header.bg-accent .material-symbols-outlined{color:#fff}
.lab-card-header.bg-info-custom{background:linear-gradient(135deg,#0369a1,#0ea5e9);color:#fff}
.lab-card-header.bg-info-custom .material-symbols-outlined{color:#fff}
.lab-card-body{padding:16px 20px}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
.info-row:last-child{border-bottom:none}
.info-label{color:#64748b;font-weight:500}
.info-value{color:#1e293b;font-weight:600;text-align:right}
.lab-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.lab-badge-teal{background:var(--lab-lt);color:var(--lab-dk)}
.lab-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-btn-primary{background:var(--lab);color:#fff}.lab-btn-primary:hover{background:var(--lab-dk);color:#fff}
.lab-btn-success{background:#10b981;color:#fff}.lab-btn-success:hover{background:#059669;color:#fff}
.lab-btn-outline{background:#fff;color:var(--lab);border:1.5px solid var(--lab)}.lab-btn-outline:hover{background:var(--lab-lt);color:var(--lab)}
.lab-btn-secondary{background:#f1f5f9;color:#475569;border:1.5px solid var(--lb)}.lab-btn-secondary:hover{background:#e2e8f0}
.form-control,.form-select{border:1.5px solid var(--lb);border-radius:10px;font-size:13px;transition:border-color .15s}
.form-control:focus,.form-select:focus{border-color:var(--lab);box-shadow:0 0 0 3px rgba(1,102,52,.1)}
.form-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:4px}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Enter Lab Result</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratory.dashboard') }}">Laboratory</a></li>
            <li class="breadcrumb-item"><a href="{{ route('lab-orders.index') }}">Orders</a></li>
            <li class="breadcrumb-item active">Enter Result</li>
        </ol>
    </nav>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="lab-card">
            <div class="lab-card-header bg-accent"><span class="material-symbols-outlined">assignment</span> Order Information</div>
            <div class="lab-card-body">
                <div class="info-row"><span class="info-label">Patient</span><span class="info-value">{{ $order->encounter?->patient_name ?? 'Unknown' }}</span></div>
                <div class="info-row"><span class="info-label">BOSCHMA ID</span><span class="info-value"><span class="lab-badge lab-badge-teal">{{ $order->encounter?->patient_boschma_no ?? 'N/A' }}</span></span></div>
                <div class="info-row"><span class="info-label">Test</span><span class="info-value">{{ $order->laboratoryTest?->name ?? $order->test_name ?? 'Unknown' }}</span></div>
                <div class="info-row"><span class="info-label">Ordered By</span><span class="info-value">{{ $order->orderedBy?->name ?? 'Unknown' }}</span></div>
                <div class="info-row"><span class="info-label">Order Date</span><span class="info-value">{{ $order->created_at->format('d M Y H:i') }}</span></div>
                @if($order->clinical_indication)
                <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9">
                    <div class="info-label" style="font-size:11px;margin-bottom:4px">Clinical Indication</div>
                    <div style="font-size:13px;color:#1e293b;font-weight:600">{{ $order->clinical_indication }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($order->laboratoryTest?->reference_range)
        <div class="lab-card">
            <div class="lab-card-header bg-info-custom"><span class="material-symbols-outlined">info</span> Reference Range</div>
            <div class="lab-card-body">
                <p class="mb-0" style="font-size:13px;color:#1e293b">{{ $order->laboratoryTest->reference_range }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <form action="{{ route('lab-results.store', $order->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="lab-card">
                <div class="lab-card-header"><span class="material-symbols-outlined">edit_note</span> Test Result</div>
                <div class="lab-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Result Value <span class="text-danger">*</span></label>
                            <textarea name="result_value" class="form-control @error('result_value') is-invalid @enderror" rows="4" required placeholder="Enter the test result value...">{{ old('result_value') }}</textarea>
                            @error('result_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $order->laboratoryTest?->unit ?? '') }}" placeholder="e.g., mg/dL, mmol/L">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Result Status <span class="text-danger">*</span></label>
                            <select name="result_status" class="form-select @error('result_status') is-invalid @enderror" required>
                                <option value="normal" {{ old('result_status') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ old('result_status') == 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="critical" {{ old('result_status') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('result_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Interpretation / Comments</label>
                            <textarea name="interpretation" class="form-control" rows="3" placeholder="Lab technician's comments or interpretation...">{{ old('interpretation') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Attach Result Files <span class="text-muted fw-normal">(optional)</span></label>
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
                            <div class="text-muted" style="font-size:11px;margin-top:6px">Accepted formats: PDF, JPG, PNG. Max size: 5MB per file</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="submit" name="action" value="save" class="lab-btn lab-btn-primary">
                    <span class="material-symbols-outlined" style="font-size:16px">save</span> Save Result
                </button>
                <button type="submit" name="action" value="save_and_send" class="lab-btn lab-btn-success">
                    <span class="material-symbols-outlined" style="font-size:16px">send</span> Save & Send to Doctor
                </button>
                <a href="{{ route('lab-orders.index') }}" class="lab-btn lab-btn-secondary">Cancel</a>
            </div>
        </form>
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

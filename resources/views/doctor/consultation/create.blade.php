@extends('layouts.app')
@section('title', 'Patient Consultation')
@section('content')
@php
    $vitalSign = $encounter->vitalSigns->first();
    $priority = $vitalSign?->overall_priority ?? 'Green';
    // Load existing consultation data if any
    $consultation = $encounter->consultations->first();
    $savedData = [];
    if ($consultation) {
        $savedData = [
            'presenting_complaints' => $consultation->presenting_complaints,
            'physical_examination' => $consultation->physical_examination,
            'provisional_diagnosis' => $consultation->diagnoses->where('diagnosis_type', 'Provisional')->pluck('icd_code_id')->toArray(),
            'confirmed_diagnosis' => $consultation->diagnoses->where('diagnosis_type', 'Confirmed')->pluck('icd_code_id')->toArray(),
            'procedures' => $consultation->procedures->pluck('procedure_name')->toArray(),
        ];
    }
    $beneficiary = $encounter->patient->enrollee;
    $age = $encounter->patient->enrollee_dob ? \Carbon\Carbon::parse($encounter->patient->enrollee_dob)->age : null;
@endphp

<style>
/* Consultation Wizard */
.consult-header { background: linear-gradient(135deg, #016634 0%, #0d8a72 100%); color:#fff; border-radius:12px 12px 0 0; padding:20px 24px 0; margin: -24px -24px 0; }
.consult-header .patient-meta { font-size:13px; opacity:.85; }
.step-bar { display:flex; align-items:center; padding: 18px 0 0; overflow-x:auto; gap:0; }
.step-item { display:flex; flex-direction:column; align-items:center; position:relative; flex:1; min-width:60px; }
.step-circle { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; border:2px solid rgba(255,255,255,.4); color:rgba(255,255,255,.6); background:transparent; cursor:pointer; transition:all .2s; flex-shrink:0; }
.step-circle.done { background:#fff; border-color:#fff; color:#016634; }
.step-circle.active { background:#fff; border-color:#fff; color:#016634; box-shadow:0 0 0 4px rgba(255,255,255,.25); }
.step-label { font-size:10px; color:rgba(255,255,255,.7); margin-top:6px; text-align:center; padding-bottom:14px; white-space:nowrap; }
.step-label.active { color:#fff; font-weight:600; }
.step-line { flex:1; height:2px; background:rgba(255,255,255,.25); margin-top:-24px; align-self:flex-start; margin-top:19px; }
.step-line.done { background:#fff; }

/* Tab Panes */
.step-pane { display:none; } .step-pane.active { display:block; }

/* Cards */
.consult-card { background:#fff; border:1px solid #e8f0ee; border-radius:12px; margin-bottom:16px; overflow:hidden; }
.consult-card-header { background:#f0f9f6; border-bottom:1px solid #e0f0eb; padding:14px 18px; font-weight:600; color:#016634; display:flex; align-items:center; gap:8px; }

/* ICD chips */
.icd-chip { display:inline-flex; align-items:center; background:#e6f5ed; color:#016634; border-radius:20px; padding:4px 12px; font-size:13px; gap:6px; margin:3px; }
.icd-chip .remove { cursor:pointer; font-weight:700; opacity:.6; }
.icd-chip .remove:hover { opacity:1; }

/* Drug table */
.drug-row { display:grid; grid-template-columns:2fr 80px 80px 1fr 40px; gap:8px; align-items:center; padding:10px 14px; border-bottom:1px solid #f0f0f0; }
.drug-row:last-child { border-bottom:none; }
.drug-row input, .drug-row select { border:1px solid #dee2e6; border-radius:6px; padding:4px 8px; font-size:13px; width:100%; }

/* Bottom nav */
.consult-footer { display:flex; justify-content:space-between; align-items:center; padding-top:20px; margin-top:20px; border-top:1px solid #e8f0ee; }
.btn-step-prev { border:2px solid #016634; color:#016634; background:#fff; border-radius:10px; padding:10px 28px; font-weight:600; }
.btn-step-next { background:#016634; color:#fff; border:none; border-radius:10px; padding:10px 32px; font-weight:600; }
.btn-step-prev:hover { background:#f0f9f6; }
.btn-step-next:hover { background:#01552b; }

/* Investigation items */
.inv-item { border:1px solid #e0e0e0; border-radius:10px; padding:14px 16px; margin-bottom:10px; cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:12px; }
.inv-item:hover { border-color:#016634; }
.inv-item.selected { border-color:#016634; background:#f0f9f6; }
.inv-item.secondary.selected { border-color:#e67e22; background:#fef9f0; }
.inv-item.tertiary.selected { border-color:#5c6bc0; background:#f5f5fe; }
.inv-check { width:20px; height:20px; border:2px solid #ccc; border-radius:4px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.inv-item.selected .inv-check { background:#016634; border-color:#016634; color:#fff; }
.inv-item.secondary.selected .inv-check { background:#e67e22; border-color:#e67e22; color:#fff; }
.inv-item.tertiary.selected .inv-check { background:#5c6bc0; border-color:#5c6bc0; color:#fff; }

/* Preview */
.preview-section { border:1px solid #e0f0eb; border-radius:12px; padding:20px; margin-bottom:16px; }
.preview-section h6 { color:#016634; font-weight:700; margin-bottom:12px; }
</style>

<!-- Patient Header -->
<div class="consult-header">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('doctor.queue') }}" class="text-white"><span class="material-symbols-outlined">arrow_back</span></a>
            <div>
                <h5 class="mb-0 text-white fw-bold">Patient Consultation</h5>
                <div class="patient-meta">{{ $encounter->patient->enrollee_name ?? 'N/A' }} | {{ $age ? $age.' yrs' : '' }}{{ $encounter->patient->enrollee_gender ? ', '.$encounter->patient->enrollee_gender : '' }}</div>
            </div>
        </div>
        <span class="badge bg-white text-dark fw-bold px-3 py-2">{{ $encounter->patient->enrollee_number ?? '' }}</span>
    </div>

    <!-- Step Indicator -->
    <div class="step-bar" id="stepBar">
        <div class="step-item">
            <div class="step-circle active" id="sc1" onclick="goStep(1)"><span class="material-symbols-outlined done-icon" style="font-size:18px;display:none">check</span><span class="num-icon">1</span></div>
            <div class="step-label active" id="sl1">Clinical Assessment</div>
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step-item">
            <div class="step-circle" id="sc2" onclick="goStep(2)"><span class="material-symbols-outlined done-icon" style="font-size:18px;display:none">check</span><span class="num-icon">2</span></div>
            <div class="step-label" id="sl2">Investigations</div>
        </div>
        <div class="step-line" id="line2"></div>
        <div class="step-item">
            <div class="step-circle" id="sc3" onclick="goStep(3)"><span class="material-symbols-outlined done-icon" style="font-size:18px;display:none">check</span><span class="num-icon">3</span></div>
            <div class="step-label" id="sl3">Confirmed Diagnosis</div>
        </div>
        <div class="step-line" id="line3"></div>
        <div class="step-item">
            <div class="step-circle" id="sc4" onclick="goStep(4)"><span class="material-symbols-outlined done-icon" style="font-size:18px;display:none">check</span><span class="num-icon">4</span></div>
            <div class="step-label" id="sl4">Treatment</div>
        </div>
        <div class="step-line" id="line4"></div>
        <div class="step-item">
            <div class="step-circle" id="sc5" onclick="goStep(5)"><span class="material-symbols-outlined done-icon" style="font-size:18px;display:none">check</span><span class="num-icon">5</span></div>
            <div class="step-label" id="sl5">Discharge & Admit</div>
        </div>
    </div>
</div>

<!-- Form -->
<form action="{{ route('doctor.consultation.store', $encounter) }}" method="POST" id="consultationForm">
@csrf
<div class="mt-4">
    @include('doctor.consultation._step1', ['savedData' => $savedData, 'icdCodes' => $icdCodes, 'vitalSign' => $vitalSign])
    @include('doctor.consultation._step2', ['savedData' => $savedData, 'serviceCategories' => $serviceCategories])
    @include('doctor.consultation._step3', ['savedData' => $savedData, 'icdCodes' => $icdCodes])
    @include('doctor.consultation._step4', ['savedData' => $savedData, 'drugs' => $drugs])
    @include('doctor.consultation._step5', ['savedData' => $savedData, 'facilities' => $facilities, 'wards' => $wards])
</div>
</form>

@endsection

@push('scripts')
<script>
// ── Global Toast Notification ────────────────────────────────────
function _showToast(type, msg) {
    let container = document.getElementById('_toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = '_toastContainer';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;min-width:300px;max-width:420px';
        document.body.appendChild(container);
    }
    const colors = { success:'#016634', danger:'#dc3545', warning:'#e67e22', info:'#0dcaf0' };
    const icons  = { success:'check_circle', danger:'error', warning:'warning', info:'info' };
    const toast  = document.createElement('div');
    toast.style.cssText = `background:#fff;border-left:4px solid ${colors[type]||'#016634'};border-radius:8px;padding:12px 16px;box-shadow:0 4px 20px rgba(0,0,0,.15);display:flex;align-items:center;gap:10px;animation:slideInToast .3s ease`;
    toast.innerHTML = `<span class="material-symbols-outlined" style="color:${colors[type]||'#016634'};font-size:20px">${icons[type]||'check_circle'}</span><span style="font-size:14px;flex:1">${String(msg).replace(/</g,'&lt;')}</span><span onclick="this.parentElement.remove()" style="cursor:pointer;color:#999;font-size:18px;line-height:1">&times;</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity='0'; toast.style.transition='opacity .4s'; setTimeout(()=>toast.remove(),400); }, 5000);
}
if (!document.getElementById('_toastStyle')) {
    const s = document.createElement('style');
    s.id = '_toastStyle';
    s.textContent = '@keyframes slideInToast{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}';
    document.head.appendChild(s);
}

// ── Step Navigation ─────────────────────────────────────────────
let currentStep = 1;
const totalSteps = 5;

function goStep(n) {
    if (n < 1 || n > totalSteps) return;
    document.querySelectorAll('.step-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    for (let i = 1; i <= totalSteps; i++) {
        const sc = document.getElementById('sc' + i);
        const sl = document.getElementById('sl' + i);
        const ni = sc.querySelector('.num-icon');
        const di = sc.querySelector('.done-icon');
        sc.classList.remove('active','done'); sl.classList.remove('active');
        if (i < n)      { sc.classList.add('done');   ni.style.display='none'; di.style.display=''; }
        else if (i===n) { sc.classList.add('active'); ni.style.display='';    di.style.display='none'; sl.classList.add('active'); }
        else            {                              ni.style.display='';    di.style.display='none'; }
        if (i < totalSteps) document.getElementById('line'+i).classList.toggle('done', i < n);
    }
    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function nextStep() { goStep(currentStep + 1); }
function prevStep() { goStep(currentStep - 1); }

// ── ICD helpers (shared by step1 provisional + step3 confirmed) ─
function _escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
const _icdData = @json($icdCodes->map(fn($c) => ['id'=>(string)$c->id,'code'=>$c->code,'desc'=>$c->description])->values());

function _renderIcdList(containerId, selectedSet, onChangeFn, query) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const q = (query || '').toLowerCase().trim();
    const filtered = q
        ? _icdData.filter(c => (c.code + ' ' + c.desc).toLowerCase().includes(q))
        : _icdData;
    const shown = filtered.slice(0, 80);
    if (!shown.length) {
        container.innerHTML = '<p class="text-center text-muted small py-3">No matching ICD codes found</p>';
        return;
    }
    container.innerHTML = shown.map(c => {
        const sel = selectedSet.has(c.id);
        return `<label class="conf-dx-item d-flex align-items-start gap-3 px-2 py-2 rounded-2 mb-1"
            style="cursor:pointer;border:1px solid ${sel?'#016634':'#eee'};background:${sel?'#f0f9f6':'#fff'}">
            <input type="checkbox" value="${_escHtml(c.id)}" ${sel?'checked':''} onchange="${onChangeFn}(this)"
                style="width:18px;height:18px;flex-shrink:0;margin-top:2px;accent-color:#016634">
            <span>
                <span class="badge me-1" style="background:#e8f0ee;color:#016634;font-size:11px">${_escHtml(c.code)}</span>
                <span style="font-size:13px">${_escHtml(c.desc)}</span>
            </span>
        </label>`;
    }).join('') + (filtered.length > 80
        ? `<p class="text-center text-muted py-2" style="font-size:12px">Showing 80 of ${filtered.length} — type more to narrow down</p>`
        : '');
}

// ── Drug Table ──────────────────────────────────────────────────
const _drugsData = @json($drugs->map(fn($d) => ['id'=>(string)$d->id, 'label'=>$d->name.($d->strength?' - '.$d->strength:'')]));
let drugRows = @json(old('drug_items', $savedData['drug_items'] ?? []));

// Read current DOM values into drugRows before any mutation
function syncDrugRows() {
    const tbody = document.getElementById('drugTbody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr[data-idx]');
    rows.forEach(tr => {
        const i = parseInt(tr.dataset.idx);
        if (drugRows[i] === undefined) return;
        const nameInput = tr.querySelector('.drug-name-input');
        const drugIdInput = tr.querySelector('.drug-id-input');
        drugRows[i].drug_id      = drugIdInput?.value || '';
        drugRows[i].drug_name    = nameInput?.value || '';
        drugRows[i].dosage       = tr.querySelector('.drug-dosage')?.value || '';
        drugRows[i].frequency    = tr.querySelector('.drug-freq')?.value || '3';
        drugRows[i].duration     = tr.querySelector('.drug-dur')?.value || '';
        drugRows[i].instructions = tr.querySelector('.drug-instr')?.value || '';
    });
}

function makeDrugRow(r, i) {
    const matchedDrug = _drugsData.find(d => d.id === String(r.drug_id));
    return `<tr data-idx="${i}" style="border-bottom:1px solid #f0f0f0">
        <td class="ps-3" style="min-width:180px;padding:8px 12px">
            <input type="text" class="form-control form-control-sm border-0 bg-light drug-name-input"
                placeholder="Search drug..." value="${matchedDrug ? matchedDrug.label : (r.drug_name||'')}"
                list="druglist" autocomplete="off"
                oninput="onDrugNameInput(this,${i})" style="min-width:160px">
            <input type="hidden" class="drug-id-input" name="drug_items[${i}][drug_id]" value="${r.drug_id||''}">
        </td>
        <td style="padding:8px 6px;min-width:90px">
            <input type="text" name="drug_items[${i}][dosage]" class="form-control form-control-sm border-0 bg-light drug-dosage"
                placeholder="e.g. 500mg" value="${r.dosage||''}">
        </td>
        <td style="padding:8px 6px;min-width:80px">
            <select name="drug_items[${i}][frequency]" class="form-select form-select-sm border-0 bg-light drug-freq">
                <option ${String(r.frequency)==='1'?'selected':''} value="1">OD</option>
                <option ${String(r.frequency)==='2'?'selected':''} value="2">BD</option>
                <option ${!r.frequency||String(r.frequency)==='3'?'selected':''} value="3">TDS</option>
                <option ${String(r.frequency)==='4'?'selected':''} value="4">QDS</option>
            </select>
        </td>
        <td style="padding:8px 6px;min-width:70px">
            <input type="number" name="drug_items[${i}][duration]" class="form-control form-control-sm border-0 bg-light drug-dur"
                placeholder="Days" min="1" value="${r.duration||''}">
        </td>
        <td style="padding:8px 6px">
            <input type="text" name="drug_items[${i}][instructions]" class="form-control form-control-sm border-0 bg-light drug-instr"
                placeholder="e.g. After food" value="${r.instructions||''}">
        </td>
        <td style="padding:8px 6px;width:40px">
            <button type="button" class="btn btn-sm text-danger p-1" onclick="removeDrug(${i})">
                <span class="material-symbols-outlined" style="font-size:18px">delete</span>
            </button>
        </td>
    </tr>`;
}

function onDrugNameInput(input, i) {
    const val = input.value.trim().toLowerCase();
    const matched = _drugsData.find(d => d.label.toLowerCase() === val);
    const hiddenId = input.closest('tr').querySelector('.drug-id-input');
    hiddenId.value = matched ? matched.id : '';
}

function renderDrugs() {
    const tbody = document.getElementById('drugTbody');
    if (!tbody) return;
    if (!drugRows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4 small">No medications added. Click "+ Add Drug" to start.</td></tr>';
        return;
    }
    tbody.innerHTML = drugRows.map((r, i) => makeDrugRow(r, i)).join('');
}

function addDrug() {
    syncDrugRows();
    drugRows.push({ drug_id:'', drug_name:'', dosage:'', frequency:'3', duration:'', instructions:'' });
    renderDrugs();
}
function removeDrug(i) {
    syncDrugRows();
    drugRows.splice(i, 1);
    renderDrugs();
}

// ── Init ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    goStep(1);
    renderDrugs();
});
</script>
@endpush

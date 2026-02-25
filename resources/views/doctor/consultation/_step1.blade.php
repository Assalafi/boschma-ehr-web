
    @if($vitalSign)
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">monitor_heart</span>
            Vital Signs (Triage)
        </div>
        <div class="p-3">
            <div class="row g-2 text-center">
                @foreach([['Temp','temperature','°C'],['BP','blood_pressure_systolic','/'.($vitalSign->blood_pressure_diastolic??'?').' mmHg'],['Pulse','pulse_rate','bpm'],['SpO2','spo2','%'],['RR','respiration_rate','/min'],['Weight','weight','kg']] as [$lbl,$fld,$unit])
                <div class="col-4 col-md-2">
                    <div class="bg-light rounded-3 p-2">
                        <div class="text-muted" style="font-size:11px">{{ $lbl }}</div>
                        <div class="fw-bold" style="color:#016634">{{ $vitalSign->$fld ?? '-' }}</div>
                        <div style="font-size:10px;color:#999">{{ $unit }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
<div class="step-pane" id="step1">
    <h4 class="fw-bold mb-1" style="color:#016634">Clinical Assessment</h4>
    <p class="text-muted small mb-4">Record patient history, examination findings, and provisional diagnosis</p>

    <!-- History -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">history_edu</span>
            History Taking / Presenting Complaints
        </div>
        <div class="p-3">
            <textarea name="presenting_complaints" id="presenting_complaints" class="form-control border-0 bg-light rounded-3" rows="4"
                placeholder="Describe the patient's presenting complaints, onset, duration, severity..."
                style="resize:none">{{ old('presenting_complaints', $savedData['presenting_complaints'] ?? '') }}</textarea>
        </div>
    </div>

    <!-- Physical Examination -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">stethoscope</span>
            Physical Examination
        </div>
        <div class="p-3">
            <textarea name="physical_examination" id="physical_examination" class="form-control border-0 bg-light rounded-3" rows="5"
                placeholder="Document all examination findings here...">{{ old('physical_examination', $savedData['physical_examination'] ?? '') }}</textarea>
        </div>
    </div>

    <!-- Provisional Diagnosis -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">diagnosis</span>
            Provisional Diagnosis (<span id="provDxNum">0</span>)
            <span class="badge ms-auto" style="background:#e8f0ee;color:#016634">Optional</span>
        </div>
        <div class="p-3">
            <!-- Selected chips (visual) -->
            <div id="provChips" class="mb-2"></div>
            <p class="text-muted small mb-3" id="noProvDxMsg">No provisional diagnosis selected yet.</p>
            <!-- Hidden inputs for form submission -->
            <div id="provDxInputs"></div>

            <!-- Search -->
            <div class="input-group mb-3">
                <span class="input-group-text border-0 bg-light"><span class="material-symbols-outlined" style="font-size:18px;color:#016634">search</span></span>
                <input type="text" id="provDxSearch" class="form-control border-0 bg-light" placeholder="Search ICD codes or descriptions...">
            </div>
            <div style="max-height:360px;overflow-y:auto" id="provDxList">
                <p class="text-center text-muted small py-4">Loading codes…</p>
            </div>
        </div>
    </div>

    <div class="consult-footer">
        <a href="{{ route('doctor.queue') }}" class="btn-step-prev btn">Back to Queue</a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-3" id="step1SaveBtn" onclick="updateClinicalAssessment()">
                <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">update</span> Update Assessment
            </button>
            <button type="button" class="btn-step-next btn" onclick="nextStep()">Next</button>
        </div>
    </div>
</div>

<script>
const _step1SaveUrl = '{{ route("doctor.consultation.update-clinical-assessment", $encounter) }}';
const _step1Csrf   = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

async function updateClinicalAssessment() {
    const btn = document.getElementById('step1SaveBtn');
    const complaints = document.getElementById('presenting_complaints')?.value?.trim();
    if (!complaints) {
        _showToast('warning', 'Please enter presenting complaints before saving.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const body = new FormData();
    body.append('presenting_complaints', complaints);
    body.append('physical_examination', document.getElementById('physical_examination')?.value || '');
    _provSelected.forEach(id => body.append('provisional_diagnosis[]', id));

    try {
        const res  = await fetch(_step1SaveUrl, {
            method: 'POST',
            body,
            headers: { 'X-CSRF-TOKEN': _step1Csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        _showToast('success', data.message || 'Clinical assessment updated.');
    } catch (e) {
        _showToast('danger', e.message || 'Failed to save assessment.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">save</span> Save';
    }
}
</script>

<script>
// Pre-populate from saved/old data
const _provSelected = new Set(@json(array_values(array_map('strval', old('provisional_diagnosis', $savedData['provisional_diagnosis'] ?? [])))));

function _provRender(query) {
    _renderIcdList('provDxList', _provSelected, 'onProvDxChange', query);
}

function _provRebuildInputs() {
    const c = document.getElementById('provDxInputs');
    if (!c) return;
    c.innerHTML = '';
    _provSelected.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'provisional_diagnosis[]'; inp.value = id;
        c.appendChild(inp);
    });
}

function _provRebuildChips() {
    const c = document.getElementById('provChips');
    if (!c) return;
    c.innerHTML = '';
    _provSelected.forEach(id => {
        const code = _icdData.find(x => x.id === id);
        if (!code) return;
        const chip = document.createElement('span');
        chip.className = 'icd-chip'; chip.dataset.id = id;
        chip.innerHTML = `${_escHtml(code.code)} – ${_escHtml(code.desc.substring(0,45))}${code.desc.length>45?'…':''} <span class="remove" onclick="uncheckProvIcd('${id}')">✕</span>`;
        c.appendChild(chip);
    });
}

function onProvDxChange(cb) {
    const id = cb.value;
    if (cb.checked) { _provSelected.add(id); } else { _provSelected.delete(id); }
    // Update row highlight in the rendered list
    cb.closest('.conf-dx-item').style.border     = cb.checked ? '1px solid #016634' : '1px solid #eee';
    cb.closest('.conf-dx-item').style.background = cb.checked ? '#f0f9f6' : '#fff';
    _provRebuildChips();
    _provRebuildInputs();
    updateProvDxCount();
}

function uncheckProvIcd(id) {
    _provSelected.delete(id);
    _provRebuildChips();
    _provRebuildInputs();
    updateProvDxCount();
    // Re-render list so the unchecked item reflects new state
    _provRender(document.getElementById('provDxSearch')?.value || '');
}

function updateProvDxCount() {
    const n = _provSelected.size;
    const el = document.getElementById('provDxNum');
    if (el) el.textContent = n;
    const msg = document.getElementById('noProvDxMsg');
    if (msg) msg.style.display = n > 0 ? 'none' : '';
}

document.getElementById('provDxSearch')?.addEventListener('input', function() {
    _provRender(this.value);
});

document.addEventListener('DOMContentLoaded', function() {
    _provRender('');
    _provRebuildChips();
    _provRebuildInputs();
    updateProvDxCount();
});
</script>


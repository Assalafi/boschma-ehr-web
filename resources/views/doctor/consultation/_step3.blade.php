<div class="step-pane" id="step3">
    <h4 class="fw-bold mb-1" style="color:#016634">Confirmed Diagnosis</h4>
    <p class="text-muted small mb-4">Select confirmed diagnosis from ICD-11 after investigation results</p>

    <!-- Selected chips summary -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">check_circle</span>
            Selected Diagnoses (<span id="confDxNum">0</span>)
        </div>
        <div class="p-3">
            <div id="confChips" class="mb-1"></div>
            <div id="confDxInputs"></div>
            <p class="text-muted small mb-0" id="noDxMsg">No confirmed diagnosis selected yet.</p>
        </div>
    </div>

    <!-- Search + Checkbox list -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">search</span>
            Search &amp; Select Diagnoses (ICD-11)
        </div>
        <div class="p-3">
            <div class="input-group mb-3">
                <span class="input-group-text border-0 bg-light"><span class="material-symbols-outlined" style="font-size:18px;color:#016634">search</span></span>
                <input type="text" id="confDxSearch" class="form-control border-0 bg-light" placeholder="Search ICD codes or descriptions...">
            </div>
            <div style="max-height:420px;overflow-y:auto" id="confDxList">
                <p class="text-center text-muted small py-4">Loading codes…</p>
            </div>
        </div>
    </div>

    <div class="consult-footer">
        <button type="button" class="btn-step-prev btn" onclick="prevStep()">Previous</button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-3" id="step3SaveBtn" onclick="updateConfirmedDiagnosis()">
                <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">update</span> Update Diagnosis
            </button>
            <button type="button" class="btn-step-next btn" onclick="nextStep()">Next</button>
        </div>
    </div>
</div>

<script>
const _step3SaveUrl = '{{ route("doctor.consultation.update-confirmed-diagnosis", $encounter) }}';
const _step3Csrf   = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _confSelected = new Set(@json(array_values(array_map('strval', old('confirmed_diagnosis', $savedData['confirmed_diagnosis'] ?? [])))));

function _confRender(query) {
    _renderIcdList('confDxList', _confSelected, 'onConfDxChange', query);
}

function _confRebuildInputs() {
    const c = document.getElementById('confDxInputs');
    if (!c) return;
    c.innerHTML = '';
    _confSelected.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'confirmed_diagnosis[]'; inp.value = id;
        c.appendChild(inp);
    });
}

function _confRebuildChips() {
    const c = document.getElementById('confChips');
    if (!c) return;
    c.innerHTML = '';
    _confSelected.forEach(id => {
        const code = _icdData.find(x => x.id === id);
        if (!code) return;
        const chip = document.createElement('span');
        chip.className = 'icd-chip'; chip.dataset.id = id;
        chip.innerHTML = `${_escHtml(code.code)} – ${_escHtml(code.desc.substring(0,45))}${code.desc.length>45?'…':''} <span class="remove" onclick="uncheckConfIcd('${id}')">✕</span>`;
        c.appendChild(chip);
    });
}

function onConfDxChange(cb) {
    const id = cb.value;
    if (cb.checked) { _confSelected.add(id); } else { _confSelected.delete(id); }
    cb.closest('.conf-dx-item').style.border     = cb.checked ? '1px solid #016634' : '1px solid #eee';
    cb.closest('.conf-dx-item').style.background = cb.checked ? '#f0f9f6' : '#fff';
    _confRebuildChips();
    _confRebuildInputs();
    updateConfDxCount();
}

function uncheckConfIcd(id) {
    _confSelected.delete(id);
    _confRebuildChips();
    _confRebuildInputs();
    updateConfDxCount();
    _confRender(document.getElementById('confDxSearch')?.value || '');
}

function updateConfDxCount() {
    const n = _confSelected.size;
    document.getElementById('confDxNum').textContent = n;
    const msg = document.getElementById('noDxMsg');
    if (msg) msg.style.display = n > 0 ? 'none' : '';
}

document.getElementById('confDxSearch')?.addEventListener('input', function() {
    _confRender(this.value);
});

async function updateConfirmedDiagnosis() {
    const btn = document.getElementById('step3SaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const body = new FormData();
    _confSelected.forEach(id => body.append('confirmed_diagnosis[]', id));

    try {
        const res  = await fetch(_step3SaveUrl, {
            method: 'POST',
            body,
            headers: { 'X-CSRF-TOKEN': _step3Csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        _showToast('success', data.message || 'Confirmed diagnosis saved.');
        saveDraft(); // also sync session draft
    } catch (e) {
        _showToast('danger', e.message || 'Failed to save confirmed diagnosis.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">save</span> Save';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    _confRender('');
    _confRebuildChips();
    _confRebuildInputs();
    updateConfDxCount();
});
</script>

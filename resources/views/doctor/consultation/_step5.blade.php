<div class="step-pane" id="step5">
    <h4 class="fw-bold mb-1" style="color:#016634">Discharge & Admit</h4>
    <p class="text-muted small mb-4">Complete the encounter by selecting a patient outcome</p>

    <!-- ═══ OUTCOME CARDS ═══ -->
    <div class="consult-card mb-4">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">check_circle</span>
            Patient Outcome
        </div>
        <div class="p-3">
            <div class="row g-3 mb-3" id="outcomeCards">
                @foreach([
                    ['Treated','check_circle','Patient treated & discharged','#059669','#dcfce7'],
                    ['Admit','local_hospital','Admit patient to facility','#2563eb','#dbeafe'],
                    ['Follow-up','event','Schedule follow-up visit','#d97706','#fef3c7'],
                    ['Refer','arrow_forward','Refer to another facility','#e67e22','#fef3e8'],
                ] as [$val,$icon,$desc,$color,$bg])
                <div class="col-md-3">
                    <div class="outcome-card border rounded-3 p-3 text-center h-100" data-outcome="{{ $val }}" onclick="_selectOutcome('{{ $val }}')" style="cursor:pointer;transition:all .2s;border-color:#e2e8f0!important">
                        <span class="material-symbols-outlined d-block mb-2" style="font-size:36px;color:{{ $color }}">{{ $icon }}</span>
                        <div class="fw-bold" style="font-size:15px">{{ $val }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $desc }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <input type="hidden" id="selectedOutcome" value="">

            <!-- ── TREATED: Discharge Note ── -->
            <div id="dischargeNoteFields" style="display:none" class="mb-3">
                <label class="form-label small fw-medium text-muted">Discharge Note</label>
                <textarea id="dischargeNote" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Final clinical notes, instructions to patient..."></textarea>
            </div>

            <!-- ── FOLLOW-UP: Date picker ── -->
            <div id="followUpFields" style="display:none" class="mb-3">
                <label class="form-label small fw-medium text-muted">Follow-up Date <span class="text-danger">*</span></label>
                <input type="date" id="followUpDate" class="form-control border-0 bg-light rounded-3" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
            </div>

            <!-- ── ADMIT: Ward → Room → Bed allocation ── -->
            <div id="admitFields" style="display:none">
                <div class="alert py-2 px-3 mb-3 d-flex align-items-center gap-2" style="background:#dbeafe;border:1px solid #93c5fd;border-radius:10px;font-size:13px">
                    <span class="material-symbols-outlined" style="font-size:16px;color:#2563eb">info</span>
                    <span class="text-dark">Select a ward, then a room and bed will be loaded automatically.</span>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Admission Type -->
                    <div class="col-md-4">
                        <label class="form-label small fw-medium text-muted">Admission Type <span class="text-danger">*</span></label>
                        <select id="admissionType" class="form-select border-0 bg-light rounded-3">
                            <option value="">Select type...</option>
                            <option value="emergency">Emergency</option>
                            <option value="elective">Elective</option>
                            <option value="observation">Observation</option>
                        </select>
                    </div>
                    <!-- Ward -->
                    <div class="col-md-4">
                        <label class="form-label small fw-medium text-muted">Ward <span class="text-danger">*</span></label>
                        <select id="admitWard" class="form-select border-0 bg-light rounded-3" onchange="_loadRooms(this.value)">
                            <option value="">Select ward...</option>
                            @foreach($wards as $ward)
                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Room -->
                    <div class="col-md-4">
                        <label class="form-label small fw-medium text-muted">Room</label>
                        <select id="admitRoom" class="form-select border-0 bg-light rounded-3" onchange="_loadBeds(this.value)" disabled>
                            <option value="">Select ward first...</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Bed -->
                    <div class="col-md-4">
                        <label class="form-label small fw-medium text-muted">Bed</label>
                        <select id="admitBed" class="form-select border-0 bg-light rounded-3" disabled>
                            <option value="">Select room first...</option>
                        </select>
                    </div>
                    <!-- Condition on Admission -->
                    <div class="col-md-8">
                        <label class="form-label small fw-medium text-muted">Condition on Admission</label>
                        <input type="text" id="conditionOnAdmission" class="form-control border-0 bg-light rounded-3" placeholder="e.g. Stable, Critical, Conscious and alert...">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-medium text-muted">Admission Notes</label>
                    <textarea id="admissionNotes" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Additional notes for admission..."></textarea>
                </div>

                <!-- Bed availability indicator -->
                <div id="bedAvailability" style="display:none" class="mb-3"></div>
            </div>

            <!-- ── REFER: Facility search ── -->
            <div id="referFields" style="display:none">
                <div class="mb-3">
                    <label class="form-label small fw-medium text-muted">Select Receiving Facility <span class="text-danger">*</span></label>
                    <input type="text" id="facilitySearch" class="form-control border-0 bg-light rounded-3" placeholder="Search facility by name or location..." autocomplete="off">
                    <input type="hidden" id="selectedFacilityId" value="">
                    <div id="facilitySelected" class="mt-2" style="display:none"></div>
                </div>
                <div id="facilityList" style="max-height:220px;overflow-y:auto;border:1px solid #e8f0ee;border-radius:10px;display:none"></div>
                <div class="mb-3 mt-3">
                    <label class="form-label small fw-medium text-muted">Reason for Referral <span class="text-danger">*</span></label>
                    <textarea id="referralReason" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Why is this patient being referred..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-medium text-muted">Clinical Findings</label>
                    <textarea id="referralFindings" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Summary of clinical findings..."></textarea>
                </div>
            </div>

            <button type="button" class="btn px-4 py-2 rounded-3 fw-semibold" id="dischargeBtn" onclick="_submitDischarge()" disabled style="background:#016634;color:#fff;border:none;opacity:.5">
                <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">task_alt</span>
                <span id="dischargeBtnText">Select an outcome above</span>
            </button>
        </div>
    </div>

    <div class="consult-footer">
        <button type="button" class="btn-step-prev btn" onclick="prevStep()">Previous</button>
        <button type="submit" class="btn-step-next btn px-4">
            <span class="material-symbols-outlined align-middle me-1" style="font-size:18px">check_circle</span>
            Submit Consultation
        </button>
    </div>
</div>

@php
    $facilitiesJson = $facilities->map(function($f) {
        return ['id'=>$f->id,'name'=>$f->name,'type'=>$f->type ?? '','location'=>trim(($f->city ?? '').', '.($f->state ?? ''), ', ')];
    });
@endphp

<script>
const _referPatientUrl  = '{{ route("doctor.consultation.refer-patient", $encounter) }}';
const _dischargeUrl     = '{{ route("doctor.consultation.discharge", $encounter) }}';
const _wardRoomsUrl     = '{{ url("doctor/consultation/ward") }}';
const _roomBedsUrl      = '{{ url("doctor/consultation/room") }}';
const _step5Csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _facilitiesData   = @json($facilitiesJson);

// ── Outcome Selection ──────────────────────────────────────────────
function _selectOutcome(val) {
    document.getElementById('selectedOutcome').value = val;
    document.querySelectorAll('.outcome-card').forEach(c => {
        const isSelected = c.dataset.outcome === val;
        c.style.borderColor = isSelected ? '#016634' : '#e2e8f0';
        c.style.background  = isSelected ? '#f0f9f6' : '#fff';
        c.style.boxShadow   = isSelected ? '0 0 0 2px #016634' : 'none';
    });
    document.getElementById('dischargeNoteFields').style.display = val === 'Treated' ? '' : 'none';
    document.getElementById('followUpFields').style.display      = val === 'Follow-up' ? '' : 'none';
    document.getElementById('admitFields').style.display          = val === 'Admit' ? '' : 'none';
    document.getElementById('referFields').style.display          = val === 'Refer' ? '' : 'none';

    const btn = document.getElementById('dischargeBtn');
    btn.disabled = false;
    btn.style.opacity = '1';
    const labels = { 'Treated':'Discharge Patient', 'Admit':'Admit Patient', 'Follow-up':'Schedule Follow-up', 'Refer':'Refer Patient' };
    document.getElementById('dischargeBtnText').textContent = labels[val] || 'Confirm';
}

// ── Ward → Room → Bed Cascading ────────────────────────────────────
async function _loadRooms(wardId) {
    const roomSel = document.getElementById('admitRoom');
    const bedSel  = document.getElementById('admitBed');
    roomSel.innerHTML = '<option value="">Loading...</option>';
    roomSel.disabled = true;
    bedSel.innerHTML = '<option value="">Select room first...</option>';
    bedSel.disabled = true;
    document.getElementById('bedAvailability').style.display = 'none';

    if (!wardId) { roomSel.innerHTML = '<option value="">Select ward first...</option>'; return; }

    try {
        const res = await fetch(_wardRoomsUrl + '/' + wardId + '/rooms', {
            headers: { 'X-Requested-With':'XMLHttpRequest' }
        });
        const rooms = await res.json();
        if (!rooms.length) {
            roomSel.innerHTML = '<option value="">No rooms in this ward</option>';
            return;
        }
        roomSel.innerHTML = '<option value="">Select room...</option>' +
            rooms.map(r => `<option value="${r.id}">${r.name} (${r.available_beds} beds free)</option>`).join('');
        roomSel.disabled = false;
    } catch(e) {
        roomSel.innerHTML = '<option value="">Failed to load rooms</option>';
    }
}

async function _loadBeds(roomId) {
    const bedSel = document.getElementById('admitBed');
    const avail  = document.getElementById('bedAvailability');
    bedSel.innerHTML = '<option value="">Loading...</option>';
    bedSel.disabled = true;

    if (!roomId) { bedSel.innerHTML = '<option value="">Select room first...</option>'; avail.style.display = 'none'; return; }

    try {
        const res = await fetch(_roomBedsUrl + '/' + roomId + '/beds', {
            headers: { 'X-Requested-With':'XMLHttpRequest' }
        });
        const beds = await res.json();
        if (!beds.length) {
            bedSel.innerHTML = '<option value="">No available beds</option>';
            avail.innerHTML = '<div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background:#fef2f2;border:1px solid #fecaca;font-size:13px"><span class="material-symbols-outlined" style="font-size:16px;color:#dc2626">warning</span><span class="text-danger">No available beds in this room. Patient can still be admitted without a bed assignment.</span></div>';
            avail.style.display = '';
            return;
        }
        bedSel.innerHTML = '<option value="">Select bed...</option>' +
            beds.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
        bedSel.disabled = false;
        avail.innerHTML = '<div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background:#dcfce7;border:1px solid #86efac;font-size:13px"><span class="material-symbols-outlined" style="font-size:16px;color:#059669">check_circle</span><span style="color:#059669">' + beds.length + ' bed(s) available in this room</span></div>';
        avail.style.display = '';
    } catch(e) {
        bedSel.innerHTML = '<option value="">Failed to load beds</option>';
    }
}

// ── Submit ──────────────────────────────────────────────────────────
async function _submitDischarge() {
    const outcome = document.getElementById('selectedOutcome').value;
    if (!outcome) { _showToast('warning', 'Please select an outcome.'); return; }

    // Validate per outcome
    if (outcome === 'Follow-up' && !document.getElementById('followUpDate').value) {
        _showToast('warning', 'Please select a follow-up date.'); return;
    }
    if (outcome === 'Admit') {
        if (!document.getElementById('admitWard').value) { _showToast('warning', 'Please select a ward.'); return; }
        if (!document.getElementById('admissionType').value) { _showToast('warning', 'Please select an admission type.'); return; }
    }
    if (outcome === 'Refer') {
        if (!document.getElementById('selectedFacilityId').value) { _showToast('warning', 'Please select a receiving facility.'); return; }
        if (!document.getElementById('referralReason').value.trim()) { _showToast('warning', 'Please provide a reason for referral.'); return; }
    }

    const btn = document.getElementById('dischargeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    try {
        let url, body;
        if (outcome === 'Refer') {
            url = _referPatientUrl;
            body = {
                to_facility_id:    document.getElementById('selectedFacilityId').value,
                reason:            document.getElementById('referralReason').value.trim(),
                clinical_findings: document.getElementById('referralFindings').value.trim(),
            };
        } else {
            url = _dischargeUrl;
            body = {
                outcome: outcome,
                clinical_note:  document.getElementById('dischargeNote')?.value?.trim() || null,
                follow_up_date: document.getElementById('followUpDate')?.value || null,
            };
            // Add admission fields
            if (outcome === 'Admit') {
                body.ward_id                = document.getElementById('admitWard').value;
                body.bed_id                 = document.getElementById('admitBed').value || null;
                body.admission_type         = document.getElementById('admissionType').value;
                body.condition_on_admission = document.getElementById('conditionOnAdmission')?.value?.trim() || null;
                body.admission_notes        = document.getElementById('admissionNotes')?.value?.trim() || null;
            }
        }
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':_step5Csrf, 'X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify(body)
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        if (data.errors) throw new Error(Object.values(data.errors).flat().join(', '));
        _showToast('success', data.message || 'Done.');
        setTimeout(() => { window.location.href = data.redirect || '{{ route("doctor.queue") }}'; }, 1200);
    } catch(e) {
        _showToast('danger', e.message || 'Failed.');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">task_alt</span><span id="dischargeBtnText">Confirm</span>';
    }
}

// ── Facility Search & Referral ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('facilitySearch');
    const listBox     = document.getElementById('facilityList');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        if (q.length < 2) { listBox.style.display = 'none'; return; }
        const matches = _facilitiesData.filter(f =>
            f.name.toLowerCase().includes(q) || (f.location && f.location.toLowerCase().includes(q))
        ).slice(0, 30);

        if (!matches.length) {
            listBox.innerHTML = '<div class="p-3 text-muted text-center small">No facilities found</div>';
        } else {
            listBox.innerHTML = matches.map(f => `
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="cursor:pointer;transition:background .1s"
                     onmouseover="this.style.background='#f0f9f6'" onmouseout="this.style.background=''"
                     onclick="_pickFacility(${f.id}, '${f.name.replace(/'/g,"\\'")}', '${(f.type||'').replace(/'/g,"\\'")}', '${(f.location||'').replace(/'/g,"\\'")}')">
                    <div>
                        <div class="fw-medium" style="font-size:14px">${f.name}</div>
                        <div class="text-muted" style="font-size:11px">${[f.type, f.location].filter(Boolean).join(' · ')}</div>
                    </div>
                    <span class="material-symbols-outlined" style="font-size:16px;color:#016634">arrow_forward</span>
                </div>`).join('');
        }
        listBox.style.display = '';
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !listBox.contains(e.target)) listBox.style.display = 'none';
    });
});

function _pickFacility(id, name, type, location) {
    document.getElementById('selectedFacilityId').value = id;
    document.getElementById('facilitySearch').value = '';
    document.getElementById('facilityList').style.display = 'none';
    const badge = document.getElementById('facilitySelected');
    badge.innerHTML = `<div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background:#e6f5ed;border:1px solid #c3e6d8">
        <span class="material-symbols-outlined" style="font-size:18px;color:#016634">local_hospital</span>
        <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:14px;color:#016634">${name}</div>
            <div class="text-muted" style="font-size:11px">${[type, location].filter(Boolean).join(' · ')}</div>
        </div>
        <span class="material-symbols-outlined" style="cursor:pointer;color:#999;font-size:18px" onclick="_clearFacility()">close</span>
    </div>`;
    badge.style.display = '';
}

function _clearFacility() {
    document.getElementById('selectedFacilityId').value = '';
    document.getElementById('facilitySelected').style.display = 'none';
}
</script>

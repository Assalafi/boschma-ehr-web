<div class="step-pane" id="step4">
    <h4 class="fw-bold mb-1" style="color:#016634">Treatment</h4>
    <p class="text-muted small mb-4">Select treatment type and provide details</p>

    <!-- Treatment / Pharmacy -->
    <div class="consult-card mb-4" id="treatmentSection">
        <div class="consult-card-header d-flex justify-content-between align-items-center">
            <div>
                <span class="material-symbols-outlined" style="font-size:20px">medication</span>
                Medication &amp; Prescriptions
            </div>
        </div>
        <div class="p-1">
            @foreach([['Medication','pill','ttMed','medSection'],['Procedures','build','ttProc','procSection']] as [$label,$icon,$id,$sec])
            @php $checked = in_array($label, old('treatment_types', $savedData['treatment_types'] ?? [])); @endphp
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom" style="cursor:pointer" onclick="toggleTreatment('{{ $id }}','{{ $sec }}')">
                <div class="d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined" style="color:#016634;font-size:22px">{{ $icon }}</span>
                    <span class="fw-medium">{{ $label }}</span>
                </div>
                <div class="form-check m-0">
                    <input class="form-check-input treatment-check" type="checkbox" name="treatment_types[]" value="{{ $label }}" id="{{ $id }}" {{ $checked ? 'checked' : '' }} style="width:20px;height:20px;border-color:#016634" onclick="event.stopPropagation()">
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Medications Section -->
    <div id="medSection" style="{{ in_array('Medication', old('treatment_types', $savedData['treatment_types'] ?? [])) ? '' : 'display:none' }}">
        <div class="consult-card mb-3">
            <div class="consult-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:20px">medication</span>
                    Medications <span class="badge ms-1" style="background:#e8f0ee;color:#016634" id="drugCount">0</span>
                </div>
                <button type="button" class="btn btn-success btn-sm px-3 rounded-pill" id="sendToPharmacyBtn" onclick="sendToPharmacy()">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy
                </button>
            </div>
            <div class="p-3">
                <!-- Search bar -->
                <div class="position-relative mb-3" style="z-index:1050">
                    <div class="input-group shadow-sm" style="border-radius:10px;overflow:hidden">
                        <span class="input-group-text border-0 bg-light" style="padding-left:14px">
                            <span class="material-symbols-outlined" style="font-size:19px;color:#016634">search</span>
                        </span>
                        <input type="text" id="drugSearchInput" class="form-control border-0 bg-light"
                            placeholder="Type at least 4 letters to search by name, form, or strength…"
                            autocomplete="off">
                        <span class="input-group-text border-0 bg-light" id="drugSearchSpinner" style="display:none">
                            <span class="spinner-border spinner-border-sm" style="width:.85rem;height:.85rem;color:#016634"></span>
                        </span>
                    </div>
                    <!-- Dropdown results -->
                    <div id="drugSearchResults"
                        class="bg-white rounded-3 shadow-lg"
                        style="position:fixed;z-index:1060;max-height:420px;overflow-y:auto;display:none;border:1px solid #d0e8e0;min-width:500px;max-width:800px">
                    </div>
                </div>

                <!-- Selected drugs -->
                <div id="drugPrescriptionList"></div>
                <p id="drugEmptyMsg" class="text-center text-muted small py-3 mb-0" style="display:none">No medications added yet. Search above to add.</p>
            </div>
        </div>
    </div>

    <!-- Procedures Section -->
    <div id="procSection" style="{{ in_array('Procedures', old('treatment_types', $savedData['treatment_types'] ?? [])) ? '' : 'display:none' }}">
        <div class="consult-card mb-4">
            <div class="consult-card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="material-symbols-outlined" style="font-size:20px">build</span>
                    Procedures Performed
                </div>
                <button type="button" class="btn btn-outline-success btn-sm px-3 rounded-pill" id="saveProceduresBtn" onclick="updateProcedures()" style="color:#016634;border-color:#016634">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">update</span> Update Procedures
                </button>
            </div>
            <div class="p-1">
                @foreach(['Wound dressing','Suturing / Wound closure','Incision & Drainage (I&D)','Nebulization','IV Cannulation','IV Fluid Administration','Urinary Catheterization','Nasogastric Tube (NGT)','Oxygen Therapy','ECG','Blood Transfusion','Stabilization'] as $p)
                @php $checked = in_array($p, old('procedures', $savedData['procedures'] ?? [])); @endphp
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom" style="cursor:pointer" onclick="this.querySelector('input').click()">
                    <span style="font-size:14px">{{ $p }}</span>
                    <input class="form-check-input" type="checkbox" name="procedures[]" value="{{ $p }}" {{ $checked ? 'checked' : '' }} style="width:20px;height:20px;border-color:#016634" onclick="event.stopPropagation()">
                </div>
                @endforeach
            </div>
        </div>
    </div>


    <!-- Sent-to-Pharmacy Panel (always visible when prescriptions exist) -->
    @php
        $sentPrescriptions = $encounter->consultations->flatMap(fn($c) => $c->prescriptions)->filter(fn($rx) => $rx->status !== \App\Models\Prescription::STATUS_CANCELLED);
    @endphp
    @include('doctor.consultation._sent_to_pharmacy_panel')

    <!-- Clinical Notes -->
    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">note_alt</span>
            Clinical Notes / Treatment Plan
        </div>
        <div class="p-3">
            <textarea name="clinical_note" id="clinical_note" class="form-control border-0 bg-light rounded-3" rows="3"
                placeholder="Additional notes, treatment plan, counselling given...">{{ old('clinical_note', $savedData['clinical_note'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="consult-footer">
        <button type="button" class="btn-step-prev btn" onclick="prevStep()">Previous</button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success px-4 rounded-3" id="sendToPharmacyBtnFooter" onclick="sendToPharmacy()" style="color:#016634;border-color:#016634">
                <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy
            </button>
            <button type="button" class="btn-step-next btn" onclick="nextStep()">Next</button>
        </div>
    </div>
</div>

<script>
const _pharmacySendUrl  = '{{ route("doctor.consultation.send-to-pharmacy", $encounter) }}';
const _recallItemBase   = '{{ url("doctor/consultation/item") }}';
const _proceduresSaveUrl = '{{ route("doctor.consultation.update-procedures", $encounter) }}';
const _proceduresCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _step4Csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _drugSearchUrl    = '{{ route("doctor.consultation.drugs.search") }}';

// ── Drug Search & Prescription List ──────────────────────────────────────────

const _selectedDrugs = new Map(); // drugId → drug object + rx fields
let _drugSearchTimer = null;

const _freqLabels = {
    1: 'OD – Once daily',
    2: 'BD – Twice daily',
    3: 'TDS – Three times/day',
    4: 'QDS – Four times/day',
    6: 'Q4H – Every 4 hours',
};

function _stockBadge(status, qty) {
    const cfg = {
        out: ['danger',  'inventory_2', 'Out of Stock'],
        low: ['warning', 'inventory_2', `Low Stock (${qty})`],
        in:  ['success', 'inventory_2', `In Stock (${qty})`],
    };
    const [color, icon, label] = cfg[status] ?? ['secondary', 'inventory_2', `${qty} units`];
    return `<span class="badge bg-${color} d-inline-flex align-items-center gap-1" style="font-size:11px">
        <span class="material-symbols-outlined" style="font-size:13px">${icon}</span>${label}
    </span>`;
}

function _renderDrugResults(drugs) {
    const box = document.getElementById('drugSearchResults');
    if (!drugs.length) {
        box.innerHTML = `<div class="p-4 text-muted text-center">
            <span class="material-symbols-outlined" style="font-size:32px;color:#d0e8e0">search_off</span>
            <div class="mt-2" style="font-size:14px">No drugs found</div>
            <div class="small">Try a different search term</div>
        </div>`;
        box.style.display = '';
        return;
    }
    box.innerHTML = drugs.map(d => {
        const alreadyAdded = _selectedDrugs.has(d.id);
        const details = [d.dosage_form, d.strength, d.unit].filter(Boolean).join(' · ');
        const price   = d.unit_price > 0 ? `₦${d.unit_price.toFixed(2)}/unit` : '';
        return `<div class="d-flex align-items-start justify-content-between gap-3 px-4 py-3 border-bottom drug-result-row"
                    style="cursor:${alreadyAdded ? 'default' : 'pointer'};background:${alreadyAdded ? '#f8fffe' : '#fff'};transition:background-color 0.2s"
                    onmouseover="this.style.backgroundColor='${alreadyAdded ? '#f0f9f6' : '#f8fffe'}'"
                    onmouseout="this.style.backgroundColor='${alreadyAdded ? '#f8fffe' : '#fff'}'"
                    ${alreadyAdded ? '' : `onclick="_addDrug(${JSON.stringify(d).replace(/"/g,'&quot;')})"`}>
            <div style="min-width:0;flex:1">
                <div class="fw-semibold" style="font-size:14px;color:#0a3d35;line-height:1.3">${d.name}</div>
                <div class="text-muted" style="font-size:12px;line-height:1.4">${details}${price ? ' · <span style=\'color:#016634\'>' + price + '</span>' : ''}</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                ${_stockBadge(d.stock_status, d.stock)}
                ${alreadyAdded
                    ? `<span class="badge bg-secondary" style="font-size:11px">Added</span>`
                    : `<button type="button" class="btn btn-sm px-3 py-2 rounded-pill"
                          style="background:#e8f0ee;color:#016634;font-size:12px;border:none"
                          onclick="event.stopPropagation();_addDrug(${JSON.stringify(d).replace(/"/g,'&quot;')})">
                          <span class="material-symbols-outlined align-middle" style="font-size:14px">add</span> Add
                       </button>`
                }
            </div>
        </div>`;
    }).join('');
    box.style.display = '';
}

function _addDrug(drug) {
    if (_selectedDrugs.has(drug.id)) return;
    drug.dosage       = '';
    drug.frequency    = 3;
    drug.duration     = 5;
    drug.instructions = '';
    drug.quantity     = drug.frequency * drug.duration;
    _selectedDrugs.set(drug.id, drug);
    _renderPrescriptionList();
    // Refresh results to show "Added" badge
    const input = document.getElementById('drugSearchInput');
    if (input.value.length >= 4) _fetchDrugs(input.value);
}

function _removeDrug(drugId) {
    _selectedDrugs.delete(drugId);
    _renderPrescriptionList();
    // Refresh results
    const input = document.getElementById('drugSearchInput');
    if (input.value.length >= 4) _fetchDrugs(input.value);
}


function _onDrugFieldChange(drugId, field, value) {
    const d = _selectedDrugs.get(drugId);
    if (!d) return;
    d[field] = value;
    if (field === 'frequency' || field === 'duration') {
        d.quantity = (parseInt(d.frequency) || 1) * (parseInt(d.duration) || 1);
        const safeId = drugId.replace(/-/g, '_');
        const qtyEl = document.getElementById(`drug-qty-${safeId}`);
        if (qtyEl) qtyEl.textContent = d.quantity;
    }
}

function _renderPrescriptionList() {
    const list = document.getElementById('drugPrescriptionList');
    const countEl = document.getElementById('drugCount');
    const emptyMsg = document.getElementById('drugEmptyMsg');

    countEl.textContent = _selectedDrugs.size;

    if (!_selectedDrugs.size) {
        list.innerHTML = '';
        emptyMsg.style.display = '';
        return;
    }
    emptyMsg.style.display = 'none';

    list.innerHTML = [..._selectedDrugs.values()].map((d, i) => {
        const details = [d.dosage_form, d.strength, d.unit].filter(Boolean).join(' · ');
        const price   = d.unit_price > 0 ? `₦${d.unit_price.toFixed(2)}/unit` : '';
        const safeId  = d.id.replace(/-/g, '_');
        return `<div class="border rounded-3 mb-3 overflow-hidden" style="border-color:#d0e8e0!important">
            <!-- Drug header -->
            <div class="d-flex align-items-start justify-content-between px-3 py-2" style="background:#f0f9f6">
                <div>
                    <div class="fw-semibold" style="color:#0a3d35;font-size:14px">
                        <span class="material-symbols-outlined align-middle me-1" style="font-size:15px;color:#016634">medication</span>
                        ${d.name}
                    </div>
                    <div class="text-muted" style="font-size:11px">
                        ${details}${price ? ` · <span style='color:#016634'>${price}</span>` : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    ${_stockBadge(d.stock_status, d.stock)}
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="_removeDrug('${d.id}')" title="Remove">
                        <span class="material-symbols-outlined" style="font-size:16px">close</span>
                    </button>
                </div>
            </div>
            <!-- Prescription fields -->
            <div class="px-3 py-3 row g-2">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium text-muted mb-1">Dosage</label>
                    <input type="text" class="form-control form-control-sm border-0 bg-light rounded-2"
                        placeholder="e.g. 1 tablet"
                        value="${d.dosage}"
                        oninput="_onDrugFieldChange('${d.id}','dosage',this.value)">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium text-muted mb-1">Frequency</label>
                    <select class="form-select form-select-sm border-0 bg-light rounded-2"
                        onchange="_onDrugFieldChange('${d.id}','frequency',parseInt(this.value))">
                        ${Object.entries(_freqLabels).map(([v, l]) =>
                            `<option value="${v}" ${d.frequency == v ? 'selected' : ''}>${l}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium text-muted mb-1">Duration (days)</label>
                    <input type="number" class="form-control form-control-sm border-0 bg-light rounded-2"
                        min="1" max="365" value="${d.duration}"
                        oninput="_onDrugFieldChange('${d.id}','duration',parseInt(this.value)||1)">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium text-muted mb-1">Qty (auto)</label>
                    <div class="form-control form-control-sm border-0 rounded-2 text-center fw-bold"
                        id="drug-qty-${safeId}"
                        style="background:#e8f0ee;color:#016634">${d.quantity}</div>
                </div>
                <div class="col-12 col-md-auto flex-md-fill">
                    <label class="form-label small fw-medium text-muted mb-1">Instructions</label>
                    <input type="text" class="form-control form-control-sm border-0 bg-light rounded-2"
                        placeholder="e.g. Take after food"
                        value="${d.instructions}"
                        oninput="_onDrugFieldChange('${d.id}','instructions',this.value)">
                </div>
            </div>
        </div>`;
    }).join('');
}

async function _fetchDrugs(q) {
    const spinner = document.getElementById('drugSearchSpinner');
    const box = document.getElementById('drugSearchResults');
    spinner.style.display = '';
    
    // Show loading state
    box.innerHTML = `<div class="p-4 text-muted text-center">
        <span class="spinner-border spinner-border-sm" style="width:1.5rem;height:1.5rem;color:#016634"></span>
        <div class="mt-2" style="font-size:14px">Searching drugs...</div>
    </div>`;
    _drugDropdownPosition();
    box.style.display = '';
    
    const excludeIds = [..._selectedDrugs.keys()];
    const params = new URLSearchParams({ q });
    excludeIds.forEach(id => params.append('exclude[]', id));
    
    console.log('Fetching drugs:', { url: `${_drugSearchUrl}?${params}`, q, excludeIds });
    
    try {
        const res = await fetch(`${_drugSearchUrl}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _step4Csrf }
        });
        console.log('Search response status:', res.status);
        const data = await res.json();
        console.log('Search response data:', data);
        _renderDrugResults(data);
    } catch(e) {
        console.error('Search error:', e);
        box.innerHTML = `<div class="p-3 text-danger small">Search failed. Please try again. (${e.message})</div>`;
        box.style.display = '';
    } finally {
        spinner.style.display = 'none';
    }
}

// Wire up search input
function _drugDropdownPosition() {
    const input = document.getElementById('drugSearchInput');
    const box   = document.getElementById('drugSearchResults');
    if (!input || !box) return;
    const rect = input.getBoundingClientRect();
    // fixed positioning is viewport-relative — no scroll offset
    box.style.top  = (rect.bottom + 8) + 'px';
    box.style.left = rect.left + 'px';
    box.style.width = Math.max(rect.width, 500) + 'px';
}

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('drugSearchInput');
    const box   = document.getElementById('drugSearchResults');
    if (!input) return;

    // Move dropdown to body so it's never clipped by overflow:hidden ancestors
    document.body.appendChild(box);

    input.addEventListener('input', function() {
        clearTimeout(_drugSearchTimer);
        const q = this.value.trim();
        if (q.length < 4) { box.style.display = 'none'; return; }
        _drugDropdownPosition();
        _drugSearchTimer = setTimeout(() => _fetchDrugs(q), 300);
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !box.contains(e.target)) {
            box.style.display = 'none';
        }
    });

    input.addEventListener('focus', function() {
        if (this.value.trim().length >= 4 && box.innerHTML) {
            _drugDropdownPosition();
            box.style.display = '';
        }
    });

    window.addEventListener('resize', function() {
        if (box.style.display !== 'none') _drugDropdownPosition();
    });
    window.addEventListener('scroll', function() {
        if (box.style.display !== 'none') _drugDropdownPosition();
    }, true);
});
// ─────────────────────────────────────────────────────────────────────────────

async function refreshSentToPharmacyPanel() {
    try {
        const res = await fetch('{{ route("doctor.consultation.start", $encounter) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        
        // Update the Sent to Pharmacy panel
        if (data.panel) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data.panel, 'text/html');
            const newPanel = doc.getElementById('sentToPharmacyPanel');
            const existingPanel = document.getElementById('sentToPharmacyPanel');
            if (newPanel && existingPanel) {
                existingPanel.replaceWith(newPanel);
            } else if (newPanel && !existingPanel) {
                // Insert before Clinical Notes
                const clinicalNotes = document.querySelector('.consult-card:has(textarea[name="clinical_note"])');
                if (clinicalNotes) {
                    clinicalNotes.parentNode.insertBefore(newPanel, clinicalNotes);
                }
            }
        }
        
        // Update the drugs dropdown to exclude sent drugs
        if (data.drugs) {
            const selects = document.querySelectorAll('select[name="drug_id[]"]');
            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Select Drug...</option>';
                data.drugs.forEach(drug => {
                    const opt = document.createElement('option');
                    opt.value = drug.id;
                    opt.textContent = drug.name;
                    if (drug.id == currentValue) opt.selected = true;
                    select.appendChild(opt);
                });
            });
        }
    } catch (e) {
        console.error('Failed to refresh Sent to Pharmacy panel:', e);
    }
}

async function recallItem(itemId, drugName) {
    if (!confirm(`Recall "${drugName}" from the pharmacy queue?\n\nThis will delete the item so it will no longer appear for dispensing.`)) return;

    const btn = document.getElementById('recall-btn-' + itemId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:12px;height:12px"></span>'; }

    try {
        const res  = await fetch(`${_recallItemBase}/${itemId}/recall`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _step4Csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        _showToast('success', data.message || 'Item recalled.');

        // Remove the row entirely from the DOM
        const row = document.getElementById('rx-item-row-' + itemId);
        if (row) {
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                // If this was the last item in the prescription group, hide the whole group
                const tbody = row.closest('tbody');
                if (tbody && tbody.children.length === 0) {
                    const groupDiv = row.closest('.px-3');
                    if (groupDiv) groupDiv.remove();
                }
                // If no prescriptions left, hide the whole panel
                const panel = document.getElementById('sentToPharmacyPanel');
                if (panel && panel.querySelector('tbody') && panel.querySelector('tbody').children.length === 0) {
                    panel.remove();
                }
            }, 300);
        }
        
        // Refresh the drugs dropdown to restore the recalled drug
        await refreshSentToPharmacyPanel();
    } catch (e) {
        _showToast('danger', e.message || 'Failed to recall item.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined align-middle" style="font-size:14px">undo</span> Recall'; }
    }
}

async function sendToPharmacy() {
    if (!_selectedDrugs.size) {
        alert('Please add at least one medication before sending to pharmacy.');
        return;
    }

    const drug_items = [..._selectedDrugs.values()].map(d => ({
        drug_id:      d.id,
        dosage:       d.dosage       || 'As directed',
        frequency:    d.frequency    || 3,
        duration:     d.duration     || 5,
        instructions: d.instructions || '',
        quantity:     d.quantity     || (d.frequency * d.duration),
    }));

    const btn1 = document.getElementById('sendToPharmacyBtn');
    const btn2 = document.getElementById('sendToPharmacyBtnFooter');
    
    if (btn1) {
        btn1.disabled = true;
        btn1.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:1rem;height:1rem"></span> Sending...';
    }
    if (btn2) {
        btn2.disabled = true;
        btn2.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:1rem;height:1rem"></span> Sending...';
    }

    try {
        const res = await fetch(_pharmacySendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _step4Csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ drug_items })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        
        _showToast('success', data.message || 'Prescription sent to pharmacy successfully.');
        
        // Clear the selected drugs list
        _selectedDrugs.clear();
        _renderPrescriptionList();
        document.getElementById('drugSearchInput').value = '';
        document.getElementById('drugSearchResults').style.display = 'none';
        
        if (btn1) {
            btn1.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy';
            btn1.disabled = false;
        }
        if (btn2) {
            btn2.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy';
            btn2.disabled = false;
        }
        
        // Refresh the Sent to Pharmacy panel to show the newly sent items
        await refreshSentToPharmacyPanel();
        
        // Redirect to patient queue after successful send
        setTimeout(() => {
            window.location.href = '{{ route("doctor.queue") }}';
        }, 1500);
    } catch (e) {
        _showToast('danger', e.message || 'Failed to send to pharmacy.');
        if (btn1) {
            btn1.disabled = false;
            btn1.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy';
        }
        if (btn2) {
            btn2.disabled = false;
            btn2.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Pharmacy';
        }
    }
}

async function updateProcedures() {
    const checked = [...document.querySelectorAll('#procSection input[name="procedures[]"]:checked')];
    if (!checked.length) {
        alert('Please select at least one procedure before saving.');
        return;
    }

    const procedures = checked.map(cb => cb.value);

    const btn = document.getElementById('saveProceduresBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:1rem;height:1rem"></span> Saving...';

    try {
        const res = await fetch(_proceduresSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _step4Csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ procedures })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        
        _showToast('success', data.message || 'Procedures updated.');
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">check</span> Updated';
    } catch (e) {
        _showToast('danger', e.message || 'Failed to update procedures.');
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">update</span> Update Procedures';
        btn.disabled = false;
    }
}

function toggleTreatment(checkId, sectionId) {
    const cb = document.getElementById(checkId);
    cb.checked = !cb.checked;
    document.getElementById(sectionId).style.display = cb.checked ? '' : 'none';
}

// Initialize event listeners for treatment checkboxes
document.querySelectorAll('.treatment-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const map = { ttMed:'medSection', ttProc:'procSection' };
        if (map[this.id]) document.getElementById(map[this.id]).style.display = this.checked ? '' : 'none';
    });
});

</script>

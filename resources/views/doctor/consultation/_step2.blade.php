<div class="step-pane" id="step2">
    <h4 class="fw-bold mb-1" style="color:#016634">Investigations</h4>
    <p class="text-muted small mb-4">Order investigations and services for the patient</p>

    <div class="consult-card">
        <div class="consult-card-header">
            <span class="material-symbols-outlined" style="font-size:20px">science</span>
            Investigation Required
        </div>
        <div class="p-3">
            <select name="investigation_required" id="invReqSelect" class="form-select border-0 bg-light rounded-3" style="font-size:15px;padding:12px 16px">
                <option value="No" {{ old('investigation_required', $savedData['investigation_required'] ?? 'No') == 'No' ? 'selected' : '' }}>No</option>
                <option value="Yes" {{ old('investigation_required', $savedData['investigation_required'] ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
            </select>
        </div>
    </div>

    <div id="invSearchArea" style="{{ old('investigation_required', $savedData['investigation_required'] ?? 'No') == 'Yes' ? '' : 'display:none' }}">

        <!-- Selected chips -->
        <div id="invChipsWrap" class="mb-3">
            <div id="invChips" class="d-flex flex-wrap gap-2">
                @php
                function parseServiceValue($value) {
                    if (strpos($value, '::') !== false) {
                        [$name, $itemId] = explode('::', $value, 2);
                        return ['name' => $name, 'id' => $itemId];
                    }
                    return ['name' => $value, 'id' => null];
                }
                @endphp
                @foreach(old('lab_tests', $savedData['lab_tests'] ?? []) as $t)
                @php $parsed = parseServiceValue($t); @endphp
                <span class="icd-chip" data-val="{{ $parsed['name'] }}" data-cat="lab" @if($parsed['id']) data-item-id="{{ $parsed['id'] }}" @endif title="{{ $parsed['name'] }} (Lab)">
                    {{ $parsed['name'] }} <small style="opacity:0.7">(lab)</small> <span class="remove" onclick="removeInv(this)">✕</span>
                    <input type="hidden" name="lab_tests[]" value="{{ $t }}">
                </span>
                @endforeach
                @foreach(old('radiology_tests', $savedData['radiology_tests'] ?? []) as $t)
                @php $parsed = parseServiceValue($t); @endphp
                <span class="icd-chip" data-val="{{ $parsed['name'] }}" data-cat="rad" @if($parsed['id']) data-item-id="{{ $parsed['id'] }}" @endif style="background:#fef3e8;color:#d68910" title="{{ $parsed['name'] }} (Radiology)">
                    {{ $parsed['name'] }} <small style="opacity:0.7">(rad)</small> <span class="remove" onclick="removeInv(this)">✕</span>
                    <input type="hidden" name="radiology_tests[]" value="{{ $t }}">
                </span>
                @endforeach
                @foreach(old('other_services', $savedData['other_services'] ?? []) as $t)
                @php $parsed = parseServiceValue($t); @endphp
                <span class="icd-chip" data-val="{{ $parsed['name'] }}" data-cat="other" @if($parsed['id']) data-item-id="{{ $parsed['id'] }}" @endif style="background:#f0f0fe;color:#5c6bc0" title="{{ $parsed['name'] }} (Other)">
                    {{ $parsed['name'] }} <small style="opacity:0.7">(other)</small> <span class="remove" onclick="removeInv(this)">✕</span>
                    <input type="hidden" name="other_services[]" value="{{ $t }}">
                </span>
                @endforeach
            </div>
        </div>

        <!-- Search + Service List -->
        <div class="consult-card">
            <div class="consult-card-header">
                <span class="material-symbols-outlined" style="font-size:20px">search</span>
                Search Investigations &amp; Services
            </div>
            <div class="p-3">
                <input type="text" id="invSearch" class="form-control border-0 bg-light rounded-3 mb-3" placeholder="Search tests and services..." style="padding:12px 16px">
                <div id="invServiceList" style="max-height:500px;overflow-y:auto;border:1px solid #e8f0ee;border-radius:8px;padding:8px">

                @php
                $labSaved     = old('lab_tests',      $savedData['lab_tests']      ?? []);
                $radSaved     = old('radiology_tests', $savedData['radiology_tests'] ?? []);
                $otherSaved   = old('other_services',  $savedData['other_services']  ?? []);
                @endphp

                @php
                    // Flatten all services into a single list
                    $allItems = collect();
                    foreach($serviceCategories as $catName => $catItems) {
                        $lower   = strtolower($catName);
                        $catType = (str_contains($lower, 'laboratory') || str_contains($lower, 'haematolog') || str_contains($lower, 'hematolog'))
                                    ? 'lab'
                                    : (str_contains($lower, 'radiolog') ? 'rad' : 'other');
                        foreach($catItems as $item) {
                            $item->_catName = $catName;
                            $item->_catType = $catType;
                            $allItems->push($item);
                        }
                    }
                @endphp

                @foreach($allItems as $item)
                @php
                    $t = $item->item_name;
                    $catType = $item->_catType;
                    $catName = $item->_catName;
                    $typeName = $item->type_name ?? '';
                    $checked = match($catType) {
                        'lab'   => in_array($t, $labSaved),
                        'rad'   => in_array($t, $radSaved),
                        default => in_array($t, $otherSaved),
                    };
                    $itemClass   = $catType === 'rad' ? 'inv-item secondary' : ($catType === 'other' ? 'inv-item tertiary' : 'inv-item');
                    $badgeBg     = $catType === 'rad' ? '#fef3e8' : ($catType === 'other' ? '#f0f0fe' : '#e8f0ee');
                    $badgeColor  = $catType === 'rad' ? '#e67e22' : ($catType === 'other' ? '#5c6bc0' : '#016634');
                @endphp
                <div class="{{ $itemClass }} {{ $checked ? 'selected' : '' }}"
                    data-s="{{ strtolower($t . ' ' . $typeName . ' ' . $catName) }}"
                    data-val="{{ $t }}" data-cat="{{ $catType }}" 
                    data-item-id="{{ $item->id ?? '' }}"
                    data-full-name="{{ $t }} ({{ $catName }})"
                    onclick="toggleInv(this)">
                    <div class="inv-check">{{ $checked ? '✓' : '' }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-medium" style="font-size:14px">{{ $t }}</div>
                        <div>
                            <span class="badge me-1" style="background:{{ $badgeBg }};color:{{ $badgeColor }};font-size:10px">{{ $typeName }}</span>
                            <span class="badge" style="background:#f5f5f5;color:#666;font-size:10px">{{ $catName }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>{{-- /invServiceList --}}
            </div>
        </div>

        <div class="consult-card">
            <div class="consult-card-header"><span class="material-symbols-outlined" style="font-size:20px">note</span> Investigation Notes</div>
            <div class="p-3">
                <textarea name="investigation_note" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Special instructions for lab/radiology...">{{ old('investigation_note', $savedData['investigation_note'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Status monitoring panel -->
    <div class="consult-card" id="invStatusCard" style="display:none">
        <div class="consult-card-header" style="cursor:pointer" onclick="this.nextElementSibling.classList.toggle('d-none')">
            <span class="material-symbols-outlined" style="font-size:20px">biotech</span>
            Investigation Request Status
            <span class="ms-auto material-symbols-outlined" style="font-size:18px">expand_more</span>
        </div>
        <div class="p-3" id="invStatusBody"><p class="text-muted small mb-0">Loading...</p></div>
    </div>

    <div class="consult-footer">
        <button type="button" class="btn-step-prev btn" onclick="prevStep()">Previous</button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success px-4 rounded-3" id="sendToLabBtn" onclick="sendToLab()" style="background:#016634;border:none">
                <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Lab
            </button>
            <button type="button" class="btn-step-next btn" onclick="nextStep()">Next</button>
        </div>
    </div>
</div>

<!-- Referral Facility Modal -->
<div class="modal fade" id="referralModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#016634;color:#fff">
                <h5 class="modal-title"><span class="material-symbols-outlined align-middle me-2">local_hospital</span>Select Referral Facilities</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="referralModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmReferrals()" style="background:#016634;border:none">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">check</span> Confirm Referrals
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const _invEncounterId = '{{ $encounter->id }}';
const _invSendUrl     = '{{ route("doctor.consultation.send-to-lab", $encounter) }}';
const _invReferralUrl = '{{ route("doctor.consultation.service-referral", $encounter) }}';
const _invStatusUrl  = '{{ route("doctor.consultation.service-order-status", $encounter) }}';
const _recallBaseUrl   = '{{ url("doctor/consultation/" . $encounter->id . "/service-order") }}';
const _removeItemUrl   = '{{ route("doctor.consultation.service-order-item-remove", $encounter) }}';
const _recallCsrf      = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _csrf            = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// Tracks service names already persisted in the DB (pre-sent + newly sent this session)
const _sentServiceNames = new Set(@json($orderedServiceNames ?? []));

document.getElementById('invReqSelect')?.addEventListener('change', function(){
    document.getElementById('invSearchArea').style.display = this.value === 'Yes' ? '' : 'none';
});

document.getElementById('invSearch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#invServiceList .inv-item').forEach(el => {
        el.style.display = el.dataset.s.includes(q) ? '' : 'none';
    });
    document.querySelectorAll('#invServiceList .mb-1').forEach(el => {
        el.style.display = '';
    });
});

function toggleInv(el) {
    const val = el.dataset.val, cat = el.dataset.cat;
    const itemId = el.dataset.itemId || '';
    const chips = document.getElementById('invChips');
    
    // Check for existing item with same name AND category (or item ID if available)
    const existing = itemId ? 
        chips.querySelector(`[data-item-id="${CSS.escape(itemId)}"]`) :
        chips.querySelector(`[data-val="${CSS.escape(val)}"][data-cat="${CSS.escape(cat)}"]`);
        
    if (existing) {
        // Route through removeInv so server delete fires for already-sent services
        const removeBtn = existing.querySelector('.remove');
        if (removeBtn) {
            removeInv(removeBtn);
        } else {
            existing.remove();
        }
        el.classList.remove('selected');
        el.querySelector('.inv-check').textContent = '';
        return;
    } else {
        el.classList.add('selected');
        el.querySelector('.inv-check').textContent = '\u2713';
        const chip = document.createElement('span');
        chip.className = 'icd-chip';
        chip.dataset.val = val; 
        chip.dataset.cat = cat;
        if (itemId) chip.dataset.itemId = itemId;
        
        if (cat === 'rad')   chip.style.cssText = 'background:#fef3e8;color:#d68910';
        if (cat === 'other') chip.style.cssText = 'background:#f0f0fe;color:#5c6bc0';
        
        const inputName = cat === 'rad' ? 'radiology_tests[]' : cat === 'lab' ? 'lab_tests[]' : 'other_services[]';
        const fullName = el.dataset.fullName || val;
        chip.title = fullName; // Show full name with category on hover
        
        // Include item ID in hidden input to distinguish variants
        const inputValue = itemId ? `${val}::${itemId}` : val;
        chip.innerHTML = `${_escHtml(val)} <small style="opacity:0.7">(${cat})</small> <span class="remove" onclick="removeInv(this)">&times;</span><input type="hidden" name="${inputName}" value="${_escHtml(inputValue)}">`;
        chips.appendChild(chip);
    }
}

async function removeInv(removeBtn) {
    const chip = removeBtn.closest('[data-val]');
    const val  = chip.dataset.val;

    if (_sentServiceNames.has(val)) {
        // Service already in DB — delete from server first
        removeBtn.textContent = '…';
        removeBtn.style.pointerEvents = 'none';
        try {
            const res  = await fetch(_removeItemUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
                body: JSON.stringify({ service_name: val })
            });
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            _sentServiceNames.delete(val);
            _loadInvStatus();
        } catch (e) {
            _showToast('danger', e.message || 'Failed to remove service.');
            removeBtn.textContent = '\u00d7';
            removeBtn.style.pointerEvents = '';
            return; // Keep the chip — server delete failed
        }
    }

    chip.remove();
    const invEl = document.querySelector(`#invServiceList .inv-item[data-val="${CSS.escape(val)}"]`);
    if (invEl) { invEl.classList.remove('selected'); invEl.querySelector('.inv-check').textContent = ''; }
}

// ── Send to Lab ──────────────────────────────────────────────────────────────
async function sendToLab() {
    const chips = [...document.querySelectorAll('#invChips [data-val]')];
    if (!chips.length) {
        alert('Please select at least one investigation or service first.');
        return;
    }
    // Read hidden input value which contains "Name::uuid" format (or just "Name" for legacy)
    const services = chips.map(c => {
        const input = c.querySelector('input[type="hidden"]');
        return input ? input.value : c.dataset.val;
    });
    const btn = document.getElementById('sendToLabBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

    try {
        const res  = await fetch(_invSendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
            body: JSON.stringify({ services })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        // Track newly sent local services so X-button removal calls the server
        // Use chip name (dataset.val), not the "Name::uuid" service value
        if (data.order_created) {
            chips.forEach(c => _sentServiceNames.add(c.dataset.val));
        }

        if (data.needs_referral && data.needs_referral.length > 0) {
            _buildReferralModal(data.needs_referral, data.order_created);
            new bootstrap.Modal(document.getElementById('referralModal')).show();
        } else {
            const added = data.order_created?.added ?? 0;
            const msg   = data.order_created
                ? `Order ${data.order_created.order_number}: ${added} new service(s) added.`
                : 'No new local services to add.';
            _showInvAlert('success', msg);
            _loadInvStatus();
            
            // Show choice dialog if requested
            if (data.show_choice) {
                _showChoiceDialog(data.message, 'lab');
            } else {
                // Redirect to patient queue after successful send
                setTimeout(() => {
                    window.location.href = '{{ route("doctor.queue") }}';
                }, 1500);
            }
        }
    } catch (e) {
        _showInvAlert('danger', e.message || 'Failed to send services to lab.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1" style="font-size:16px">send</span> Send to Lab';
    }
}

function _showChoiceDialog(message, type) {
    const modalHtml = `
        <div class="modal fade" id="choiceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Order Sent Successfully</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="handleChoice('continue')">
                                <span class="material-symbols-outlined align-middle me-1">edit_note</span>
                                Continue Consultation
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="handleChoice('queue')">
                                <span class="material-symbols-outlined align-middle me-1">queue</span>
                                Go to Patient Queue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('choiceModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('choiceModal'));
    modal.show();
    
    // Clean up when modal is hidden
    document.getElementById('choiceModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function handleChoice(choice) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('choiceModal'));
    modal.hide();
    
    if (choice === 'queue') {
        window.location.href = '{{ route("doctor.queue") }}';
    }
    // If 'continue', just close the modal and stay on consultation page
}

function _buildReferralModal(needsReferral, orderCreated) {
    let html = '';
    if (orderCreated) {
        html += `<div class="alert alert-success py-2 mb-3"><span class="material-symbols-outlined align-middle me-1" style="font-size:16px">check_circle</span>Order <strong>${_escHtml(orderCreated.order_number)}</strong> created for ${orderCreated.count} local service(s).</div>`;
    }
    html += '<p class="text-muted small mb-3">The following services are <strong>not available</strong> at this facility. Select a referral facility for each:</p>';
    needsReferral.forEach((item) => {
        const ex       = item.existing_referral;
        const isPending = !ex || ex.status === 'pending';
        const statusBadge = ex
            ? `<span class="badge ms-2 bg-${ex.status === 'pending' ? 'warning text-dark' : ex.status === 'completed' ? 'success' : 'secondary'}">${_escHtml(ex.status)}</span>`
            : '';
        const opts = item.facilities.map(f => {
            const sel = ex && ex.to_facility_id == f.id ? 'selected' : '';
            return `<option value="${_escHtml(f.id)}" ${sel}>${_escHtml(f.name)} (${_escHtml(f.lga || '')} &bull; ${_escHtml(f.type || '')})</option>`;
        }).join('');

        const lockedNote = !isPending
            ? `<div class="alert alert-warning py-1 px-2 mb-2 small"><span class="material-symbols-outlined align-middle me-1" style="font-size:14px">lock</span>Cannot change facility — referral is <strong>${_escHtml(ex.status)}</strong></div>`
            : '';

        html += `
        <div class="border rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2 d-flex align-items-center" style="font-size:14px">
                <span class="material-symbols-outlined me-1" style="font-size:16px;color:#e67e22">warning</span>${_escHtml(item.name)}${statusBadge}
            </div>
            <input type="hidden" class="ref-item-id" value="${_escHtml(item.id)}">
            ${lockedNote}
            ${opts ? `<select class="form-select form-select-sm ref-facility-select mb-2" ${!isPending ? 'disabled' : ''}>
                <option value="">-- Select facility --</option>${opts}
            </select>` : '<p class="text-danger small mb-1">No facility offers this service currently.</p>'}
            <input type="text" class="form-control form-control-sm ref-reason" placeholder="Reason (optional)" value="${ex ? _escHtml(ex.reason || '') : ''}" ${!isPending ? 'disabled' : ''}>
        </div>`;
    });
    document.getElementById('referralModalBody').innerHTML = html;
}

async function confirmReferrals() {
    const rows = document.querySelectorAll('#referralModalBody .border');
    const referrals = [];
    let valid = true;
    rows.forEach(row => {
        const itemId  = row.querySelector('.ref-item-id')?.value;
        const selEl   = row.querySelector('.ref-facility-select');
        const reason  = row.querySelector('.ref-reason')?.value;
        if (!selEl || selEl.disabled) return; // skip locked (non-pending) rows
        if (!selEl.value) { valid = false; selEl.classList.add('is-invalid'); return; }
        referrals.push({ service_item_id: itemId, to_facility_id: selEl.value, reason });
    });
    if (!valid) return;
    if (!referrals.length) {
        bootstrap.Modal.getInstance(document.getElementById('referralModal'))?.hide();
        return;
    }

    try {
        const res  = await fetch(_invReferralUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
            body: JSON.stringify({ referrals })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        bootstrap.Modal.getInstance(document.getElementById('referralModal'))?.hide();
        _showInvAlert('success', data.message || 'Referrals saved successfully.');
        _loadInvStatus();
        
        // Show choice dialog after referrals are saved
        _showChoiceDialog('Services sent to lab successfully. What would you like to do next?', 'lab');
    } catch(e) {
        alert('Error: ' + (e.message || 'Failed to save referrals'));
    }
}

// ── Status Panel ─────────────────────────────────────────────────────────────
function _statusBadge(s) {
    const map = { pending:'warning', authorized:'info', in_progress:'primary', processing:'primary', completed:'success', cancelled:'danger' };
    return `<span class="badge bg-${map[s]||'secondary'}">${s}</span>`;
}

async function _loadInvStatus() {
    const card = document.getElementById('invStatusCard');
    const body = document.getElementById('invStatusBody');
    card.style.display = '';
    body.innerHTML = '<p class="text-muted small mb-0">Loading...</p>';
    try {
        const res  = await fetch(_invStatusUrl);
        const data = await res.json();
        let html = '';
        
        if (data.services?.length) {
            html += '<p class="fw-semibold text-muted small mb-2">INVESTIGATION SERVICES</p>';
            data.services.forEach(service => {
                const canRecall = service.status === 'pending';
                const recallBtn = canRecall
                    ? `<button class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:11px" onclick="recallServiceItem('${service.type === 'referral' ? service.referral_id : service.order_item_id}','${_escHtml(service.name)}',this)" title="Recall this service"><span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle">undo</span></button>`
                    : '';
                
                const facilityInfo = service.type === 'referral' 
                    ? `<div class="text-muted small">Referred to: ${_escHtml(service.referral_facility || 'Unknown')}</div>`
                    : service.order_number 
                    ? `<div class="text-muted small">Order: ${_escHtml(service.order_number)} • ${_escHtml(service.order_facility || 'Current facility')}</div>`
                    : '';
                
                html += `<div class="border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span style="font-size:14px; font-weight:500">${_escHtml(service.name)}</span>
                            ${facilityInfo}
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            ${_statusBadge(service.status)}
                            ${recallBtn}
                        </div>
                    </div>
                </div>`;
            });
        }
        
        body.innerHTML = html || '<p class="text-muted small mb-0">No investigation services ordered yet.</p>';
    } catch(e) {
        body.innerHTML = '<p class="text-danger small mb-0">Failed to load status.</p>';
    }
}

function _showInvAlert(type, msg) {
    _showToast(type, msg);
}

async function recallOrder(orderId, orderNumber, btn) {
    if (!confirm(`Recall order ${orderNumber}? This will cancel ALL services in this order.`)) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Recalling...';

    try {
        const res = await fetch(_recallUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': _csrf }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed to recall order');

        _showInvAlert('success', `Order ${orderNumber} recalled successfully.`);
        _loadInvStatus(); // refresh the panel
    } catch (e) {
        _showToast('danger', e.message || 'Failed to recall order.');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle">undo</span> Recall';
    }
}

async function recallServiceItem(itemId, itemName, btn) {
    if (!confirm(`Recall service "${itemName}"? This will cancel this specific service.`)) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res = await fetch(_removeItemUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
            body: JSON.stringify({ service_name: itemName })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed to recall service');

        _showInvAlert('success', `Service "${itemName}" recalled successfully.`);
        _loadInvStatus(); // refresh the panel
        
        // Remove from sent services tracking if it was sent
        if (window._sentServiceNames) {
            window._sentServiceNames.delete(itemName);
        }
        
        // Remove from chips if it exists
        const chip = document.querySelector(`#invChips [data-val="${CSS.escape(itemName)}"]`);
        if (chip) {
            chip.remove();
            // Also uncheck from the service list
            const invEl = document.querySelector(`#invServiceList .inv-item[data-val="${CSS.escape(itemName)}"]`);
            if (invEl) { 
                invEl.classList.remove('selected'); 
                invEl.querySelector('.inv-check').textContent = ''; 
            }
        }
    } catch (e) {
        _showToast('danger', e.message || 'Failed to recall service.');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:12px;vertical-align:middle">undo</span>';
    }
}

const _orderedServiceNames = @json($orderedServiceNames ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Pre-select services already sent to lab / referred
    if (_orderedServiceNames.length) {
        const reqSel = document.getElementById('invReqSelect');
        if (reqSel) reqSel.value = 'Yes';
        const area = document.getElementById('invSearchArea');
        if (area) area.style.display = '';

        _orderedServiceNames.forEach(name => {
            const el = document.querySelector(`#invServiceList .inv-item[data-val="${CSS.escape(name)}"]`);
            if (el && !el.classList.contains('selected')) toggleInv(el);
        });

        _loadInvStatus();
    } else {
        fetch(_invStatusUrl)
            .then(r => r.json())
            .then(data => { if (data.orders?.length || data.referrals?.length) _loadInvStatus(); })
            .catch(() => {});
    }
});
</script>

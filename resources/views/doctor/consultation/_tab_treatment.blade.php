<div class="tab-pane fade" id="tab5">
    <h5 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle me-2">medication</span>Treatment & Outcome</h5>
    
    <!-- Treatment Type -->
    <div class="mb-4">
        <label class="form-label fw-medium">Treatment Type</label>
        <div class="row">
            <div class="col-md-3 col-6 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="treatment_types[]" value="Medication" id="ttMed" {{ in_array('Medication', old('treatment_types', $savedData['treatment_types'] ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ttMed"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">pill</span> Medication</label>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="treatment_types[]" value="Procedure" id="ttProc" {{ in_array('Procedure', old('treatment_types', $savedData['treatment_types'] ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ttProc"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">healing</span> Procedure</label>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="treatment_types[]" value="Counselling" id="ttCoun" {{ in_array('Counselling', old('treatment_types', $savedData['treatment_types'] ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ttCoun"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">psychology</span> Counselling</label>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="treatment_types[]" value="Referral" id="ttRef" {{ in_array('Referral', old('treatment_types', $savedData['treatment_types'] ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ttRef"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">send</span> Referral</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Medications -->
    <div class="mb-3" id="medDiv" style="display:none">
        <label class="form-label fw-medium">Prescribe Medications</label>
        <input type="text" class="form-control mb-2" id="searchDrug" placeholder="🔍 Search drugs...">
        <div class="border rounded p-2" style="max-height:200px;overflow-y:auto" id="drugList">
            @foreach($drugs ?? [] as $drug)
            <div class="form-check drug-item py-1" data-s="{{ strtolower($drug->name . ' ' . ($drug->generic_name ?? '')) }}">
                <input class="form-check-input" type="checkbox" name="medications[]" value="{{ $drug->id }}" id="drug{{ $drug->id }}" {{ in_array($drug->id, old('medications', $savedData['medications'] ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label small" for="drug{{ $drug->id }}">
                    <strong>{{ $drug->name }}</strong>
                    @if($drug->generic_name)<span class="text-muted"> ({{ $drug->generic_name }})</span>@endif
                    @if($drug->strength)<span class="badge bg-secondary ms-1">{{ $drug->strength }}</span>@endif
                </label>
            </div>
            @endforeach
        </div>
        <small class="text-muted">Select medications to prescribe. Detailed dosage instructions can be added after saving.</small>
    </div>

    <!-- Procedures -->
    <div class="mb-3" id="procDiv" style="display:none">
        <label class="form-label fw-medium">Procedures Performed</label>
        <div class="row">
            @foreach(['Wound dressing','Suturing','Incision & Drainage','Nebulization','Stabilization','Catheterization','IV Fluid'] as $p)
            <div class="col-md-4 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="procedures[]" value="{{ $p }}" id="pr{{ Str::slug($p) }}" {{ in_array($p, old('procedures', $savedData['procedures'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="pr{{ Str::slug($p) }}">{{ $p }}</label></div></div>
            @endforeach
        </div>
    </div>

    <!-- Counselling -->
    <div class="mb-3" id="counDiv" style="display:none">
        <label class="form-label fw-medium">Counselling Given</label>
        <div class="row">
            @foreach(['Diagnosis Explanation','Lifestyle Modification','Medication Adherence','Follow-up Instructions'] as $c)
            <div class="col-md-3 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="counselling[]" value="{{ $c }}" id="co{{ Str::slug($c) }}" {{ in_array($c, old('counselling', $savedData['counselling'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="co{{ Str::slug($c) }}">{{ $c }}</label></div></div>
            @endforeach
        </div>
    </div>

    <!-- Clinical Notes -->
    <div class="mb-3">
        <label class="form-label fw-medium">Clinical Notes / Treatment Plan</label>
        <textarea name="clinical_note" id="clinical_note" class="form-control" rows="3" placeholder="Treatment plan, instructions, notes...">{{ old('clinical_note', $savedData['clinical_note'] ?? '') }}</textarea>
    </div>

    <!-- Outcome -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-medium">Outcome <span class="text-danger">*</span></label>
            <select name="outcome" id="outcome" class="form-select" required>
                <option value="">Select Outcome...</option>
                <option value="Improved" {{ old('outcome', $savedData['outcome'] ?? '') == 'Improved' ? 'selected' : '' }}>Improved - Discharge</option>
                <option value="Follow-up" {{ old('outcome', $savedData['outcome'] ?? '') == 'Follow-up' ? 'selected' : '' }}>Follow-up Required</option>
                <option value="Refer" {{ old('outcome', $savedData['outcome'] ?? '') == 'Refer' ? 'selected' : '' }}>Refer to Specialist</option>
                <option value="Admit" {{ old('outcome', $savedData['outcome'] ?? '') == 'Admit' ? 'selected' : '' }}>Admit to Ward</option>
                <option value="Discharged" {{ old('outcome', $savedData['outcome'] ?? '') == 'Discharged' ? 'selected' : '' }}>Discharged</option>
            </select>
        </div>
        <div class="col-md-6" id="fuDiv" style="{{ old('outcome', $savedData['outcome'] ?? '') == 'Follow-up' ? '' : 'display:none' }}">
            <label class="form-label fw-medium">Follow-up Date</label>
            <input type="date" name="follow_up_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ old('follow_up_date', $savedData['follow_up_date'] ?? '') }}">
        </div>
    </div>

    <div class="alert alert-light border small">
        <span class="material-symbols-outlined align-middle me-1">info</span> 
        <strong>Next Steps:</strong> After saving, you'll be taken to the consultation details page where you can add detailed prescriptions with dosage, frequency, and duration.
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" onclick="goTab(3)"><span class="material-symbols-outlined align-middle me-1">arrow_back</span> Previous</button>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="saveTab(5)"><span class="material-symbols-outlined align-middle me-1">save</span> Save Draft</button>
            <button type="submit" class="btn btn-success btn-lg"><span class="material-symbols-outlined align-middle me-1">arrow_forward</span> Save & Continue</button>
        </div>
    </div>
</div>

<script>
// Treatment type toggles
document.getElementById('ttMed')?.addEventListener('change', function(){ document.getElementById('medDiv').style.display = this.checked ? '' : 'none'; });
document.getElementById('ttProc')?.addEventListener('change', function(){ document.getElementById('procDiv').style.display = this.checked ? '' : 'none'; });
document.getElementById('ttCoun')?.addEventListener('change', function(){ document.getElementById('counDiv').style.display = this.checked ? '' : 'none'; });

// Outcome - follow-up date toggle
document.getElementById('outcome')?.addEventListener('change', function(){
    document.getElementById('fuDiv').style.display = this.value === 'Follow-up' ? '' : 'none';
});

// Drug search
document.getElementById('searchDrug')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#drugList .drug-item').forEach(el => {
        el.style.display = el.dataset.s.includes(q) ? '' : 'none';
    });
});

// Initialize states on page load
document.addEventListener('DOMContentLoaded', function(){
    if(document.getElementById('ttMed')?.checked) document.getElementById('medDiv').style.display = '';
    if(document.getElementById('ttProc')?.checked) document.getElementById('procDiv').style.display = '';
    if(document.getElementById('ttCoun')?.checked) document.getElementById('counDiv').style.display = '';
});
</script>

<div class="tab-pane fade" id="tab4">
    <h5 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle me-2">biotech</span>Investigations</h5>
    
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="investigation_required" id="invReq" value="1" {{ old('investigation_required', $savedData['investigation_required'] ?? false) ? 'checked' : '' }}>
        <label class="form-check-label fw-medium" for="invReq">Investigations Required</label>
    </div>

    <div id="invOpts" style="{{ old('investigation_required', $savedData['investigation_required'] ?? false) ? '' : 'display:none' }}">
        <!-- Laboratory -->
        <div class="card border mb-3">
            <div class="card-header bg-primary text-white py-2"><h6 class="mb-0 small"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">science</span> Laboratory Tests</h6></div>
            <div class="card-body py-2">
                <div class="mb-2"><strong class="small text-muted">Hematology</strong></div>
                <div class="row mb-2">
                    @foreach(['FBC/CBC','PCV','Hemoglobin','WBC Count','ESR','Blood Film','Genotype','Blood Group'] as $t)
                    <div class="col-md-3 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="lab_tests[]" value="{{ $t }}" id="l{{ Str::slug($t) }}" {{ in_array($t, old('lab_tests', $savedData['lab_tests'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="l{{ Str::slug($t) }}">{{ $t }}</label></div></div>
                    @endforeach
                </div>
                <div class="mb-2"><strong class="small text-muted">Clinical Chemistry</strong></div>
                <div class="row mb-2">
                    @foreach(['RBS','FBS','LFT','RFT/KFT','E/U/Cr','Lipid Profile','HbA1C'] as $t)
                    <div class="col-md-3 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="lab_tests[]" value="{{ $t }}" id="l{{ Str::slug($t) }}" {{ in_array($t, old('lab_tests', $savedData['lab_tests'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="l{{ Str::slug($t) }}">{{ $t }}</label></div></div>
                    @endforeach
                </div>
                <div class="mb-2"><strong class="small text-muted">Microbiology & Serology</strong></div>
                <div class="row">
                    @foreach(['Urinalysis','Urine MCS','Stool MCS','Blood Culture','Malaria (MP)','Widal','HIV','Hep B','Hep C','Pregnancy Test'] as $t)
                    <div class="col-md-3 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="lab_tests[]" value="{{ $t }}" id="l{{ Str::slug($t) }}" {{ in_array($t, old('lab_tests', $savedData['lab_tests'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="l{{ Str::slug($t) }}">{{ $t }}</label></div></div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Radiology -->
        <div class="card border mb-3">
            <div class="card-header bg-info text-white py-2"><h6 class="mb-0 small"><span class="material-symbols-outlined align-middle me-1" style="font-size:18px">radiology</span> Radiology</h6></div>
            <div class="card-body py-2">
                <div class="row">
                    @foreach(['Chest X-Ray','Abdominal X-Ray','Pelvic X-Ray','USS Abdomen','USS Pelvis','Obstetric USS','Echo','CT Scan','MRI'] as $t)
                    <div class="col-md-3 col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="radiology_tests[]" value="{{ $t }}" id="r{{ Str::slug($t) }}" {{ in_array($t, old('radiology_tests', $savedData['radiology_tests'] ?? [])) ? 'checked' : '' }}><label class="form-check-label small" for="r{{ Str::slug($t) }}">{{ $t }}</label></div></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small">Investigation Notes</label>
            <textarea name="investigation_note" class="form-control" rows="2" placeholder="Special instructions...">{{ old('investigation_note', $savedData['investigation_note'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" onclick="goTab(2)"><span class="material-symbols-outlined align-middle me-1">arrow_back</span> Previous</button>
        <button type="button" class="btn btn-primary" onclick="saveTab(4)">Save & Continue <span class="material-symbols-outlined align-middle ms-1">arrow_forward</span></button>
    </div>
</div>

<div class="tab-pane fade" id="tab3">
    <h5 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle me-2">medical_information</span>Diagnosis (ICD-11)</h5>
    
    <!-- Provisional Diagnosis -->
    <div class="mb-4">
        <label class="form-label fw-medium">Provisional Diagnosis <span class="badge bg-warning text-dark">Before Investigations</span></label>
        <input type="text" class="form-control mb-2" id="searchProv" placeholder="🔍 Search ICD codes...">
        <div class="border rounded p-2" style="max-height:180px;overflow-y:auto" id="provList">
            <div class="card card-body">
            @foreach($icdCodes->take(100) as $code)
            <div class="form-check icd-item py-1" data-s="{{ strtolower($code->code.' '.$code->description) }}">
                <input class="form-check-input" type="checkbox" name="provisional_diagnosis[]" value="{{ $code->id }}" id="p{{ $code->id }}" {{ in_array($code->id, old('provisional_diagnosis', $savedData['provisional_diagnosis'] ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label small" for="p{{ $code->id }}"><span class="badge bg-secondary">{{ $code->code }}</span> {{ Str::limit($code->description, 45) }}</label>
            </div>
            @endforeach
                
            </div>
        </div>
    </div>

    <!-- Confirmed Diagnosis -->
    <div class="mb-4">
        <label class="form-label fw-medium">Confirmed Diagnosis <span class="badge bg-success">After Investigations</span></label>
        <input type="text" class="form-control mb-2" id="searchConf" placeholder="🔍 Search ICD codes...">
        <div class="border rounded p-2" style="max-height:180px;overflow-y:auto" id="confList">
            <div class="card card-body">
            @foreach($icdCodes->take(100) as $code)
            <div class="form-check icd-item py-1" data-s="{{ strtolower($code->code.' '.$code->description) }}">
                <input class="form-check-input" type="checkbox" name="confirmed_diagnosis[]" value="{{ $code->id }}" id="c{{ $code->id }}" {{ in_array($code->id, old('confirmed_diagnosis', $savedData['confirmed_diagnosis'] ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label small" for="c{{ $code->id }}"><span class="badge bg-success">{{ $code->code }}</span> {{ Str::limit($code->description, 45) }}</label>
            </div>
            @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" onclick="goTab(1)"><span class="material-symbols-outlined align-middle me-1">arrow_back</span> Previous</button>
        <button type="button" class="btn btn-primary" onclick="saveTab(3)">Save & Continue <span class="material-symbols-outlined align-middle ms-1">arrow_forward</span></button>
    </div>
</div>

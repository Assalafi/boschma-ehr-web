<div class="tab-pane fade" id="tab2">
    <h5 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle me-2">stethoscope</span>Physical Examination</h5>
    
    <div class="mb-3">
        <label class="form-label fw-medium">General Examination</label>
        <textarea name="general_examination" class="form-control" rows="2" placeholder="General appearance, consciousness, pallor, jaundice, cyanosis, clubbing, edema...">{{ old('general_examination', $savedData['general_examination'] ?? '') }}</textarea>
    </div>
    
    <div class="mb-3">
        <label class="form-label fw-medium">Systemic Examination</label>
        <textarea name="physical_examination" id="physical_examination" class="form-control" rows="5" placeholder="CVS: ...
RS: ...
GIT: ...
CNS: ...
MSK: ...">{{ old('physical_examination', $savedData['physical_examination'] ?? '') }}</textarea>
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" onclick="goTab(0)"><span class="material-symbols-outlined align-middle me-1">arrow_back</span> Previous</button>
        <button type="button" class="btn btn-primary" onclick="saveTab(2)">Save & Continue <span class="material-symbols-outlined align-middle ms-1">arrow_forward</span></button>
    </div>
</div>

<div class="tab-pane fade show active" id="tab1">
    <h5 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle me-2">description</span>History Taking</h5>
    
    <div class="mb-3">
        <label class="form-label fw-medium">Presenting Complaints <span class="text-danger">*</span></label>
        <textarea name="presenting_complaints" id="presenting_complaints" class="form-control" rows="3" placeholder="Chief complaint, onset, duration, severity...">{{ old('presenting_complaints', $savedData['presenting_complaints'] ?? '') }}</textarea>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Past Medical History</label>
            <textarea name="past_medical_history" class="form-control" rows="2" placeholder="Previous illnesses, chronic conditions...">{{ old('past_medical_history', $savedData['past_medical_history'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Past Surgical History</label>
            <textarea name="past_surgical_history" class="form-control" rows="2" placeholder="Previous surgeries...">{{ old('past_surgical_history', $savedData['past_surgical_history'] ?? '') }}</textarea>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label fw-medium">Drug History</label>
            <textarea name="drug_history" class="form-control" rows="2" placeholder="Current medications...">{{ old('drug_history', $savedData['drug_history'] ?? '') }}</textarea>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-medium">Allergy History</label>
            <textarea name="allergy_history" class="form-control" rows="2" placeholder="Known allergies...">{{ old('allergy_history', $savedData['allergy_history'] ?? '') }}</textarea>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-medium">Social History</label>
            <textarea name="social_history" class="form-control" rows="2" placeholder="Smoking, alcohol...">{{ old('social_history', $savedData['social_history'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <a href="{{ route('doctor.queue') }}" class="btn btn-outline-secondary"><span class="material-symbols-outlined align-middle me-1">arrow_back</span> Back</a>
        <button type="button" class="btn btn-primary" onclick="saveTab(1)">Save & Continue <span class="material-symbols-outlined align-middle ms-1">arrow_forward</span></button>
    </div>
</div>

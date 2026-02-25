<div class="card border-0 rounded-3 sticky-top" style="top: 80px;">
    <div class="card-header bg-{{ $priority == 'Red' ? 'danger' : ($priority == 'Yellow' ? 'warning' : 'primary') }} text-{{ $priority == 'Yellow' ? 'dark' : 'white' }} p-3">
        <div class="d-flex align-items-center">
            <div class="wh-45 bg-white rounded-circle d-flex align-items-center justify-content-center me-2 overflow-hidden">
                @if($encounter->patient->enrollee_photo ?? false)
                    <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/'$encounter->patient->enrollee_photo) }}" class="wh-45 object-fit-cover" alt="">
                @else
                    <span class="material-symbols-outlined text-primary">person</span>
                @endif
            </div>
            <div>
                <strong class="d-block">{{ Str::limit($encounter->patient->enrollee_name ?? 'N/A', 18) }}</strong>
                <small>{{ $encounter->patient->enrollee_gender ?? '' }} | {{ $encounter->patient->enrollee_dob ? \Carbon\Carbon::parse($encounter->patient->enrollee_dob)->age . 'yrs' : '' }}</small>
            </div>
        </div>
    </div>
    @if($encounter->reason_for_visit)
    <div class="card-body p-3 border-bottom bg-light">
        <small class="text-muted d-block">Chief Complaint:</small>
        <small class="fw-medium text-danger">{{ $encounter->reason_for_visit }}</small>
    </div>
    @endif
    @if($vitalSign)
    <div class="card-body p-3">
        <small class="text-muted d-block mb-2">Vital Signs</small>
        <div class="row g-1 text-center small">
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">Temp</div><strong>{{ $vitalSign->temperature }}°</strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">BP</div><strong>{{ $vitalSign->blood_pressure_systolic }}/{{ $vitalSign->blood_pressure_diastolic }}</strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">Pulse</div><strong>{{ $vitalSign->pulse_rate }}</strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">SpO2</div><strong>{{ $vitalSign->spo2 }}%</strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">Resp</div><strong>{{ $vitalSign->respiration_rate }}</strong></div></div>
            <div class="col-4"><div class="bg-light rounded p-1"><div style="font-size:10px" class="text-muted">Wt</div><strong>{{ $vitalSign->weight ?? '-' }}</strong></div></div>
        </div>
    </div>
    @endif
</div>

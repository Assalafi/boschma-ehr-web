@if($encounters->count() > 0)
<div class="table-responsive">
  <table class="table table-hover align-middle" style="font-size:13px">
    <thead class="bg-light">
      <tr>
        <th>Patient</th>
        <th>ID</th>
        <th>Program</th>
        <th>Ward/Bed</th>
        <th>Admission Type</th>
        <th>Admitted By</th>
        <th>Admission Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($encounters as $enc)
        @php
          $patient = $enc->patient;
          $admission = $enc->admission;
        @endphp
        <tr>
          <td>
            <div class="d-flex align-items-center">
              @if($patient->enrollee_photo)
                <img src="{{ $patient->enrollee_photo }}" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover">
              @else
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;color:#fff;font-size:12px">
                  {{ strtoupper(substr($patient->enrollee_name ?? 'U', 0, 1)) }}
                </div>
              @endif
              <div>
                <div class="fw-medium">{{ $patient->enrollee_name ?? 'Unknown' }}</div>
                <div class="text-muted small">{{ $patient->enrollee_gender }}, {{ \Carbon\Carbon::parse($patient->enrollee_dob)->age ?? '?' }}y</div>
              </div>
            </div>
          </td>
          <td>
            <span class="badge bg-light text-dark">{{ $patient->enrollee_number }}</span>
          </td>
          <td>
            <span class="badge bg-light text-dark">{{ $enc->program->name ?? '-' }}</span>
          </td>
          <td>
            @if($admission)
              <div>
                <div class="fw-medium">{{ $admission->ward->name ?? '-' }}</div>
                <div class="text-muted small">Bed: {{ $admission->bed->name ?? 'Not assigned' }}</div>
              </div>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>
            <span class="badge {{ $admission->admission_type === 'emergency' ? 'bg-danger' : ($admission->admission_type === 'elective' ? 'bg-primary' : 'bg-warning') }} text-white">
              {{ ucfirst($admission->admission_type ?? '-') }}
            </span>
          </td>
          <td>
            <div class="small">
              <div>{{ $admission->admittedBy->name ?? '-' }}</div>
            </div>
          </td>
          <td>
            <div class="small">
              {{ $admission->admission_date->format('M j, Y H:i') ?? '-' }}
            </div>
          </td>
          <td>
            <div class="btn-group btn-group-sm">
              @if($enc->consultations->first())
                <a href="{{ route('doctor.consultation.show', $enc->consultations->first()->id) }}" class="btn btn-outline-primary btn-sm" title="View Consultation">
                  <span class="material-symbols-outlined" style="font-size:16px">visibility</span>
                </a>
                <a href="{{ route('doctor.consultation.start', $enc->id) }}?continue=1" class="btn btn-success btn-sm" title="Continue Consultation">
                  <span class="material-symbols-outlined" style="font-size:16px">play_arrow</span> Continue
                </a>
              @endif
              @if($admission)
                <button type="button" class="btn btn-outline-info btn-sm" onclick="manageAdmission('{{ $enc->id }}', '{{ $admission->id }}')" title="Manage Admission">
                  <span class="material-symbols-outlined" style="font-size:16px">bed</span>
                </button>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@else
<div class="text-center py-5 text-muted">
  <span class="material-symbols-outlined" style="font-size:48px;display:block;margin:0 auto 16px;opacity:0.3">local_hospital</span>
  No admitted patients found
</div>
@endif

<script>
function manageAdmission(encounterId, admissionId) {
  // TODO: Open modal to manage admission (discharge, transfer, etc.)
  console.log('Manage admission:', encounterId, admissionId);
}
</script>

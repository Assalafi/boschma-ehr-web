@extends('layouts.app')
@section('title', 'Manage Admission')
@section('content')

<style>
:root { --nurse-primary: #016634; --nurse-primary-dark: #01552b; --nurse-primary-light: #e6f5ed; }
.nurse-page { font-size: 14px; }
.nurse-header { background: linear-gradient(135deg, var(--nurse-primary-dark), var(--nurse-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.nurse-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 20px; }
.nurse-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.nurse-badge-emergency { background: #fee2e2; color: #991b1b; }
.nurse-badge-elective { background: #dbeafe; color: #1e40af; }
.nurse-badge-observation { background: #fef3c7; color: #92400e; }
.nurse-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.nurse-btn-primary { background: var(--nurse-primary); color: #fff; }
.nurse-btn-primary:hover { background: var(--nurse-primary-dark); color: #fff; }
</style>

<div class="nurse-page">

{{-- Header --}}
<div class="nurse-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}" style="color:rgba(255,255,255,.7)">Nurse Station</a></li>
        <li class="breadcrumb-item"><a href="{{ route('nurse.admitted.index') }}" style="color:rgba(255,255,255,.7)">Admitted Patients</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Manage Admission</li>
      </ol>
    </nav>
    <h4 class="mb-0">Manage Admission</h4>
  </div>
  <a href="{{ route('nurse.admitted.index') }}" class="nurse-btn" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Back to List
  </a>
</div>

<div class="row">
  {{-- Patient Info --}}
  <div class="col-md-4">
    <div class="nurse-card">
      <div class="card-body">
        <h6 class="card-title mb-3">Patient Information</h6>
        @php
          $patient = $admission->encounter->patient;
        @endphp
        <div class="text-center mb-3">
          @if($patient->enrollee_photo)
            <img src="{{ $patient->enrollee_photo }}" class="rounded-circle" style="width:80px;height:80px;object-fit:cover">
          @else
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width:80px;height:80px;color:#fff;font-size:32px">
              {{ strtoupper(substr($patient->enrollee_name ?? 'U', 0, 1)) }}
            </div>
          @endif
        </div>
        <table class="table table-sm">
          <tr>
            <td class="text-muted" style="width:40%">Name:</td>
            <td class="fw-medium">{{ $patient->enrollee_name ?? 'Unknown' }}</td>
          </tr>
          <tr>
            <td class="text-muted">ID:</td>
            <td><span class="badge bg-light text-dark">{{ $patient->enrollee_number }}</span></td>
          </tr>
          <tr>
            <td class="text-muted">Gender:</td>
            <td>{{ $patient->enrollee_gender }}</td>
          </tr>
          <tr>
            <td class="text-muted">Age:</td>
            <td>{{ \Carbon\Carbon::parse($patient->enrollee_dob)->age ?? '?' }} years</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  {{-- Admission Details & Room/Bed Management --}}
  <div class="col-md-8">
    <div class="nurse-card">
      <div class="card-body">
        <h6 class="card-title mb-3">Admission Details</h6>
        
        <form method="POST" action="{{ route('nurse.admitted.update-room-bed', $admission) }}">
          @csrf
          @method('PUT')
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-medium">Admission Type</label>
              <div>
                <span class="nurse-badge {{ $admission->admission_type === 'emergency' ? 'nurse-badge-emergency' : ($admission->admission_type === 'elective' ? 'nurse-badge-elective' : 'nurse-badge-observation') }}">
                  {{ ucfirst($admission->admission_type) }}
                </span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-medium">Admission Date</label>
              <div class="form-control-plaintext">{{ $admission->admission_date->format('M j, Y H:i') }}</div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label small fw-medium">Ward *</label>
              <select name="ward_id" id="ward_id" class="form-select" required onchange="loadBeds(this.value)">
                <option value="">Select Ward</option>
                @foreach($wards as $ward)
                  <option value="{{ $ward->id }}" {{ $admission->ward_id == $ward->id ? 'selected' : '' }}>{{ $ward->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-medium">Bed</label>
              <select name="bed_id" id="bed_id" class="form-select">
                <option value="">Select Bed (Optional)</option>
                @foreach($availableBeds as $bed)
                  <option value="{{ $bed->id }}" {{ $admission->bed_id == $bed->id ? 'selected' : '' }}>
                    {{ $bed->name }} {{ $bed->is_occupied ? '(Occupied)' : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label small fw-medium">Admitted By</label>
              <div class="form-control-plaintext">{{ $admission->admittedBy->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-medium">Condition on Admission</label>
              <div class="form-control-plaintext">{{ $admission->condition_on_admission ?? '-' }}</div>
            </div>
            
            @if($admission->admission_notes)
              <div class="col-12">
                <label class="form-label small fw-medium">Admission Notes</label>
                <div class="form-control-plaintext">{{ $admission->admission_notes }}</div>
              </div>
            @endif
            
            <div class="col-12">
              <button type="submit" class="nurse-btn nurse-btn-primary">
                <span class="material-symbols-outlined" style="font-size:16px">save</span> Update Room/Bed
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    
    {{-- Recent Consultations --}}
    @if($admission->encounter->consultations->count() > 0)
      <div class="nurse-card">
        <div class="card-body">
          <h6 class="card-title mb-3">Recent Consultations</h6>
          <div class="table-responsive">
            <table class="table table-sm" style="font-size:12px">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Doctor</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($admission->encounter->consultations->sortByDesc('updated_at')->take(5) as $consultation)
                  <tr>
                    <td>{{ $consultation->updated_at->format('M j, Y H:i') }}</td>
                    <td>{{ $consultation->doctor->name ?? '-' }}</td>
                    <td>
                      <span class="badge {{ $consultation->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">
                        {{ $consultation->status }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('doctor.consultation.show', $consultation->id) }}" class="btn btn-sm btn-outline-primary">
                        <span class="material-symbols-outlined" style="font-size:14px">visibility</span>
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

</div>

<script>
function loadBeds(wardId) {
    if (!wardId) {
        document.getElementById('bed_id').innerHTML = '<option value="">Select Ward First</option>';
        return;
    }
    
    fetch(`/api/wards/${wardId}/beds`)
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">Select Bed (Optional)</option>';
            data.beds.forEach(bed => {
                const occupiedText = bed.is_occupied ? ' (Occupied)' : '';
                html += `<option value="${bed.id}">${bed.name}${occupiedText}</option>`;
            });
            document.getElementById('bed_id').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading beds:', error);
        });
}

// Load beds for current ward on page load
document.addEventListener('DOMContentLoaded', function() {
    const wardId = document.getElementById('ward_id').value;
    if (wardId) {
        loadBeds(wardId);
    }
});
</script>
@endsection

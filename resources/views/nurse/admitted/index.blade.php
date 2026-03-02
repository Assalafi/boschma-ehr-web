@extends('layouts.app')
@section('title', 'Admitted Patients')
@section('content')

<style>
:root { --nurse-primary: #016634; --nurse-primary-dark: #01552b; --nurse-primary-light: #e6f5ed; }
.nurse-page { font-size: 14px; }
.nurse-header { background: linear-gradient(135deg, var(--nurse-primary-dark), var(--nurse-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.nurse-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
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
        <li class="breadcrumb-item active" style="color:#fff">Admitted Patients</li>
      </ol>
    </nav>
    <h4 class="mb-0">Admitted Patients</h4>
  </div>
  <a href="{{ route('nurse.dashboard') }}" class="nurse-btn" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

{{-- Filters --}}
<div class="nurse-card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label small fw-medium">Search Patient</label>
        <input type="text" name="search" class="form-control" placeholder="Name or ID..." value="{{ request('search') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-medium">Ward</label>
        <select name="ward_id" class="form-select">
          <option value="">All Wards</option>
          @foreach($wards as $id=>$name)
            <option value="{{ $id }}" {{ request('ward_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="nurse-btn nurse-btn-primary w-100">
          <span class="material-symbols-outlined" style="font-size:16px">search</span> Search
        </button>
      </div>
      <div class="col-md-2">
        <a href="{{ route('nurse.admitted.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
      </div>
    </form>
  </div>
</div>

{{-- Admitted Patients List --}}
<div class="nurse-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle" style="font-size:13px">
      <thead class="bg-light">
        <tr>
          <th>Patient</th>
          <th>ID</th>
          <th>Ward/Bed</th>
          <th>Admission Type</th>
          <th>Admitted By</th>
          <th>Admission Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($admissions as $admission)
          @php
            $patient = $admission->encounter->patient;
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
              <div>
                <div class="fw-medium">{{ $admission->ward->name ?? '-' }}</div>
                <div class="text-muted small">Bed: {{ $admission->bed->name ?? 'Not assigned' }}</div>
              </div>
            </td>
            <td>
              <span class="nurse-badge {{ $admission->admission_type === 'emergency' ? 'nurse-badge-emergency' : ($admission->admission_type === 'elective' ? 'nurse-badge-elective' : 'nurse-badge-observation') }}">
                {{ ucfirst($admission->admission_type) }}
              </span>
            </td>
            <td>
              <div class="small">
                <div>{{ $admission->admittedBy->name ?? '-' }}</div>
              </div>
            </td>
            <td>
              <div class="small">
                {{ $admission->admission_date->format('M j, Y H:i') }}
              </div>
            </td>
            <td>
              <a href="{{ route('nurse.admitted.show', $admission) }}" class="btn btn-sm btn-primary">
                <span class="material-symbols-outlined" style="font-size:16px">visibility</span> Manage
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <span class="material-symbols-outlined" style="font-size:48px;display:block;margin:0 auto 16px;opacity:0.3">bed</span>
              No admitted patients found
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  {{-- Pagination --}}
  @if($admissions->hasPages())
    <div class="card-footer">
      {{ $admissions->links() }}
    </div>
  @endif
</div>

</div>
@endsection

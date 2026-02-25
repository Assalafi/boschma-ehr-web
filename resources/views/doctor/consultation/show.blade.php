@extends('layouts.app')
@section('title', 'Consultation Details')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-teal { background: var(--doc-primary-light); color: var(--doc-primary-dark); }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.doc-btn-success { background: #059669; color: #fff; }
.doc-btn-success:hover { background: #047857; color: #fff; }
.doc-btn-info { background: #0284c7; color: #fff; }
.doc-btn-info:hover { background: #0369a1; color: #fff; }
.patient-sidebar { position: sticky; top: 80px; }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row .info-label { color: #94a3b8; }
.info-row .info-value { font-weight: 600; color: #1e293b; }
.dx-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 10px; font-size: 12px; margin: 3px; }
.dx-chip-confirmed { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.dx-chip-provisional { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.rx-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.rx-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--doc-border); }
.rx-table tbody td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.rx-table tbody tr:hover { background: #f8fafb; }
.section-body { padding: 20px; }
.section-title-inline { font-size: 12px; font-weight: 700; color: var(--doc-primary); text-transform: uppercase; letter-spacing: .3px; margin-bottom: 8px; }
</style>

@php
    $vitalSign = $consultation->encounter->vitalSigns->first();
    $priority = $vitalSign?->overall_priority ?? 'Green';
    $pc = [
        'Red'=>'red','Yellow'=>'amber','Green'=>'green',
        'High'=>'red','high'=>'red',
        'Critical'=>'red','critical'=>'critical',
        'Urgent'=>'amber','urgent'=>'urgent',
        'Normal'=>'green','normal'=>'normal'
    ];
    $beneficiary = $consultation->encounter->patient->enrollee;
@endphp

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item"><a href="{{ route('doctor.consultation.history') }}" style="color:rgba(255,255,255,.7)">History</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Consultation</li>
      </ol>
    </nav>
    <h4 class="mb-0">Consultation Details</h4>
  </div>
  <span class="doc-badge doc-badge-{{ $consultation->status == 'Completed' ? 'green' : 'amber' }}" style="font-size:13px;padding:6px 16px">{{ $consultation->status }}</span>
</div>

<div class="row g-3">
  {{-- Sidebar --}}
  <div class="col-lg-4">
    <div class="patient-sidebar">
      <div class="doc-card">
        <div style="background:{{ $priority == 'Red' ? '#dc2626' : ($priority == 'Yellow' ? '#d97706' : 'var(--doc-primary)') }};color:#fff;padding:20px;text-align:center">
          <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;overflow:hidden">
            @if($consultation->encounter->patient->enrollee_photo ?? false)
              <img src="{{ $consultation->encounter->patient->enrollee_photo }}" style="width:100%;height:100%;object-fit:cover" alt="">
            @else
              <span class="material-symbols-outlined" style="font-size:28px">person</span>
            @endif
          </div>
          <h5 style="margin:0 0 4px;color:#fff;font-weight:700;font-size:16px">{{ $consultation->encounter->patient->enrollee_name ?? 'N/A' }}</h5>
          <span style="display:inline-block;padding:3px 12px;border-radius:20px;background:rgba(255,255,255,.2);font-size:12px">{{ $consultation->encounter->patient->enrollee_number ?? '' }}</span>
        </div>
        <div style="padding:16px 20px">
          <div class="info-row"><span class="info-label">Doctor</span><span class="info-value">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</span></div>
          <div class="info-row"><span class="info-label">Priority</span><span class="doc-badge doc-badge-{{ $pc[$priority] ?? 'green' }}">{{ $priority }}</span></div>
          <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $consultation->created_at->format('d M Y, H:i') }}</span></div>
        </div>
      </div>

      @if($consultation->status == 'Completedss')
      <div class="doc-card">
        <div style="padding:16px 20px;display:flex;flex-direction:column;gap:8px">
          <button class="doc-btn doc-btn-primary" style="justify-content:center" data-bs-toggle="modal" data-bs-target="#prescriptionModal">
            <span class="material-symbols-outlined" style="font-size:16px">medication</span> Add Prescription
          </button>
          <button class="doc-btn doc-btn-info" style="justify-content:center" data-bs-toggle="modal" data-bs-target="#investigationModal">
            <span class="material-symbols-outlined" style="font-size:16px">biotech</span> Request Investigation
          </button>
          <button class="doc-btn doc-btn-success" style="justify-content:center" data-bs-toggle="modal" data-bs-target="#completeModal">
            <span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Complete Consultation
          </button>
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Main --}}
  <div class="col-lg-8">
    {{-- Clinical Assessment --}}
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">stethoscope</span> Clinical Assessment</div>
      <div class="section-body">
        <div class="section-title-inline">Presenting Complaints</div>
        <p style="color:#334155;line-height:1.6;margin-bottom:16px">{{ $consultation->presenting_complaints ?? 'Not documented' }}</p>
        @if($consultation->physical_examination)
        <div class="section-title-inline">Physical Examination</div>
        <p style="color:#334155;line-height:1.6;margin-bottom:0">{{ $consultation->physical_examination }}</p>
        @endif
      </div>
    </div>

    {{-- Diagnoses --}}
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">diagnosis</span> Diagnoses</div>
      <div class="section-body">
        @forelse($consultation->diagnoses as $diagnosis)
        <div class="dx-chip {{ $diagnosis->diagnosis_type == 'Confirmed' ? 'dx-chip-confirmed' : 'dx-chip-provisional' }}">
          <span class="material-symbols-outlined" style="font-size:14px">{{ $diagnosis->diagnosis_type == 'Confirmed' ? 'check_circle' : 'pending' }}</span>
          <strong>{{ $diagnosis->icdCode->code ?? '' }}</strong> — {{ $diagnosis->icdCode->description ?? 'Unknown' }}
          <span style="font-size:10px;opacity:.7">({{ $diagnosis->diagnosis_type }})</span>
        </div>
        @empty
        <p style="color:#94a3b8;text-align:center;margin:0">No diagnoses recorded</p>
        @endforelse
      </div>
    </div>

    {{-- Prescriptions --}}
    <div class="doc-card">
      <div class="doc-card-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">medication</span> Prescriptions</div>
      @if($consultation->prescriptions->flatMap->items->isNotEmpty())
      <div style="overflow-x:auto">
        <table class="rx-table">
          <thead><tr><th>Drug</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Status</th></tr></thead>
          <tbody>
            @foreach($consultation->prescriptions->flatMap->items as $item)
            <tr>
              <td style="font-weight:600;color:#1e293b">{{ $item->drug->name ?? 'Unknown' }}</td>
              <td>{{ $item->dosage }}</td>
              <td>{{ $item->frequency }}x/day</td>
              <td>{{ $item->duration }} days</td>
              <td><span class="doc-badge {{ $item->dispensing_status == 'Dispensed' ? 'doc-badge-green' : 'doc-badge-amber' }}">{{ $item->dispensing_status }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div style="text-align:center;padding:32px;color:#94a3b8">
        <span class="material-symbols-outlined" style="font-size:40px;opacity:.4">medication</span>
        <p style="margin:8px 0 0;font-size:13px">No prescriptions</p>
      </div>
      @endif
    </div>

    <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline"><span class="material-symbols-outlined" style="font-size:14px">arrow_back</span> Back to Dashboard</a>
  </div>
</div>

</div>

{{-- Complete Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('doctor.consultation.complete', $consultation) }}" method="POST">
      @csrf
      <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
        <div class="modal-header" style="background:var(--doc-primary);color:#fff;border-radius:16px 16px 0 0;padding:16px 20px">
          <h5 class="modal-title" style="font-weight:700;font-size:15px"><span class="material-symbols-outlined align-middle me-2" style="font-size:18px">check_circle</span>Complete Consultation</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:20px">
          <div class="mb-3">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Outcome <span style="color:#dc2626">*</span></label>
            <select name="outcome" class="form-select" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
              <option value="Improved">Improved - Discharge</option>
              <option value="Follow-up">Follow-up Required</option>
              <option value="Refer">Refer to Specialist</option>
              <option value="Admit">Admit</option>
              <option value="Discharged">Discharged</option>
            </select>
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Follow-up Date</label>
            <input type="date" name="follow_up_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
          </div>
          <div class="mb-0">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Final Notes</label>
            <textarea name="final_notes" class="form-control" rows="3" style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px;resize:none"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f1f5f9">
          <button type="button" class="doc-btn doc-btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="doc-btn doc-btn-success">Complete</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Prescription Modal --}}
<div class="modal fade" id="prescriptionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('doctor.consultation.prescription', $consultation) }}" method="POST">
      @csrf
      <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
        <div class="modal-header" style="background:var(--doc-primary);color:#fff;border-radius:16px 16px 0 0;padding:16px 20px">
          <h5 class="modal-title" style="font-weight:700;font-size:15px"><span class="material-symbols-outlined align-middle me-2" style="font-size:18px">medication</span>Add Prescription</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:20px">
          <div class="row g-3">
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Drug <span style="color:#dc2626">*</span></label>
              <select name="items[0][drug_id]" class="form-select" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
                <option value="">Select…</option>
                @foreach($drugs as $drug)<option value="{{ $drug->id }}">{{ $drug->name }}</option>@endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Dosage <span style="color:#dc2626">*</span></label>
              <input type="text" name="items[0][dosage]" class="form-control" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
            </div>
            <div class="col-md-4">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Frequency</label>
              <select name="items[0][frequency]" class="form-select" style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px"><option value="2">2x/day</option><option value="3">3x/day</option></select>
            </div>
            <div class="col-md-4">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Duration</label>
              <input type="number" name="items[0][duration]" class="form-control" value="7" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
            </div>
            <div class="col-md-4">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Quantity</label>
              <input type="number" name="items[0][quantity]" class="form-control" value="14" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
            </div>
            <div class="col-12">
              <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Instructions</label>
              <input type="text" name="items[0][instructions]" class="form-control" style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
            </div>
          </div>
        </div>
        <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f1f5f9">
          <button type="button" class="doc-btn doc-btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="doc-btn doc-btn-primary">Save Prescription</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Investigation Modal --}}
<div class="modal fade" id="investigationModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('doctor.consultation.investigation', $consultation) }}" method="POST">
      @csrf
      <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
        <div class="modal-header" style="background:#0284c7;color:#fff;border-radius:16px 16px 0 0;padding:16px 20px">
          <h5 class="modal-title" style="font-weight:700;font-size:15px"><span class="material-symbols-outlined align-middle me-2" style="font-size:18px">biotech</span>Request Investigation</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:20px">
          <div class="mb-3">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Type <span style="color:#dc2626">*</span></label>
            <select name="type" class="form-select" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
              <option value="Laboratory">Laboratory</option>
              <option value="Radiology">Radiology</option>
            </select>
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Category <span style="color:#dc2626">*</span></label>
            <input type="text" name="category" class="form-control" placeholder="e.g., Hematology" required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px">
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Tests <span style="color:#dc2626">*</span></label>
            <select name="tests[]" class="form-select" multiple required style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px;min-height:140px">
              <option value="Full Blood Count">Full Blood Count</option>
              <option value="Urinalysis">Urinalysis</option>
              <option value="Malaria Test">Malaria Test</option>
              <option value="Blood Sugar">Blood Sugar</option>
              <option value="Liver Function">Liver Function</option>
              <option value="Kidney Function">Kidney Function</option>
              <option value="X-Ray">X-Ray</option>
              <option value="Ultrasound">Ultrasound</option>
            </select>
          </div>
          <div class="mb-0">
            <label style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:4px">Notes</label>
            <textarea name="notes" class="form-control" rows="2" style="border-radius:8px;border:1.5px solid #e2e8f0;font-size:13px;resize:none"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="padding:12px 20px;border-top:1px solid #f1f5f9">
          <button type="button" class="doc-btn doc-btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="doc-btn doc-btn-info">Request</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@extends('layouts.app')
@section('title', 'Patient Queue')
@section('content')

<style>
:root { --doc-primary: #016634; --doc-primary-dark: #01552b; --doc-primary-light: #e6f5ed; --doc-border: #e2e8f0; --bs-primary:#016634; --bs-primary-rgb:1,102,52; }
.doc-page { font-size: 14px; }
.doc-header { background: linear-gradient(135deg, var(--doc-primary-dark), var(--doc-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.doc-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.doc-card { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.doc-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--doc-border); display: flex; align-items: center; gap: 8px; color: #1e293b; }
.doc-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.doc-badge-red { background: #fee2e2; color: #991b1b; }
.doc-badge-amber { background: #fef3c7; color: #92400e; }
.doc-badge-green { background: #dcfce7; color: #166534; }
.doc-badge-blue { background: #dbeafe; color: #1e40af; }
.doc-badge-gray { background: #f1f5f9; color: #475569; }
.doc-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.doc-btn-primary { background: var(--doc-primary); color: #fff; }
.doc-btn-primary:hover { background: var(--doc-primary-dark); color: #fff; }
.doc-btn-outline { background: transparent; border: 1.5px solid var(--doc-border); color: #64748b; }
.doc-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.stat-mini { background: #fff; border-radius: 14px; border: 1px solid var(--doc-border); padding: 18px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.stat-mini-value { font-size: 28px; font-weight: 800; line-height: 1; }
.stat-mini-label { font-size: 11px; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: .3px; margin-top: 4px; }
.q-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.q-table thead th { background: #f8fafc; padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--doc-border); white-space: nowrap; }
.q-table tbody td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.q-table tbody tr:hover { background: #f8fafb; }
</style>

<div class="doc-page">

<div class="doc-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}" style="color:rgba(255,255,255,.7)">Doctor Station</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Patient Queue</li>
      </ol>
    </nav>
    <h4 class="mb-0">Patient Queue</h4>
  </div>
  <a href="{{ route('doctor.dashboard') }}" class="doc-btn doc-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Dashboard
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-value" style="color:#d97706">{{ $waitingCount ?? 0 }}</div>
      <div class="stat-mini-label">Waiting</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-value" style="color:#2563eb">{{ $inConsultationCount ?? 0 }}</div>
      <div class="stat-mini-label">In Consultation</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-mini">
      <div class="stat-mini-value" style="color:#059669">{{ $completedCount ?? 0 }}</div>
      <div class="stat-mini-label">Completed Today</div>
    </div>
  </div>
</div>

<div class="doc-card">
  <div class="doc-card-header">
    <span class="material-symbols-outlined" style="font-size:16px;color:var(--doc-primary)">queue</span> Patients Waiting for Consultation
  </div>
  <div style="overflow-x:auto">
    <table class="q-table">
      <thead>
        <tr><th>Queue</th><th>Patient</th><th>BOSCHMA ID</th><th>Priority</th><th>Vitals</th><th>Wait Time</th><th style="text-align:center">Actions</th></tr>
      </thead>
      <tbody>
        @forelse($encounters ?? [] as $index => $encounter)
        @php
          $vitals = $encounter->vitalSigns->last();
          $priorityColors = [1 => 'red', 2 => 'red', 3 => 'amber', 4 => 'green', 5 => 'blue'];
          $priorityLabels = [1 => 'Immediate', 2 => 'Urgent', 3 => 'Less Urgent', 4 => 'Non-Urgent', 5 => 'Minor'];
        @endphp
        <tr>
          <td><span class="doc-badge doc-badge-gray">{{ $index + 1 }}</span></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center">
                <span class="material-symbols-outlined" style="font-size:18px;color:#94a3b8">person</span>
              </div>
              <div>
                <div style="font-weight:600;color:#1e293b;font-size:13px">{{ $encounter->patient_name }}</div>
                <div style="font-size:11px;color:#94a3b8">{{ $encounter->nature_of_visit }}</div>
              </div>
            </div>
          </td>
          <td><span class="doc-badge doc-badge-blue">{{ $encounter->patient_boschma_no }}</span></td>
          <td>
            @if($vitals)
            <span class="doc-badge doc-badge-{{ $priorityColors[$vitals->overall_priority] ?? 'gray' }}">{{ $priorityLabels[$vitals->overall_priority] ?? 'Unknown' }}</span>
            @else <span class="doc-badge doc-badge-gray">N/A</span> @endif
          </td>
          <td style="font-size:11px;color:#475569">
            @if($vitals)
              <strong>BP:</strong> {{ $vitals->blood_pressure_systolic }}/{{ $vitals->blood_pressure_diastolic }}<br>
              <strong>T:</strong> {{ $vitals->temperature }}°C | <strong>P:</strong> {{ $vitals->pulse_rate }}
            @else <span style="color:#94a3b8">No vitals</span> @endif
          </td>
          <td>
            @php $waitTime = $encounter->visit_date->diffForHumans(null, true); @endphp
            <span class="doc-badge {{ $encounter->visit_date->diffInMinutes() > 30 ? 'doc-badge-red' : 'doc-badge-gray' }}">{{ $waitTime }}</span>
          </td>
          <td style="text-align:center">
            <a href="{{ route('consultations.create', $encounter->id) }}" class="doc-btn doc-btn-primary" style="padding:6px 12px">
              <span class="material-symbols-outlined" style="font-size:14px">medical_services</span> Consult
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:48px 20px">
            <span class="material-symbols-outlined" style="font-size:52px;color:#059669;opacity:.5">check_circle</span>
            <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No patients waiting</h5>
            <p style="color:#94a3b8;font-size:13px">All patients have been attended to</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

</div>
@endsection

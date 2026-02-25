@extends('layouts.app')
@section('title', 'Pharmacy Dashboard')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.stat-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: 22px; display: flex; align-items: center; gap: 16px; transition: transform .15s, box-shadow .15s; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon .material-symbols-outlined { font-size: 26px; }
.stat-label { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .4px; }
.stat-value { font-size: 26px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-link { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; margin-top: 6px; text-decoration: none; transition: gap .15s; }
.stat-link:hover { gap: 8px; }
.action-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); padding: 24px; text-align: center; text-decoration: none !important; color: #1e293b !important; transition: all .15s; display: flex; flex-direction: column; align-items: center; gap: 10px; height: 100%; }
.action-card:hover { border-color: var(--pharm-primary); box-shadow: 0 4px 12px rgba(1,102,52,.12); transform: translateY(-2px); }
.action-card .action-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
.action-card .action-icon .material-symbols-outlined { font-size: 28px; }
.action-card .action-label { font-weight: 600; font-size: 13px; }
.action-card .action-desc { font-size: 11px; color: #94a3b8; }
.section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.section-title .material-symbols-outlined { font-size: 18px; color: var(--pharm-primary); }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header">
  <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
    <ol class="breadcrumb mb-0" style="font-size:12px">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,.7)">Home</a></li>
      <li class="breadcrumb-item active" style="color:#fff">Pharmacy</li>
    </ol>
  </nav>
  <h4 class="mb-1">Pharmacy Dashboard</h4>
  <p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:0">
    <span class="material-symbols-outlined align-middle me-1" style="font-size:15px">calendar_today</span>{{ now()->format('l, d M Y') }}
    <span class="mx-2" style="opacity:.3">|</span>
    <span class="material-symbols-outlined align-middle me-1" style="font-size:15px">schedule</span>{{ now()->format('H:i') }}
  </p>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fef3c7">
        <span class="material-symbols-outlined" style="color:#d97706">prescriptions</span>
      </div>
      <div>
        <div class="stat-label">Pending Rx</div>
        <div class="stat-value">{{ $pendingPrescriptions ?? 0 }}</div>
        <a href="{{ route('pharmacy.queue') }}" class="stat-link" style="color:#d97706">View Queue <span class="material-symbols-outlined" style="font-size:14px">arrow_forward</span></a>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dcfce7">
        <span class="material-symbols-outlined" style="color:#059669">check_circle</span>
      </div>
      <div>
        <div class="stat-label">Dispensed Today</div>
        <div class="stat-value">{{ $todayDispensations ?? 0 }}</div>
        <a href="{{ route('pharmacy.history') }}" class="stat-link" style="color:#059669">View History <span class="material-symbols-outlined" style="font-size:14px">arrow_forward</span></a>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dbeafe">
        <span class="material-symbols-outlined" style="color:#2563eb">payments</span>
      </div>
      <div>
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value" style="font-size:22px">GHS {{ number_format($todayRevenue ?? 0, 2) }}</div>
        <span class="stat-link" style="color:#2563eb;cursor:default">From dispensations</span>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fee2e2">
        <span class="material-symbols-outlined" style="color:#dc2626">warning</span>
      </div>
      <div>
        <div class="stat-label">Low Stock Alerts</div>
        <div class="stat-value">{{ $lowStockDrugs ?? 0 }}</div>
        <a href="{{ route('stock-management.index') }}" class="stat-link" style="color:#dc2626">Manage Stock <span class="material-symbols-outlined" style="font-size:14px">arrow_forward</span></a>
      </div>
    </div>
  </div>
</div>

{{-- Quick Actions --}}
<div class="section-title">
  <span class="material-symbols-outlined">bolt</span> Quick Actions
</div>
<div class="row g-3">
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('pharmacy.queue') }}" class="action-card">
      <div class="action-icon" style="background:#fef3c7">
        <span class="material-symbols-outlined" style="color:#d97706">queue</span>
      </div>
      <div class="action-label">Dispensation Queue</div>
      <div class="action-desc">View & process pending prescriptions</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('pharmacy.history') }}" class="action-card">
      <div class="action-icon" style="background:#dcfce7">
        <span class="material-symbols-outlined" style="color:#059669">history</span>
      </div>
      <div class="action-label">Dispensation History</div>
      <div class="action-desc">Browse completed dispensations</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('stock-management.index') }}" class="action-card">
      <div class="action-icon" style="background:#dbeafe">
        <span class="material-symbols-outlined" style="color:#2563eb">warehouse</span>
      </div>
      <div class="action-label">Stock Management</div>
      <div class="action-desc">Manage drug inventory & stock</div>
    </a>
  </div>
  <div class="col-lg-3 col-sm-6">
    <a href="{{ route('dashboard') }}" class="action-card">
      <div class="action-icon" style="background:var(--pharm-primary-light)">
        <span class="material-symbols-outlined" style="color:var(--pharm-primary)">dashboard</span>
      </div>
      <div class="action-label">Main Dashboard</div>
      <div class="action-desc">Return to system overview</div>
    </a>
  </div>
</div>

</div>
@endsection

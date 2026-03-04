@extends('layouts.app')
@section('title', 'Pharmacy — Drug Inventory')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px; color: #fff; }
.pharm-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.pharm-card-header { padding: 16px 20px; border-bottom: 1px solid var(--pharm-border); display: flex; align-items: center; justify-content: space-between; }
.pharm-card-header h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .4px; color: #475569; margin: 0; display: flex; align-items: center; gap: 8px; }
.pharm-card-body { padding: 20px; }
.stat-tile { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: 18px; display: flex; align-items: center; gap: 14px; transition: transform .15s, box-shadow .15s; height: 100%; }
.stat-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.stat-tile .tile-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-tile .tile-icon .material-symbols-outlined { font-size: 24px; }
.stat-tile .tile-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.stat-tile .tile-value { font-size: 22px; font-weight: 800; color: #1e293b; line-height: 1.2; }
.program-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid var(--pharm-border); background: #fff; transition: all .15s; cursor: pointer; text-decoration: none !important; color: #475569 !important; }
.program-chip:hover, .program-chip.active { background: var(--pharm-primary); color: #fff !important; border-color: var(--pharm-primary); }
.program-chip .chip-count { background: rgba(0,0,0,.08); padding: 1px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; }
.program-chip.active .chip-count { background: rgba(255,255,255,.25); }
.drug-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.drug-table thead th { background: #f8fafc; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #64748b; padding: 10px 14px; border-bottom: 1px solid var(--pharm-border); position: sticky; top: 0; }
.drug-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 13px; }
.drug-table tbody tr:hover { background: #f8fafc; }
.drug-table tbody tr:last-child td { border-bottom: 0; }
.stock-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
.stock-badge.in-stock { background: #dcfce7; color: #059669; }
.stock-badge.low-stock { background: #fef3c7; color: #d97706; }
.stock-badge.out-stock { background: #fee2e2; color: #dc2626; }
.stock-badge.expiring { background: #fce7f3; color: #db2777; }
.stock-bar { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; width: 80px; }
.stock-bar .fill { height: 100%; border-radius: 3px; transition: width .3s; }
.program-tag { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; background: #ede9fe; color: #7c3aed; margin-right: 4px; }
.search-input { border: 1px solid var(--pharm-border); border-radius: 10px; padding: 8px 14px 8px 36px; font-size: 13px; background: #f8fafc; transition: border-color .15s; width: 100%; }
.search-input:focus { border-color: var(--pharm-primary); outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(1,102,52,.08); }
.search-wrap { position: relative; }
.search-wrap .material-symbols-outlined { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #94a3b8; }
.expiry-row { font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 4px; margin-top: 2px; }
.batch-detail { font-size: 11px; color: #64748b; line-height: 1.6; }
.pharm-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; }
.pharm-nav a { padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; color: #64748b; background: #f1f5f9; transition: all .15s; }
.pharm-nav a:hover { background: #e2e8f0; }
.pharm-nav a.active { background: var(--pharm-primary); color: #fff; }
.filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header">
  <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
    <ol class="breadcrumb mb-0" style="font-size:12px">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,.7)">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
      <li class="breadcrumb-item active" style="color:#fff">Drug Inventory</li>
    </ol>
  </nav>
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1">Drug Inventory</h4>
      <p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:0">
        Complete view of all drugs and stock levels in your facility
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('pharmacy.reports') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;font-size:12px;font-weight:600">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">analytics</span>Reports
      </a>
    </div>
  </div>
</div>

{{-- Navigation --}}
<div class="pharm-nav">
  <a href="{{ route('pharmacy.dashboard') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">dashboard</span>Dashboard</a>
  <a href="{{ route('pharmacy.queue') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">queue</span>Queue</a>
  <a href="{{ route('pharmacy.drugs') }}" class="active"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">medication</span>Drugs</a>
  <a href="{{ route('pharmacy.history') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">history</span>History</a>
  <a href="{{ route('pharmacy.reports') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">analytics</span>Reports</a>
</div>

{{-- Summary Tiles --}}
<div class="row g-3 mb-4">
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="color:#2563eb">inventory_2</span></div>
      <div>
        <div class="tile-label">Total Drugs</div>
        <div class="tile-value">{{ number_format($totalDrugs) }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#dcfce7"><span class="material-symbols-outlined" style="color:#059669">check_circle</span></div>
      <div>
        <div class="tile-label">In Stock</div>
        <div class="tile-value" style="color:#059669">{{ number_format($inStockCount) }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#fee2e2"><span class="material-symbols-outlined" style="color:#dc2626">cancel</span></div>
      <div>
        <div class="tile-label">Out of Stock</div>
        <div class="tile-value" style="color:#dc2626">{{ number_format($outOfStockCount) }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#fef3c7"><span class="material-symbols-outlined" style="color:#d97706">warning</span></div>
      <div>
        <div class="tile-label">Low Stock</div>
        <div class="tile-value" style="color:#d97706">{{ number_format($lowStockCount) }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#fce7f3"><span class="material-symbols-outlined" style="color:#db2777">event_busy</span></div>
      <div>
        <div class="tile-label">Expiring (90d)</div>
        <div class="tile-value" style="color:#db2777">{{ number_format($expiringCount) }}</div>
      </div>
    </div>
  </div>
  <div class="col-xl-2 col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:var(--pharm-primary-light)"><span class="material-symbols-outlined" style="color:var(--pharm-primary)">payments</span></div>
      <div>
        <div class="tile-label">Stock Value</div>
        <div class="tile-value" style="font-size:16px;color:var(--pharm-primary)">₦{{ number_format($totalStockValue, 0) }}</div>
      </div>
    </div>
  </div>
</div>

{{-- Stock by Program --}}
@if($stockByProgram->count())
<div class="pharm-card mb-4">
  <div class="pharm-card-header">
    <h6><span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">category</span> Stock by Program</h6>
  </div>
  <div class="pharm-card-body" style="padding:14px 20px">
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('pharmacy.drugs', request()->except('program_id')) }}" class="program-chip {{ !$programId ? 'active' : '' }}">
        All Programs <span class="chip-count">{{ $totalDrugs }}</span>
      </a>
      @foreach($stockByProgram as $sp)
        <a href="{{ route('pharmacy.drugs', array_merge(request()->query(), ['program_id' => $sp->program_id])) }}" class="program-chip {{ $programId == $sp->program_id ? 'active' : '' }}">
          {{ $sp->program_name }}
          <span class="chip-count">{{ $sp->drug_count }} drugs · {{ number_format($sp->total_qty) }} units</span>
        </a>
      @endforeach
    </div>
  </div>
</div>
@endif

{{-- Filters & Search --}}
<div class="pharm-card mb-4">
  <div class="pharm-card-body" style="padding:14px 20px">
    <form method="GET" action="{{ route('pharmacy.drugs') }}" class="filter-bar">
      @if($programId)
        <input type="hidden" name="program_id" value="{{ $programId }}">
      @endif
      <div class="search-wrap" style="flex:1;min-width:200px">
        <span class="material-symbols-outlined">search</span>
        <input type="text" name="search" class="search-input" placeholder="Search drugs by name, form, or strength..." value="{{ request('search') }}">
      </div>
      <select name="stock_status" class="form-select form-select-sm" style="width:auto;border-radius:10px;font-size:12px;border-color:var(--pharm-border)">
        <option value="">All Stock Levels</option>
        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock (&lt;10)</option>
      </select>
      <button type="submit" class="btn btn-sm" style="background:var(--pharm-primary);color:#fff;border-radius:10px;font-size:12px;font-weight:600;padding:7px 16px">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:14px">filter_list</span>Filter
      </button>
      @if(request()->hasAny(['search', 'stock_status', 'program_id']))
        <a href="{{ route('pharmacy.drugs') }}" class="btn btn-sm" style="background:#f1f5f9;color:#64748b;border-radius:10px;font-size:12px;font-weight:600;padding:7px 16px">Clear</a>
      @endif
    </form>
  </div>
</div>

{{-- Drug Table --}}
<div class="pharm-card">
  <div class="pharm-card-header">
    <h6><span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">medication</span> Drug List <span style="font-weight:400;color:#94a3b8">({{ $drugs->total() }} drugs)</span></h6>
  </div>
  <div style="overflow-x:auto">
    <table class="drug-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Drug Name</th>
          <th>Form</th>
          <th>Strength</th>
          <th>Unit Price</th>
          <th style="text-align:center">Available Qty</th>
          <th>Stock Status</th>
          <th>Batches</th>
          <th>Nearest Expiry</th>
        </tr>
      </thead>
      <tbody>
        @forelse($drugs as $i => $drug)
        @php
          $totalQty = $drug->stocks->sum('quantity_remaining');
          $activeBatches = $drug->stocks->where('quantity_remaining', '>', 0);
          $nearestExpiry = $activeBatches->sortBy('expiry_date')->first()?->expiry_date;
          $maxQty = max($totalQty, 100);
          $barPct = $maxQty > 0 ? min(100, ($totalQty / $maxQty) * 100) : 0;
          $barColor = $totalQty == 0 ? '#dc2626' : ($totalQty < 10 ? '#d97706' : '#059669');
          $isExpiringSoon = $nearestExpiry && $nearestExpiry->lt(now()->addDays(90));
          $programs = $drug->stocks->pluck('program')->unique('id')->filter();
        @endphp
        <tr>
          <td style="color:#94a3b8;font-size:12px">{{ $drugs->firstItem() + $i }}</td>
          <td>
            <div style="font-weight:600;color:#1e293b">{{ $drug->name }}</div>
            @foreach($programs as $prog)
              @if($prog)
                <span class="program-tag">{{ $prog->name }}</span>
              @endif
            @endforeach
          </td>
          <td style="color:#64748b">{{ $drug->dosage_form ?? '—' }}</td>
          <td style="color:#64748b">{{ $drug->strength ?? '—' }} {{ $drug->unit ?? '' }}</td>
          <td style="color:#1e293b;font-weight:600">₦{{ number_format($drug->unit_price ?? 0, 2) }}</td>
          <td style="text-align:center">
            <div style="font-weight:800;font-size:16px;color:{{ $barColor }}">{{ number_format($totalQty) }}</div>
            <div class="stock-bar mx-auto mt-1">
              <div class="fill" style="width:{{ $barPct }}%;background:{{ $barColor }}"></div>
            </div>
          </td>
          <td>
            @if($totalQty == 0)
              <span class="stock-badge out-stock"><span class="material-symbols-outlined" style="font-size:12px">cancel</span>Out of Stock</span>
            @elseif($totalQty < 10)
              <span class="stock-badge low-stock"><span class="material-symbols-outlined" style="font-size:12px">warning</span>Low Stock</span>
            @else
              <span class="stock-badge in-stock"><span class="material-symbols-outlined" style="font-size:12px">check_circle</span>In Stock</span>
            @endif
            @if($isExpiringSoon)
              <span class="stock-badge expiring mt-1" style="display:inline-flex"><span class="material-symbols-outlined" style="font-size:12px">schedule</span>Expiring</span>
            @endif
          </td>
          <td>
            <span style="font-weight:700;color:#1e293b">{{ $activeBatches->count() }}</span>
            <span style="font-size:11px;color:#94a3b8"> active</span>
          </td>
          <td>
            @if($nearestExpiry)
              <div style="font-weight:600;color:{{ $isExpiringSoon ? '#dc2626' : '#1e293b' }}">
                {{ $nearestExpiry->format('d M Y') }}
              </div>
              <div class="expiry-row">
                <span class="material-symbols-outlined" style="font-size:12px">schedule</span>
                {{ $nearestExpiry->diffForHumans() }}
              </div>
            @else
              <span style="color:#94a3b8">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:40px">
            <span class="material-symbols-outlined" style="font-size:48px;color:#e2e8f0">medication</span>
            <p style="color:#94a3b8;margin-top:8px">No drugs found matching your criteria</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($drugs->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--pharm-border)">
    {{ $drugs->links() }}
  </div>
  @endif
</div>

</div>
@endsection

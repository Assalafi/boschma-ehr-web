@extends('layouts.app')
@section('title', 'Pharmacy — Reports & Analytics')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 24px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px; color: #fff; }
.pharm-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 20px; }
.pharm-card-header { padding: 16px 20px; border-bottom: 1px solid var(--pharm-border); display: flex; align-items: center; justify-content: space-between; }
.pharm-card-header h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: .4px; color: #475569; margin: 0; display: flex; align-items: center; gap: 8px; }
.pharm-card-body { padding: 20px; }
.stat-tile { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: 18px; display: flex; align-items: center; gap: 14px; transition: transform .15s, box-shadow .15s; height: 100%; }
.stat-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.stat-tile .tile-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-tile .tile-icon .material-symbols-outlined { font-size: 24px; }
.stat-tile .tile-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.stat-tile .tile-value { font-size: 22px; font-weight: 800; color: #1e293b; line-height: 1.2; }
.stat-tile .tile-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.perf-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.perf-table thead th { background: #f8fafc; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #64748b; padding: 10px 14px; border-bottom: 1px solid var(--pharm-border); }
.perf-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 13px; }
.perf-table tbody tr:hover { background: #f8fafc; }
.perf-table tbody tr:last-child td { border-bottom: 0; }
.rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; font-size: 12px; font-weight: 800; }
.rank-1 { background: #fef3c7; color: #d97706; }
.rank-2 { background: #e2e8f0; color: #475569; }
.rank-3 { background: #fed7aa; color: #ea580c; }
.rank-default { background: #f1f5f9; color: #94a3b8; }
.perf-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
.perf-bar .fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--pharm-primary), #059669); }
.chart-bar-container { display: flex; align-items: end; gap: 3px; height: 120px; padding: 0 4px; }
.chart-bar { flex: 1; border-radius: 4px 4px 0 0; background: var(--pharm-primary); min-width: 8px; position: relative; transition: all .2s; cursor: pointer; opacity: .7; }
.chart-bar:hover { opacity: 1; }
.chart-bar .chart-tooltip { display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #1e293b; color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 10px; white-space: nowrap; margin-bottom: 4px; z-index: 10; }
.chart-bar:hover .chart-tooltip { display: block; }
.chart-labels { display: flex; gap: 3px; padding: 4px 4px 0; }
.chart-labels span { flex: 1; text-align: center; font-size: 9px; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; }
.method-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.method-row:last-child { border-bottom: 0; }
.method-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.program-rev-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.program-rev-row:last-child { border-bottom: 0; }
.pharm-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; }
.pharm-nav a { padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; color: #64748b; background: #f1f5f9; transition: all .15s; }
.pharm-nav a:hover { background: #e2e8f0; }
.pharm-nav a.active { background: var(--pharm-primary); color: #fff; }
.drug-rank { display: flex; align-items: center; gap: 10px; }
.drug-rank-bar { flex: 1; height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.drug-rank-bar .fill { height: 100%; border-radius: 3px; }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header">
  <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
    <ol class="breadcrumb mb-0" style="font-size:12px">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:rgba(255,255,255,.7)">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
      <li class="breadcrumb-item active" style="color:#fff">Reports</li>
    </ol>
  </nav>
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <h4 class="mb-1">Reports & Analytics</h4>
      <p style="font-size:13px;color:rgba(255,255,255,.7);margin-bottom:0">
        {{ $facility->name ?? 'Facility' }} · {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
      </p>
    </div>
    <form method="GET" action="{{ route('pharmacy.reports') }}" class="d-flex gap-2 align-items-center">
      <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" style="border-radius:8px;font-size:12px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);width:140px">
      <span style="color:rgba(255,255,255,.5);font-size:12px">to</span>
      <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" style="border-radius:8px;font-size:12px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);width:140px">
      <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600">
        <span class="material-symbols-outlined align-middle" style="font-size:14px">filter_list</span> Apply
      </button>
    </form>
  </div>
</div>

{{-- Navigation --}}
<div class="pharm-nav">
  <a href="{{ route('pharmacy.dashboard') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">dashboard</span>Dashboard</a>
  <a href="{{ route('pharmacy.queue') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">queue</span>Queue</a>
  <a href="{{ route('pharmacy.drugs') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">medication</span>Drugs</a>
  <a href="{{ route('pharmacy.history') }}"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">history</span>History</a>
  <a href="{{ route('pharmacy.reports') }}" class="active"><span class="material-symbols-outlined align-middle me-1" style="font-size:15px">analytics</span>Reports</a>
</div>

{{-- Overview Stats --}}
<div class="row g-3 mb-4">
  <div class="col-xl col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#dcfce7"><span class="material-symbols-outlined" style="color:#059669">receipt_long</span></div>
      <div>
        <div class="tile-label">Dispensations</div>
        <div class="tile-value">{{ number_format($totalDispensations) }}</div>
        <div class="tile-sub">Total transactions</div>
      </div>
    </div>
  </div>
  <div class="col-xl col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#dbeafe"><span class="material-symbols-outlined" style="color:#2563eb">payments</span></div>
      <div>
        <div class="tile-label">Total Revenue</div>
        <div class="tile-value" style="font-size:18px">₦{{ number_format($totalRevenue, 2) }}</div>
        <div class="tile-sub">Drug sales</div>
      </div>
    </div>
  </div>
  <div class="col-xl col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#fef3c7"><span class="material-symbols-outlined" style="color:#d97706">account_balance_wallet</span></div>
      <div>
        <div class="tile-label">Copayments</div>
        <div class="tile-value" style="font-size:18px">₦{{ number_format($totalCopayment ?? 0, 2) }}</div>
        <div class="tile-sub">Patient copays</div>
      </div>
    </div>
  </div>
  <div class="col-xl col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:var(--pharm-primary-light)"><span class="material-symbols-outlined" style="color:var(--pharm-primary)">medication</span></div>
      <div>
        <div class="tile-label">Items Dispensed</div>
        <div class="tile-value">{{ number_format($totalItemsDispensed) }}</div>
        <div class="tile-sub">Drug units</div>
      </div>
    </div>
  </div>
  <div class="col-xl col-sm-4 col-6">
    <div class="stat-tile">
      <div class="tile-icon" style="background:#ede9fe"><span class="material-symbols-outlined" style="color:#7c3aed">group</span></div>
      <div>
        <div class="tile-label">Unique Patients</div>
        <div class="tile-value">{{ number_format($uniquePatients) }}</div>
        <div class="tile-sub">Patients served</div>
      </div>
    </div>
  </div>
</div>

{{-- Daily Trend Chart --}}
@if($dailyTrend->count())
<div class="pharm-card">
  <div class="pharm-card-header">
    <h6><span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">trending_up</span> Daily Dispensation Trend</h6>
    <span style="font-size:11px;color:#94a3b8">{{ $dailyTrend->count() }} days</span>
  </div>
  <div class="pharm-card-body">
    @php $maxCount = $dailyTrend->max('count') ?: 1; @endphp
    <div class="chart-bar-container">
      @foreach($dailyTrend as $day)
        <div class="chart-bar" style="height: {{ max(4, ($day->count / $maxCount) * 100) }}%">
          <div class="chart-tooltip">
            {{ \Carbon\Carbon::parse($day->date)->format('d M') }}<br>
            {{ $day->count }} dispenses · ₦{{ number_format($day->revenue, 0) }}
          </div>
        </div>
      @endforeach
    </div>
    <div class="chart-labels">
      @foreach($dailyTrend as $day)
        <span>{{ \Carbon\Carbon::parse($day->date)->format('d') }}</span>
      @endforeach
    </div>
    <div class="d-flex justify-content-between mt-2" style="font-size:11px;color:#94a3b8">
      <span>{{ \Carbon\Carbon::parse($dailyTrend->first()->date)->format('d M Y') }}</span>
      <span>Avg: {{ round($dailyTrend->avg('count'), 1) }} dispenses/day · ₦{{ number_format($dailyTrend->avg('revenue'), 0) }}/day</span>
      <span>{{ \Carbon\Carbon::parse($dailyTrend->last()->date)->format('d M Y') }}</span>
    </div>
  </div>
</div>
@endif

<div class="row g-3">
  {{-- Pharmacist Performance --}}
  <div class="col-xl-8">
    <div class="pharm-card">
      <div class="pharm-card-header">
        <h6><span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">leaderboard</span> Pharmacist Performance</h6>
        <span style="font-size:11px;color:#94a3b8">{{ $pharmacistPerformance->count() }} pharmacists</span>
      </div>
      @if($pharmacistPerformance->count())
      @php $maxDisp = $pharmacistPerformance->max('total_dispensations') ?: 1; @endphp
      <div style="overflow-x:auto">
        <table class="perf-table">
          <thead>
            <tr>
              <th style="width:40px">Rank</th>
              <th>Pharmacist</th>
              <th style="text-align:center">Dispensations</th>
              <th style="text-align:center">Items</th>
              <th style="text-align:right">Revenue</th>
              <th style="text-align:center">Active Days</th>
              <th>Performance</th>
              <th>Last Active</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pharmacistPerformance as $i => $perf)
            @php
              $rankClass = match($i) { 0 => 'rank-1', 1 => 'rank-2', 2 => 'rank-3', default => 'rank-default' };
              $perfPct = ($perf->total_dispensations / $maxDisp) * 100;
              $avgPerDay = $perf->active_days > 0 ? round($perf->total_dispensations / $perf->active_days, 1) : 0;
            @endphp
            <tr>
              <td><span class="rank-badge {{ $rankClass }}">{{ $i + 1 }}</span></td>
              <td>
                <div style="font-weight:600;color:#1e293b">{{ $perf->pharmacist_name }}</div>
                <div style="font-size:11px;color:#94a3b8">{{ $avgPerDay }} avg/day</div>
              </td>
              <td style="text-align:center;font-weight:700;color:#1e293b">{{ number_format($perf->total_dispensations) }}</td>
              <td style="text-align:center;color:#64748b">{{ number_format($perf->total_items) }}</td>
              <td style="text-align:right;font-weight:600;color:var(--pharm-primary)">₦{{ number_format($perf->total_revenue, 0) }}</td>
              <td style="text-align:center;color:#64748b">{{ $perf->active_days }}</td>
              <td style="min-width:100px">
                <div class="perf-bar">
                  <div class="fill" style="width:{{ $perfPct }}%"></div>
                </div>
              </td>
              <td style="font-size:12px;color:#64748b">{{ \Carbon\Carbon::parse($perf->last_dispense)->format('d M H:i') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="pharm-card-body text-center" style="padding:40px">
        <span class="material-symbols-outlined" style="font-size:48px;color:#e2e8f0">person_off</span>
        <p style="color:#94a3b8;margin-top:8px">No dispensation activity in this period</p>
      </div>
      @endif
    </div>
  </div>

  {{-- Revenue Breakdown --}}
  <div class="col-xl-4">
    {{-- By Payment Method --}}
    <div class="pharm-card">
      <div class="pharm-card-header">
        <h6><span class="material-symbols-outlined" style="font-size:16px;color:#2563eb">credit_card</span> By Payment Method</h6>
      </div>
      <div class="pharm-card-body">
        @php
          $methodColors = ['Insurance' => '#2563eb', 'Cash' => '#059669', 'HMO' => '#7c3aed', 'Unknown' => '#94a3b8'];
          $totalMethodRev = $revenueByPayment->sum('revenue') ?: 1;
        @endphp
        @forelse($revenueByPayment as $method)
        @php $color = $methodColors[$method->method] ?? '#' . substr(md5($method->method), 0, 6); @endphp
        <div class="method-row">
          <div class="method-dot" style="background:{{ $color }}"></div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:13px;color:#1e293b">{{ $method->method }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ $method->count }} transactions</div>
          </div>
          <div style="text-align:right">
            <div style="font-weight:700;font-size:13px;color:#1e293b">₦{{ number_format($method->revenue, 0) }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ round(($method->revenue / $totalMethodRev) * 100, 1) }}%</div>
          </div>
        </div>
        @empty
        <p style="color:#94a3b8;text-align:center;margin:20px 0">No data</p>
        @endforelse
      </div>
    </div>

    {{-- By Program --}}
    <div class="pharm-card">
      <div class="pharm-card-header">
        <h6><span class="material-symbols-outlined" style="font-size:16px;color:#7c3aed">category</span> By Program</h6>
      </div>
      <div class="pharm-card-body">
        @php $totalProgRev = $revenueByProgram->sum('revenue') ?: 1; @endphp
        @forelse($revenueByProgram as $prog)
        @php $pct = round(($prog->revenue / $totalProgRev) * 100, 1); @endphp
        <div class="program-rev-row">
          <div style="flex:1">
            <div style="font-weight:600;font-size:13px;color:#1e293b">{{ $prog->program_name }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ number_format($prog->items) }} items · {{ $prog->count }} dispenses</div>
            <div class="perf-bar mt-1" style="height:4px">
              <div class="fill" style="width:{{ $pct }}%;background:#7c3aed"></div>
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-weight:700;font-size:13px;color:#1e293b">₦{{ number_format($prog->revenue, 0) }}</div>
            <div style="font-size:11px;color:#94a3b8">{{ $pct }}%</div>
          </div>
        </div>
        @empty
        <p style="color:#94a3b8;text-align:center;margin:20px 0">No data</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

{{-- Top Dispensed Drugs --}}
<div class="pharm-card">
  <div class="pharm-card-header">
    <h6><span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">trending_up</span> Top 20 Dispensed Drugs</h6>
  </div>
  @if($topDrugs->count())
  @php $maxDrugQty = $topDrugs->max('total_dispensed') ?: 1; @endphp
  <div style="overflow-x:auto">
    <table class="perf-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Drug Name</th>
          <th>Form</th>
          <th>Strength</th>
          <th style="text-align:center">Total Dispensed</th>
          <th style="text-align:center">Dispenses</th>
          <th style="text-align:right">Revenue</th>
          <th style="min-width:120px">Volume</th>
        </tr>
      </thead>
      <tbody>
        @foreach($topDrugs as $i => $td)
        @php $barPct = ($td->total_dispensed / $maxDrugQty) * 100; @endphp
        <tr>
          <td>
            <span class="rank-badge {{ match($i) { 0 => 'rank-1', 1 => 'rank-2', 2 => 'rank-3', default => 'rank-default' } }}">{{ $i + 1 }}</span>
          </td>
          <td style="font-weight:600;color:#1e293b">{{ $td->drug_name }}</td>
          <td style="color:#64748b">{{ $td->dosage_form ?? '—' }}</td>
          <td style="color:#64748b">{{ $td->strength ?? '—' }}</td>
          <td style="text-align:center;font-weight:700;color:#1e293b">{{ number_format($td->total_dispensed) }}</td>
          <td style="text-align:center;color:#64748b">{{ number_format($td->dispense_count) }}</td>
          <td style="text-align:right;font-weight:600;color:var(--pharm-primary)">₦{{ number_format($td->total_revenue, 0) }}</td>
          <td>
            <div class="drug-rank-bar">
              <div class="fill" style="width:{{ $barPct }}%;background:linear-gradient(90deg, var(--pharm-primary), #059669)"></div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="pharm-card-body text-center" style="padding:40px">
    <span class="material-symbols-outlined" style="font-size:48px;color:#e2e8f0">medication</span>
    <p style="color:#94a3b8;margin-top:8px">No dispensation data for this period</p>
  </div>
  @endif
</div>

</div>
@endsection

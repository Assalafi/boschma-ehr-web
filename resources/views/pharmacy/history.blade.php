@extends('layouts.app')
@section('title', 'Dispensation History')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.pharm-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
.pharm-card-header { padding: 16px 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--pharm-border); display: flex; align-items: center; gap: 8px; }
.pharm-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.pharm-badge-teal { background: var(--pharm-primary-light); color: var(--pharm-primary-dark); }
.pharm-badge-amber { background: #fef3c7; color: #92400e; }
.pharm-badge-green { background: #dcfce7; color: #166534; }
.pharm-badge-red { background: #fee2e2; color: #991b1b; }
.pharm-badge-blue { background: #dbeafe; color: #1e40af; }
.pharm-badge-gray { background: #f1f5f9; color: #475569; }
.pharm-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.pharm-btn-primary { background: var(--pharm-primary); color: #fff; }
.pharm-btn-primary:hover { background: var(--pharm-primary-dark); color: #fff; }
.pharm-btn-outline { background: transparent; border: 1.5px solid var(--pharm-border); color: #64748b; }
.pharm-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.pharm-btn-amber { background: #f59e0b; color: #fff; }
.pharm-btn-amber:hover { background: #d97706; color: #fff; }
.pharm-search input, .pharm-search select { padding: 7px 12px; border: 1.5px solid var(--pharm-border); border-radius: 8px; font-size: 13px; transition: border-color .2s; }
.pharm-search input:focus, .pharm-search select:focus { border-color: var(--pharm-primary); outline: none; box-shadow: 0 0 0 3px rgba(1,102,52,.1); }
.rx-block { border-bottom: 1px solid var(--pharm-border); padding: 20px 24px; transition: background .1s; }
.rx-block:last-child { border-bottom: none; }
.rx-block:hover { background: #fafcfc; }
.rx-patient { display: flex; align-items: center; gap: 14px; }
.rx-avatar { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--pharm-primary-light); }
.rx-avatar .material-symbols-outlined { font-size: 22px; color: var(--pharm-primary); }
.rx-name { font-weight: 700; color: #1e293b; font-size: 14px; }
.rx-meta { color: #94a3b8; font-size: 11px; }
.rx-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 14px; }
.rx-table thead th { background: #f8fafc; padding: 8px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
.rx-table tbody td { padding: 10px 12px; font-size: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #475569; }
.rx-table tfoot td { padding: 10px 12px; font-size: 12px; background: #f8fafc; border-top: 2px solid #e2e8f0; }
.drug-name { font-weight: 600; color: #1e293b; font-size: 12px; }
.drug-detail { color: #94a3b8; font-size: 10px; }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state .material-symbols-outlined { font-size: 56px; color: #94a3b8; opacity: .4; }
.empty-state h5 { font-weight: 700; color: #1e293b; margin-top: 12px; }
.empty-state p { color: #94a3b8; font-size: 13px; }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
        <li class="breadcrumb-item active" style="color:#fff">History</li>
      </ol>
    </nav>
    <h4 class="mb-0">Dispensation History</h4>
  </div>
  <a href="{{ route('pharmacy.queue') }}" class="pharm-btn pharm-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">queue</span> Go to Queue
  </a>
</div>

{{-- Filter card --}}
<div class="pharm-card" style="margin-bottom:20px">
  <div class="pharm-card-header" style="justify-content:space-between;color:#1e293b;flex-wrap:wrap;gap:12px">
    <div class="d-flex align-items-center gap-2">
      <span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">history</span>
      Completed Dispensations
    </div>
    <form method="GET" action="{{ route('pharmacy.history') }}" class="pharm-search d-flex gap-2 align-items-center">
      <input type="date" name="date" value="{{ request('date') }}" style="min-width:160px">
      <button type="submit" class="pharm-btn pharm-btn-primary" style="padding:7px 14px">
        <span class="material-symbols-outlined" style="font-size:15px">filter_alt</span> Filter
      </button>
      @if(request('date'))
        <a href="{{ route('pharmacy.history') }}" class="pharm-btn pharm-btn-outline" style="padding:7px 12px">Clear</a>
      @endif
    </form>
  </div>

  {{-- Rx blocks --}}
  @forelse($prescriptions as $rx)
  @php
    $patient   = $rx->consultation?->encounter?->patient;
    $enrollee  = $patient?->enrolleeDetails;
    $patName   = $enrollee?->fullname ?? ($enrollee?->name ?? 'Unknown');
    $boschmaNo = $patient?->enrollee_number ?? '—';
    $totalCost = $rx->items->flatMap->dispensations->sum('cost_of_medication');
    $sc = match($rx->status) {
      'Fully Dispensed'     => 'green',
      'Partially Dispensed' => 'blue',
      'Cancelled'           => 'red',
      default               => 'gray',
    };
  @endphp
  <div class="rx-block">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
      <div class="rx-patient">
        <div class="rx-avatar">
          <span class="material-symbols-outlined">person</span>
        </div>
        <div>
          <div class="rx-name">{{ $patName }}</div>
          <div class="rx-meta">{{ $boschmaNo }} &bull; File: {{ $patient?->file_number ?? '—' }}</div>
        </div>
      </div>
      <div class="text-end">
        <span class="pharm-badge pharm-badge-{{ $sc }}">{{ $rx->status }}</span>
        <div class="rx-meta mt-1">Rx: <code style="font-size:10px;background:#f1f5f9;padding:1px 6px;border-radius:3px">{{ $rx->prescription_number ?? '—' }}</code></div>
        <div class="rx-meta">Dr. {{ $rx->prescribedBy?->name ?? '—' }}</div>
      </div>
    </div>

    <div style="overflow-x:auto;border-radius:10px;border:1px solid #f1f5f9">
      <table class="rx-table">
        <thead>
          <tr>
            <th>Drug</th>
            <th style="text-align:center">Prescribed</th>
            <th style="text-align:center">Dispensed</th>
            <th>Dispensed By</th>
            <th>Time</th>
            <th style="text-align:right">Cost</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rx->items as $item)
          @php $lastD = $item->dispensations->sortByDesc('dispensing_date_time')->first(); @endphp
          <tr>
            <td>
              <div class="drug-name">{{ $item->drug?->name ?? 'Unknown' }}</div>
              <div class="drug-detail">{{ $item->dosage ?? '' }}{{ $item->dosage && $item->frequency ? ' · ' : '' }}{{ match((string)$item->frequency) { '1' => 'OD', '2' => 'BD', '3' => 'TDS', '4' => 'QDS', default => $item->frequency ?? '' } }}{{ $item->duration ? ' · ' . $item->duration . 'd' : '' }}</div>
            </td>
            <td style="text-align:center"><span class="pharm-badge pharm-badge-blue">{{ $item->quantity }}</span></td>
            <td style="text-align:center"><span class="pharm-badge {{ $item->dispensations->sum('quantity_dispensed') > 0 ? 'pharm-badge-green' : 'pharm-badge-gray' }}">{{ $item->dispensations->sum('quantity_dispensed') }}</span></td>
            <td style="font-size:11px;color:#64748b">{{ $lastD?->dispensingOfficer?->name ?? '—' }}</td>
            <td style="font-size:11px;color:#64748b">{{ $lastD ? $lastD->dispensing_date_time->format('d M Y H:i') : '—' }}</td>
            <td style="text-align:right;font-weight:600;color:#1e293b">GHS {{ number_format($item->dispensations->sum('cost_of_medication'), 2) }}</td>
            <td style="text-align:center">
              @if($item->dispensing_status === \App\Models\PrescriptionItem::STATUS_DISPENSED)
                <span class="pharm-badge pharm-badge-green">Dispensed</span>
              @elseif($item->dispensing_status === 'Cancelled')
                <span class="pharm-badge pharm-badge-red">Cancelled</span>
              @else
                <span class="pharm-badge pharm-badge-amber">Pending</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" style="text-align:right;font-weight:600;color:#64748b">Total Cost</td>
            <td style="text-align:right;font-weight:700;color:#1e293b;font-size:13px">GHS {{ number_format($totalCost, 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @empty
  <div class="empty-state">
    <span class="material-symbols-outlined">history</span>
    <h5>No dispensation history</h5>
    <p>{{ request('date') ? 'No dispensations found for the selected date.' : 'Completed dispensations will appear here.' }}</p>
    <a href="{{ route('pharmacy.queue') }}" class="pharm-btn pharm-btn-amber" style="margin-top:8px">
      <span class="material-symbols-outlined" style="font-size:14px">queue</span> Go to Queue
    </a>
  </div>
  @endforelse

  @if($prescriptions->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--pharm-border);display:flex;justify-content:flex-end">
    {{ $prescriptions->withQueryString()->links() }}
  </div>
  @endif
</div>

</div>
@endsection

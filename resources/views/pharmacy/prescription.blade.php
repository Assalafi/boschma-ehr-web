@extends('layouts.app')
@section('title', 'Dispense Prescription')
@section('content')
@php
    $patient  = $prescription->consultation ? $prescription->consultation->encounter->patient : null;
    $enrollee = $patient ? $patient->enrolleeDetails : null;
    $patName  = $enrollee ? ($enrollee->fullname ?: $enrollee->name) : 'Unknown';
    $boschmaNo= $patient ? $patient->enrollee_number : '—';
    $doctor   = $prescription->prescribedBy;
    $encounter= $prescription->consultation ? $prescription->consultation->encounter : null;
    $isSecondaryFac = $facility && strtolower($facility->type) === 'secondary';
    $pendingItems = $prescription->items->filter(fn($i) => $i->dispensing_status === \App\Models\PrescriptionItem::STATUS_PENDING);
    $dispensedItems = $prescription->items->filter(fn($i) => $i->dispensing_status === \App\Models\PrescriptionItem::STATUS_DISPENSED);
    $cancelledItems = $prescription->items->filter(fn($i) => $i->dispensing_status === 'Cancelled');
    $totalItems = $prescription->items->count();
    $progressPct = $totalItems > 0 ? round(($dispensedItems->count() / $totalItems) * 100) : 0;
@endphp

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-surface: #f8fafc; --pharm-border: #e2e8f0; }
.pharm-page { font-size: 14px; }
.pharm-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); border-radius: 16px; padding: 20px 28px; color: #fff; margin-bottom: 24px; }
.pharm-header h4 { font-weight: 700; letter-spacing: -0.3px;;color:#fff}
.pharm-card { background: #fff; border-radius: 14px; border: 1px solid var(--pharm-border); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.pharm-card-header { padding: 14px 18px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--pharm-border); display: flex; align-items: center; gap: 8px; }
.pharm-card-body { padding: 16px 18px; }
.pharm-info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.pharm-info-row:last-child { border-bottom: none; }
.pharm-info-row .label { color: #64748b; }
.pharm-info-row .value { font-weight: 600; color: #1e293b; }
.pharm-avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--pharm-primary-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; }
.pharm-avatar .material-symbols-outlined { font-size: 28px; color: var(--pharm-primary); }
.pharm-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.pharm-badge-teal { background: var(--pharm-primary-light); color: var(--pharm-primary-dark); }
.pharm-badge-amber { background: #fef3c7; color: #92400e; }
.pharm-badge-green { background: #dcfce7; color: #166534; }
.pharm-badge-red { background: #fee2e2; color: #991b1b; }
.pharm-badge-blue { background: #dbeafe; color: #1e40af; }
.pharm-badge-gray { background: #f1f5f9; color: #475569; }
.pharm-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.pharm-table thead th { background: #f8fafc; padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--pharm-border); white-space: nowrap; }
.pharm-table tbody td { padding: 12px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pharm-table tbody tr:hover { background: #f8fafc; }
.pharm-table tbody tr.row-dispensed { opacity: .6; }
.pharm-table tbody tr.row-cancelled { opacity: .45; }
.pharm-table .drug-name { font-weight: 600; color: #1e293b; font-size: 13px; margin-bottom: 1px; }
.pharm-table .drug-detail { color: #94a3b8; font-size: 11px; }
.pharm-qty-input { width: 64px; padding: 4px 6px; border: 1.5px solid var(--pharm-border); border-radius: 8px; text-align: center; font-size: 13px; font-weight: 600; transition: border-color .2s; }
.pharm-qty-input:focus { border-color: var(--pharm-primary); outline: none; box-shadow: 0 0 0 3px rgba(1,102,52,.12); }
.pharm-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; }
.pharm-btn-primary { background: var(--pharm-primary); color: #fff; }
.pharm-btn-primary:hover { background: var(--pharm-primary-dark); color: #fff; }
.pharm-btn-outline { background: transparent; border: 1.5px solid var(--pharm-border); color: #64748b; }
.pharm-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; }
.pharm-btn-danger-ghost { background: transparent; border: 1.5px solid #fecaca; color: #dc2626; }
.pharm-btn-danger-ghost:hover { background: #fef2f2; }
.pharm-btn-amber { background: #f59e0b; color: #fff; }
.pharm-btn-amber:hover { background: #d97706; color: #fff; }
.pharm-btn-success { background: #059669; color: #fff; }
.pharm-btn-success:hover { background: #047857; color: #fff; }
.pharm-summary { border: 2px solid var(--pharm-primary); position: sticky; top: 20px; }
.pharm-summary .pharm-card-header { background: linear-gradient(135deg, var(--pharm-primary-dark), var(--pharm-primary)); color: #fff; }
.summary-empty { text-align: center; padding: 20px 0; color: #94a3b8; }
.summary-empty .material-symbols-outlined { font-size: 40px; opacity: .35; }
.summary-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
.summary-item:last-child { border-bottom: none; }
.copay-bar { background: linear-gradient(90deg, #fef3c7, #fde68a); border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; }
.copay-bar .label { color: #92400e; font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 4px; }
.copay-bar .amount { color: #92400e; font-weight: 700; font-size: 14px; }
.pharm-select { padding: 7px 12px; border: 1.5px solid var(--pharm-border); border-radius: 8px; font-size: 13px; width: 100%; transition: border-color .2s; }
.pharm-select:focus { border-color: var(--pharm-primary); outline: none; box-shadow: 0 0 0 3px rgba(1,102,52,.12); }
.log-row td { background: #fafffe !important; padding: 4px 12px !important; }
.log-row small { color: #94a3b8; font-size: 11px; }
.progress-ring { width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
.progress-ring-fill { height: 100%; border-radius: 3px; transition: width .5s ease; }
.pharm-toast { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 320px; }
.pharm-check { width: 18px; height: 18px; border-radius: 4px; cursor: pointer; accent-color: var(--pharm-primary); }
</style>

<div class="pharm-page">

{{-- Header banner --}}
<div class="pharm-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pharmacy.queue') }}" style="color:rgba(255,255,255,.7)">Queue</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Dispense</li>
      </ol>
    </nav>
    <h4 class="mb-1">Dispense Prescription</h4>
    <div style="font-size:13px;color:rgba(255,255,255,.8)">
      <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">person</span>{{ $patName }}
      <span class="mx-2" style="opacity:.4">|</span>
      <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">badge</span>{{ $boschmaNo }}
      @if($doctor)
      <span class="mx-2" style="opacity:.4">|</span>
      <span class="material-symbols-outlined align-middle me-1" style="font-size:16px">stethoscope</span>Dr. {{ $doctor->name }}
      @endif
    </div>
  </div>
  <div class="text-end">
    <div class="d-flex align-items-center gap-3">
      @php $statusColors = ['Pending'=>'pharm-badge-amber','Partially Dispensed'=>'pharm-badge-blue','Fully Dispensed'=>'pharm-badge-green','Cancelled'=>'pharm-badge-red']; $sc = $statusColors[$prescription->status] ?? 'pharm-badge-gray'; @endphp
      <span class="pharm-badge {{ $sc }}" style="font-size:12px;padding:5px 14px">{{ $prescription->status }}</span>
      <a href="{{ route('pharmacy.queue') }}" class="pharm-btn pharm-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
        <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Queue
      </a>
    </div>
  </div>
</div>

<div id="toastContainer" class="pharm-toast"></div>

<div class="row g-3">
  {{-- LEFT SIDEBAR --}}
  <div class="col-xl-3 col-lg-4">
    <div style="position:sticky;top:20px">

    {{-- Dispensing Summary (first) --}}
    @if($pendingItems->count() > 0)
    <div class="pharm-card pharm-summary">
      <div class="pharm-card-header">
        <span class="material-symbols-outlined" style="font-size:16px">shopping_cart</span> Dispensing Summary
        <span class="ms-auto pharm-badge pharm-badge-teal" id="selectedBadge" style="font-size:10px">0 selected</span>
      </div>
      <div class="pharm-card-body">
        <div id="summaryEmpty" class="summary-empty">
          <span class="material-symbols-outlined">inventory_2</span>
          <p class="mb-0 mt-1" style="font-size:12px">Check items to begin dispensing</p>
        </div>
        <div id="summaryItems" style="display:none">
          <div id="summaryList"></div>
          <div style="margin:12px 0;height:1px;background:var(--pharm-border)"></div>
          <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px">
            <span style="color:#64748b">Total Medication Cost</span>
            <span id="summaryTotal" style="font-weight:700;font-size:15px;color:#1e293b">₦ 0.00</span>
          </div>
          @if($isSecondaryFac)
          <div class="copay-bar mb-3">
            <span class="label">
              <span class="material-symbols-outlined" style="font-size:14px">payments</span> Copayment (10%)
            </span>
            <span class="amount" id="summaryCopay">₦ 0.00</span>
          </div>
          @endif
          <div class="mb-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.3px;margin-bottom:6px;display:block">Payment Method <span style="color:#dc2626">*</span></label>
            <select id="paymentMethod" class="pharm-select">
              <option value="">Choose method…</option>
              <option value="Cash">💵 Cash</option>
              <option value="Card">💳 Card</option>
              <option value="Mobile Money">📱 Mobile Money</option>
              <option value="Insurance">🏥 Insurance</option>
            </select>
          </div>
          <button type="button" class="pharm-btn pharm-btn-success w-100 justify-content-center" style="padding:10px;font-size:13px" onclick="openBulkConfirm()">
            <span class="material-symbols-outlined" style="font-size:17px">medication</span>
            Dispense Selected Items
          </button>
        </div>
      </div>
    </div>
    @endif

    {{-- Patient card --}}
    <div class="pharm-card">
      <div class="pharm-card-header" style="color: var(--pharm-primary)">
        <span class="material-symbols-outlined" style="font-size:16px">person</span> Patient
      </div>
      <div class="pharm-card-body text-center" style="padding-bottom:12px">
        <div class="pharm-avatar"><span class="material-symbols-outlined">person</span></div>
        <div style="font-weight:700;font-size:15px;color:#1e293b">{{ $patName }}</div>
        <span class="pharm-badge pharm-badge-teal mt-1">{{ $boschmaNo }}</span>
      </div>
      <div style="padding:0 18px 14px">
        <div class="pharm-info-row">
          <span class="label">File No.</span>
          <span class="value">{{ $patient ? $patient->file_number : '—' }}</span>
        </div>
        <div class="pharm-info-row">
          <span class="label">Type</span>
          <span class="value">{{ ucfirst($patient ? $patient->enrollee_type : '—') }}</span>
        </div>
        <div class="pharm-info-row">
          <span class="label">Visit</span>
          <span class="value">{{ $encounter ? $encounter->visit_date->format('d M Y') : '—' }}</span>
        </div>
        @if($facility)
        <div class="pharm-info-row">
          <span class="label">Facility</span>
          <span class="pharm-badge {{ $isSecondaryFac ? 'pharm-badge-amber' : 'pharm-badge-blue' }}">{{ ucfirst($facility->type ?? '—') }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- Prescription card --}}
    <div class="pharm-card">
      <div class="pharm-card-header" style="color:#64748b">
        <span class="material-symbols-outlined" style="font-size:16px">receipt_long</span> Prescription
      </div>
      <div class="pharm-card-body" style="padding-top:10px">
        <div class="pharm-info-row">
          <span class="label">Rx No.</span>
          <code style="font-size:11px;background:#f1f5f9;padding:2px 8px;border-radius:4px">{{ $prescription->prescription_number ?? '—' }}</code>
        </div>
        <div class="pharm-info-row">
          <span class="label">Doctor</span>
          <span class="value">{{ $doctor ? $doctor->name : '—' }}</span>
        </div>
        <div class="pharm-info-row">
          <span class="label">Date</span>
          <span class="value">{{ $prescription->prescription_date ? \Carbon\Carbon::parse($prescription->prescription_date)->format('d M Y') : $prescription->created_at->format('d M Y') }}</span>
        </div>
        {{-- Progress bar --}}
        <div style="margin-top:12px">
          <div class="d-flex justify-content-between mb-1" style="font-size:11px;color:#64748b">
            <span>Dispensing Progress</span>
            <span>{{ $dispensedItems->count() }}/{{ $totalItems }}</span>
          </div>
          <div class="progress-ring">
            <div class="progress-ring-fill" style="width:{{ $progressPct }}%;background:{{ $progressPct == 100 ? '#059669' : 'var(--pharm-primary)' }}"></div>
          </div>
        </div>
        {{-- Stats chips --}}
        <div class="d-flex flex-wrap gap-1 mt-2" style="font-size:11px">
          @if($pendingItems->count())<span class="pharm-badge pharm-badge-amber">{{ $pendingItems->count() }} Pending</span>@endif
          @if($dispensedItems->count())<span class="pharm-badge pharm-badge-green">{{ $dispensedItems->count() }} Dispensed</span>@endif
          @if($cancelledItems->count())<span class="pharm-badge pharm-badge-red">{{ $cancelledItems->count() }} Cancelled</span>@endif
        </div>
      </div>
    </div>

    </div>{{-- end sticky wrapper --}}
  </div>

  {{-- RIGHT: Items Table --}}
  <div class="col-xl-9 col-lg-8">
    <div class="pharm-card" style="margin-bottom:0">
      <div class="pharm-card-header" style="color:#1e293b;justify-content:space-between">
        <div class="d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">medication</span>
          Prescription Items
        </div>
        <span class="pharm-badge pharm-badge-gray">{{ $totalItems }} item{{ $totalItems > 1 ? 's' : '' }}</span>
      </div>
      <div style="overflow-x:auto">
        <table class="pharm-table">
          <thead>
            <tr>
              <th style="width:40px;text-align:center;padding-left:14px">
                @if($pendingItems->count() > 0)
                <input type="checkbox" id="selectAll" class="pharm-check" title="Select all pending" onchange="toggleSelectAll(this)">
                @endif
              </th>
              <th>Medication</th>
              <th style="text-align:center">Dosage</th>
              <th style="text-align:center">Freq</th>
              <th style="text-align:center">Days</th>
              <th style="text-align:center">Rx Qty</th>
              <th style="text-align:center">Given</th>
              <th style="text-align:center">Stock</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:center;width:80px">Dispense Qty</th>
              <th style="text-align:center;width:90px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($prescription->items as $item)
            @php
              $drug     = $item->drug;
              $stock    = $drug ? $drug->getCurrentStockAttribute($facilityId) : 0;
              $dispensed= $item->dispensations->sum('quantity_dispensed');
              $remaining= $item->quantity - $dispensed;
              $isPending  = $item->dispensing_status === \App\Models\PrescriptionItem::STATUS_PENDING;
              $isDispensed= $item->dispensing_status === \App\Models\PrescriptionItem::STATUS_DISPENSED;
              $isCancelled= $item->dispensing_status === 'Cancelled';
              $encounterCompleted = ($prescription->consultation && $prescription->consultation->encounter && $prescription->consultation->encounter->status === \App\Models\Encounter::STATUS_COMPLETED);
            @endphp
            <tr id="item-row-{{ $item->id }}" class="{{ $isDispensed ? 'row-dispensed' : '' }}{{ $isCancelled ? 'row-cancelled' : '' }}">
              <td style="text-align:center;padding-left:14px">
                @if($isPending && !$encounterCompleted)
                <input type="checkbox" class="pharm-check item-check"
                  value="{{ $item->id }}"
                  data-unit-price="{{ $drug ? $drug->getSellingPrice($facilityId) : 0 }}"
                  data-max-stock="{{ $stock }}"
                  data-drug="{{ $drug->name ?? 'Unknown' }}"
                  onchange="onItemCheck()">
                @endif
              </td>
              <td>
                <div class="drug-name">{{ $drug ? $drug->name : 'Unknown Drug' }}</div>
                <div class="drug-detail">{{ trim(($drug ? $drug->dosage_form : '') . ' ' . ($drug ? $drug->strength : '')) ?: '' }}</div>
              </td>
              <td style="text-align:center"><span style="color:#64748b;font-size:12px">{{ $item->dosage ?? '—' }}</span></td>
              <td style="text-align:center">
                <span style="color:#64748b;font-size:12px">{{ match((string)$item->frequency) {
                  '1' => 'OD','2' => 'BD','3' => 'TDS','4' => 'QDS',
                  default => $item->frequency ?? '—'
                } }}</span>
              </td>
              <td style="text-align:center"><span style="color:#64748b;font-size:12px">{{ $item->duration ?? '—' }}</span></td>
              <td style="text-align:center"><span class="pharm-badge pharm-badge-blue">{{ $item->quantity }}</span></td>
              <td style="text-align:center"><span class="pharm-badge {{ $dispensed > 0 ? 'pharm-badge-green' : 'pharm-badge-gray' }}">{{ $dispensed }}</span></td>
              <td style="text-align:center">
                <span class="pharm-badge {{ $stock >= max($remaining,1) ? 'pharm-badge-green' : ($stock > 0 ? 'pharm-badge-amber' : 'pharm-badge-red') }}">{{ $stock }}</span>
              </td>
              <td style="text-align:center">
                @if($isPending)
                  <span class="pharm-badge pharm-badge-amber">Pending</span>
                @elseif($isDispensed)
                  <span class="pharm-badge pharm-badge-green">Dispensed</span>
                @else
                  <span class="pharm-badge pharm-badge-red">Cancelled</span>
                @endif
              </td>
              <td style="text-align:center">
                @if($isPending && !$encounterCompleted)
                <input type="number" class="pharm-qty-input" id="qty-{{ $item->id }}"
                  value="{{ max($remaining, 1) }}" min="1" max="{{ $stock > 0 ? $stock : 999 }}"
                  oninput="onQtyChange('{{ $item->id }}')">
                @else
                <span style="color:#cbd5e1">—</span>
                @endif
              </td>
              <td style="text-align:center">
                @if($isPending && !$encounterCompleted)
                  <button type="button" class="pharm-btn pharm-btn-danger-ghost" style="padding:4px 8px"
                    onclick="confirmCancel('{{ $item->id }}')" id="btn-cancel-{{ $item->id }}" title="Cancel">
                    <span class="material-symbols-outlined" style="font-size:14px">close</span>
                  </button>
                @elseif($isDispensed && !$encounterCompleted)
                  <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="pharm-btn pharm-btn-amber" style="padding:4px 8px"
                      onclick="showEditQuantity('{{ $item->id }}', {{ $item->quantity }}, {{ $dispensed }})" title="Adjust qty">
                      <span class="material-symbols-outlined" style="font-size:14px">edit</span>
                    </button>
                    <button type="button" class="pharm-btn pharm-btn-danger-ghost" style="padding:4px 8px"
                      onclick="confirmCancel('{{ $item->id }}')" id="btn-cancel-{{ $item->id }}" title="Cancel">
                      <span class="material-symbols-outlined" style="font-size:14px">close</span>
                    </button>
                  </div>
                @elseif($isDispensed)
                  <span class="material-symbols-outlined" style="font-size:16px;color:#059669" title="Locked">lock</span>
                @else
                  <span class="material-symbols-outlined" style="font-size:16px;color:#dc2626" title="Cancelled">block</span>
                @endif
              </td>
            </tr>
            @if($item->dispensations->isNotEmpty())
            <tr class="log-row">
              <td colspan="11" style="padding-left:54px !important">
                <small>
                  @foreach($item->dispensations as $d)
                    <span class="material-symbols-outlined align-middle" style="font-size:12px;color:#94a3b8">schedule</span>
                    {{ $d->quantity_dispensed > 0 ? $d->quantity_dispensed . ' unit(s)' : 'Returned ' . abs($d->quantity_dispensed) }}
                    — {{ $d->dispensing_date_time->format('d M H:i') }}
                    by {{ $d->dispensingOfficer?->name ?? 'Staff' }}
                    @if($d->payment_method) · {{ $d->payment_method }} @endif
                    @if($d->copayment_amount > 0) · <span style="color:#92400e">Copay ₦ {{ number_format($d->copayment_amount,2) }}</span> @endif
                    @if(!$loop->last) <span style="color:#e2e8f0;margin:0 4px">|</span> @endif
                  @endforeach
                </small>
              </td>
            </tr>
            @endif
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Bulk Confirm Modal --}}
<div class="modal fade" id="bulkDispenseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:linear-gradient(135deg,#01552b,#016634);color:#fff;border:none;padding:18px 24px">
        <h6 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size:15px">
          <span class="material-symbols-outlined" style="font-size:20px">fact_check</span> Confirm Dispensing
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px 24px">
        <p style="font-size:12px;color:#64748b;margin-bottom:12px">Review the items below before confirming:</p>
        <div id="bulk-confirm-list" style="max-height:200px;overflow-y:auto;margin-bottom:12px"></div>
        <div style="height:1px;background:#e2e8f0;margin:12px 0"></div>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="color:#64748b;font-size:13px">Total Cost</span>
          <span id="bulk-confirm-total" style="font-weight:700;font-size:16px;color:#1e293b">₦ 0.00</span>
        </div>
        <div id="bulk-confirm-copay-row" class="copay-bar mb-2">
          <span class="label">
            <span class="material-symbols-outlined" style="font-size:14px">payments</span> Copayment (10%)
          </span>
          <span class="amount" id="bulk-confirm-copay">₦ 0.00</span>
        </div>
        <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f8fafc;font-size:13px">
          <span class="material-symbols-outlined" style="font-size:16px;color:#64748b">credit_card</span>
          <span style="color:#64748b">Payment:</span>
          <strong id="bulk-confirm-method" style="color:#1e293b">—</strong>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:14px 24px">
        <button type="button" class="pharm-btn pharm-btn-outline" data-bs-dismiss="modal">
          <span class="material-symbols-outlined" style="font-size:14px">arrow_back</span> Back
        </button>
        <button type="button" class="pharm-btn pharm-btn-success" id="btnConfirmBulk" onclick="executeBulkDispense()" style="padding:8px 20px">
          <span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Confirm &amp; Dispense
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Edit Quantity Modal --}}
<div class="modal fade" id="editQuantityModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:#f59e0b;color:#fff;border:none;padding:16px 20px">
        <h6 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size:14px">
          <span class="material-symbols-outlined" style="font-size:18px">edit</span> Adjust Quantity
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px">
        <div class="d-flex gap-3 mb-3" style="font-size:12px">
          <div class="text-center flex-fill p-2 rounded" style="background:#f8fafc">
            <div style="color:#64748b;margin-bottom:2px">Prescribed</div>
            <div id="modal-prescribed-qty" style="font-weight:700;font-size:18px;color:#1e293b">0</div>
          </div>
          <div class="text-center flex-fill p-2 rounded" style="background:#f0fdf4">
            <div style="color:#64748b;margin-bottom:2px">Dispensed</div>
            <div id="modal-current-qty" style="font-weight:700;font-size:18px;color:#059669">0</div>
          </div>
        </div>
        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.3px;margin-bottom:6px;display:block">New Total Quantity</label>
        <input type="number" id="new-quantity" class="pharm-qty-input" style="width:100%;padding:10px;font-size:16px" min="0" max="999">
        <p style="font-size:11px;color:#94a3b8;margin-top:6px;margin-bottom:0">Enter the new total dispensed amount</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:12px 20px">
        <button type="button" class="pharm-btn pharm-btn-outline" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="pharm-btn pharm-btn-primary" onclick="updateQuantity()">
          <span class="material-symbols-outlined" style="font-size:14px">check</span> Update
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const _bulkUrl  = '{{ route("pharmacy.prescription.bulk-dispense", $prescription->id) }}';
const _itemUrl  = '{{ url("pharmacy/item") }}';
const _csrf     = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const _isSecondary = {{ ($facility && strtolower($facility->type) === 'secondary') ? 'true' : 'false' }};
let _editItemId = null;

/* ── Toast ──────────────────────────────────────────── */
function showToast(type, msg) {
    const colors = { success:'#059669', danger:'#dc2626', warning:'#d97706', info:'#2563eb' };
    const icons  = { success:'check_circle', danger:'error', warning:'warning', info:'info' };
    const id = 'toast-' + Date.now();
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend',
        `<div id="${id}" style="background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:14px 18px;margin-bottom:8px;display:flex;align-items:center;gap:10px;animation:slideIn .3s ease;border-left:4px solid ${colors[type]||'#64748b'}">
            <span class="material-symbols-outlined" style="font-size:20px;color:${colors[type]||'#64748b'}">${icons[type]||'info'}</span>
            <span style="font-size:13px;color:#1e293b;flex:1">${msg}</span>
            <span class="material-symbols-outlined" style="font-size:16px;color:#94a3b8;cursor:pointer" onclick="this.parentElement.remove()">close</span>
        </div>`);
    setTimeout(() => { const el = document.getElementById(id); if(el) { el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(),300); } }, 5000);
}

/* ── Checkbox & qty ─────────────────────────────────── */
function toggleSelectAll(cb) {
    document.querySelectorAll('.item-check').forEach(c => c.checked = cb.checked);
    updateSummary();
}

function onItemCheck() {
    const all = document.querySelectorAll('.item-check');
    const sa  = document.getElementById('selectAll');
    if (sa) sa.checked = [...all].every(c => c.checked);
    updateSummary();
}

function onQtyChange(itemId) {
    const chk = document.querySelector(`.item-check[value="${itemId}"]`);
    if (chk && chk.checked) updateSummary();
}

function getCheckedItems() {
    return [...document.querySelectorAll('.item-check:checked')].map(c => {
        const qty = parseInt(document.getElementById('qty-' + c.value)?.value) || 1;
        const price = parseFloat(c.dataset.unitPrice) || 0;
        return { id: c.value, qty, price, drug: c.dataset.drug, maxStock: parseInt(c.dataset.maxStock) || 0 };
    });
}

/* ── Summary panel ──────────────────────────────────── */
function updateSummary() {
    const items = getCheckedItems();
    const empty = document.getElementById('summaryEmpty');
    const panel = document.getElementById('summaryItems');
    const badge = document.getElementById('selectedBadge');

    if (badge) badge.textContent = items.length + ' selected';

    if (!items.length) {
        if (empty) empty.style.display = '';
        if (panel) panel.style.display = 'none';
        return;
    }
    if (empty) empty.style.display = 'none';
    if (panel) panel.style.display = '';

    let total = 0;
    const list = document.getElementById('summaryList');
    if (list) {
        list.innerHTML = items.map(i => {
            const cost = i.qty * i.price;
            total += cost;
            return `<div class="summary-item">
                <span style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${i.drug}">${i.drug}</span>
                <span style="color:#64748b;white-space:nowrap">&times;${i.qty} &nbsp; <strong style="color:#1e293b">₦ ${cost.toFixed(2)}</strong></span>
            </div>`;
        }).join('');
    }

    const copay = _isSecondary ? total * 0.10 : 0;
    const totalEl = document.getElementById('summaryTotal');
    const copayEl = document.getElementById('summaryCopay');
    if (totalEl) totalEl.textContent = '₦ ' + total.toFixed(2);
    if (copayEl) copayEl.textContent = '₦ ' + copay.toFixed(2);
}

/* ── Bulk dispense ──────────────────────────────────── */
function openBulkConfirm() {
    const items  = getCheckedItems();
    const method = document.getElementById('paymentMethod')?.value;

    if (!items.length) { showToast('warning', 'Select at least one item to dispense.'); return; }
    if (!method)       { showToast('warning', 'Please select a payment method first.'); return; }

    for (const i of items) {
        if (i.qty > i.maxStock) {
            showToast('danger', `Insufficient stock for "${i.drug}". Available: ${i.maxStock}, Requested: ${i.qty}`);
            return;
        }
        if (i.qty < 1) {
            showToast('danger', `Quantity for "${i.drug}" must be at least 1.`);
            return;
        }
    }

    let total = 0;
    const rows = items.map(i => {
        const cost = i.qty * i.price;
        total += cost;
        return `<div class="d-flex justify-content-between align-items-center" style="padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:13px">
            <div>
                <span style="font-weight:600;color:#1e293b">${i.drug}</span>
                <span style="color:#94a3b8;margin-left:6px">&times;${i.qty}</span>
            </div>
            <strong style="color:#1e293b">₦ ${cost.toFixed(2)}</strong>
        </div>`;
    }).join('');

    document.getElementById('bulk-confirm-list').innerHTML    = rows;
    document.getElementById('bulk-confirm-total').textContent = '₦ ' + total.toFixed(2);
    document.getElementById('bulk-confirm-method').textContent= method;

    const copayRow = document.getElementById('bulk-confirm-copay-row');
    if (copayRow) {
        if (_isSecondary) {
            copayRow.style.display = '';
            document.getElementById('bulk-confirm-copay').textContent = '₦ ' + (total * 0.10).toFixed(2);
        } else {
            copayRow.style.display = 'none';
        }
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkDispenseModal')).show();
}

async function executeBulkDispense() {
    const items  = getCheckedItems();
    const method = document.getElementById('paymentMethod')?.value;
    const btn    = document.getElementById('btnConfirmBulk');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing…';

    try {
        const res = await fetch(_bulkUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ items: items.map(i => ({ id: i.id, qty: i.qty })), payment_method: method }),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        bootstrap.Modal.getInstance(document.getElementById('bulkDispenseModal')).hide();
        showToast('success', data.message || 'Items dispensed successfully!');
        setTimeout(() => window.location.reload(), 1200);
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Confirm & Dispense';
        showToast('danger', e.message || 'Dispensing failed. Please try again.');
    }
}

/* ── Individual actions ─────────────────────────────── */
function confirmCancel(itemId) {
    if (confirm('Cancel this prescription item? This action cannot be undone.')) {
        doItemAction(itemId, 'cancel');
    }
}

function showEditQuantity(itemId, prescribedQty, currentQty) {
    _editItemId = itemId;
    document.getElementById('modal-prescribed-qty').textContent = prescribedQty;
    document.getElementById('modal-current-qty').textContent    = currentQty;
    document.getElementById('new-quantity').value               = currentQty;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editQuantityModal')).show();
}

function updateQuantity() {
    const qty = parseInt(document.getElementById('new-quantity').value);
    if (isNaN(qty) || qty < 0) { showToast('danger', 'Please enter a valid quantity.'); return; }
    bootstrap.Modal.getInstance(document.getElementById('editQuantityModal')).hide();
    doItemAction(_editItemId, 'update', qty);
}

async function doItemAction(itemId, action, quantity = null) {
    const btn = document.getElementById('btn-cancel-' + itemId);
    if (btn) btn.disabled = true;

    const body = { action };
    if (quantity !== null) body.quantity = quantity;

    try {
        const res = await fetch(`${_itemUrl}/${itemId}/dispense`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        showToast('success', data.message);
        setTimeout(() => window.location.reload(), 1000);
    } catch(e) {
        if (btn) btn.disabled = false;
        showToast('danger', e.message || 'Action failed.');
    }
}
</script>

<style>
@keyframes slideIn { from { transform:translateX(100%);opacity:0 } to { transform:translateX(0);opacity:1 } }
</style>

</div>{{-- end pharm-page --}}
@endsection

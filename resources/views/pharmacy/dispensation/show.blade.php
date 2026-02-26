@extends('layouts.app')
@section('title', 'Dispense Prescription')
@section('content')

<style>
:root { --pharm-primary: #016634; --pharm-primary-dark: #01552b; --pharm-primary-light: #e6f5ed; --pharm-border: #e2e8f0; }
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
.pharm-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.pharm-badge-teal { background: var(--pharm-primary-light); color: var(--pharm-primary-dark); }
.pharm-badge-amber { background: #fef3c7; color: #92400e; }
.pharm-badge-green { background: #dcfce7; color: #166534; }
.pharm-badge-red { background: #fee2e2; color: #991b1b; }
.pharm-badge-blue { background: #dbeafe; color: #1e40af; }
.pharm-badge-gray { background: #f1f5f9; color: #475569; }
.pharm-avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--pharm-primary-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; }
.pharm-avatar .material-symbols-outlined { font-size: 28px; color: var(--pharm-primary); }
.pharm-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.pharm-table thead th { background: #f8fafc; padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 2px solid var(--pharm-border); white-space: nowrap; }
.pharm-table tbody td { padding: 12px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pharm-table tbody tr:hover { background: #f8fafc; }
.drug-name { font-weight: 600; color: #1e293b; font-size: 13px; }
.drug-detail { color: #94a3b8; font-size: 11px; }
.pharm-qty-input { width: 72px; padding: 6px 8px; border: 1.5px solid var(--pharm-border); border-radius: 8px; text-align: center; font-size: 13px; font-weight: 600; transition: border-color .2s; }
.pharm-qty-input:focus { border-color: var(--pharm-primary); outline: none; box-shadow: 0 0 0 3px rgba(1,102,52,.12); }
.pharm-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all .15s; text-decoration: none; }
.pharm-btn-success { background: #059669; color: #fff; }
.pharm-btn-success:hover { background: #047857; color: #fff; }
.pharm-btn-outline { background: transparent; border: 1.5px solid var(--pharm-border); color: #64748b; }
.pharm-btn-outline:hover { border-color: #cbd5e1; background: #f8fafc; color: #475569; }
.notes-card { background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 14px 18px; margin-bottom: 16px; }
.notes-card h6 { font-size: 12px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
.notes-card p { font-size: 13px; color: #78350f; margin: 0; }
</style>

<div class="pharm-page">

{{-- Header --}}
<div class="pharm-header d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <nav style="--bs-breadcrumb-divider: '/'" class="mb-2">
      <ol class="breadcrumb mb-0" style="font-size:12px">
        <li class="breadcrumb-item"><a href="{{ route('pharmacy.dashboard') }}" style="color:rgba(255,255,255,.7)">Pharmacy</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dispensation.index') }}" style="color:rgba(255,255,255,.7)">Queue</a></li>
        <li class="breadcrumb-item active" style="color:#fff">Dispense</li>
      </ol>
    </nav>
    <h4 class="mb-0">Dispense Prescription</h4>
  </div>
  <a href="{{ route('dispensation.index') }}" class="pharm-btn pharm-btn-outline" style="border-color:rgba(255,255,255,.3);color:#fff">
    <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Queue
  </a>
</div>

<div class="row g-3">
  {{-- Sidebar --}}
  <div class="col-lg-4">
    <div style="position:sticky;top:20px">

    {{-- Patient card --}}
    <div class="pharm-card">
      <div class="pharm-card-header" style="color:var(--pharm-primary)">
        <span class="material-symbols-outlined" style="font-size:16px">person</span> Patient
      </div>
      <div class="pharm-card-body text-center" style="padding-bottom:12px">
        <div class="pharm-avatar"><span class="material-symbols-outlined">person</span></div>
        <div style="font-weight:700;font-size:15px;color:#1e293b">{{ $prescription->encounter?->patient_name ?? 'Unknown' }}</div>
        <span class="pharm-badge pharm-badge-teal mt-1">{{ $prescription->encounter?->patient_boschma_no ?? 'N/A' }}</span>
      </div>
      <div style="padding:0 18px 14px">
        <div class="pharm-info-row">
          <span class="label">Prescribed By</span>
          <span class="value">{{ $prescription->prescribedBy?->name ?? 'Unknown' }}</span>
        </div>
        <div class="pharm-info-row">
          <span class="label">Date</span>
          <span class="value">{{ $prescription->created_at->format('d M Y H:i') }}</span>
        </div>
        <div class="pharm-info-row">
          <span class="label">Status</span>
          <span class="pharm-badge {{ $prescription->status == 'dispensed' ? 'pharm-badge-green' : 'pharm-badge-amber' }}">
            {{ ucfirst($prescription->status) }}
          </span>
        </div>
      </div>
    </div>

    @if($prescription->notes)
    <div class="notes-card">
      <h6><span class="material-symbols-outlined" style="font-size:15px">note</span> Doctor's Notes</h6>
      <p>{{ $prescription->notes }}</p>
    </div>
    @endif

    </div>
  </div>

  {{-- Main --}}
  <div class="col-lg-8">
    <form action="{{ route('dispensation.dispense', $prescription->id) }}" method="POST">
      @csrf

      <div class="pharm-card">
        <div class="pharm-card-header" style="color:#1e293b">
          <span class="material-symbols-outlined" style="font-size:16px;color:var(--pharm-primary)">medication</span>
          Prescription Items
        </div>
        <div style="overflow-x:auto">
          <table class="pharm-table">
            <thead>
              <tr>
                <th>Drug</th>
                <th style="text-align:center">Dosage</th>
                <th style="text-align:center">Freq</th>
                <th style="text-align:center">Days</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:center">Stock</th>
                <th style="text-align:center">Dispense</th>
              </tr>
            </thead>
            <tbody>
              @foreach($prescription->items as $item)
              @php $stock = $item->drug?->current_stock ?? 0; @endphp
              <tr>
                <td>
                  <div class="drug-name">{{ $item->drug?->name ?? 'Unknown' }}</div>
                  <div class="drug-detail">{{ $item->drug?->strength ?? '' }}</div>
                </td>
                <td style="text-align:center;color:#64748b;font-size:12px">{{ $item->dosage ?? '—' }}</td>
                <td style="text-align:center;color:#64748b;font-size:12px">{{ $item->frequency ?? '—' }}</td>
                <td style="text-align:center;color:#64748b;font-size:12px">{{ $item->duration ?? '—' }}</td>
                <td style="text-align:center"><span class="pharm-badge pharm-badge-blue">{{ $item->quantity }}</span></td>
                <td style="text-align:center">
                  <span class="pharm-badge {{ $stock >= $item->quantity ? 'pharm-badge-green' : 'pharm-badge-red' }}">{{ $stock }}</span>
                </td>
                <td style="text-align:center">
                  <input type="hidden" name="items[{{ $item->id }}][prescription_item_id]" value="{{ $item->id }}">
                  <input type="number"
                    name="items[{{ $item->id }}][quantity_dispensed]"
                    class="pharm-qty-input"
                    value="{{ min($item->quantity, $stock) }}"
                    min="0" max="{{ $stock }}">
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="pharm-card">
        <div class="pharm-card-header" style="color:#64748b">
          <span class="material-symbols-outlined" style="font-size:16px">edit_note</span> Dispense Notes
        </div>
        <div class="pharm-card-body">
          <textarea name="dispensation_notes" class="form-control" rows="2" placeholder="Any notes about the dispense…"
            style="border-radius:8px;border:1.5px solid var(--pharm-border);font-size:13px;resize:vertical"></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mb-4">
        <button type="submit" class="pharm-btn pharm-btn-success">
          <span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Complete Dispense
        </button>
        <a href="{{ route('dispensation.index') }}" class="pharm-btn pharm-btn-outline">
          <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span> Back to Queue
        </a>
      </div>
    </form>
  </div>
</div>

</div>
@endsection

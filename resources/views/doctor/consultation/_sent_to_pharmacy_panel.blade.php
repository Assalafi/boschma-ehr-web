@if($sentPrescriptions->isNotEmpty())
<div class="consult-card mb-4" id="sentToPharmacyPanel">
    <div class="consult-card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size:20px;color:#016634">local_pharmacy</span>
            <span>Sent to Pharmacy</span>
            <span class="badge rounded-pill ms-1" style="background:#016634;color:#fff;font-size:11px">
                {{ $sentPrescriptions->sum(fn($rx) => $rx->items->count()) }} item(s)
            </span>
        </div>
        <small class="text-muted">Recall removes the item from the pharmacy queue</small>
    </div>

    @foreach($sentPrescriptions as $rx)
    <div class="px-3 pt-3 pb-1 {{ !$loop->last ? 'border-bottom mb-2' : '' }}">
        <div class="d-flex align-items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-muted" style="font-size:16px">receipt_long</span>
            <code style="font-size:12px;color:#016634">{{ $rx->prescription_number ?? '—' }}</code>
            <small class="text-muted">{{ $rx->prescription_date ? \Carbon\Carbon::parse($rx->prescription_date)->format('d M Y H:i') : $rx->created_at->format('d M Y H:i') }}</small>
            @php
                $rxBadge = match($rx->status) {
                    'Pending'              => ['warning','text-dark','pending'],
                    'Partially Dispensed'  => ['info','text-white','partial'],
                    'Fully Dispensed'      => ['success','text-white','check_circle'],
                    'Cancelled'            => ['danger','text-white','cancel'],
                    default                => ['secondary','text-white','help'],
                };
            @endphp
            <span class="badge bg-{{ $rxBadge[0] }} {{ $rxBadge[1] }}" style="font-size:11px">{{ $rx->status }}</span>
        </div>

        <div class="table-responsive mb-2">
            <table class="table table-sm align-middle mb-0" style="font-size:13px">
                <thead>
                    <tr style="background:#f8fffe;border-bottom:1.5px solid #e0f0eb">
                        <th class="ps-2 py-1" style="color:#016634;font-weight:600">Drug</th>
                        <th class="py-1" style="color:#016634;font-weight:600">Dosage</th>
                        <th class="py-1" style="color:#016634;font-weight:600">Freq</th>
                        <th class="py-1" style="color:#016634;font-weight:600">Days</th>
                        <th class="text-center py-1" style="color:#016634;font-weight:600">Qty</th>
                        <th class="py-1" style="color:#016634;font-weight:600">Status</th>
                        <th class="text-center py-1" style="color:#016634;font-weight:600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rx->items as $rxItem)
                    <tr id="rx-item-row-{{ $rxItem->id }}" class="{{ $rxItem->dispensing_status === 'Cancelled' ? 'opacity-50' : '' }}">
                        <td class="ps-2 py-2">
                            <strong>{{ $rxItem->drug?->name ?? 'Unknown' }}</strong>
                            @if($rxItem->drug?->strength)
                            <br><small class="text-muted">{{ $rxItem->drug->strength }}</small>
                            @endif
                        </td>
                        <td class="py-2">{{ $rxItem->dosage ?? '—' }}</td>
                        <td class="py-2">{{ match((string)$rxItem->frequency) {
                            '1' => 'OD', '2' => 'BD', '3' => 'TDS', '4' => 'QDS', default => $rxItem->frequency ?? '—'
                        } }}</td>
                        <td class="py-2">{{ $rxItem->duration ? $rxItem->duration.'d' : '—' }}</td>
                        <td class="text-center py-2">
                            <span class="badge bg-primary rounded-pill">{{ $rxItem->quantity }}</span>
                        </td>
                        <td class="py-2" id="rx-status-{{ $rxItem->id }}">
                            @if($rxItem->dispensing_status === \App\Models\PrescriptionItem::STATUS_DISPENSED)
                                <span class="badge bg-success" style="font-size:11px">
                                    <span class="material-symbols-outlined align-middle" style="font-size:11px">check_circle</span> Dispensed
                                </span>
                            @elseif($rxItem->dispensing_status === 'Cancelled')
                                <span class="badge bg-secondary" style="font-size:11px">Recalled</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size:11px">Pending</span>
                            @endif
                        </td>
                        <td class="text-center py-2">
                            @if($rxItem->dispensing_status === \App\Models\PrescriptionItem::STATUS_PENDING)
                            <button type="button"
                                id="recall-btn-{{ $rxItem->id }}"
                                class="btn btn-sm btn-outline-danger px-2 py-1"
                                style="font-size:12px"
                                onclick="recallItem('{{ $rxItem->id }}', '{{ addslashes($rxItem->drug?->name ?? 'this item') }}')">
                                <span class="material-symbols-outlined align-middle" style="font-size:14px">undo</span> Recall
                            </button>
                            @elseif($rxItem->dispensing_status === \App\Models\PrescriptionItem::STATUS_DISPENSED)
                            <span class="text-success" style="font-size:12px">
                                <span class="material-symbols-outlined align-middle" style="font-size:14px">lock</span>
                            </span>
                            @else
                            <span class="text-muted" style="font-size:12px">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif

@extends('layouts.app')

@section('title', 'Lab Orders')

@section('content')
<style>
:root{--lab:#016634;--lab-dk:#01552b;--lab-lt:#e6f5ed;--lb:#e2e8f0;--bs-primary:#016634;--bs-primary-rgb:1,102,52}
.lab-page{font-size:14px}
.lab-header{background:linear-gradient(135deg,var(--lab-dk),var(--lab));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.lab-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.lab-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.lab-header .breadcrumb-item.active{color:#fff}
.lab-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
.lab-card-header{padding:16px 20px;font-weight:600;font-size:13px;border-bottom:1px solid var(--lb);display:flex;align-items:center;justify-content:space-between;color:#1e293b}
.stat-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);padding:20px;text-align:center;transition:transform .15s,box-shadow .15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.08)}
.stat-card .stat-val{font-size:28px;font-weight:800;line-height:1;margin-bottom:4px}
.stat-card .stat-lbl{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px}
.lab-table{width:100%;border-collapse:separate;border-spacing:0}
.lab-table thead th{background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid var(--lb);white-space:nowrap}
.lab-table tbody td{padding:12px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.lab-table tbody tr:hover{background:#f8fafb}
.lab-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.lab-badge-amber{background:#fef3c7;color:#92400e}.lab-badge-blue{background:#dbeafe;color:#1e40af}
.lab-badge-green{background:#dcfce7;color:#166534}.lab-badge-red{background:#fee2e2;color:#991b1b}
.lab-badge-gray{background:#f1f5f9;color:#475569}.lab-badge-teal{background:var(--lab-lt);color:var(--lab-dk)}
.lab-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-btn-primary{background:var(--lab);color:#fff}.lab-btn-primary:hover{background:var(--lab-dk);color:#fff}
.lab-btn-outline{background:#fff;color:var(--lab);border:1.5px solid var(--lab)}.lab-btn-outline:hover{background:var(--lab-lt);color:var(--lab)}
.filter-pills{display:flex;gap:4px;flex-wrap:wrap}
.filter-pill{padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none!important;transition:all .15s;border:1.5px solid var(--lb);color:#64748b;background:#fff}
.filter-pill:hover{border-color:var(--lab);color:var(--lab)}
.filter-pill.active{background:var(--lab);color:#fff!important;border-color:var(--lab)}
.empty-state{padding:60px 20px;text-align:center}
.empty-state .material-symbols-outlined{font-size:64px;color:#cbd5e1}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Laboratory Orders</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratory.dashboard') }}">Laboratory</a></li>
            <li class="breadcrumb-item active">Orders</li>
        </ol>
    </nav>
</div>

<div class="row g-3 mb-4">
    @php
    $stats = [
        ['Pending',          $pendingCount ?? 0,          '#f59e0b', '#fef3c7'],
        ['Sample Collected', $sampleCollectedCount ?? 0,  '#3b82f6', '#dbeafe'],
        ['Processing',       $processingCount ?? 0,       '#016634', '#e6f5ed'],
        ['Completed Today',  $completedCount ?? 0,        '#10b981', '#dcfce7'],
    ];
    @endphp
    @foreach($stats as [$lbl,$val,$color,$bg])
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
            <div class="stat-lbl">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="lab-card mb-4">
    <div class="lab-card-header">
        <span class="d-flex align-items-center gap-2"><span class="material-symbols-outlined" style="font-size:18px;color:var(--lab)">science</span> Investigation Orders</span>
        <div class="filter-pills">
            <a href="{{ route('lab-orders.index', ['status' => 'pending']) }}" class="filter-pill {{ request('status') == 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('lab-orders.index', ['status' => 'collected']) }}" class="filter-pill {{ request('status') == 'collected' ? 'active' : '' }}">Collected</a>
            <a href="{{ route('lab-orders.index', ['status' => 'processing']) }}" class="filter-pill {{ request('status') == 'processing' ? 'active' : '' }}">Processing</a>
            <a href="{{ route('lab-orders.index', ['status' => 'completed']) }}" class="filter-pill {{ request('status') == 'completed' ? 'active' : '' }}">Completed</a>
            <a href="{{ route('lab-orders.index') }}" class="filter-pill {{ !request('status') ? 'active' : '' }}">All</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="lab-table">
            <thead>
                <tr>
                    <th style="padding-left:20px">Order #</th>
                    <th>Patient</th>
                    <th>Test</th>
                    <th>Ordered By</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders ?? [] as $order)
                @php
                    $statusMap = [
                        'pending' => 'amber',
                        'sample_collected' => 'blue',
                        'processing' => 'teal',
                        'completed' => 'green',
                        'cancelled' => 'red',
                    ];
                    $badgeClass = $statusMap[$order->status] ?? 'gray';
                @endphp
                <tr>
                    <td style="padding-left:20px"><span class="lab-badge lab-badge-gray" style="font-family:monospace">{{ $order->order_number ?? substr($order->id, 0, 8) }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <span class="material-symbols-outlined" style="font-size:18px;color:#94a3b8">person</span>
                            </div>
                            <div>
                                <div class="fw-semibold" style="color:#1e293b;font-size:13px">{{ $order->encounter?->patient_name ?? 'Unknown' }}</div>
                                <div class="text-muted" style="font-size:11px">{{ $order->encounter?->patient_boschma_no ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px">{{ $order->laboratoryTest?->name ?? $order->test_name ?? 'Unknown Test' }}</td>
                    <td class="text-muted" style="font-size:12px">{{ $order->orderedBy?->name ?? 'Unknown' }}</td>
                    <td><span class="lab-badge lab-badge-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td>
                    <td class="text-muted" style="font-size:12px">{{ $order->created_at->format('H:i') }}</td>
                    <td style="text-align:center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if($order->status == 'pending')
                            <a href="{{ route('lab-orders.collect', $order->id) }}" class="lab-btn" style="background:#dbeafe;color:#1e40af;font-size:11px;padding:5px 10px" title="Collect Sample">
                                <span class="material-symbols-outlined" style="font-size:14px">science</span>
                            </a>
                            @elseif($order->status == 'sample_collected' || $order->status == 'processing')
                            <a href="{{ route('lab-results.create', $order->id) }}" class="lab-btn" style="background:#dcfce7;color:#166534;font-size:11px;padding:5px 10px" title="Enter Result">
                                <span class="material-symbols-outlined" style="font-size:14px">edit_note</span>
                            </a>
                            @endif
                            <a href="{{ route('lab-orders.show', $order->id) }}" class="lab-btn lab-btn-outline" style="font-size:11px;padding:5px 10px" title="View">
                                <span class="material-symbols-outlined" style="font-size:14px">visibility</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="material-symbols-outlined">science</span>
                            <p class="mt-3 mb-1 fw-semibold" style="color:#1e293b">No orders found</p>
                            <p class="small text-muted mb-0">No laboratory orders match the current filter</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($orders) && $orders->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--lb)">
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif
</div>

</div>
@endsection

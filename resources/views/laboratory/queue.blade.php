@extends('layouts.app')

@section('title', 'Lab Queue')

@section('content')
<style>
:root{--lab:#016634;--lab-dk:#01552b;--lab-lt:#e6f5ed;--lb:#e2e8f0;--bs-primary:#016634;--bs-primary-rgb:1,102,52}
.lab-page{font-size:14px}
.lab-header{background:linear-gradient(135deg,var(--lab-dk),var(--lab));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.lab-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.lab-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.lab-header .breadcrumb-item.active{color:#fff}
.lab-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
.lab-table{width:100%;border-collapse:separate;border-spacing:0}
.lab-table thead th{background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid var(--lb);white-space:nowrap}
.lab-table tbody td{padding:12px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.lab-table tbody tr:hover{background:#f8fafb}
.lab-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.lab-badge-amber{background:#fef3c7;color:#92400e}.lab-badge-blue{background:#dbeafe;color:#1e40af}
.lab-badge-green{background:#dcfce7;color:#166534}.lab-badge-gray{background:#f1f5f9;color:#475569}
.lab-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-btn-primary{background:var(--lab);color:#fff}.lab-btn-primary:hover{background:var(--lab-dk);color:#fff}
.lab-btn-outline{background:#fff;color:var(--lab);border:1.5px solid var(--lab)}.lab-btn-outline:hover{background:var(--lab-lt);color:var(--lab)}
.lab-tabs{display:flex;gap:4px;margin-bottom:0}
.lab-tab{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px 10px 0 0;font-size:13px;font-weight:600;color:#64748b;text-decoration:none!important;background:#f1f5f9;border:1px solid transparent;border-bottom:none;transition:all .15s}
.lab-tab:hover{color:var(--lab);background:#fff}
.lab-tab.active{background:#fff;color:var(--lab);border-color:var(--lb);position:relative}
.lab-tab.active::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:2px;background:#fff}
.lab-tab .tab-count{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.search-bar{padding:16px 20px;border-bottom:1px solid var(--lb);display:flex;gap:10px;align-items:center}
.search-bar input{border:1.5px solid var(--lb);border-radius:10px;padding:8px 14px;font-size:13px;max-width:360px;width:100%;transition:border-color .15s}
.search-bar input:focus{outline:none;border-color:var(--lab);box-shadow:0 0 0 3px rgba(1,102,52,.1)}
.empty-state{padding:60px 20px;text-align:center}
.empty-state .material-symbols-outlined{font-size:64px;color:#cbd5e1}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Lab Queue</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratory.dashboard') }}">Laboratory</a></li>
            <li class="breadcrumb-item active">Queue</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" style="background:#dcfce7;color:#166534;border-left:4px solid #10b981!important">
    <span class="material-symbols-outlined align-middle me-2" style="font-size:18px">check_circle</span>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="lab-tabs">
    @php $tabs = [['pending','Pending','#f59e0b','#fef3c7','assignment_late'],['in_progress','In Progress','#3b82f6','#dbeafe','labs'],['completed','Completed','#10b981','#dcfce7','task_alt']]; @endphp
    @foreach($tabs as [$key,$label,$color,$bg,$icon])
    <a class="lab-tab {{ $tab===$key?'active':'' }}" href="{{ route('laboratory.queue', array_merge(request()->query(), ['tab'=>$key])) }}">
        <span class="material-symbols-outlined" style="font-size:16px">{{ $icon }}</span>
        {{ $label }}
        <span class="tab-count" style="background:{{ $bg }};color:{{ $color }}">{{ $counts[$key] }}</span>
    </a>
    @endforeach
</div>

<div class="lab-card" style="border-top-left-radius:0">
    <div class="search-bar">
        <form method="GET" action="{{ route('laboratory.queue') }}" class="d-flex gap-2 w-100">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search patient name or file number…">
            <button type="submit" class="lab-btn lab-btn-primary"><span class="material-symbols-outlined" style="font-size:16px">search</span></button>
            @if(request('search'))
            <a href="{{ route('laboratory.queue', ['tab'=>$tab]) }}" class="lab-btn lab-btn-outline">Clear</a>
            @endif
        </form>
    </div>

    @if($orders->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined">science</span>
        <p class="mt-3 mb-1 fw-semibold" style="color:#1e293b">No {{ str_replace('_',' ',$tab) }} orders found</p>
        <p class="small text-muted mb-0">{{ request('search') ? 'Try a different search term.' : 'Orders will appear here when doctors request lab tests.' }}</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="lab-table">
            <thead>
                <tr>
                    <th style="padding-left:20px">Patient</th>
                    <th>Test</th>
                    <th>Category</th>
                    <th>Ordered By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align:right;padding-right:20px">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $item)
            @php
                $patient = $item->serviceOrder?->encounter?->patient;
                $info    = $patient?->beneficiary;
                $name    = $info?->fullname ?? $info?->name ?? 'Unknown';
                $file    = $patient?->file_number ?? '—';
                $enc     = $item->serviceOrder?->encounter;
                $sc      = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green'];
                $badge   = $sc[$item->status] ?? 'gray';
            @endphp
            <tr>
                <td style="padding-left:20px">
                    <div class="fw-semibold" style="color:#1e293b">{{ $name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $file }}</div>
                    @if($enc)
                    <div class="text-muted" style="font-size:11px">{{ $enc->visit_date?->format('d M Y') }}</div>
                    @endif
                </td>
                <td>
                    <div class="fw-semibold" style="color:#1e293b">{{ $item->serviceItem?->name ?? '—' }}</div>
                    @if($item->serviceItem?->description)
                    <div class="text-muted" style="font-size:11px">{{ Str::limit($item->serviceItem->description, 60) }}</div>
                    @endif
                </td>
                <td><span class="lab-badge lab-badge-gray">{{ $item->serviceItem?->serviceType?->name ?? '—' }}</span></td>
                <td class="text-muted" style="font-size:12px">{{ $item->serviceOrder?->orderedBy?->name ?? '—' }}</td>
                <td class="text-muted" style="font-size:12px">{{ $item->created_at->format('d M Y H:i') }}</td>
                <td>
                    <span class="lab-badge lab-badge-{{ $badge }}">
                        <span class="material-symbols-outlined" style="font-size:12px">{{ ['pending'=>'schedule','in_progress'=>'labs','completed'=>'check_circle'][$item->status] ?? 'circle' }}</span>
                        {{ ucfirst(str_replace('_',' ',$item->status)) }}
                    </span>
                    @if($item->latestResult)
                    <div style="font-size:11px;color:#10b981;margin-top:2px"><span class="material-symbols-outlined align-middle" style="font-size:12px">check_circle</span> Result ready</div>
                    @endif
                </td>
                <td style="text-align:right;padding-right:20px">
                    <div class="d-flex gap-1 justify-content-end">
                        @if($item->status === 'pending')
                        <form method="POST" action="{{ route('laboratory.order.status', $item) }}">
                            @csrf
                            <input type="hidden" name="status" value="in_progress">
                            <button class="lab-btn" style="background:#dbeafe;color:#1e40af;font-size:11px;padding:5px 10px" title="Mark In Progress">
                                <span class="material-symbols-outlined" style="font-size:14px">labs</span>
                            </button>
                        </form>
                        @elseif($item->status === 'in_progress')
                        <form method="POST" action="{{ route('laboratory.order.status', $item) }}">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button class="lab-btn" style="background:#fef3c7;color:#92400e;font-size:11px;padding:5px 10px" title="Revert to Pending">
                                <span class="material-symbols-outlined" style="font-size:14px">undo</span>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('laboratory.order.show', $item) }}" class="lab-btn lab-btn-primary" style="font-size:11px;padding:5px 10px" title="View & Record Result">
                            <span class="material-symbols-outlined" style="font-size:14px">open_in_new</span>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $orders->links() }}</div>
    @endif
</div>

</div>
@endsection

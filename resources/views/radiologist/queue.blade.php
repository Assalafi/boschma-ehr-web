@extends('layouts.app')

@section('title', 'Radiology Worklist')

@section('content')
<style>
:root{--rad:#7c3aed;--rad-dk:#6d28d9;--rad-lt:#ede9fe;--lb:#e2e8f0;--bs-primary:#7c3aed;--bs-primary-rgb:124,58,237}
.rad-page{font-size:14px}
.rad-header{background:linear-gradient(135deg,var(--rad-dk),var(--rad));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.rad-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.rad-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.rad-header .breadcrumb-item.active{color:#fff}
.rad-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
.rad-table{width:100%;border-collapse:separate;border-spacing:0}
.rad-table thead th{background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid var(--lb);white-space:nowrap}
.rad-table tbody td{padding:12px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.rad-table tbody tr:hover{background:#f8fafb}
.rad-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.rad-badge-amber{background:#fef3c7;color:#92400e}.rad-badge-blue{background:#dbeafe;color:#1e40af}
.rad-badge-green{background:#dcfce7;color:#166534}.rad-badge-gray{background:#f1f5f9;color:#475569}
.rad-badge-violet{background:#ede9fe;color:#5b21b6}
.rad-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.rad-btn-primary{background:var(--rad);color:#fff}.rad-btn-primary:hover{background:var(--rad-dk);color:#fff}
.rad-btn-outline{background:#fff;color:var(--rad);border:1.5px solid var(--rad)}.rad-btn-outline:hover{background:var(--rad-lt);color:var(--rad)}
.rad-pagination{display:flex;align-items:center;justify-content:center;gap:4px;padding:12px 20px;border-top:1px solid var(--lb)}
.rad-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border:1.5px solid var(--lb);border-radius:6px;font-size:12px;font-weight:600;color:#475569;background:#fff;cursor:pointer;transition:all .15s;text-decoration:none}
.rad-page-btn:hover{border-color:#cbd5e1;background:#f8fafc;color:#475569}
.rad-page-btn.active{background:var(--rad);border-color:var(--rad);color:#fff}
.rad-page-btn:disabled{opacity:.4;cursor:not-allowed}
.rad-tabs{display:flex;gap:4px;margin-bottom:0}
.rad-tab{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px 10px 0 0;font-size:13px;font-weight:600;color:#64748b;text-decoration:none!important;background:#f1f5f9;border:1px solid transparent;border-bottom:none;transition:all .15s}
.rad-tab:hover{color:var(--rad);background:#fff}
.rad-tab.active{background:#fff;color:var(--rad);border-color:var(--lb);position:relative}
.rad-tab.active::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:2px;background:#fff}
.rad-tab .tab-count{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700}
.search-bar{padding:16px 20px;border-bottom:1px solid var(--lb);display:flex;gap:10px;align-items:center}
.search-bar input{border:1.5px solid var(--lb);border-radius:10px;padding:8px 14px;font-size:13px;max-width:360px;width:100%;transition:border-color .15s}
.search-bar input:focus{outline:none;border-color:var(--rad);box-shadow:0 0 0 3px rgba(124,58,237,.1)}
.empty-state{padding:60px 20px;text-align:center}
.empty-state .material-symbols-outlined{font-size:64px;color:#cbd5e1}
</style>
<div class="rad-page">

<div class="rad-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Radiology Worklist</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('radiologist.dashboard') }}">Radiology</a></li>
            <li class="breadcrumb-item active">Worklist</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" style="background:#dcfce7;color:#166534;border-left:4px solid #10b981!important">
    <span class="material-symbols-outlined align-middle me-2" style="font-size:18px">check_circle</span>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="rad-tabs">
    @php $tabs = [['pending','Pending','#f59e0b','#fef3c7','assignment_late'],['in_progress','In Progress','#3b82f6','#dbeafe','radiology'],['completed','Completed','#10b981','#dcfce7','task_alt']]; @endphp
    @foreach($tabs as [$key,$label,$color,$bg,$icon])
    <a class="rad-tab {{ $tab===$key?'active':'' }}" href="{{ route('radiologist.queue', array_merge(request()->query(), ['tab'=>$key])) }}">
        <span class="material-symbols-outlined" style="font-size:16px">{{ $icon }}</span>
        {{ $label }}
        <span class="tab-count" style="background:{{ $bg }};color:{{ $color }}">{{ $counts[$key] }}</span>
    </a>
    @endforeach
</div>

<div class="rad-card" style="border-top-left-radius:0">
    <div class="search-bar">
        <form method="GET" action="{{ route('radiologist.queue') }}" class="d-flex gap-2 w-100 align-items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search patient name or file number…" style="max-width:300px">
            <select name="program" class="form-select form-select-sm" style="max-width:200px">
                <option value="">All Programs</option>
                @foreach($programs as $id=>$name)
                <option value="{{ $id }}" {{ request('program') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rad-btn rad-btn-primary"><span class="material-symbols-outlined" style="font-size:16px">search</span></button>
            @if(request('search') || request('program'))
            <a href="{{ route('radiologist.queue', ['tab'=>$tab]) }}" class="rad-btn rad-btn-outline">Clear</a>
            @endif
        </form>
    </div>

    @if($orders->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined">radiology</span>
        <p class="mt-3 mb-1 fw-semibold" style="color:#1e293b">No {{ str_replace('_',' ',$tab) }} radiology studies found</p>
        <p class="small text-muted mb-0">{{ request('search') ? 'Try a different search term.' : 'Studies will appear here when doctors request radiology investigations.' }}</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="rad-table">
            <thead>
                <tr>
                    <th style="padding-left:20px">Patient</th>
                    <th>Study / Investigation</th>
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
                $info    = $patient?->enrollee;
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
                <td><span class="rad-badge rad-badge-violet">{{ $item->serviceItem?->serviceType?->name ?? '—' }}</span></td>
                <td class="text-muted" style="font-size:12px">{{ $item->serviceOrder?->orderedBy?->name ?? '—' }}</td>
                <td class="text-muted" style="font-size:12px">{{ $item->created_at->format('d M Y H:i') }}</td>
                <td>
                    <span class="rad-badge rad-badge-{{ $badge }}">
                        <span class="material-symbols-outlined" style="font-size:12px">{{ ['pending'=>'schedule','in_progress'=>'radiology','completed'=>'check_circle'][$item->status] ?? 'circle' }}</span>
                        {{ ucfirst(str_replace('_',' ',$item->status)) }}
                    </span>
                    @if($item->latestResult)
                    <div style="font-size:11px;color:#10b981;margin-top:2px"><span class="material-symbols-outlined align-middle" style="font-size:12px">check_circle</span> Report ready</div>
                    @endif
                </td>
                <td style="text-align:right;padding-right:20px">
                    <div class="d-flex gap-1 justify-content-end">
                        @if($item->status === 'pending')
                        <form method="POST" action="{{ route('radiologist.order.status', $item) }}">
                            @csrf
                            <input type="hidden" name="status" value="in_progress">
                            <button class="rad-btn" style="background:#dbeafe;color:#1e40af;font-size:11px;padding:5px 10px" title="Mark In Progress">
                                <span class="material-symbols-outlined" style="font-size:14px">radiology</span>
                            </button>
                        </form>
                        @elseif($item->status === 'in_progress')
                        <form method="POST" action="{{ route('radiologist.order.status', $item) }}">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button class="rad-btn" style="background:#fef3c7;color:#92400e;font-size:11px;padding:5px 10px" title="Revert to Pending">
                                <span class="material-symbols-outlined" style="font-size:14px">undo</span>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('radiologist.order.show', $item) }}" class="rad-btn rad-btn-primary" style="font-size:11px;padding:5px 10px" title="View & Record Report">
                            <span class="material-symbols-outlined" style="font-size:14px">open_in_new</span>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="rad-pagination">
        @if($orders->hasPages())

            {{-- Previous --}}
            @if($orders->onFirstPage())
                <span class="rad-page-btn" disabled>
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_left</span>
                </span>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="rad-page-btn">
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_left</span>
                </a>
            @endif

            {{-- Page numbers --}}
            @php
                $onEachSide  = 3;
                $currentPage = $orders->currentPage();
                $lastPage    = $orders->lastPage();
                $start       = max(1, $currentPage - $onEachSide);
                $end         = min($lastPage, $currentPage + $onEachSide);
            @endphp

            @if($start > 1)
                <a href="{{ $orders->url(1) }}" class="rad-page-btn">1</a>
                @if($start > 2)
                    <span class="rad-page-btn disabled">...</span>
                @endif
            @endif

            @for($i = $start; $i <= $end; $i++)
                @if($i == $currentPage)
                    <span class="rad-page-btn active">{{ $i }}</span>
                @else
                    <a href="{{ $orders->url($i) }}" class="rad-page-btn">{{ $i }}</a>
                @endif
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="rad-page-btn disabled">...</span>
                @endif
                <a href="{{ $orders->url($lastPage) }}" class="rad-page-btn">{{ $lastPage }}</a>
            @endif

            {{-- Next --}}
            @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="rad-page-btn">
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_right</span>
                </a>
            @else
                <span class="rad-page-btn" disabled>
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_right</span>
                </span>
            @endif

        @endif
    </div>
    @endif
</div>

</div>
@endsection

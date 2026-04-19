@extends('layouts.app')
@section('title','Lab Results History')
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
.lab-badge-green{background:#dcfce7;color:#166534}.lab-badge-red{background:#fee2e2;color:#991b1b}.lab-badge-gray{background:#f1f5f9;color:#475569}
.lab-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-btn-primary{background:var(--lab);color:#fff}.lab-btn-primary:hover{background:var(--lab-dk);color:#fff}
.lab-btn-outline{background:#fff;color:var(--lab);border:1.5px solid var(--lab)}.lab-btn-outline:hover{background:var(--lab-lt);color:var(--lab)}
.lab-pagination{display:flex;align-items:center;justify-content:center;gap:4px;padding:12px 20px;border-top:1px solid var(--lb)}
.lab-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border:1.5px solid var(--lb);border-radius:6px;font-size:12px;font-weight:600;color:#475569;background:#fff;cursor:pointer;transition:all .15s;text-decoration:none}
.lab-page-btn:hover{border-color:#cbd5e1;background:#f8fafc;color:#475569}
.lab-page-btn.active{background:var(--lab);border-color:var(--lab);color:#fff}
.lab-page-btn:disabled{opacity:.4;cursor:not-allowed}
.filter-bar{padding:16px 20px;border-bottom:1px solid var(--lb);display:flex;gap:12px;flex-wrap:wrap;align-items:end}
.filter-bar label{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;display:block}
.filter-bar input,.filter-bar select{border:1.5px solid var(--lb);border-radius:10px;padding:8px 14px;font-size:13px;transition:border-color .15s}
.filter-bar input:focus,.filter-bar select:focus{outline:none;border-color:var(--lab);box-shadow:0 0 0 3px rgba(1,102,52,.1)}
.empty-state{padding:60px 20px;text-align:center}
.empty-state .material-symbols-outlined{font-size:64px;color:#cbd5e1}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Results History</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item"><a href="{{ route('laboratory.dashboard') }}">Laboratory</a></li>
            <li class="breadcrumb-item active">History</li>
        </ol>
    </nav>
</div>

<div class="lab-card">
    <form method="GET" action="{{ route('laboratory.history') }}" class="filter-bar">
        <div>
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Patient name or file no." style="width:220px">
        </div>
        <div>
            <label>Program</label>
            <select name="program" style="width:180px">
                <option value="">All Programs</option>
                @foreach($programs as $id=>$name)
                <option value="{{ $id }}" {{ request('program') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width:160px">
        </div>
        <div>
            <label>To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width:160px">
        </div>
        <div class="d-flex gap-2" style="padding-bottom:1px">
            <button type="submit" class="lab-btn lab-btn-primary"><span class="material-symbols-outlined" style="font-size:16px">search</span> Filter</button>
            @if(request()->hasAny(['search','program','date_from','date_to']))
            <a href="{{ route('laboratory.history') }}" class="lab-btn lab-btn-outline">Clear</a>
            @endif
        </div>
    </form>

    @if($orders->isEmpty())
    <div class="empty-state">
        <span class="material-symbols-outlined">history</span>
        <p class="mt-3 mb-1 fw-semibold" style="color:#1e293b">No completed results found</p>
        <p class="small text-muted mb-0">{{ request()->hasAny(['search','date_from','date_to']) ? 'Try adjusting your filters.' : 'Completed results will appear here.' }}</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="lab-table">
            <thead>
                <tr>
                    <th style="padding-left:20px">Patient</th>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Remark</th>
                    <th>Reported By</th>
                    <th>Completed</th>
                    <th style="text-align:right;padding-right:20px">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $item)
            @php
                $patient=$item->serviceOrder?->encounter?->patient;
                $info=$patient?->enrollee;
                $name=$info?->fullname??$info?->name??'Unknown';
                $res=$item->latestResult;
            @endphp
            <tr>
                <td style="padding-left:20px">
                    <div class="fw-semibold" style="color:#1e293b">{{ $name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $patient?->file_number??'—' }}</div>
                </td>
                <td>
                    <div class="fw-semibold" style="color:#1e293b">{{ $item->serviceItem?->name??'—' }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $item->serviceItem?->serviceType?->name }}</div>
                </td>
                <td>
                    @if($res?->result_value)
                    <span class="fw-semibold" style="color:#1e293b">{{ $res->result_value }}</span>
                    @if($res->reference_range)<br><span class="text-muted" style="font-size:11px">Ref: {{ $res->reference_range }}</span>@endif
                    @else<span class="text-muted" style="font-size:11px">—</span>@endif
                </td>
                <td>
                    @if($res?->remark)
                    @php $rc=in_array($res->remark,['Normal','Negative'])?'green':'red'; @endphp
                    <span class="lab-badge lab-badge-{{ $rc }}">{{ $res->remark }}</span>
                    @else<span class="text-muted" style="font-size:11px">—</span>@endif
                </td>
                <td class="text-muted" style="font-size:12px">{{ $res?->reportedBy?->name??$item->serviceOrder?->orderedBy?->name??'—' }}</td>
                <td class="text-muted" style="font-size:12px">{{ $item->updated_at->format('d M Y') }}</td>
                <td style="text-align:right;padding-right:20px">
                    <a href="{{ route('laboratory.order.show',$item) }}" class="lab-btn lab-btn-outline" style="font-size:11px;padding:5px 10px" title="View">
                        <span class="material-symbols-outlined" style="font-size:14px">open_in_new</span> View
                    </a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="lab-pagination">
        @if($orders->hasPages())
            {{-- Previous Button --}}
            @if($orders->onFirstPage())
                <span class="lab-page-btn" disabled>
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_left</span>
                </span>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="lab-page-btn">
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_left</span>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach($elements = $orders->links()->elements as $element)
                @if(is_string($element))
                    <span class="lab-page-btn disabled">{{ $element }}</span>
                @elseif(isset($element['url']))
                    <a href="{{ $element['url'] }}" class="lab-page-btn {{ $element['active'] ? 'active' : '' }}">
                        {{ $element['page'] }}
                    </a>
                @else
                    <span class="lab-page-btn disabled">...</span>
                @endif
            @endforeach

            {{-- Next Button --}}
            @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="lab-page-btn">
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_right</span>
                </a>
            @else
                <span class="lab-page-btn" disabled>
                    <span class="material-symbols-outlined" style="font-size:14px">chevron_right</span>
                </span>
            @endif
        @endif
    </div>
    @endif
</div>

</div>
@endsection

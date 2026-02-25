@extends('layouts.app')

@section('title', 'Laboratory Dashboard')

@section('content')
<style>
:root{--lab:#016634;--lab-dk:#01552b;--lab-lt:#e6f5ed;--lb:#e2e8f0;--bs-primary:#016634;--bs-primary-rgb:1,102,52}
.lab-page{font-size:14px}
.lab-header{background:linear-gradient(135deg,var(--lab-dk),var(--lab));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.lab-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.lab-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.lab-header .breadcrumb-item.active{color:#fff}
.stat-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);padding:22px;display:flex;align-items:center;gap:16px;transition:transform .15s,box-shadow .15s;height:100%;text-decoration:none!important;color:inherit!important}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.08)}
.stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon .material-symbols-outlined{font-size:26px}
.stat-label{font-size:12px;color:#64748b;font-weight:500;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px}
.stat-value{font-size:26px;font-weight:800;color:#1e293b;line-height:1}
.action-card{background:#fff;border-radius:14px;border:1px solid var(--lb);padding:24px;text-align:center;text-decoration:none!important;color:#1e293b!important;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:10px;height:100%}
.action-card:hover{border-color:var(--lab);box-shadow:0 4px 12px rgba(1,102,52,.12);transform:translateY(-2px)}
.action-card .action-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center}
.action-card .action-icon .material-symbols-outlined{font-size:28px}
.action-card .action-label{font-weight:600;font-size:13px}
.action-card .action-desc{font-size:11px;color:#94a3b8}
.section-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title .material-symbols-outlined{font-size:18px;color:var(--lab)}
.overview-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;height:100%}
.overview-header{padding:16px 20px;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--lb);display:flex;align-items:center;gap:8px;color:#1e293b}
.progress{height:6px;border-radius:3px}
.btn-primary{--bs-btn-bg:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#01552b;--bs-btn-hover-border-color:#01552b;--bs-btn-active-bg:#01552b;--bs-btn-active-border-color:#014a24}
.btn-outline-primary{--bs-btn-color:#016634;--bs-btn-border-color:#016634;--bs-btn-hover-bg:#016634;--bs-btn-hover-border-color:#016634;--bs-btn-active-bg:#016634;--bs-btn-active-border-color:#016634}
</style>
<div class="lab-page">

<div class="lab-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Laboratory Dashboard</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item active">Laboratory</li>
        </ol>
    </nav>
</div>

<div class="section-title"><span class="material-symbols-outlined">monitoring</span> Overview</div>
<div class="row g-3 mb-4">
    @php
    $tiles = [
        ['Pending Orders',    $stats['pending'],         '#f59e0b','#fef3c7','assignment_late', route('laboratory.queue', ['tab'=>'pending'])],
        ['In Progress',       $stats['in_progress'],     '#3b82f6','#dbeafe','labs',            route('laboratory.queue', ['tab'=>'in_progress'])],
        ['Completed Today',   $stats['completed_today'], '#10b981','#dcfce7','task_alt',        route('laboratory.history')],
        ['Total Completed',   $stats['completed_total'], '#016634','#e6f5ed','verified',        route('laboratory.history')],
        ["Today's Orders",    $stats['today_total'],     '#6366f1','#e0e7ff','today',           route('laboratory.queue')],
        ['This Week',         $stats['week_total'],       '#64748b','#f1f5f9','date_range',      route('laboratory.history')],
    ];
    @endphp
    @foreach($tiles as [$label,$val,$color,$bg,$icon,$link])
    <div class="col-xxl-2 col-md-4 col-sm-6">
        <a href="{{ $link }}" class="stat-card">
            <div class="stat-icon" style="background:{{ $bg }}">
                <span class="material-symbols-outlined" style="color:{{ $color }}">{{ $icon }}</span>
            </div>
            <div>
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value" style="color:{{ $color }}">{{ $val }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="section-title"><span class="material-symbols-outlined">bolt</span> Quick Actions</div>
        <div class="row g-3">
            @php
            $actions = [
                ['Pending Queue',   $stats['pending'].' orders waiting', '#f59e0b','#fef3c7','assignment_late', route('laboratory.queue',['tab'=>'pending'])],
                ['In Progress',     $stats['in_progress'].' orders active','#3b82f6','#dbeafe','labs',          route('laboratory.queue',['tab'=>'in_progress'])],
                ['Results History', 'View completed results',              '#10b981','#dcfce7','history',         route('laboratory.history')],
                ['Full Queue',      'All lab orders',                      '#016634','#e6f5ed','queue',           route('laboratory.queue')],
            ];
            @endphp
            @foreach($actions as [$lbl,$desc,$color,$bg,$icon,$link])
            <div class="col-sm-6">
                <a href="{{ $link }}" class="action-card">
                    <div class="action-icon" style="background:{{ $bg }}">
                        <span class="material-symbols-outlined" style="color:{{ $color }}">{{ $icon }}</span>
                    </div>
                    <div class="action-label">{{ $lbl }}</div>
                    <div class="action-desc">{{ $desc }}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-4">
        <div class="section-title"><span class="material-symbols-outlined">pie_chart</span> Status Breakdown</div>
        <div class="overview-card">
            <div class="overview-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--lab)">analytics</span> Workload</div>
            <div class="p-3">
                @php
                $total = max(1, $stats['pending'] + $stats['in_progress'] + $stats['completed_total']);
                $rows = [
                    ['Pending',     $stats['pending'],         '#f59e0b'],
                    ['In Progress', $stats['in_progress'],     '#3b82f6'],
                    ['Completed',   $stats['completed_total'], '#10b981'],
                ];
                @endphp
                @foreach($rows as [$lbl,$n,$c])
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:12px;font-weight:600;color:#475569">{{ $lbl }}</span>
                        <span style="font-size:12px;font-weight:700;color:{{ $c }}">{{ $n }}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width:{{ round($n/$total*100) }}%;background:{{ $c }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

</div>
@endsection

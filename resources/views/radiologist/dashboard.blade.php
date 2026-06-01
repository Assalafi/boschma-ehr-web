@extends('layouts.app')

@section('title', 'Radiologist Dashboard')

@section('content')
<style>
:root{--rad:#7c3aed;--rad-dk:#6d28d9;--rad-lt:#ede9fe;--lb:#e2e8f0;--bs-primary:#7c3aed;--bs-primary-rgb:124,58,237}
.rad-page{font-size:14px}
.rad-header{background:linear-gradient(135deg,var(--rad-dk),var(--rad));border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px}
.rad-header h4{font-weight:700;letter-spacing:-.3px;margin-bottom:0;color:#fff}
.rad-header .breadcrumb-item a{color:rgba(255,255,255,.7)!important;text-decoration:none}
.rad-header .breadcrumb-item.active{color:#fff}
.stat-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);padding:22px;display:flex;align-items:center;gap:16px;transition:transform .15s,box-shadow .15s;height:100%;text-decoration:none!important;color:inherit!important}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.08)}
.stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon .material-symbols-outlined{font-size:26px}
.stat-label{font-size:12px;color:#64748b;font-weight:500;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px}
.stat-value{font-size:26px;font-weight:800;color:#1e293b;line-height:1}
.action-card{background:#fff;border-radius:14px;border:1px solid var(--lb);padding:24px;text-align:center;text-decoration:none!important;color:#1e293b!important;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:10px;height:100%}
.action-card:hover{border-color:var(--rad);box-shadow:0 4px 12px rgba(124,58,237,.12);transform:translateY(-2px)}
.action-card .action-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center}
.action-card .action-icon .material-symbols-outlined{font-size:28px}
.action-card .action-label{font-weight:600;font-size:13px}
.action-card .action-desc{font-size:11px;color:#94a3b8}
.section-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title .material-symbols-outlined{font-size:18px;color:var(--rad)}
.overview-card{background:#fff;border-radius:14px;border:1px solid var(--lb);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;height:100%}
.overview-header{padding:16px 20px;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--lb);display:flex;align-items:center;gap:8px;color:#1e293b}
.progress{height:6px;border-radius:3px}
.btn-primary{--bs-btn-bg:#7c3aed;--bs-btn-border-color:#7c3aed;--bs-btn-hover-bg:#6d28d9;--bs-btn-hover-border-color:#6d28d9}
</style>
<div class="rad-page">

<div class="rad-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4><span class="material-symbols-outlined align-middle me-2" style="font-size:22px">radiology</span>Radiologist Dashboard</h4>
    <nav style="--bs-breadcrumb-divider:'>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1" style="font-size:12px">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="d-flex align-items-center"><span class="material-symbols-outlined" style="font-size:16px">home</span></a></li>
            <li class="breadcrumb-item active">Radiology</li>
        </ol>
    </nav>
</div>

<div class="section-title"><span class="material-symbols-outlined">monitoring</span> Overview</div>
<div class="row g-3 mb-4">
    @php
    $tiles = [
        ['Pending Studies',   $stats['pending'],         '#f59e0b','#fef3c7','assignment_late', route('radiologist.queue', ['tab'=>'pending'])],
        ['In Progress',       $stats['in_progress'],     '#3b82f6','#dbeafe','radiology',       route('radiologist.queue', ['tab'=>'in_progress'])],
        ['Reported Today',    $stats['completed_today'], '#10b981','#dcfce7','task_alt',        route('radiologist.history')],
        ['Total Reported',    $stats['completed_total'], '#7c3aed','#ede9fe','verified',        route('radiologist.history')],
        ["Today's Studies",   $stats['today_total'],     '#6366f1','#e0e7ff','today',           route('radiologist.queue')],
        ['This Week',         $stats['week_total'],      '#64748b','#f1f5f9','date_range',      route('radiologist.history')],
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
                ['Pending Worklist', $stats['pending'].' studies awaiting',    '#f59e0b','#fef3c7','assignment_late', route('radiologist.queue',['tab'=>'pending'])],
                ['In Progress',      $stats['in_progress'].' studies active',  '#3b82f6','#dbeafe','radiology',      route('radiologist.queue',['tab'=>'in_progress'])],
                ['Reports History',  'View completed radiology reports',        '#10b981','#dcfce7','history',        route('radiologist.history')],
                ['Full Worklist',    'All radiology investigations',            '#7c3aed','#ede9fe','queue',          route('radiologist.queue')],
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
            <div class="overview-header"><span class="material-symbols-outlined" style="font-size:16px;color:var(--rad)">analytics</span> Workload</div>
            <div class="p-3">
                @php
                $total = max(1, $stats['pending'] + $stats['in_progress'] + $stats['completed_total']);
                $rows = [
                    ['Pending',     $stats['pending'],         '#f59e0b'],
                    ['In Progress', $stats['in_progress'],     '#3b82f6'],
                    ['Reported',    $stats['completed_total'], '#10b981'],
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

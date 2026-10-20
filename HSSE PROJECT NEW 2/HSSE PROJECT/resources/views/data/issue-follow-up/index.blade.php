@extends('layouts.app', [
    'title' => 'Issue & Follow Up - Pemantauan Temuan HSSE',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .filter-grid-ifu {
        display: grid;
        grid-template-columns: 135px 135px 145px 175px 135px 140px 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .stats-grid-hsse {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .charts-row-hsse {
        display: grid;
        grid-template-columns: 1.25fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .donut-layout-hsse {
        display: grid;
        grid-template-columns: 46% 54%;
        align-items: center;
        height: 175px;
        gap: 8px;
    }

    /* Highlight Temuan Terbaru */
    .highlight-ifu-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #cbd5e1;
        border-left: 4px solid #059669;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .highlight-ifu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }

    .highlight-ifu-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .highlight-ifu-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
        border-color: #94a3b8;
    }

    .ifu-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        font-size: 10.5px;
        display: inline-block;
    }

    .status-pill-ifu {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-open {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .status-in-progress {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .status-closed {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .badge-overdue {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
    }

    .priority-pill {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 600;
    }

    .priority-critical { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .priority-high { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .priority-medium { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .priority-low { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* Badge & Tab Divisi HSSE (H, S, E) */
    .division-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 5px;
        font-size: 10.5px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .division-h { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .division-s, .division-s-social { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .division-s-security { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .division-e { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .division-unclassified { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

    .division-tabs-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    @media (max-width: 1280px) {
        .division-tabs-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 768px) {
        .division-tabs-grid { grid-template-columns: 1fr; }
    }

    .division-tab-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 14px;
        text-decoration: none;
        color: #1e293b;
        transition: all 0.15s ease;
    }

    .division-tab-card:hover {
        border-color: #94a3b8;
        transform: translateY(-1px);
        color: #0f172a;
    }

    .division-tab-card.active {
        border-color: #059669;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.12);
    }

    .timeline-log-item {
        position: relative;
        padding-left: 20px;
        padding-bottom: 12px;
        border-left: 2px solid #cbd5e1;
        margin-left: 8px;
    }

    .timeline-log-item:last-child {
        padding-bottom: 0;
    }

    .timeline-log-dot {
        position: absolute;
        left: -6px;
        top: 2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #059669;
        border: 2px solid #fff;
    }

    @media (max-width: 1280px) {
        .stats-grid-hsse { grid-template-columns: repeat(3, 1fr); }
        .charts-row-hsse { grid-template-columns: 1fr; }
        .filter-grid-ifu { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 768px) {
        .stats-grid-hsse { grid-template-columns: 1fr; }
        .filter-grid-ifu { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<!-- 1. HEADER HALAMAN (Konsisten dengan HSSE Plan, Daily Work, Events) -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-clipboard-check-fill"></i>
        </div>
        <div>
            <h1 class="page-heading">
                Issue &amp; Follow Up
                <span class="ms-2 fs-6 fw-semibold text-secondary">Pemantauan Temuan &amp; Tindak Lanjut HSSE (4 Divisi: H, S, S, E)</span>
            </h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">Data</span>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Issue &amp; Follow Up</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button type="button" class="btn-action-primary" id="btnTambahTemuan" onclick="openCreateIssueModal()">
                <i class="bi bi-plus-lg"></i> Tambah Temuan
            </button>
        @endadminManagement
        @canExportPdf
        <a href="{{ route('issue-follow-up.export', request()->query()) }}" id="btnExportIssue" class="btn-action-outline">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </a>
        @endcanExportPdf
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 small mb-3 d-flex align-items-center justify-content-between">
        <div><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-center justify-content-between">
        <div><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger py-2 px-3 small mb-3">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Mohon periksa kembali input Anda:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- 5 KARTU STATISTIK DIVISI HSSE (Semua Divisi, H, S, S, E) -->
@php
    $currDiv = strtoupper(trim((string) ($activeDivision ?? request('hsse_division', ''))));
    $divSum = $divisionSummary ?? [];
    $baseQueryWithoutDiv = collect(request()->query())->except(['hsse_division', 'division', 'page'])->all();
@endphp
<div class="division-tabs-grid" id="hsseDivisionTabs">
    <!-- 1. Semua Divisi -->
    <a href="{{ route('issue-follow-up.index', $baseQueryWithoutDiv) }}"
       class="division-tab-card {{ $currDiv === '' ? 'active' : '' }}" data-division="">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold text-dark" style="font-size: 12.5px;">
                <i class="bi bi-grid-fill text-secondary me-1"></i> Semua Divisi
            </span>
            <span class="badge bg-dark rounded-pill">{{ $divSum['all']['total'] ?? $stats['total_issues'] }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-warning-emphasis fw-semibold">Open: {{ $divSum['all']['open'] ?? 0 }}</span>
            <span class="text-primary fw-semibold">In Progress: {{ $divSum['all']['in_progress'] ?? 0 }}</span>
            <span class="text-success fw-semibold">Closed: {{ $divSum['all']['closed'] ?? 0 }}</span>
        </div>
    </a>

    <!-- 2. H — Health & Savety -->
    <a href="{{ route('issue-follow-up.index', array_merge($baseQueryWithoutDiv, ['hsse_division' => 'H'])) }}"
       class="division-tab-card {{ $currDiv === 'H' ? 'active' : '' }}" data-division="H">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="division-pill division-h">
                <i class="bi bi-heart-pulse-fill"></i> H — Health &amp; Savety
            </span>
            <span class="badge bg-info text-dark rounded-pill">{{ $divSum['H']['total'] ?? 0 }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-warning-emphasis fw-semibold">Open: {{ $divSum['H']['open'] ?? 0 }}</span>
            <span class="text-primary fw-semibold">Progress: {{ $divSum['H']['in_progress'] ?? 0 }}</span>
            <span class="text-success fw-semibold">Closed: {{ $divSum['H']['closed'] ?? 0 }}</span>
        </div>
    </a>

    <!-- 3. S — Sosial -->
    <a href="{{ route('issue-follow-up.index', array_merge($baseQueryWithoutDiv, ['hsse_division' => 'S_SOCIAL'])) }}"
       class="division-tab-card {{ in_array($currDiv, ['S_SOCIAL', 'S', 'SOSIAL'], true) ? 'active' : '' }}" data-division="S_SOCIAL">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="division-pill division-s-social">
                <i class="bi bi-people-fill"></i> S — Sosial
            </span>
            <span class="badge bg-danger rounded-pill">{{ $divSum['S_SOCIAL']['total'] ?? ($divSum['S']['total'] ?? 0) }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-warning-emphasis fw-semibold">Open: {{ $divSum['S_SOCIAL']['open'] ?? ($divSum['S']['open'] ?? 0) }}</span>
            <span class="text-primary fw-semibold">Progress: {{ $divSum['S_SOCIAL']['in_progress'] ?? ($divSum['S']['in_progress'] ?? 0) }}</span>
            <span class="text-success fw-semibold">Closed: {{ $divSum['S_SOCIAL']['closed'] ?? ($divSum['S']['closed'] ?? 0) }}</span>
        </div>
    </a>

    <!-- 4. S — Scurity -->
    <a href="{{ route('issue-follow-up.index', array_merge($baseQueryWithoutDiv, ['hsse_division' => 'S_SECURITY'])) }}"
       class="division-tab-card {{ in_array($currDiv, ['S_SECURITY', 'SECURITY', 'SCURITY'], true) ? 'active' : '' }}" data-division="S_SECURITY">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="division-pill division-s-security">
                <i class="bi bi-shield-lock-fill"></i> S — Scurity
            </span>
            <span class="badge rounded-pill" style="background: #f59e0b; color: #fff;">{{ $divSum['S_SECURITY']['total'] ?? 0 }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-warning-emphasis fw-semibold">Open: {{ $divSum['S_SECURITY']['open'] ?? 0 }}</span>
            <span class="text-primary fw-semibold">Progress: {{ $divSum['S_SECURITY']['in_progress'] ?? 0 }}</span>
            <span class="text-success fw-semibold">Closed: {{ $divSum['S_SECURITY']['closed'] ?? 0 }}</span>
        </div>
    </a>

    <!-- 5. E — Emvironment -->
    <a href="{{ route('issue-follow-up.index', array_merge($baseQueryWithoutDiv, ['hsse_division' => 'E'])) }}"
       class="division-tab-card {{ $currDiv === 'E' ? 'active' : '' }}" data-division="E">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="division-pill division-e">
                <i class="bi bi-tree-fill"></i> E — Emvironment
            </span>
            <span class="badge bg-success rounded-pill">{{ $divSum['E']['total'] ?? 0 }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-warning-emphasis fw-semibold">Open: {{ $divSum['E']['open'] ?? 0 }}</span>
            <span class="text-primary fw-semibold">Progress: {{ $divSum['E']['in_progress'] ?? 0 }}</span>
            <span class="text-success fw-semibold">Closed: {{ $divSum['E']['closed'] ?? 0 }}</span>
        </div>
    </a>

    <!-- Tab: Belum Diklasifikasikan (Hanya tampil bila terdapat data lama yang belum diklasifikasikan) -->
    @if(($divSum['unclassified']['total'] ?? 0) > 0 || $currDiv === 'UNCLASSIFIED')
    <a href="{{ route('issue-follow-up.index', array_merge($baseQueryWithoutDiv, ['hsse_division' => 'unclassified'])) }}"
       class="division-tab-card {{ $currDiv === 'UNCLASSIFIED' ? 'active' : '' }}" data-division="unclassified">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="division-pill division-unclassified">
                <i class="bi bi-question-circle"></i> Belum Diklasifikasikan
            </span>
            <span class="badge bg-secondary rounded-pill">{{ $divSum['unclassified']['total'] ?? 0 }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-1" style="font-size: 10.5px;">
            <span class="text-muted">Data historis sebelum klasifikasi 4 divisi</span>
        </div>
    </a>
    @endif
</div>

<!-- 2. BARIS PENCARIAN & FILTER -->
<div class="filter-card">
    <form method="GET" action="{{ route('issue-follow-up.index') }}" id="ifuFilterForm">
        <div class="filter-grid-ifu" style="grid-template-columns: 130px 130px 150px 140px 160px 125px 125px 1fr auto;">
            <!-- Dari Tanggal (reported_date) -->
            <div class="filter-input-wrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="date" name="start_date" id="filterStartDate" class="form-control form-control-filter" value="{{ request('start_date') }}" title="Dari Tanggal Temuan">
            </div>

            <!-- Sampai Tanggal (reported_date) -->
            <div class="filter-input-wrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="date" name="end_date" id="filterEndDate" class="form-control form-control-filter" value="{{ request('end_date') }}" title="Sampai Tanggal Temuan">
            </div>

            <!-- Divisi HSSE (H, S, E) -->
            <div>
                <select name="hsse_division" id="filterHsseDivision" class="form-select form-select-filter">
                    <option value="">Semua Divisi</option>
                    @foreach(\App\Models\IssueFollowUp::HSSE_DIVISIONS as $divCode => $divLabel)
                        <option value="{{ $divCode }}" {{ strtoupper((string) request('hsse_division')) === $divCode ? 'selected' : '' }}>{{ $divLabel }}</option>
                    @endforeach
                    @if(($divSum['unclassified']['total'] ?? 0) > 0)
                        <option value="unclassified" {{ strtolower((string) request('hsse_division')) === 'unclassified' ? 'selected' : '' }}>Belum Diklasifikasikan</option>
                    @endif
                </select>
            </div>

            <!-- Status -->
            <div>
                <select name="status" id="filterStatus" class="form-select form-select-filter">
                    <option value="">Semua Status</option>
                    <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                    <option value="Overdue" {{ request('status') === 'Overdue' ? 'selected' : '' }}>Overdue (Lewat Tenggat)</option>
                </select>
            </div>

            <!-- Kategori -->
            <div>
                <select name="category" id="filterCategory" class="form-select form-select-filter">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Unit -->
            <div>
                <select name="unit" id="filterUnit" class="form-select form-select-filter">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ request('unit') === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <!-- PIC -->
            <div>
                <input type="text" name="pic" id="filterPic" class="form-control form-control-filter px-2" value="{{ request('pic') }}" placeholder="Filter PIC..." list="picListOptions">
                <datalist id="picListOptions">
                    @foreach($pics as $p)
                        <option value="{{ $p }}">
                    @endforeach
                </datalist>
            </div>

            <!-- Pencarian Judul / Deskripsi -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" name="search" id="filterSearch" class="form-control form-control-filter" value="{{ request('search') }}" placeholder="Cari judul, deskripsi, lokasi, kode temuan...">
            </div>

            <!-- Aksi Filter & Reset -->
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-success px-3">Filter</button>
                <a href="{{ route('issue-follow-up.index') }}" class="btn btn-sm btn-outline-secondary px-2">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- 3. RINGKASAN STATISTIK (5 KARTU HORIZONTAL DARI DATABASE) -->
<div class="stats-grid-hsse">
    <!-- 1. Total Temuan -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-clipboard-data-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Temuan</div>
            <div class="stat-number" id="statTotalIssues">{{ number_format($stats['total_issues'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Seluruh temuan tercatat</span></div>
        </div>
    </div>

    <!-- 2. Open -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-exclamation-circle-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Open</div>
            <div class="stat-number text-warning" id="statOpenIssues">{{ number_format($stats['open_issues'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Belum selesai ditangani</span></div>
        </div>
    </div>

    <!-- 3. In Progress -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-arrow-repeat"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">In Progress</div>
            <div class="stat-number text-primary" id="statInProgressIssues">{{ number_format($stats['in_progress_issues'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Sedang ditindaklanjuti</span></div>
        </div>
    </div>

    <!-- 4. Closed -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Closed</div>
            <div class="stat-number text-success" id="statClosedIssues">{{ number_format($stats['closed_issues'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Diselesaikan &amp; ditutup</span></div>
        </div>
    </div>

    <!-- 5. Overdue -->
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background:#fef2f2; color:#dc2626;">
            <i class="bi bi-alarm-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Overdue</div>
            <div class="stat-number text-danger" id="statOverdueIssues">{{ number_format($stats['overdue_issues'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="text-danger fw-semibold" style="font-size:10.5px;">Lewat tenggat &amp; belum Closed</span></div>
        </div>
    </div>
</div>

<!-- 4. GRAFIK (3 PANEL DARI DATABASE) -->
<div class="charts-row-hsse">
    <!-- Grafik A: Tren Temuan per Bulan -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Grafik Tren Temuan</h2>
            <p class="chart-subtitle">Jumlah temuan berdasarkan bulan pencatatan (reported_date)</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="ifuTrendChartCanvas"></canvas>
        </div>
    </div>

    <!-- Grafik B: Status Temuan (Doughnut + Legend) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Grafik Status Temuan</h2>
            <p class="chart-subtitle">Perbandingan komposisi Open, In Progress, dan Closed</p>
        </div>
        <div class="donut-layout-hsse">
            <div style="height:140px; position:relative;">
                <canvas id="ifuStatusChartCanvas"></canvas>
            </div>
            <div class="custom-legend-list" id="ifuStatusLegendList">
                @php
                    $totalForPct = max(1, $stats['total_issues']);
                @endphp
                @foreach($statusChart['labels'] as $idx => $sLabel)
                    @php
                        $val = $statusChart['data'][$idx] ?? 0;
                        $pct = $stats['total_issues'] > 0 ? round(($val / $totalForPct) * 100) : 0;
                        $color = $statusChart['colors'][$idx] ?? '#64748b';
                    @endphp
                    <div class="custom-legend-item">
                        <span class="legend-indicator" style="background:{{ $color }}"></span>
                        <span class="legend-name">{{ $sLabel }}</span>
                        <span class="legend-stats">{{ $val }} ({{ $pct }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Grafik C: Temuan per Kategori / Unit -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Temuan per Kategori &amp; Unit</h2>
            <p class="chart-subtitle">Distribusi temuan berdasarkan kategori di database</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="ifuCategoryChartCanvas"></canvas>
        </div>
    </div>
</div>

<!-- 5. BAGIAN HIGHLIGHT TEMUAN TERBARU (Hanya Temuan Aktif: Open & In Progress) -->
<div class="highlight-ifu-card" id="latestFindingsSection">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success"><i class="bi bi-stars me-1"></i> Highlight</span>
            <h2 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Temuan Terbaru</h2>
            <span class="text-muted small">(Temuan aktif Open &amp; In Progress, diurutkan dari Tanggal Temuan terbaru)</span>
        </div>
        <span class="badge bg-light text-secondary border" style="font-size:11px;" id="latestHighlightsCountBadge">
            {{ $latestHighlights->count() }} Temuan Aktif
        </span>
    </div>

    <div id="latestHighlightsContainer">
        @if($latestHighlights->isNotEmpty())
            <div class="highlight-ifu-grid">
                @foreach($latestHighlights as $hl)
                    @php
                        $hlBadgeClass = match($hl->status) {
                            'Closed', 'Close' => 'status-closed',
                            'In Progress' => 'status-in-progress',
                            default => 'status-open',
                        };
                    @endphp
                    <div class="highlight-ifu-item" data-highlight-id="{{ $hl->id }}" data-highlight-status="{{ $hl->status }}">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="ifu-code-badge">{{ $hl->issue_code }}</span>
                                    <span class="division-pill {{ $hl->division_badge_class }}">{{ $hl->division_label }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    @if($hl->isOverdue())
                                        <span class="badge-overdue"><i class="bi bi-exclamation-triangle-fill"></i> Overdue</span>
                                    @endif
                                    <span class="status-pill-ifu {{ $hlBadgeClass }}">{{ $hl->status }}</span>
                                </div>
                            </div>
                            <div class="fw-bold text-dark mb-1" style="font-size: 13px; line-height: 1.35;">
                                {{ $hl->title }}
                            </div>
                            <div class="text-muted small mb-2" style="font-size: 11px;">
                                <div class="mb-1">
                                    <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                    Tanggal Temuan: <strong class="text-dark">{{ $hl->reported_date ? $hl->reported_date->format('d/m/Y') : '-' }}</strong>
                                </div>
                                <div class="mb-1">
                                    <i class="bi bi-geo-alt me-1 text-secondary"></i>
                                    {{ $hl->unit }} • {{ $hl->category }} {{ $hl->location ? '(' . $hl->location . ')' : '' }}
                                </div>
                                <div class="mb-1">
                                    <i class="bi bi-person-badge me-1 text-secondary"></i>
                                    PIC: <strong class="text-dark">{{ $hl->pic }}</strong>
                                </div>
                                <div>
                                    <i class="bi bi-hourglass-split me-1 text-secondary"></i>
                                    Tenggat:
                                    @if($hl->due_date)
                                        <strong class="{{ $hl->isOverdue() ? 'text-danger' : 'text-dark' }}">
                                            {{ $hl->due_date->format('d/m/Y') }}
                                        </strong>
                                    @else
                                        <span>-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
                            <span class="priority-pill {{ $hl->priority_badge_class }}">{{ $hl->priority ?: 'Sedang' }}</span>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 11px;" onclick="openIssueDetailModal({{ $hl->id }})">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </button>
                                <a href="{{ route('issue-follow-up.show', $hl->id) }}" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" title="Buka Halaman Detail">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3 text-muted small">
                <i class="bi bi-check2-circle text-success me-1"></i>
                Tidak ada temuan aktif (Open / In Progress) yang memerlukan penanganan saat ini. Temuan yang sudah selesai (Closed) tetap tersimpan di tabel daftar temuan di bawah.
            </div>
        @endif
    </div>
</div>

<!-- 6. TABEL DAFTAR TEMUAN -->
<div class="table-card">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                Daftar Temuan &amp; Tindak Lanjut
                <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size:10.5px;">{{ $issues->total() }} Data</span>
            </h2>
        </div>

        <div class="table-controls">
            <form method="GET" action="{{ route('issue-follow-up.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except(['sort', 'per_page', 'page']) as $k => $v)
                    @if(!is_array($v))
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach
                <select name="sort" class="form-select form-select-sm" style="width:165px; font-size:11.5px" onchange="this.form.submit()">
                    <option value="date-desc" {{ request('sort', 'date-desc') === 'date-desc' ? 'selected' : '' }}>Terbaru (Default)</option>
                    <option value="date-asc" {{ request('sort') === 'date-asc' ? 'selected' : '' }}>Terlama</option>
                    <option value="due-asc" {{ request('sort') === 'due-asc' ? 'selected' : '' }}>Tenggat Terdekat</option>
                    <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Prioritas Tertinggi</option>
                    <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </form>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="issueFollowUpTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 115px;">Tanggal &amp; Kode</th>
                    <th style="width: 135px;">Divisi HSSE</th>
                    <th style="width: 210px;">Judul Temuan</th>
                    <th>Deskripsi Singkat</th>
                    <th style="width: 165px;">Unit / Kategori / Lokasi</th>
                    <th style="width: 125px;">PIC</th>
                    <th style="width: 115px; text-align: center;">Tenggat Follow Up</th>
                    <th style="width: 115px; text-align: center;">Status</th>
                    <th style="width: 155px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="issueTableBody">
                @forelse($issues as $index => $issue)
                    @php
                        $statusBadge = match($issue->status) {
                            'Closed', 'Close' => 'status-closed',
                            'In Progress' => 'status-in-progress',
                            default => 'status-open',
                        };
                    @endphp
                    <tr data-id="{{ $issue->id }}">
                        <td style="text-align: center; color: #64748b;">{{ $issues->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $issue->reported_date ? $issue->reported_date->format('d/m/Y') : '-' }}</div>
                            <span class="ifu-code-badge mt-1">{{ $issue->issue_code }}</span>
                        </td>
                        <td>
                            <span class="division-pill {{ $issue->division_badge_class }}">{{ $issue->division_label }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $issue->title }}</div>
                            <span class="priority-pill {{ $issue->priority_badge_class }} mt-1">{{ $issue->priority ?: 'Sedang' }}</span>
                        </td>
                        <td>
                            <div class="text-secondary" style="font-size: 11.5px; line-height: 1.4;">
                                {{ \Illuminate\Support\Str::limit($issue->description, 95) }}
                            </div>
                            @if($issue->notes)
                                <div class="text-muted mt-1" style="font-size: 10.5px;">
                                    <i class="bi bi-chat-left-text me-1"></i>Tindak lanjut terakhir: {{ \Illuminate\Support\Str::limit($issue->notes, 60) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $issue->unit }}</div>
                            <div class="text-primary small fw-semibold" style="font-size: 11px;">{{ $issue->category }}</div>
                            @if($issue->location)
                                <div class="text-muted" style="font-size: 10.5px;"><i class="bi bi-geo-alt"></i> {{ $issue->location }}</div>
                            @endif
                        </td>
                        <td class="fw-semibold text-dark">{{ $issue->pic }}</td>
                        <td style="text-align: center;">
                            @if($issue->due_date)
                                <div class="fw-semibold {{ $issue->isOverdue() ? 'text-danger' : 'text-dark' }}">
                                    {{ $issue->due_date->format('d/m/Y') }}
                                </div>
                                @if($issue->isOverdue())
                                    <span class="badge-overdue mt-1"><i class="bi bi-alarm"></i> Overdue</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="status-pill-ifu {{ $statusBadge }}">{{ $issue->status }}</span>
                            @if($issue->status === 'Closed' && $issue->closed_date)
                                <div class="text-muted mt-1" style="font-size: 10px;">
                                    Ditutup: {{ $issue->closed_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex align-items-center gap-1 flex-wrap justify-content-center">
                                <!-- Detail -->
                                <button type="button" class="btn-action-icon btn-action-view" onclick="openIssueDetailModal({{ $issue->id }})" title="Lihat Detail & Riwayat Tindak Lanjut">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @adminManagement
                                    <!-- Tambah Tindak Lanjut (Follow Up) -->
                                    <button type="button" class="btn-action-icon" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;" onclick="openFollowUpModal({{ $issue->id }}, '{{ addslashes($issue->title) }}', '{{ $issue->status }}')" title="Tambah Tindak Lanjut (Follow Up)">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>

                                    <!-- Tutup Temuan (Close) atau Buka Kembali (Reopen) -->
                                    @if($issue->status !== 'Closed')
                                        <button type="button" class="btn-action-icon" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;" onclick="closeIssuePrompt({{ $issue->id }}, '{{ addslashes($issue->title) }}')" title="Tutup Temuan (Tandai Closed)">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn-action-icon" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a;" onclick="reopenIssuePrompt({{ $issue->id }}, '{{ addslashes($issue->title) }}')" title="Buka Kembali Temuan (Reopen)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif

                                    <!-- Edit -->
                                    <button type="button" class="btn-action-icon btn-action-edit" onclick="openEditIssueModal({{ $issue->id }})" title="Edit Temuan">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button" class="btn-action-icon btn-action-delete" onclick="deleteIssuePrompt({{ $issue->id }}, '{{ addslashes($issue->title) }}', '{{ $issue->issue_code }}')" title="Hapus Temuan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-4 text-center">
                            <div class="py-4">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle bg-light text-secondary" style="width:56px;height:56px;font-size:24px;">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <h6 class="fw-bold text-secondary mb-1">Belum ada data temuan pada filter ini</h6>
                                <p class="text-muted small mb-3">Klik tombol <strong>+ Tambah Temuan</strong> untuk mencatat temuan baru ke database.</p>
                                <button type="button" class="btn-action-primary mx-auto" onclick="openCreateIssueModal()">
                                    <i class="bi bi-plus-lg"></i> Tambah Temuan
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($issues->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="text-muted small">
                Menampilkan {{ $issues->firstItem() }}–{{ $issues->lastItem() }} dari {{ $issues->total() }} temuan
            </div>
            <div>
                {{ $issues->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- MODAL 1: TAMBAH / EDIT TEMUAN -->
<div class="modal fade" id="issueFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="issueForm" method="POST" action="{{ route('issue-follow-up.store') }}" enctype="multipart/form-data" onsubmit="saveIssueForm(event)">
                @csrf
                <input type="hidden" id="formIssueId" name="id" value="">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-clipboard-plus"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="issueFormModalTitle">Tambah Temuan HSSE Baru</h5>
                            <div class="text-muted" style="font-size:11px;">Catat temuan lapangan, divisi H/S/E, penanggung jawab (PIC), dan tenggat tindak lanjut</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Temuan <span class="text-danger">*</span></label>
                            <input type="date" name="reported_date" id="formReportedDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Divisi HSSE (H / S / E) <span class="text-danger">*</span></label>
                            <select name="hsse_division" id="formHsseDivision" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Divisi (H / S / E) --</option>
                                @foreach($divisions as $divCode => $divMeta)
                                    <option value="{{ $divCode }}">{{ is_array($divMeta) ? ($divMeta['label'] ?? $divCode) : $divMeta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Kategori Temuan <span class="text-danger">*</span></label>
                            <select name="category" id="formCategory" class="form-select form-select-sm" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Judul Temuan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="formTitle" class="form-control form-control-sm" placeholder="Contoh: Kebocoran valve pipa brine / kabel terbuka di area kerja" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Unit / Area <span class="text-danger">*</span></label>
                            <select name="unit" id="formUnit" class="form-select form-select-sm" required>
                                @foreach($units as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Lokasi Spesifik</label>
                            <input type="text" name="location" id="formLocation" class="form-control form-control-sm" placeholder="Contoh: Wellpad 9 / Ruang Turbin">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Deskripsi Temuan <span class="text-danger">*</span></label>
                            <textarea name="description" id="formDescription" class="form-control form-control-sm" rows="3" placeholder="Jelaskan kondisi temuan secara rinci..." required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">PIC / Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" name="pic" id="formPic" class="form-control form-control-sm" placeholder="Nama PIC / Divisi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Tenggat Follow Up</label>
                            <input type="date" name="due_date" id="formDueDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tingkat Risiko / Prioritas</label>
                            <select name="priority" id="formPriority" class="form-select form-select-sm">
                                @foreach($priorities as $prio)
                                    <option value="{{ $prio }}" {{ $prio === 'Sedang' ? 'selected' : '' }}>{{ $prio }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Status Awal / Saat Ini</label>
                            <select name="status" id="formStatus" class="form-select form-select-sm">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Dokumentasi Foto / Lampiran (Opsional)</label>
                            <input type="file" name="attachment" id="formAttachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Rencana Tindak Lanjut (Action Plan)</label>
                            <textarea name="action_plan" id="formActionPlan" class="form-control form-control-sm" rows="2" placeholder="Langkah perbaikan yang direncanakan..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Catatan Awal / Tindak Lanjut</label>
                            <textarea name="notes" id="formNotes" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitIssue">
                        <i class="bi bi-save me-1"></i> Simpan Temuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: TAMBAH TINDAK LANJUT (FOLLOW UP) -->
<div class="modal fade" id="followUpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="followUpForm" onsubmit="submitFollowUpForm(event)">
                <input type="hidden" id="fuIssueId" value="">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold">Tambah Catatan Tindak Lanjut</h5>
                            <div class="text-muted" style="font-size:11px;" id="fuIssueSubtitle">Riwayat tindak lanjut sebelumnya tetap tersimpan</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Ketika tindak lanjut ditambahkan pada temuan berstatus <strong>Open</strong>, status otomatis diperbarui menjadi <strong>In Progress</strong> (kecuali langsung ditandai <strong>Closed</strong>).
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;">Status Tindak Lanjut</label>
                        <select id="fuStatus" name="status" class="form-select form-select-sm">
                            <option value="In Progress">In Progress (Sedang Ditindaklanjuti)</option>
                            <option value="Closed">Closed (Selesai &amp; Ditutup)</option>
                            <option value="Open">Open</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;">Catatan Progres / Tindak Lanjut <span class="text-danger">*</span></label>
                        <textarea id="fuNote" name="note" class="form-control form-control-sm" rows="3" required placeholder="Tuliskan progres perbaikan atau tindakan yang telah dilakukan..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:12px;">Lampiran Bukti Tindak Lanjut (Opsional)</label>
                        <input type="file" id="fuAttachment" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitFollowUp">
                        <i class="bi bi-send-check me-1"></i> Simpan Tindak Lanjut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: DETAIL TEMUAN & RIWAYAT KRONOLOGIS -->
<div class="modal fade" id="issueDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="detailModalTitle">Detail Temuan &amp; Riwayat Follow Up</h5>
                        <div class="text-muted" style="font-size:11px;" id="detailModalSubtitle">Informasi lengkap dan log kronologis</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="issueDetailModalBody">
                <div class="text-center py-4 text-muted">Memuat detail temuan...</div>
            </div>
            <div class="modal-footer border-top bg-light justify-content-between">
                <a href="#" id="detailFullPageLink" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Penuh
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="detailBtnFollowUp">
                        <i class="bi bi-chat-dots me-1"></i> Tambah Follow Up
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const IFU_BASE_URL = "{{ url('/issue-follow-up') }}";

    const initialTrendChart = @json($trendChart);
    const initialStatusChart = @json($statusChart);
    const initialCategoryChart = @json($categoryChart);

    document.addEventListener('DOMContentLoaded', function () {
        initIfuCharts(initialTrendChart, initialStatusChart, initialCategoryChart);
    });

    function initIfuCharts(trendData, statusData, categoryData) {
        if (typeof Chart === 'undefined') return;

        // A. Grafik Tren Temuan per Bulan
        const trendCtx = document.getElementById('ifuTrendChartCanvas')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendData.labels && trendData.labels.length ? trendData.labels : ['Belum Ada Data'],
                    datasets: [
                        {
                            label: 'Total Temuan',
                            data: trendData.total && trendData.total.length ? trendData.total : [0],
                            backgroundColor: '#059669',
                            borderRadius: 4,
                            maxBarThickness: 32
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // B. Grafik Status Temuan (Doughnut)
        const statusCtx = document.getElementById('ifuStatusChartCanvas')?.getContext('2d');
        if (statusCtx) {
            const rawVals = statusData.data || [0, 0, 0];
            const sumVals = rawVals.reduce((a, b) => a + b, 0);
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.labels || ['Open', 'In Progress', 'Closed'],
                    datasets: [{
                        data: sumVals > 0 ? rawVals : [1],
                        backgroundColor: sumVals > 0 ? statusData.colors : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: sumVals > 0 }
                    }
                }
            });
        }

        // C. Grafik Temuan per Kategori
        const catCtx = document.getElementById('ifuCategoryChartCanvas')?.getContext('2d');
        if (catCtx) {
            const cLabels = categoryData.labels && categoryData.labels.length ? categoryData.labels : ['Belum Ada Data'];
            const cValues = categoryData.data && categoryData.data.length ? categoryData.data : [0];
            new Chart(catCtx, {
                type: 'bar',
                data: {
                    labels: cLabels,
                    datasets: [{
                        label: 'Jumlah Temuan',
                        data: cValues,
                        backgroundColor: categoryData.colors && categoryData.colors.length ? categoryData.colors : ['#2563eb'],
                        borderRadius: 4,
                        maxBarThickness: 24
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        y: { ticks: { font: { size: 9.5 } } }
                    }
                }
            });
        }
    }

    function openCreateIssueModal() {
        const form = document.getElementById('issueForm');
        if (form) form.reset();

        document.getElementById('formIssueId').value = '';
        document.getElementById('formReportedDate').value = "{{ date('Y-m-d') }}";
        const activeFilter = "{{ strtoupper(trim((string) ($activeDivision ?? request('hsse_division', '')))) }}";
        let defaultDiv = '';
        if (['H', 'S_SOCIAL', 'S_SECURITY', 'E'].includes(activeFilter)) {
            defaultDiv = activeFilter;
        } else if (activeFilter === 'S') {
            defaultDiv = 'S_SOCIAL';
        }
        if (document.getElementById('formHsseDivision')) {
            document.getElementById('formHsseDivision').value = defaultDiv;
        }
        document.getElementById('formPriority').value = 'Sedang';
        document.getElementById('formStatus').value = 'Open';
        document.getElementById('issueFormModalTitle').textContent = 'Tambah Temuan HSSE Baru';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('issueFormModal')).show();
    }

    async function openEditIssueModal(id) {
        try {
            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                Swal.fire('Error', data.message || 'Data tidak ditemukan.', 'error');
                return;
            }

            const i = data.issue;
            document.getElementById('formIssueId').value = i.id;
            document.getElementById('formReportedDate').value = i.reported_date ? String(i.reported_date).substring(0, 10) : '';
            if (document.getElementById('formHsseDivision')) {
                let divCode = i.hsse_division || '';
                if (divCode === 'S') divCode = 'S_SOCIAL';
                document.getElementById('formHsseDivision').value = divCode;
            }
            document.getElementById('formTitle').value = i.title || '';
            document.getElementById('formCategory').value = i.category || '';
            document.getElementById('formUnit').value = i.unit || 'PMU';
            document.getElementById('formLocation').value = i.location || '';
            document.getElementById('formDescription').value = i.description || '';
            document.getElementById('formPic').value = i.pic || '';
            document.getElementById('formDueDate').value = i.due_date ? String(i.due_date).substring(0, 10) : '';
            document.getElementById('formPriority').value = i.priority || 'Sedang';
            document.getElementById('formStatus').value = i.status || 'Open';
            document.getElementById('formActionPlan').value = i.action_plan || '';
            document.getElementById('formNotes').value = i.notes || '';
            document.getElementById('issueFormModalTitle').textContent = `Edit Temuan [${i.issue_code}]`;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('issueFormModal')).show();
        } catch (err) {
            Swal.fire('Error', 'Gagal memuat data temuan.', 'error');
        }
    }

    async function saveIssueForm(e) {
        e.preventDefault();
        const id = document.getElementById('formIssueId').value;
        const isEdit = Boolean(id);
        const url = isEdit ? `${IFU_BASE_URL}/${id}` : IFU_BASE_URL;

        const form = document.getElementById('issueForm');
        const formData = new FormData(form);
        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        const btn = document.getElementById('btnSubmitIssue');
        btn.disabled = true;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const body = await res.json();
            btn.disabled = false;

            if (!res.ok || !body.success) {
                let msg = body.message || 'Gagal menyimpan data temuan.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: msg });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('issueFormModal'))?.hide();
            syncIfuHighlightsAndStats(body);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: body.message,
                timer: 1400,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } catch (err) {
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan saat menyimpan temuan.', 'error');
        }
    }

    function openFollowUpModal(id, title, currentStatus) {
        document.getElementById('fuIssueId').value = id;
        document.getElementById('fuIssueSubtitle').textContent = `Temuan: ${title}`;
        document.getElementById('fuNote').value = '';
        document.getElementById('fuAttachment').value = '';
        document.getElementById('fuStatus').value = currentStatus === 'Open' ? 'In Progress' : currentStatus;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('followUpModal')).show();
    }

    async function submitFollowUpForm(e) {
        e.preventDefault();
        const id = document.getElementById('fuIssueId').value;
        if (!id) return;

        const formData = new FormData();
        formData.append('status', document.getElementById('fuStatus').value);
        formData.append('note', document.getElementById('fuNote').value);
        const fileInput = document.getElementById('fuAttachment');
        if (fileInput && fileInput.files[0]) {
            formData.append('attachment', fileInput.files[0]);
        }

        const btn = document.getElementById('btnSubmitFollowUp');
        btn.disabled = true;

        try {
            const res = await fetch(`${IFU_BASE_URL}/${id}/follow-up`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const body = await res.json();
            btn.disabled = false;

            if (!res.ok || !body.success) {
                let msg = body.message || 'Gagal menyimpan tindak lanjut.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('followUpModal'))?.hide();
            syncIfuHighlightsAndStats(body);
            Swal.fire({
                icon: 'success',
                title: 'Tindak Lanjut Tersimpan',
                text: body.message,
                timer: 1400,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } catch (err) {
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
    }

    function closeIssuePrompt(id, title) {
        Swal.fire({
            title: 'Tutup Temuan (Closed)?',
            html: `Tandai temuan <strong>${escapeHtml(title)}</strong> sebagai <strong>Closed</strong>?<br><small class="text-muted">Temuan tetap tersimpan di database dan riwayat penyelesaiannya akan dicatat.</small>`,
            input: 'textarea',
            inputPlaceholder: 'Catatan penyelesaian / penutupan temuan...',
            inputValue: 'Tindak lanjut perbaikan telah selesai diverifikasi dan temuan ditutup (Closed).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Tutup Temuan',
            cancelButtonText: 'Batal'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}/close`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ note: result.value || 'Temuan diselesaikan dan ditutup.' })
            });
            const body = await res.json();
            if (res.ok && body.success) {
                syncIfuHighlightsAndStats(body);
                Swal.fire({ icon: 'success', title: 'Closed', text: body.message, timer: 1300, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal menutup temuan.', 'error');
            }
        });
    }

    function reopenIssuePrompt(id, title) {
        Swal.fire({
            title: 'Buka Kembali Temuan (Reopen)?',
            html: `Buka kembali temuan <strong>${escapeHtml(title)}</strong> menjadi <strong>In Progress</strong>?`,
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan membuka kembali temuan ini (wajib)...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Buka Kembali',
            cancelButtonText: 'Batal',
            preConfirm: (val) => {
                if (!val || !val.trim()) {
                    Swal.showValidationMessage('Alasan membuka kembali temuan wajib diisi.');
                }
                return val;
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}/reopen`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ note: result.value, status: 'In Progress' })
            });
            const body = await res.json();
            if (res.ok && body.success) {
                syncIfuHighlightsAndStats(body);
                Swal.fire({ icon: 'success', title: 'Dibuka Kembali', text: body.message, timer: 1300, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal membuka kembali temuan.', 'error');
            }
        });
    }

    function deleteIssuePrompt(id, title, code) {
        Swal.fire({
            title: 'Hapus Temuan?',
            html: `Apakah Anda yakin ingin menghapus temuan <strong>[${escapeHtml(code)}] ${escapeHtml(title)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const body = await res.json();
            if (res.ok && body.success) {
                syncIfuHighlightsAndStats(body);
                Swal.fire({ icon: 'success', title: 'Terhapus', text: body.message, timer: 1300, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal menghapus temuan.', 'error');
            }
        });
    }

    function syncIfuHighlightsAndStats(body) {
        if (body.stats) {
            const s = body.stats;
            if (document.getElementById('statTotalIssues')) document.getElementById('statTotalIssues').textContent = s.total_issues ?? 0;
            if (document.getElementById('statOpenIssues')) document.getElementById('statOpenIssues').textContent = s.open_issues ?? 0;
            if (document.getElementById('statInProgressIssues')) document.getElementById('statInProgressIssues').textContent = s.in_progress_issues ?? 0;
            if (document.getElementById('statClosedIssues')) document.getElementById('statClosedIssues').textContent = s.closed_issues ?? 0;
            if (document.getElementById('statOverdueIssues')) document.getElementById('statOverdueIssues').textContent = s.overdue_issues ?? 0;
        }

        if (Array.isArray(body.latest_highlights)) {
            const activeHighlights = body.latest_highlights.filter(hl => hl.status === 'Open' || hl.status === 'In Progress');
            const badgeEl = document.getElementById('latestHighlightsCountBadge');
            const containerEl = document.getElementById('latestHighlightsContainer');
            if (badgeEl) badgeEl.textContent = `${activeHighlights.length} Temuan Aktif`;
            if (containerEl) {
                if (!activeHighlights.length) {
                    containerEl.innerHTML = `
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-check2-circle text-success me-1"></i>
                            Tidak ada temuan aktif (Open / In Progress) yang memerlukan penanganan saat ini. Temuan yang sudah selesai (Closed) tetap tersimpan di tabel daftar temuan di bawah.
                        </div>
                    `;
                } else {
                    containerEl.innerHTML = `<div class="highlight-ifu-grid">` + activeHighlights.map(hl => {
                        const badgeCls = hl.status === 'In Progress' ? 'status-in-progress' : 'status-open';
                        const divBadgeCls = hl.division_badge_class || 'division-unclassified';
                        const divLabel = hl.division_label || 'Belum Diklasifikasikan';
                        return `
                            <div class="highlight-ifu-item" data-highlight-id="${hl.id}" data-highlight-status="${escapeHtml(hl.status)}">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                            <span class="ifu-code-badge">${escapeHtml(hl.issue_code)}</span>
                                            <span class="division-pill ${divBadgeCls}">${escapeHtml(divLabel)}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            ${hl.is_overdue ? `<span class="badge-overdue"><i class="bi bi-exclamation-triangle-fill"></i> Overdue</span>` : ''}
                                            <span class="status-pill-ifu ${badgeCls}">${escapeHtml(hl.status)}</span>
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 13px; line-height: 1.35;">${escapeHtml(hl.title)}</div>
                                    <div class="text-muted small mb-2" style="font-size: 11px;">
                                        <div class="mb-1"><i class="bi bi-calendar-event me-1 text-secondary"></i>Tanggal Temuan: <strong class="text-dark">${formatDate(hl.reported_date)}</strong></div>
                                        <div class="mb-1"><i class="bi bi-geo-alt me-1 text-secondary"></i>${escapeHtml(hl.unit)} • ${escapeHtml(hl.category)} ${hl.location ? '(' + escapeHtml(hl.location) + ')' : ''}</div>
                                        <div class="mb-1"><i class="bi bi-person-badge me-1 text-secondary"></i>PIC: <strong class="text-dark">${escapeHtml(hl.pic)}</strong></div>
                                        <div><i class="bi bi-hourglass-split me-1 text-secondary"></i>Tenggat: <strong class="${hl.is_overdue ? 'text-danger' : 'text-dark'}">${formatDate(hl.due_date)}</strong></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
                                    <span class="priority-pill ${hl.priority_badge_class || 'priority-medium'}">${escapeHtml(hl.priority || 'Sedang')}</span>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 11px;" onclick="openIssueDetailModal(${hl.id})">
                                            <i class="bi bi-eye me-1"></i> Lihat Detail
                                        </button>
                                        <a href="${IFU_BASE_URL}/${hl.id}" class="btn btn-sm btn-light border py-1 px-2" style="font-size: 11px;" title="Buka Halaman Detail">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('') + `</div>`;
                }
            }
        }
    }

    async function openIssueDetailModal(id) {
        const modalEl = document.getElementById('issueDetailModal');
        const bodyEl = document.getElementById('issueDetailModalBody');
        document.getElementById('detailFullPageLink').href = `${IFU_BASE_URL}/${id}`;
        bodyEl.innerHTML = `<div class="text-center py-4 text-muted"><i class="bi bi-arrow-repeat me-1"></i> Memuat rincian temuan...</div>`;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        try {
            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                bodyEl.innerHTML = `<div class="text-danger text-center py-3">${escapeHtml(data.message)}</div>`;
                return;
            }

            const i = data.issue;
            const logs = data.chronological_logs || data.logs || [];

            document.getElementById('detailModalTitle').textContent = `[${i.issue_code}] ${i.title}`;
            document.getElementById('detailModalSubtitle').textContent = `${i.division_label || 'Belum Diklasifikasikan'} • ${i.unit} • ${i.category} • PIC: ${i.pic}`;

            const btnFu = document.getElementById('detailBtnFollowUp');
            if (btnFu) {
                btnFu.onclick = () => {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                    openFollowUpModal(i.id, i.title, i.status);
                };
            }

            const logsHtml = logs.length > 0 ? logs.map(l => {
                const dt = l.followed_up_at ? new Date(l.followed_up_at).toLocaleString('id-ID') : '-';
                const statusTransition = l.old_status && l.old_status !== l.new_status
                    ? `<span class="badge bg-light text-dark border ms-1">${escapeHtml(l.old_status)} → ${escapeHtml(l.new_status)}</span>`
                    : `<span class="badge bg-success-subtle text-success ms-1">${escapeHtml(l.new_status || '-')}</span>`;
                return `
                    <div class="timeline-log-item">
                        <div class="timeline-log-dot"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-dark" style="font-size:12px;">
                                ${escapeHtml(l.user_name || 'Sistem')} ${statusTransition}
                            </div>
                            <span class="text-muted" style="font-size:10.5px;">${dt}</span>
                        </div>
                        <div class="text-secondary mt-1" style="font-size:12px;">${escapeHtml(l.note)}</div>
                        ${l.attachment_path ? `<div class="mt-1"><a href="/${l.attachment_path}" target="_blank" class="small text-primary"><i class="bi bi-paperclip"></i> Lihat Lampiran</a></div>` : ''}
                    </div>
                `;
            }).join('') : `<div class="text-muted small">Belum ada riwayat tindak lanjut.</div>`;

            bodyEl.innerHTML = `
                <div class="row g-3 mb-3" style="font-size:12.5px;">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="mb-2"><span class="text-muted">Divisi HSSE:</span> <span class="division-pill ${i.division_badge_class || 'division-unclassified'}">${escapeHtml(i.division_label || 'Belum Diklasifikasikan')}</span></div>
                            <div class="mb-2"><span class="text-muted">Tanggal Temuan:</span> <strong class="text-dark">${formatDate(i.reported_date)}</strong></div>
                            <div class="mb-2"><span class="text-muted">Tenggat Follow Up:</span> <strong class="${i.is_overdue ? 'text-danger' : 'text-dark'}">${formatDate(i.due_date)} ${i.is_overdue ? '(OVERDUE)' : ''}</strong></div>
                            <div class="mb-2"><span class="text-muted">PIC / Penanggung Jawab:</span> <strong class="text-primary">${escapeHtml(i.pic)}</strong></div>
                            <div class="mb-0"><span class="text-muted">Unit & Lokasi:</span> <strong>${escapeHtml(i.unit)} ${i.location ? '• ' + escapeHtml(i.location) : ''}</strong></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="mb-2"><span class="text-muted">Status Saat Ini:</span> <span class="status-pill-ifu ${i.status_badge_class}">${escapeHtml(i.status)}</span></div>
                            <div class="mb-2"><span class="text-muted">Prioritas Risiko:</span> <span class="priority-pill ${i.priority_badge_class}">${escapeHtml(i.priority || 'Sedang')}</span></div>
                            <div class="mb-2"><span class="text-muted">Kategori:</span> <strong>${escapeHtml(i.category)}</strong></div>
                            ${i.attachment_path ? `<div><span class="text-muted">Dokumentasi:</span> <a href="/${i.attachment_path}" target="_blank" class="fw-semibold text-primary"><i class="bi bi-file-earmark-image"></i> Buka Dokumentasi</a></div>` : ''}
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white rounded border mb-3" style="font-size:12.5px;">
                    <div class="fw-bold text-dark mb-1">Deskripsi Temuan</div>
                    <div class="text-secondary">${escapeHtml(i.description)}</div>
                    ${i.action_plan ? `<hr class="my-2"><div class="fw-bold text-dark mb-1">Rencana Tindak Lanjut (Action Plan)</div><div class="text-secondary">${escapeHtml(i.action_plan)}</div>` : ''}
                </div>

                <div class="p-3 bg-light rounded border">
                    <h6 class="fw-bold text-dark mb-3" style="font-size:13px;">
                        <i class="bi bi-clock-history text-success me-1"></i> Riwayat Tindak Lanjut &amp; Perubahan Status (Kronologis)
                    </h6>
                    ${logsHtml}
                </div>
            `;
        } catch (err) {
            bodyEl.innerHTML = `<div class="text-danger text-center py-3">Gagal memuat detail temuan.</div>`;
        }
    }

    function formatDate(dStr) {
        if (!dStr) return '-';
        const s = String(dStr).substring(0, 10);
        const parts = s.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : s;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush


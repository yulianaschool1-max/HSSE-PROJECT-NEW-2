@extends('layouts.app', [
    'title' => 'Manhour - Rekapitulasi Jam Kerja Aman',
    'activeModule' => 'man-hours'
])

@push('styles')
<style>
    /* =========================================================================
       1. TOP HEADER & BAR SECTION
       ========================================================================= */
    .mh-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .mh-brand-box {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .mh-brand-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #059669;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);
        flex-shrink: 0;
    }

    .mh-brand-title {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin: 0;
        letter-spacing: -0.3px;
    }

    .mh-breadcrumb {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mh-top-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-mh-primary {
        background-color: #059669;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-mh-primary:hover {
        background-color: #047857;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.35);
    }

    .btn-mh-outline {
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-mh-outline:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
        transform: translateY(-1px);
    }

    /* =========================================================================
       2. TWO HEADER CARDS (Tanggal Data & Waktu Sekarang)
       ========================================================================= */
    .mh-header-cards-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    .mh-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .mh-info-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .mh-info-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }

    .mh-info-val-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mh-info-main-val {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.2px;
    }

    .mh-info-sub {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 4px;
    }

    .mh-badge-pill {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
    }

    /* =========================================================================
       3. FILTER BAR SECTION
       ========================================================================= */
    .mh-filter-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .mh-filter-grid {
        display: grid;
        grid-template-columns: 190px 240px 190px auto;
        gap: 14px;
        align-items: flex-end;
    }

    .filter-item-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #475569;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .date-range-split {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .date-range-split .form-control,
    .mh-filter-box .form-control,
    .mh-filter-box .form-select {
        font-size: 12.5px;
        border-color: #cbd5e1;
        padding: 6.5px 10px;
        border-radius: 6px;
    }

    .date-range-split .form-control:focus,
    .mh-filter-box .form-select:focus,
    .mh-filter-box .form-control:focus {
        border-color: #059669;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
    }

    .active-period-indicator {
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: #64748b;
    }

    .active-period-indicator strong {
        color: #0f172a;
    }

    .active-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        color: #059669;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        border: 1px solid #a7f3d0;
    }

    .filter-btn-group {
        display: flex;
        gap: 8px;
    }

    .btn-apply-filter {
        background-color: #059669;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 7px 16px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-apply-filter:hover {
        background-color: #047857;
    }

    .btn-reset-filter {
        background-color: transparent;
        color: #64748b;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 7px 14px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-reset-filter:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }

    /* =========================================================================
       4. 4 SUMMARY STATISTIC CARDS
       ========================================================================= */
    .mh-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .mh-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .mh-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.04);
    }

    .mh-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .mh-stat-title {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
    }

    .mh-stat-val {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .mh-stat-val small {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        margin-left: 2px;
    }

    .mh-stat-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .mh-stat-badge-sub {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #059669;
        font-weight: 600;
        margin-top: 10px;
    }

    /* =========================================================================
       5. COMBO CHART & REKAP TENAGA KERJA HARI INI
       ========================================================================= */
    .mh-chart-row-grid {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 16px;
        margin-bottom: 22px;
    }

    .mh-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .mh-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .mh-panel-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-view-select {
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        border-color: #cbd5e1;
        border-radius: 6px;
        padding: 4px 8px;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 290px;
        width: 100%;
    }

    /* Right Table of Today's Recap */
    .today-recap-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .today-recap-table th {
        font-weight: 700;
        color: #64748b;
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        text-transform: uppercase;
    }

    .today-recap-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .progress-bar-wrap {
        width: 100%;
        background-color: #f1f5f9;
        border-radius: 6px;
        height: 8px;
        overflow: hidden;
        margin-top: 4px;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 6px;
        background-color: #059669;
        transition: width 0.3s ease;
    }

    /* =========================================================================
       6. TABLE RIWAYAT DATA MANHOUR
       ========================================================================= */
    .mh-table-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        margin-bottom: 30px;
    }

    .table-topbar-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }

    .table-title-custom {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-actions-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input-box {
        position: relative;
        min-width: 260px;
    }

    .search-input-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 12px;
    }

    .search-input-box input {
        padding-left: 30px;
        font-size: 12px;
        border-color: #cbd5e1;
        border-radius: 6px;
    }

    .btn-export-pdf-red {
        background-color: #ef4444;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .btn-export-pdf-red:hover {
        background-color: #dc2626;
        color: #ffffff;
    }

    .btn-export-excel-green {
        background-color: #059669;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .btn-export-excel-green:hover {
        background-color: #047857;
        color: #ffffff;
    }

    .table-custom-mh {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .table-custom-mh thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        padding: 10px 12px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .table-custom-mh tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .table-custom-mh tbody tr:hover td {
        background-color: #f8fafc;
    }

    .badge-status-green {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .action-icon-btn {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid transparent;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .action-icon-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .action-icon-btn.btn-delete:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    @media (max-width: 1100px) {
        .mh-header-cards-grid { grid-template-columns: 1fr; }
        .mh-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .mh-chart-row-grid { grid-template-columns: 1fr; }
        .mh-filter-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .mh-stats-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $isAdmin = auth()->user()?->isAdminManagement() ?? false;
@endphp

<!-- ===========================================================================
     1. TOP BAR: TITLE & ACTIONS (+ Input Manhour & Kelola Perusahaan)
     =========================================================================== -->
<div class="mh-top-bar">
    <div class="mh-brand-box">
        <div class="mh-brand-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <h1 class="mh-brand-title">Manhour</h1>
            <div class="mh-breadcrumb">
                <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="text-secondary">Data</span>
                <span>&rsaquo;</span>
                <span class="text-dark fw-semibold">Manhour</span>
            </div>
        </div>
    </div>

    <div class="mh-top-actions">
        @adminManagement
            <button type="button" class="btn-mh-primary" onclick="openBatchManhourModal()">
                <i class="bi bi-plus-lg"></i> Input Manhour <i class="bi bi-chevron-down ms-1" style="font-size: 11px;"></i>
            </button>
            <button type="button" class="btn-mh-outline" onclick="openManageCompaniesModal()">
                <i class="bi bi-gear"></i> Kelola Perusahaan
            </button>
        @endadminManagement
    </div>
</div>

<!-- ===========================================================================
     2. TWO HEADER CARDS: TANGGAL DATA YANG DITAMPILKAN & WAKTU SEKARANG
     =========================================================================== -->
<div class="mh-header-cards-grid">
    <!-- Card 1: Tanggal Data yang Ditampilkan -->
    <div class="mh-info-card">
        <div class="mh-info-icon-wrap" style="background: #eff6ff; color: #2563eb;">
            <i class="bi bi-calendar3"></i>
        </div>
        <div>
            <div class="mh-info-label">Tanggal Data yang Ditampilkan</div>
            <div class="mh-info-val-row">
                <span class="mh-info-main-val" id="headerPeriodLabel">{{ $formattedPeriodLabel }}</span>
                <span class="mh-badge-pill">Data Terakhir Tersedia</span>
            </div>
            <div class="mh-info-sub">Terakhir diinput: <span id="headerLastInputTime">{{ $lastInputTimeFormatted }}</span></div>
        </div>
    </div>

    <!-- Card 2: Waktu Sekarang (Live Digital Clock) -->
    <div class="mh-info-card">
        <div class="mh-info-icon-wrap" style="background: #ecfdf5; color: #059669;">
            <i class="bi bi-clock"></i>
        </div>
        <div>
            <div class="mh-info-label">Waktu Sekarang</div>
            <div class="mh-info-val-row">
                <span class="mh-info-main-val" id="liveDigitalClock" style="font-family: ui-monospace, monospace; font-size: 20px;">--:--:--</span>
            </div>
            <div class="mh-info-sub" id="liveDigitalDate">{{ now()->translatedFormat('l, d F Y') }} (WIB)</div>
        </div>
    </div>
</div>

<!-- ===========================================================================
     3. FILTER BAR: Periode Pencatatan & Unit / Perusahaan
     =========================================================================== -->
<div class="mh-filter-box">
    <form id="manhourFilterForm" onsubmit="event.preventDefault(); applyManhourFilters();">
        <div class="mh-filter-grid">
            <!-- Pilihan Tipe Periode -->
            <div>
                <label class="filter-item-label">
                    <i class="bi bi-funnel"></i> Tipe Periode
                </label>
                <select id="filterPeriodType" class="form-select form-select-sm" onchange="togglePeriodInputMode(this.value)">
                    <option value="all" {{ request('period_type', 'all') === 'all' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="daily" {{ request('period_type') === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ request('period_type') === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ request('period_type') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ request('period_type') === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom" {{ request('period_type') === 'custom' ? 'selected' : '' }}>Rentang Tanggal</option>
                </select>
            </div>

            <!-- Input Dinamis Berdasarkan Tipe Periode -->
            <div id="periodInputWrapper">
                <label class="filter-item-label" id="periodInputLabel">
                    <i class="bi bi-calendar-event"></i> Parameter Periode
                </label>

                <!-- 1. Semua Periode (Placeholder Read-Only) -->
                <div id="periodInput_all" class="period-input-variant">
                    <input type="text" class="form-control" value="Seluruh Rekaman Data" disabled readonly style="background: #f8fafc; color: #64748b;">
                </div>

                <!-- 2. Harian -->
                <div id="periodInput_daily" class="period-input-variant" style="display: none;">
                    <input type="date" id="filterDateDaily" class="form-control" value="{{ request('filter_date', date('Y-m-d')) }}">
                </div>

                <!-- 3. Mingguan -->
                <div id="periodInput_weekly" class="period-input-variant" style="display: none;">
                    <input type="date" id="filterDateWeekly" class="form-control" value="{{ request('filter_week_date', date('Y-m-d')) }}" title="Pilih sembarang tanggal dalam minggu">
                </div>

                <!-- 4. Bulanan -->
                <div id="periodInput_monthly" class="period-input-variant" style="display: none;">
                    <input type="month" id="filterMonthVal" class="form-control" value="{{ request('filter_month', date('Y-m')) }}">
                </div>

                <!-- 5. Tahunan -->
                <div id="periodInput_yearly" class="period-input-variant" style="display: none;">
                    <input type="number" id="filterYearVal" class="form-control" min="2000" max="2099" step="1" value="{{ request('filter_year', date('Y')) }}">
                </div>

                <!-- 6. Rentang Tanggal -->
                <div id="periodInput_custom" class="period-input-variant" style="display: none;">
                    <div class="date-range-split">
                        <input type="date" id="filterStart" class="form-control" value="{{ request('start_date') }}" title="Tanggal Mulai">
                        <span class="text-muted">–</span>
                        <input type="date" id="filterEnd" class="form-control" value="{{ request('end_date') }}" title="Tanggal Selesai">
                    </div>
                </div>
            </div>

            <!-- Dropdown Unit / Perusahaan -->
            <div>
                <label class="filter-item-label">
                    <i class="bi bi-building"></i> Unit / Perusahaan
                </label>
                <select id="filterUnit" class="form-select form-select-sm">
                    <option value="Semua">Semua Unit</option>
                    @foreach(['GDE', 'KODIPA', 'UTILITY', 'SECURITY', 'JOTRE', 'PMC', 'KP'] as $uName)
                        <option value="{{ $uName }}" {{ request('unit') === $uName ? 'selected' : '' }}>{{ $uName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons Terapkan & Reset -->
            <div>
                <label class="filter-item-label" style="visibility: hidden;">Aksi</label>
                <div class="filter-btn-group">
                    <button type="submit" class="btn-apply-filter">
                        <i class="bi bi-filter"></i> Terapkan Filter
                    </button>
                    <button type="button" class="btn-reset-filter" onclick="resetManhourFilters()">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Indikator Periode Aktif -->
        <div class="active-period-indicator">
            <div>
                <i class="bi bi-info-circle text-primary me-1"></i>
                Data yang ditampilkan difilter berdasarkan:
                <span class="active-period-badge" id="activePeriodDisplay">
                    <i class="bi bi-calendar2-check"></i> {{ $formattedPeriodLabel }}
                </span>
            </div>
            <div class="text-muted small">
                Terakhir diinput: <strong id="indicatorLastInputTime">{{ $lastInputTimeFormatted }}</strong>
            </div>
        </div>
    </form>
</div>

<!-- ===========================================================================
     4. 4 SUMMARY STATISTIC CARDS (DINAMIS MENGIKUTI PERIODE AKTIF)
     =========================================================================== -->
<div class="mh-stats-grid">
    <!-- Card 1: Total Pekerja (Periode) -->
    <div class="mh-stat-card">
        <div class="mh-stat-top">
            <div>
                <div class="mh-stat-title" id="cardTitleWorkers">Total Pekerja (Periode)</div>
                <div class="mh-stat-val" id="statWorkersToday">
                    {{ number_format($stats['total_workers'] ?: ($todayTotalWorkers ?: $stats['daily_workers']), 0, ',', '.') }} <small>Orang</small>
                </div>
            </div>
            <div class="mh-stat-icon-circle" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
        <div class="mh-stat-badge-sub">
            <i class="bi bi-calendar-event"></i> Periode: <span class="activePeriodText">{{ $formattedPeriodLabel }}</span>
        </div>
    </div>

    <!-- Card 2: Total Manhour (Periode) -->
    <div class="mh-stat-card">
        <div class="mh-stat-top">
            <div>
                <div class="mh-stat-title" id="cardTitleManhour">Total Manhour (Periode)</div>
                <div class="mh-stat-val" id="statManhourToday">
                    {{ number_format($stats['total_man_hours'], 0, ',', '.') }} <small>Jam</small>
                </div>
            </div>
            <div class="mh-stat-icon-circle" style="background: #ecfdf5; color: #059669;">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
        <div class="mh-stat-badge-sub">
            <i class="bi bi-check-circle-fill"></i> Manhour = Σ (Pekerja &times; Jam)
        </div>
    </div>

    <!-- Card 3: Rata-rata Jam Kerja / Orang (Periode) -->
    <div class="mh-stat-card">
        <div class="mh-stat-top">
            <div>
                <div class="mh-stat-title">Rata-rata Jam Kerja / Orang</div>
                <div class="mh-stat-val" id="statAvgHours">
                    {{ number_format($stats['avg_hours_per_worker'] ?? 0.0, 1, ',', '.') }} <small>Jam</small>
                </div>
            </div>
            <div class="mh-stat-icon-circle" style="background: #fffbeb; color: #d97706;">
                <i class="bi bi-speedometer2"></i>
            </div>
        </div>
        <div class="mh-stat-badge-sub">
            <i class="bi bi-calculator"></i> Total Manhour ÷ Total Pekerja
        </div>
    </div>

    <!-- Card 4: Total Manhour Kumulatif (Seluruh Waktu) -->
    <div class="mh-stat-card">
        <div class="mh-stat-top">
            <div>
                <div class="mh-stat-title">Total Manhour Kumulatif</div>
                <div class="mh-stat-val" id="statCumulativeManhour">
                    {{ number_format($stats['cumulative_man_hours'], 0, ',', '.') }} <small>Jam</small>
                </div>
            </div>
            <div class="mh-stat-icon-circle" style="background: #fdf2f8; color: #db2777;">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
        <div class="mh-stat-badge-sub">
            <i class="bi bi-infinity"></i> Akumulasi Seluruh Data
        </div>
    </div>
</div>

<!-- ===========================================================================
     5. CHARTS & TODAY'S BREAKDOWN SECTION
     =========================================================================== -->
<div class="mh-chart-row-grid">
    <!-- Chart: Tren Jumlah Tenaga Kerja dan Manhour -->
    <div class="mh-panel-card">
        <div class="mh-panel-header">
            <h2 class="mh-panel-title">
                <i class="bi bi-graph-up-arrow text-success"></i> Tren Jumlah Tenaga Kerja dan Manhour
            </h2>
            <div>
                <select class="form-select form-select-sm chart-view-select" id="chartViewMode">
                    <option value="daily">Tampilan: Harian</option>
                </select>
            </div>
        </div>
        <div class="chart-canvas-wrap">
            <canvas id="comboTrendChartCanvas"></canvas>
        </div>
    </div>

    <!-- Right Sidebar: Rekap Tenaga Kerja Hari Ini -->
    <div class="mh-panel-card">
        <div class="mh-panel-header">
            <h2 class="mh-panel-title">
                <i class="bi bi-pie-chart text-primary"></i> Rekap Tenaga Kerja Hari Ini
            </h2>
        </div>
        <div class="table-responsive">
            <table class="today-recap-table" id="todayRecapTable">
                <thead>
                    <tr>
                        <th style="width: 32%;">Unit / Perusahaan</th>
                        <th style="width: 28%; text-align: right;">Jumlah Pekerja</th>
                        <th style="width: 40%; text-align: right;">Persentase</th>
                    </tr>
                </thead>
                <tbody id="todayRecapTableBody">
                    @forelse($todayBreakdown as $b)
                        <tr>
                            <td class="fw-bold text-dark">{{ $b['unit'] }}</td>
                            <td style="text-align: right;">
                                <span class="fw-bold">{{ number_format($b['count'], 0, ',', '.') }}</span>
                                <span class="text-muted small">Orang</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="fw-semibold text-secondary">{{ number_format($b['percentage'], 1, ',', '.') }}%</span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill" style="width: {{ $b['percentage'] }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Belum ada data tenaga kerja hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e2e8f0; font-weight: bold;">
                        <td class="text-dark">Total</td>
                        <td style="text-align: right;" class="text-primary" id="todayRecapTotalWorkers">
                            {{ number_format($todayTotalWorkers, 0, ',', '.') }} Orang
                        </td>
                        <td style="text-align: right;" class="text-success">
                            100%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ===========================================================================
     6. TABLE RIWAYAT DATA MANHOUR
     =========================================================================== -->
<div class="mh-table-panel">
    <div class="table-topbar-flex">
        <h2 class="table-title-custom">
            <i class="bi bi-clock-history text-success"></i> Riwayat Data Manhour
        </h2>
        <div class="table-actions-right">
            <!-- Search Box -->
            <div class="search-input-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control form-control-sm" id="tableSearchBox" placeholder="Cari tanggal, unit atau perusahaan...">
            </div>

            <!-- Export PDF -->
            <a href="{{ route('man-hours.export', request()->query()) }}" id="btnExportPdf" class="btn-export-pdf-red" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
            </a>

            <!-- Export Excel -->
            <a href="{{ route('man-hours.export-excel', request()->query()) }}" id="btnExportExcel" class="btn-export-excel-green">
                <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-custom-mh" id="mainManhourTable">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 65px; text-align: center;">GDE</th>
                    <th style="width: 75px; text-align: center;">KODIPA</th>
                    <th style="width: 75px; text-align: center;">UTILITY</th>
                    <th style="width: 80px; text-align: center;">SECURITY</th>
                    <th style="width: 70px; text-align: center;">JOTRE</th>
                    <th style="width: 65px; text-align: center;">PMC</th>
                    <th style="width: 60px; text-align: center;">KP</th>
                    <th style="width: 110px; text-align: center;">Total Pekerja</th>
                    <th style="width: 120px; text-align: center;">Jam Kerja / Orang</th>
                    <th style="width: 130px; text-align: center;">Total Manhour</th>
                    <th style="width: 120px; text-align: center;">Status</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="manhourTableBody">
                @forelse($historyTableRows as $hIdx => $hRow)
                    <tr data-date="{{ $hRow['date'] }}">
                        <td style="text-align: center; color: #64748b;">{{ $hIdx + 1 }}</td>
                        <td class="fw-semibold">{{ $hRow['date_formatted'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['GDE'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['KODIPA'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['UTILITY'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['SECURITY'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['JOTRE'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['PMC'] }}</td>
                        <td style="text-align: center;">{{ $hRow['units']['KP'] }}</td>
                        <td style="text-align: center;" class="fw-bold text-dark">{{ number_format($hRow['total_workers'], 0, ',', '.') }} Orang</td>
                        <td style="text-align: center;" class="text-secondary">{{ number_format($hRow['hours_per_worker'], 1, ',', '.') }} Jam</td>
                        <td style="text-align: center;" class="fw-bold text-success">{{ number_format($hRow['total_man_hours'], 0, ',', '.') }} Jam</td>
                        <td style="text-align: center;">
                            <span class="badge-status-green">
                                <i class="bi bi-check-circle-fill"></i> Sudah Diinput
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="action-icon-btn" onclick="openBatchManhourModal('{{ $hRow['date'] }}')" title="Edit Data Tanggal Ini">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="action-icon-btn btn-delete" onclick="deleteDateManhour('{{ $hRow['date'] }}')" title="Hapus Data Tanggal Ini">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Tidak ada data Manhour pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ===========================================================================
     MODAL 1: INPUT / EDIT MANHOUR HARIAN (7 PERUSAHAAN + JAM KERJA DINAMIS)
     =========================================================================== -->
<div class="modal fade" id="modalBatchManhour" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formBatchManhour" onsubmit="submitBatchManhour(event)">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #059669; display: grid; place-items: center; font-size: 18px;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="batchModalTitle">Input Data Manhour</h5>
                            <div class="text-muted small">Input jumlah pekerja per unit/perusahaan &amp; jam kerja per orang</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Tanggal Pencatatan -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 12.5px;">Tanggal Pencatatan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="modalRecordDate" name="record_date" value="{{ date('Y-m-d') }}" required onchange="loadDateForBatchInput(this.value)">
                        <div class="form-text small text-muted">Jika tanggal ini sudah pernah diinput, nilainya akan terisi otomatis untuk diedit/diperbarui.</div>
                    </div>

                    <!-- Tabel Input 7 Perusahaan -->
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm align-middle mb-0" style="font-size: 12.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%;">Unit / Perusahaan</th>
                                    <th style="width: 30%;">Jumlah Pekerja (Orang)</th>
                                    <th style="width: 35%;">Jam Kerja / Orang (Jam)</th>
                                </tr>
                            </thead>
                            <tbody id="modalUnitsTbody">
                                @foreach(['GDE', 'KODIPA', 'UTILITY', 'SECURITY', 'JOTRE', 'PMC', 'KP'] as $idx => $comp)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $comp }}</span>
                                            <input type="hidden" name="units[{{ $idx }}][unit]" value="{{ $comp }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm modal-worker-input"
                                                   name="units[{{ $idx }}][worker_count]"
                                                   id="inputWorker_{{ $comp }}"
                                                   min="0" step="1" value="0" required oninput="calcModalLiveTotal()">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm modal-hours-input"
                                                   name="units[{{ $idx }}][hours_per_worker]"
                                                   id="inputHours_{{ $comp }}"
                                                   min="0" max="24" step="0.5" value="8" required oninput="calcModalLiveTotal()">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Live Calculated Summary Card -->
                    <div class="p-3 rounded d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Rumus Perhitungan</div>
                            <div class="text-dark small">Manhour = Σ (Jumlah Pekerja &times; Jam Kerja per Orang)</div>
                            <div class="text-muted" style="font-size: 11px;">Rata-rata Jam Kerja/Orang = Total Manhour ÷ Total Pekerja</div>
                        </div>
                        <div class="d-flex gap-4">
                            <div class="text-end">
                                <div class="text-muted small">Total Pekerja</div>
                                <div class="fs-5 fw-bold text-primary"><span id="modalLiveWorkers">0</span> <small class="fs-6 text-muted">Orang</small></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Rata-rata Jam Kerja</div>
                                <div class="fs-5 fw-bold text-warning"><span id="modalLiveAvgHours">0,0</span> <small class="fs-6 text-muted">Jam</small></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Total Manhour</div>
                                <div class="fs-5 fw-bold text-success"><span id="modalLiveManhours">0</span> <small class="fs-6 text-muted">Jam</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-mh-primary btn-sm px-4" id="btnSaveBatchManhour">
                        <i class="bi bi-check-lg"></i> Simpan Data Manhour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===========================================================================
     MODAL 2: KELOLA PERUSAHAAN (MANAJEMEN PERUSAHAAN)
     =========================================================================== -->
<div class="modal fade" id="modalManageCompanies" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: grid; place-items: center; font-size: 18px;">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Manajemen Perusahaan / Kategori</h5>
                        <div class="text-muted small">Kelola status aktif/nonaktif dan nama perusahaan mitra</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Form Tambah Perusahaan -->
                <form id="formAddCompany" onsubmit="submitNewCompany(event)" class="p-3 bg-light border rounded mb-3">
                    <div class="fw-bold text-dark mb-2 small"><i class="bi bi-plus-circle text-success me-1"></i> Tambah Perusahaan Baru</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="newCompanyName" placeholder="Contoh: MITRA BARU" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Keterangan (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="newCompanyDesc" placeholder="Keterangan perusahaan...">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold" id="btnSubmitNewCompany">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Daftar Perusahaan -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 200px;">Nama Perusahaan</th>
                                <th>Keterangan</th>
                                <th style="width: 100px; text-align: center;">Status</th>
                                <th style="width: 150px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="companyTableBody">
                            @foreach($operationalUnits as $cIdx => $cOp)
                                <tr data-id="{{ $cOp->id }}">
                                    <td class="text-center text-muted">{{ $cIdx + 1 }}</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm fw-bold" id="compName_{{ $cOp->id }}" value="{{ $cOp->name }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" id="compDesc_{{ $cOp->id }}" value="{{ $cOp->description }}">
                                    </td>
                                    <td class="text-center">
                                        @if($cOp->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="saveCompanyEdit({{ $cOp->id }})">
                                            <i class="bi bi-check-lg"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-sm {{ $cOp->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} py-0 px-2" style="font-size: 11px;" onclick="toggleCompanyActive({{ $cOp->id }})">
                                            {{ $cOp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const MH_BASE_URL = "{{ url('/man-hours') }}";
    const TARGET_COMPANIES = ['GDE', 'KODIPA', 'UTILITY', 'SECURITY', 'JOTRE', 'PMC', 'KP'];

    let comboChartInstance = null;
    let initialTrend = @json($trendChart);

    // =========================================================================
    // 1. LIVE DIGITAL CLOCK
    // =========================================================================
    function updateClock() {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveDigitalClock');
        if (clockEl) {
            clockEl.textContent = `${hrs}:${mins}:${secs}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // =========================================================================
    // 2. COMBO TREND CHART (BAR FOR WORKERS + LINE FOR MANHOUR)
    // =========================================================================
    function initComboChart(chartData) {
        const canvas = document.getElementById('comboTrendChartCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (comboChartInstance) {
            comboChartInstance.destroy();
        }

        comboChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: 'Jumlah Pekerja (Orang)',
                        data: chartData.workers || [],
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barPercentage: 0.5,
                        yAxisID: 'yWorkers',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Total Manhour (Jam)',
                        data: chartData.man_hours || [],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 4,
                        tension: 0.3,
                        borderWidth: 2,
                        yAxisID: 'yHours',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 11, weight: '600' },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.yAxisID === 'yHours') {
                                    return ` Total Manhour: ${Number(ctx.raw).toLocaleString('id-ID')} Jam`;
                                }
                                return ` Jumlah Pekerja: ${Number(ctx.raw).toLocaleString('id-ID')} Orang`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    yWorkers: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Pekerja (Orang)',
                            font: { size: 11, weight: '600' }
                        },
                        ticks: { precision: 0 }
                    },
                    yHours: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: {
                            display: true,
                            text: 'Total Manhour (Jam)',
                            font: { size: 11, weight: '600' }
                        },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // =========================================================================
    // 3. TABLE SEARCH FILTER
    // =========================================================================
    document.getElementById('tableSearchBox')?.addEventListener('input', function (e) {
        const q = (e.target.value || '').toLowerCase().trim();
        document.querySelectorAll('#manhourTableBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // =========================================================================
    // 4. PERIOD SELECTION TOGGLER & APPLY / RESET FILTERS (AJAX REFRESH)
    // =========================================================================
    function togglePeriodInputMode(mode) {
        const variants = ['all', 'daily', 'weekly', 'monthly', 'yearly', 'custom'];
        variants.forEach(v => {
            const el = document.getElementById(`periodInput_${v}`);
            if (el) el.style.display = (v === mode) ? '' : 'none';
        });

        const labelMap = {
            'all': 'Parameter Periode',
            'daily': 'Pilih Tanggal',
            'weekly': 'Pilih Tanggal dalam Minggu',
            'monthly': 'Pilih Bulan & Tahun',
            'yearly': 'Pilih Tahun',
            'custom': 'Rentang Tanggal'
        };
        const lbl = document.getElementById('periodInputLabel');
        if (lbl) {
            lbl.innerHTML = `<i class="bi bi-calendar-event"></i> ${labelMap[mode] || 'Parameter Periode'}`;
        }
    }

    function applyManhourFilters() {
        const periodType = document.getElementById('filterPeriodType')?.value || 'all';
        const unit = document.getElementById('filterUnit')?.value || 'Semua';

        const params = new URLSearchParams();
        params.append('period_type', periodType);

        if (periodType === 'daily') {
            const d = document.getElementById('filterDateDaily')?.value || '';
            if (d) params.append('filter_date', d);
        } else if (periodType === 'weekly') {
            const w = document.getElementById('filterDateWeekly')?.value || '';
            if (w) params.append('filter_week_date', w);
        } else if (periodType === 'monthly') {
            const m = document.getElementById('filterMonthVal')?.value || '';
            if (m) params.append('filter_month', m);
        } else if (periodType === 'yearly') {
            const y = document.getElementById('filterYearVal')?.value || '';
            if (y) params.append('filter_year', y);
        } else if (periodType === 'custom') {
            const start = document.getElementById('filterStart')?.value || '';
            const end = document.getElementById('filterEnd')?.value || '';
            if (start) params.append('start_date', start);
            if (end) params.append('end_date', end);
        }

        if (unit && unit !== 'Semua') {
            params.append('unit', unit);
        }

        // Update export buttons
        const btnPdf = document.getElementById('btnExportPdf');
        const btnExcel = document.getElementById('btnExportExcel');
        if (btnPdf) btnPdf.href = `${MH_BASE_URL}/export?${params.toString()}`;
        if (btnExcel) btnExcel.href = `${MH_BASE_URL}/export-excel?${params.toString()}`;

        fetch(`${MH_BASE_URL}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            // Update Header Card Period & Active Period Badge
            const periodLabel = data.formattedPeriodLabel || 'Semua Periode';
            if (document.getElementById('headerPeriodLabel')) {
                document.getElementById('headerPeriodLabel').textContent = periodLabel;
            }
            if (document.getElementById('activePeriodDisplay')) {
                document.getElementById('activePeriodDisplay').innerHTML = `<i class="bi bi-calendar2-check"></i> ${periodLabel}`;
            }
            document.querySelectorAll('.activePeriodText').forEach(el => {
                el.textContent = periodLabel;
            });

            if (data.lastInputTimeFormatted) {
                if (document.getElementById('headerLastInputTime')) {
                    document.getElementById('headerLastInputTime').textContent = data.lastInputTimeFormatted;
                }
                if (document.getElementById('indicatorLastInputTime')) {
                    document.getElementById('indicatorLastInputTime').textContent = data.lastInputTimeFormatted;
                }
            }

            // Update 4 Summary Stats (Dinamis mengikuti periode yang difilter)
            const totWorkers = Number(data.stats.total_workers || data.todayTotalWorkers || 0);
            const totMh = Number(data.stats.total_man_hours || 0);
            const avgH = Number(data.stats.avg_hours_per_worker || 0);
            const cumMh = Number(data.stats.cumulative_man_hours || 0);

            document.getElementById('statWorkersToday').innerHTML = `${totWorkers.toLocaleString('id-ID')} <small>Orang</small>`;
            document.getElementById('statManhourToday').innerHTML = `${totMh.toLocaleString('id-ID')} <small>Jam</small>`;
            document.getElementById('statAvgHours').innerHTML = `${avgH.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} <small>Jam</small>`;
            document.getElementById('statCumulativeManhour').innerHTML = `${cumMh.toLocaleString('id-ID')} <small>Jam</small>`;

            // Update Combo Chart
            if (data.trendChart) {
                initComboChart(data.trendChart);
            }

            // Update Today's Breakdown Table
            renderTodayRecap(data.todayBreakdown || [], data.todayTotalWorkers || 0);

            // Update Main History Table
            renderHistoryTable(data.historyTableRows || []);
        })
        .catch(err => console.error('Filter fetch error:', err));
    }

    function resetManhourFilters() {
        const periodTypeSelect = document.getElementById('filterPeriodType');
        if (periodTypeSelect) {
            periodTypeSelect.value = 'all';
            togglePeriodInputMode('all');
        }
        if (document.getElementById('filterStart')) document.getElementById('filterStart').value = '';
        if (document.getElementById('filterEnd')) document.getElementById('filterEnd').value = '';
        if (document.getElementById('filterUnit')) document.getElementById('filterUnit').value = 'Semua';
        applyManhourFilters();
    }

    function renderTodayRecap(rows, totalWorkers) {
        const tbody = document.getElementById('todayRecapTableBody');
        const totEl = document.getElementById('todayRecapTotalWorkers');
        if (!tbody) return;

        if (totEl) {
            totEl.textContent = `${Number(totalWorkers).toLocaleString('id-ID')} Orang`;
        }

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Belum ada data tenaga kerja.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(b => `
            <tr>
                <td class="fw-bold text-dark">${b.unit}</td>
                <td style="text-align: right;">
                    <span class="fw-bold">${Number(b.count).toLocaleString('id-ID')}</span>
                    <span class="text-muted small">Orang</span>
                </td>
                <td style="text-align: right;">
                    <span class="fw-semibold text-secondary">${Number(b.percentage).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}%</span>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width: ${b.percentage}%;"></div>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderHistoryTable(rows) {
        const tbody = document.getElementById('manhourTableBody');
        if (!tbody) return;

        if (rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="14" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Tidak ada data Manhour pada periode ini.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = rows.map((r, idx) => `
            <tr data-date="${r.date}">
                <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                <td class="fw-semibold">${r.date_formatted}</td>
                <td style="text-align: center;">${r.units.GDE || 0}</td>
                <td style="text-align: center;">${r.units.KODIPA || 0}</td>
                <td style="text-align: center;">${r.units.UTILITY || 0}</td>
                <td style="text-align: center;">${r.units.SECURITY || 0}</td>
                <td style="text-align: center;">${r.units.JOTRE || 0}</td>
                <td style="text-align: center;">${r.units.PMC || 0}</td>
                <td style="text-align: center;">${r.units.KP || 0}</td>
                <td style="text-align: center;" class="fw-bold text-dark">${Number(r.total_workers).toLocaleString('id-ID')} Orang</td>
                <td style="text-align: center;" class="text-secondary">${Number(r.hours_per_worker).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam</td>
                <td style="text-align: center;" class="fw-bold text-success">${Number(r.total_man_hours).toLocaleString('id-ID')} Jam</td>
                <td style="text-align: center;">
                    <span class="badge-status-green">
                        <i class="bi bi-check-circle-fill"></i> Sudah Diinput
                    </span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="action-icon-btn" onclick="openBatchManhourModal('${r.date}')" title="Edit Data Tanggal Ini">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="action-icon-btn btn-delete" onclick="deleteDateManhour('${r.date}')" title="Hapus Data Tanggal Ini">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // =========================================================================
    // 5. INPUT / EDIT BATCH MANHOUR MODAL
    // =========================================================================
    function openBatchManhourModal(targetDate = null) {
        const dateInput = document.getElementById('modalRecordDate');
        const chosenDate = targetDate || new Date().toISOString().substring(0, 10);
        if (dateInput) {
            dateInput.value = chosenDate;
        }

        const title = document.getElementById('batchModalTitle');
        if (title) {
            title.textContent = targetDate ? `Edit Data Manhour (${targetDate})` : 'Input Data Manhour';
        }

        loadDateForBatchInput(chosenDate);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBatchManhour')).show();
    }

    function loadDateForBatchInput(dateVal) {
        if (!dateVal) return;

        fetch(`${MH_BASE_URL}/by-date?record_date=${encodeURIComponent(dateVal)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.units)) return;

            data.units.forEach(u => {
                const wInput = document.getElementById(`inputWorker_${u.unit_name}`);
                const hInput = document.getElementById(`inputHours_${u.unit_name}`);
                if (wInput) wInput.value = u.worker_count ?? 0;
                if (hInput) hInput.value = u.hours_per_worker ?? 8;
            });

            calcModalLiveTotal();
        })
        .catch(() => {});
    }

    function calcModalLiveTotal() {
        let totW = 0;
        let totMh = 0;

        TARGET_COMPANIES.forEach(comp => {
            const wVal = parseInt(document.getElementById(`inputWorker_${comp}`)?.value || '0', 10) || 0;
            const hVal = parseFloat(document.getElementById(`inputHours_${comp}`)?.value || '8') || 0;
            totW += wVal;
            totMh += (wVal * hVal);
        });

        const avgH = totW > 0 ? (totMh / totW) : 0.0;

        document.getElementById('modalLiveWorkers').textContent = totW.toLocaleString('id-ID');
        document.getElementById('modalLiveManhours').textContent = Math.round(totMh).toLocaleString('id-ID');
        if (document.getElementById('modalLiveAvgHours')) {
            document.getElementById('modalLiveAvgHours').textContent = avgH.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        }
    }

    function submitBatchManhour(e) {
        e.preventDefault();

        const dateVal = document.getElementById('modalRecordDate')?.value;
        if (!dateVal) {
            Swal.fire('Peringatan', 'Tanggal pencatatan wajib diisi.', 'warning');
            return;
        }

        const unitsPayload = [];
        TARGET_COMPANIES.forEach((comp, idx) => {
            const wVal = parseInt(document.getElementById(`inputWorker_${comp}`)?.value || '0', 10) || 0;
            const hVal = parseFloat(document.getElementById(`inputHours_${comp}`)?.value || '8') || 0;
            unitsPayload.push({
                unit: comp,
                category: comp,
                worker_count: wVal,
                hours_per_worker: hVal
            });
        });

        const btn = document.getElementById('btnSaveBatchManhour');
        btn.disabled = true;

        fetch(`${MH_BASE_URL}/batch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                record_date: dateVal,
                units: unitsPayload
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (!data.success) {
                Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan.', 'error');
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('modalBatchManhour'))?.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan',
                text: 'Data Manhour berhasil disimpan dan disinkronkan.',
                timer: 1600,
                showConfirmButton: false
            });

            applyManhourFilters();
        })
        .catch(() => {
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan saat memproses data.', 'error');
        });
    }

    // =========================================================================
    // 6. DELETE DATE MANHOUR
    // =========================================================================
    function deleteDateManhour(dateStr) {
        Swal.fire({
            title: 'Hapus Data Manhour?',
            html: `Apakah Anda yakin ingin menghapus seluruh data Manhour pada tanggal <strong>${dateStr}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            fetch(`${MH_BASE_URL}/date/${dateStr}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    applyManhourFilters();
                } else {
                    Swal.fire('Error', data.message || 'Gagal menghapus data.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat menghapus data.', 'error'));
        });
    }

    // =========================================================================
    // 7. MANAJEMEN PERUSAHAAN (MODAL & ACTIONS)
    // =========================================================================
    function openManageCompaniesModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManageCompanies')).show();
    }

    function submitNewCompany(e) {
        e.preventDefault();
        const name = document.getElementById('newCompanyName')?.value.trim();
        const desc = document.getElementById('newCompanyDesc')?.value.trim();

        if (!name) return;

        const btn = document.getElementById('btnSubmitNewCompany');
        btn.disabled = true;

        fetch(`${MH_BASE_URL}/units`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, description: desc })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Perusahaan baru berhasil ditambahkan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message || 'Gagal menambahkan perusahaan.', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            Swal.fire('Error', 'Gagal memproses penambahan perusahaan.', 'error');
        });
    }

    function saveCompanyEdit(id) {
        const name = document.getElementById(`compName_${id}`)?.value.trim();
        const desc = document.getElementById(`compDesc_${id}`)?.value.trim();

        if (!name) {
            Swal.fire('Peringatan', 'Nama perusahaan tidak boleh kosong.', 'warning');
            return;
        }

        fetch(`${MH_BASE_URL}/units/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, description: desc })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan',
                    text: 'Perubahan data perusahaan berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Gagal memperbarui nama perusahaan.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'));
    }

    function toggleCompanyActive(id) {
        fetch(`${MH_BASE_URL}/units/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Diperbarui',
                    text: 'Status perusahaan berhasil diubah.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message || 'Gagal mengubah status perusahaan.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'));
    }

    // =========================================================================
    // INITIALIZATION
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        initComboChart(initialTrend);
        calcModalLiveTotal();

        const curPeriodType = document.getElementById('filterPeriodType')?.value || 'all';
        togglePeriodInputMode(curPeriodType);
    });
</script>
@endpush

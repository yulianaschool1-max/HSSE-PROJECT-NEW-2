@extends('layouts.app', [
    'title' => 'Gas Monitoring - Pemantauan Gas Well Pad',
    'activeModule' => 'gas-detector'
])

@push('styles')
<style>
    /* =========================================================================
       1. HEADER LAYOUT (Presisi sesuai Screenshot User)
       ========================================================================= */
    .gas-header-unified {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Baris Atas: Brand Judul di Kiri, Kartu Tanggal di Kanan */
    .gas-header-top-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    /* Kiri: Brand */
    .gas-brand-area {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-shrink: 0;
    }
    .gas-brand-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #e6f7f0;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.12);
        flex-shrink: 0;
    }
    .gas-brand-title {
        font-size: 24px;
        font-weight: 800;
        color: #0d2538;
        letter-spacing: -0.4px;
        line-height: 1.15;
        margin: 0;
        white-space: nowrap;
    }

    /* Kanan: Kartu Tanggal Data yang Ditampilkan */
    .gas-header-date-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }
    .gas-pill-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }
    .gas-pill-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        line-height: 1.2;
        margin-bottom: 3px;
        white-space: nowrap;
    }
    .gas-pill-value-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }
    .gas-pill-value {
        font-size: 16px;
        font-weight: 800;
        color: #0d2538;
        line-height: 1.2;
        white-space: nowrap;
    }
    .gas-pill-sub {
        font-size: 10.5px;
        color: #94a3b8;
        line-height: 1.2;
        margin-top: 3px;
        white-space: nowrap;
    }
    .badge-latest-pill {
        background: #2563eb;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Baris Bawah: Dua Tombol Aksi (+ Tambah Data Gas Monitoring & Kelola Well Pad) */
    .gas-header-buttons-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .btn-action-emerald {
        background: #059669;
        color: #ffffff !important;
        border: none;
        border-radius: 9px;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.22);
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-action-emerald:hover {
        background: #047857;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
    }
    .btn-action-white {
        background: #ffffff;
        color: #1e293b !important;
        border: 1.5px solid #cbd5e1;
        border-radius: 9px;
        padding: 9.5px 18px;
        font-weight: 600;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-action-white:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a !important;
        transform: translateY(-1px);
    }
        padding: 9px 16px;
        font-weight: 600;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-action-white:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a !important;
        transform: translateY(-1px);
    }

    @media (max-width: 1100px) {
        .gas-center-info-grid {
            order: 3;
            width: 100%;
            justify-content: flex-start;
        }
        .gas-actions-dock {
            order: 2;
        }
    }

    /* =========================================================================
       2. FILTER BAR (Filter Periode, Parameter Periode Dinamis, Well Pad, Indikator)
       ========================================================================= */
    .gas-filter-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 22px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
    }
    .gas-filter-grid {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }
    .filter-col-period-type {
        flex: 0 0 170px;
    }
    .filter-col-period-input {
        flex: 1 1 320px;
        min-width: 290px;
    }
    .filter-col-well-pad {
        flex: 0 0 180px;
    }
    .filter-col-indicator {
        flex: 0 0 180px;
    }
    .filter-col-actions {
        flex: 0 0 auto;
        margin-left: auto;
    }
    .filter-input-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .gas-filter-panel .form-control,
    .gas-filter-panel .form-select {
        border-color: #cbd5e1;
        font-size: 12.5px;
        font-weight: 500;
        color: #1e293b;
        border-radius: 8px;
        box-shadow: none;
        padding: 7px 11px;
        width: 100%;
        box-sizing: border-box;
    }
    .gas-filter-panel .form-control:focus,
    .gas-filter-panel .form-select:focus {
        border-color: #059669;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
    }
    .date-range-split {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }
    .date-range-split .form-control {
        flex: 1 1 0;
        min-width: 0;
    }
    .date-range-split .date-separator {
        color: #94a3b8;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
        user-select: none;
        padding: 0 2px;
    }
    .gas-active-period-indicator {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: #64748b;
        flex-wrap: wrap;
        gap: 8px;
    }
    .gas-active-period-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        color: #059669;
        font-weight: 700;
        padding: 3.5px 12px;
        border-radius: 20px;
        border: 1px solid #a7f3d0;
    }
    @media (max-width: 991px) {
        .filter-col-period-type,
        .filter-col-period-input,
        .filter-col-well-pad,
        .filter-col-indicator,
        .filter-col-actions {
            flex: 1 1 calc(50% - 16px);
        }
        .filter-col-actions {
            margin-left: 0;
        }
    }
    @media (max-width: 600px) {
        .filter-col-period-type,
        .filter-col-period-input,
        .filter-col-well-pad,
        .filter-col-indicator,
        .filter-col-actions {
            flex: 1 1 100%;
            min-width: 100%;
        }
    }

    /* =========================================================================
       3. 5 PARAMETER SUMMARY CARDS
       ========================================================================= */
    .param-card-v2 {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .param-card-v2:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
    }
    .param-card-v2-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .param-v2-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }
    .param-v2-title {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }
    .param-v2-value-wrap {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 6px;
    }
    .param-v2-value {
        font-size: 26px;
        font-weight: 800;
        color: #0f2d42;
        line-height: 1;
    }
    .param-v2-threshold {
        font-size: 11px;
        color: #64748b;
        font-weight: 500;
    }

    /* Badges */
    .badge-sesuai {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        font-size: 10.5px;
        padding: 2.5px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
    }
    .badge-tidaksesuai {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-weight: 700;
        font-size: 10.5px;
        padding: 2.5px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
    }

    /* =========================================================================
       4. CHART & STATISTIK SECTION
       ========================================================================= */
    .gas-chart-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .chart-tabs-bar {
        display: flex;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 12px;
        gap: 6px;
        overflow-x: auto;
    }
    .chart-tab-pill {
        background: transparent;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .chart-tab-pill:hover {
        background: #edf2f7;
        color: #1e293b;
    }
    .chart-tab-pill.active {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);
    }
    .chart-tab-pill.active i, .chart-tab-pill.active span {
        color: #ffffff !important;
    }

    .gas-stat-sidebar-v2 {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-row-v2 {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .stat-row-v2:last-child {
        border-bottom: none;
    }
    .stat-icon-v2 {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* =========================================================================
       5. TABEL RIWAYAT & EXPORT ACTIONS
       ========================================================================= */
    .gas-table-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        padding: 20px;
        margin-bottom: 30px;
    }
    .table-gas thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        border-top: 1px solid #edf2f7;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 11px 12px;
        vertical-align: middle;
    }
    .table-gas tbody td {
        font-size: 12.5px;
        padding: 11px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-gas tbody tr:hover {
        background-color: #f8fafc;
    }

    .btn-export-pdf {
        background-color: #dc2626;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-export-pdf:hover {
        background-color: #b91c1c;
        color: #ffffff;
    }
    .btn-export-excel {
        background-color: #059669;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-export-excel:hover {
        background-color: #047857;
        color: #ffffff;
    }

    .btn-tbl-action {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.15s;
        font-size: 13px;
        cursor: pointer;
    }
    .btn-tbl-view { background-color: #e0f2fe; color: #0284c7; }
    .btn-tbl-view:hover { background-color: #bae6fd; color: #0369a1; }
    .btn-tbl-edit { background-color: #fef3c7; color: #d97706; }
    .btn-tbl-edit:hover { background-color: #fde68a; color: #b45309; }
    .btn-tbl-del { background-color: #fee2e2; color: #dc2626; }
    .btn-tbl-del:hover { background-color: #fecaca; color: #b91c1c; }
    /* =========================================================================
       PAGINATION FIX
       ========================================================================= */
    .pagination {
        margin-bottom: 0;
        display: flex;
        gap: 4px;
    }
    .pagination .page-item .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        transition: all 0.15s ease;
    }
    .pagination .page-item.active .page-link {
        background-color: #059669;
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(5, 150, 105, 0.25);
    }
    .pagination .page-item .page-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .pagination .page-item.disabled .page-link {
        color: #cbd5e1;
        background-color: #f8fafc;
        border-color: #f1f5f9;
    }
    .pagination svg {
        width: 14px;
        height: 14px;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<!-- ===========================================================================
     1. DESAIN HEADER SESUAI GAMBAR REFERENSI USER
     =========================================================================== -->
<div class="gas-header-unified">
    <!-- Baris Atas: Judul Gas Monitoring di Kiri, Kartu Tanggal Data di Kanan -->
    <div class="gas-header-top-row">
        <!-- Kiri: Brand Icon & Judul -->
        <div class="gas-brand-area">
            <div class="gas-brand-icon">
                <i class="bi bi-broadcast-pin"></i>
            </div>
            <h1 class="gas-brand-title">Gas Monitoring</h1>
        </div>

        <!-- Kanan: Kartu Tanggal Data yang Ditampilkan -->
        <div class="gas-header-date-card">
            <div class="gas-pill-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-calendar3"></i>
            </div>
            <div>
                <div class="gas-pill-label">Tanggal Data yang Ditampilkan</div>
                <div class="gas-pill-value-row">
                    <span class="gas-pill-value" id="headerPeriodLabel">{{ $formattedPeriodLabel }}</span>
                    @if($isLatestAvailable)
                        <span class="badge-latest-pill">Data Terakhir Tersedia</span>
                    @endif
                </div>
                <div class="gas-pill-sub">Terakhir diinput: {{ $lastInputTimeFormatted }}</div>
            </div>
        </div>
    </div>

    <!-- Baris Bawah: Tombol + Tambah Data Gas Monitoring & Kelola Well Pad -->
    @adminManagement
        <div class="gas-header-buttons-row">
            <button type="button" class="btn-action-emerald" data-bs-toggle="modal" data-bs-target="#modalBatchCreateGas">
                <i class="bi bi-plus-lg"></i> Tambah Data Gas Monitoring
            </button>
            <button type="button" class="btn-action-white" onclick="openWellPadModal()">
                <i class="bi bi-gear"></i> Kelola Well Pad
            </button>
        </div>
    @endadminManagement
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 py-2 px-3 small" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3 py-2 px-3 small" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- ===========================================================================
     2. FILTER PERIODE DARI TANGGAL AWAL SAMPAI TANGGAL AKHIR (DENGAN 6 TIPE PERIODE)
     =========================================================================== -->
<form method="GET" action="{{ route('gas-detector.index') }}" id="gasFilterForm" onsubmit="return validateDateRange()">
    <div class="gas-filter-panel">
        <div class="gas-filter-grid">
            <!-- Pilihan Tipe Periode -->
            <div class="filter-col-period-type">
                <label class="filter-input-label">
                    <i class="bi bi-funnel text-success"></i> Tipe Periode
                </label>
                <select name="period_type" id="filterPeriodType" class="form-select form-select-sm" onchange="toggleGasPeriodMode(this.value)">
                    <option value="all" {{ ($periodType ?? 'daily') === 'all' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="daily" {{ ($periodType ?? 'daily') === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ ($periodType ?? 'daily') === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ ($periodType ?? 'daily') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ ($periodType ?? 'daily') === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom" {{ ($periodType ?? 'daily') === 'custom' ? 'selected' : '' }}>Rentang Tanggal</option>
                </select>
            </div>

            <!-- Input Dinamis Sesuai Tipe Periode -->
            <div id="gasPeriodInputWrapper" class="filter-col-period-input">
                <label class="filter-input-label" id="gasPeriodInputLabel">
                    <i class="bi bi-calendar-event text-success"></i> Parameter Periode
                </label>

                <!-- 1. Semua Periode -->
                <div id="gasPeriod_all" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'all' ? '' : 'display: none;' }}">
                    <input type="text" class="form-control form-control-sm" value="Seluruh Rekaman Gas" disabled readonly style="background: #f8fafc; color: #64748b;">
                </div>

                <!-- 2. Harian -->
                <div id="gasPeriod_daily" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'daily' ? '' : 'display: none;' }}">
                    <input type="date" name="filter_date" id="filterDateDaily" class="form-control form-control-sm" value="{{ request('filter_date', $startDate ?: date('Y-m-d')) }}">
                </div>

                <!-- 3. Mingguan -->
                <div id="gasPeriod_weekly" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'weekly' ? '' : 'display: none;' }}">
                    <input type="date" name="filter_week_date" id="filterDateWeekly" class="form-control form-control-sm" value="{{ request('filter_week_date', $startDate ?: date('Y-m-d')) }}" title="Pilih sembarang tanggal dalam minggu">
                </div>

                <!-- 4. Bulanan -->
                <div id="gasPeriod_monthly" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'monthly' ? '' : 'display: none;' }}">
                    <input type="month" name="filter_month" id="filterMonthVal" class="form-control form-control-sm" value="{{ request('filter_month', $startDate ? substr($startDate, 0, 7) : date('Y-m')) }}">
                </div>

                <!-- 5. Tahunan -->
                <div id="gasPeriod_yearly" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'yearly' ? '' : 'display: none;' }}">
                    <input type="number" name="filter_year" id="filterYearVal" class="form-control form-control-sm" min="2000" max="2099" step="1" value="{{ request('filter_year', $startDate ? substr($startDate, 0, 4) : date('Y')) }}">
                </div>

                <!-- 6. Rentang Tanggal -->
                <div id="gasPeriod_custom" class="gas-period-variant" style="{{ ($periodType ?? 'daily') === 'custom' ? '' : 'display: none;' }}">
                    <div class="date-range-split">
                        <input type="date" name="start_date" id="filterStartDate" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="Mulai" title="Tanggal Mulai">
                        <span class="date-separator">–</span>
                        <input type="date" name="end_date" id="filterEndDate" class="form-control form-control-sm" value="{{ $endDate }}" placeholder="Selesai" title="Tanggal Selesai">
                    </div>
                </div>
            </div>

            <!-- Filter Well Pad -->
            <div class="filter-col-well-pad">
                <label class="filter-input-label">
                    <i class="bi bi-geo-alt text-primary"></i> Well Pad
                </label>
                <select name="well_pad" id="filterWellPad" class="form-select form-select-sm">
                    <option value="all">Semua Well Pad</option>
                    @foreach($activeWellPads as $pad)
                        <option value="{{ $pad->name }}" {{ $wellPadFilter === $pad->name ? 'selected' : '' }}>
                            {{ $pad->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Indikator Parameter -->
            <div class="filter-col-indicator">
                <label class="filter-input-label">
                    <i class="bi bi-sliders text-warning"></i> Indikator
                </label>
                <select name="indicator" id="filterIndicator" class="form-select form-select-sm">
                    <option value="all" {{ $parameterFilter === 'all' ? 'selected' : '' }}>Semua Parameter</option>
                    <option value="co" {{ $parameterFilter === 'co' ? 'selected' : '' }}>CO (ppm)</option>
                    <option value="o2" {{ $parameterFilter === 'o2' ? 'selected' : '' }}>O₂ (%)</option>
                    <option value="h2s" {{ $parameterFilter === 'h2s' ? 'selected' : '' }}>H₂S (ppm)</option>
                    <option value="lel" {{ $parameterFilter === 'lel' ? 'selected' : '' }}>LEL (%LEL)</option>
                    <option value="pressure" {{ $parameterFilter === 'pressure' ? 'selected' : '' }}>Pressure (barg)</option>
                </select>
            </div>

            <!-- Tombol Terapkan & Reset -->
            <div class="filter-col-actions">
                <label class="filter-input-label" style="visibility: hidden;">Aksi</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold d-inline-flex align-items-center gap-1" style="background-color: #059669; border: none; white-space: nowrap;">
                        <i class="bi bi-funnel"></i> Terapkan
                    </button>
                    <a href="{{ route('gas-detector.index', ['period_type' => 'all']) }}" class="btn btn-sm btn-light border px-3 fw-semibold text-secondary d-inline-flex align-items-center gap-1" style="white-space: nowrap;">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>
        </div>

        <!-- Indikator Periode Aktif -->
        <div class="gas-active-period-indicator">
            <div>
                <i class="bi bi-info-circle text-primary me-1"></i>
                Data yang ditampilkan difilter berdasarkan:
                <span class="gas-active-period-badge" id="gasActivePeriodDisplay">
                    <i class="bi bi-calendar2-check"></i> {{ $formattedPeriodLabel }}
                </span>
            </div>
            <div class="text-muted small">
                Terakhir diinput: <strong id="gasIndicatorLastInput">{{ $lastInputTimeFormatted }}</strong>
            </div>
        </div>
    </div>
</form>

<!-- ===========================================================================
     3. 5 KARTU RINGKASAN PARAMETER
     =========================================================================== -->
<div class="row g-3 mb-4">
    <!-- CO -->
    <div class="col">
        <div class="param-card-v2">
            <div class="param-card-v2-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="param-v2-icon" style="background: #f0fdf4; color: #16a34a;">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <span class="param-v2-title">CO (ppm)</span>
                </div>
                <span class="{{ $summaryCards['co']['status'] === 'Sesuai' ? 'badge-sesuai' : ($summaryCards['co']['status'] === 'Tidak Sesuai' ? 'badge-tidaksesuai' : 'badge bg-light text-muted border') }}">
                    {{ $summaryCards['co']['status'] }}
                </span>
            </div>
            <div class="param-v2-value-wrap">
                <span class="param-v2-value">{{ $summaryCards['co']['val'] }}</span>
            </div>
            <div class="param-v2-threshold">{{ $summaryCards['co']['threshold'] }}</div>
        </div>
    </div>

    <!-- O2 -->
    <div class="col">
        <div class="param-card-v2">
            <div class="param-card-v2-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="param-v2-icon" style="background: #eff6ff; color: #2563eb;">
                        <span class="fw-bold fs-6">O₂</span>
                    </div>
                    <span class="param-v2-title">O₂ (%)</span>
                </div>
                <span class="{{ $summaryCards['o2']['status'] === 'Sesuai' ? 'badge-sesuai' : ($summaryCards['o2']['status'] === 'Tidak Sesuai' ? 'badge-tidaksesuai' : 'badge bg-light text-muted border') }}">
                    {{ $summaryCards['o2']['status'] }}
                </span>
            </div>
            <div class="param-v2-value-wrap">
                <span class="param-v2-value">{{ $summaryCards['o2']['val'] }}</span>
            </div>
            <div class="param-v2-threshold">{{ $summaryCards['o2']['threshold'] }}</div>
        </div>
    </div>

    <!-- H2S -->
    <div class="col">
        <div class="param-card-v2">
            <div class="param-card-v2-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="param-v2-icon" style="background: #fef2f2; color: #ef4444;">
                        <i class="bi bi-radioactive"></i>
                    </div>
                    <span class="param-v2-title">H₂S (ppm)</span>
                </div>
                <span class="{{ $summaryCards['h2s']['status'] === 'Sesuai' ? 'badge-sesuai' : ($summaryCards['h2s']['status'] === 'Tidak Sesuai' ? 'badge-tidaksesuai' : 'badge bg-light text-muted border') }}">
                    {{ $summaryCards['h2s']['status'] }}
                </span>
            </div>
            <div class="param-v2-value-wrap">
                <span class="param-v2-value">{{ $summaryCards['h2s']['val'] }}</span>
            </div>
            <div class="param-v2-threshold">{{ $summaryCards['h2s']['threshold'] }}</div>
        </div>
    </div>

    <!-- LEL -->
    <div class="col">
        <div class="param-card-v2">
            <div class="param-card-v2-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="param-v2-icon" style="background: #fffbeb; color: #d97706;">
                        <i class="bi bi-fire"></i>
                    </div>
                    <span class="param-v2-title">LEL (%LEL)</span>
                </div>
                <span class="{{ $summaryCards['lel']['status'] === 'Sesuai' ? 'badge-sesuai' : ($summaryCards['lel']['status'] === 'Tidak Sesuai' ? 'badge-tidaksesuai' : 'badge bg-light text-muted border') }}">
                    {{ $summaryCards['lel']['status'] }}
                </span>
            </div>
            <div class="param-v2-value-wrap">
                <span class="param-v2-value">{{ $summaryCards['lel']['val'] }}</span>
            </div>
            <div class="param-v2-threshold">{{ $summaryCards['lel']['threshold'] }}</div>
        </div>
    </div>

    <!-- Pressure -->
    <div class="col">
        <div class="param-card-v2">
            <div class="param-card-v2-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="param-v2-icon" style="background: #faf5ff; color: #9333ea;">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span class="param-v2-title">Pressure (barg)</span>
                </div>
                <span class="{{ $summaryCards['pressure']['status'] === 'Sesuai' ? 'badge-sesuai' : ($summaryCards['pressure']['status'] === 'Tidak Sesuai' ? 'badge-tidaksesuai' : 'badge bg-light text-muted border') }}">
                    {{ $summaryCards['pressure']['status'] }}
                </span>
            </div>
            <div class="param-v2-value-wrap">
                <span class="param-v2-value">{{ $summaryCards['pressure']['val'] }}</span>
            </div>
            <div class="param-v2-threshold">{{ $summaryCards['pressure']['threshold'] }}</div>
        </div>
    </div>
</div>

<!-- ===========================================================================
     4. GRAFIK BATANG DENGAN THRESHOLD & STATISTIK RINGKAS
     =========================================================================== -->
<div class="row g-3 mb-4">
    <!-- Bagian Kiri: Grafik Batang -->
    <div class="col-xl-8 col-lg-7">
        <div class="gas-chart-box">
            <!-- 5 Parameter Tabs -->
            <div class="chart-tabs-bar">
                <button type="button" class="chart-tab-pill active" onclick="switchChartParam('co')" id="btnTabCo">
                    <i class="bi bi-cloud text-success"></i> CO (ppm)
                </button>
                <button type="button" class="chart-tab-pill" onclick="switchChartParam('o2')" id="btnTabO2">
                    <span class="fw-bold text-primary">O₂</span> O₂ (%)
                </button>
                <button type="button" class="chart-tab-pill" onclick="switchChartParam('h2s')" id="btnTabH2s">
                    <i class="bi bi-radioactive text-danger"></i> H₂S (ppm)
                </button>
                <button type="button" class="chart-tab-pill" onclick="switchChartParam('lel')" id="btnTabLel">
                    <i class="bi bi-fire text-warning"></i> LEL (%LEL)
                </button>
                <button type="button" class="chart-tab-pill" onclick="switchChartParam('pressure')" id="btnTabPressure">
                    <i class="bi bi-speedometer2" style="color: #9333ea;"></i> Pressure (barg)
                </button>
            </div>

            <!-- Konten Grafik -->
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0" id="chartHeaderTitle" style="font-size: 14px;">
                        Grafik CO per Well Pad — {{ $formattedPeriodLabel }}
                    </h6>
                    <div id="chartThresholdBadge" class="text-danger fw-bold small">
                        Threshold 25 ppm
                    </div>
                </div>

                <div style="height: 280px; position: relative;">
                    <canvas id="gasBarChartCanvas"></canvas>
                    <div id="noDataAlert" class="d-none position-absolute top-50 start-50 translate-middle text-center text-muted">
                        <i class="bi bi-folder-x fs-1 d-block mb-1"></i>
                        Tidak ada data pengukuran pada periode yang dipilih.
                    </div>
                </div>

                <!-- Legenda Status di Bawah Grafik -->
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 mt-3 pt-2 border-top small text-muted">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <span id="legendSesuai">Sesuai (≤ 25 ppm)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                        <span>Mendekati Batas</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                        <span id="legendTidakSesuai">Tidak Sesuai (> 25 ppm)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="width: 20px; height: 2px; border-top: 2px dashed #ef4444; display: inline-block;"></span>
                        <span id="legendThreshold">Threshold (25 ppm)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Kanan: Statistik Ringkas Parameter -->
    <div class="col-xl-4 col-lg-5">
        <div class="gas-stat-sidebar-v2">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0" id="statHeaderTitle" style="font-size: 13.5px;">
                    Statistik CO — {{ $formattedPeriodLabel }}
                </h6>
                <span class="badge bg-light text-primary border" id="statUnitPill">ppm</span>
            </div>

            <!-- List Statistik -->
            <div class="my-auto">
                <!-- Rata-rata -->
                <div class="stat-row-v2">
                    <div class="stat-icon-v2" style="background: #eff6ff; color: #2563eb;">
                        &Sigma;
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted" style="font-size: 11px;">Rata-rata</div>
                        <div class="fw-bold text-dark fs-6" id="statAvgVal">
                            {{ $statisticsData['co']['avg'] !== '-' ? $statisticsData['co']['avg'] . ' ppm' : '-' }}
                        </div>
                    </div>
                </div>

                <!-- Maksimum -->
                <div class="stat-row-v2">
                    <div class="stat-icon-v2" style="background: #fef2f2; color: #ef4444;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted" style="font-size: 11px;">Maksimum</div>
                            <div class="fw-bold text-dark fs-6" id="statMaxVal">
                                {{ $statisticsData['co']['max_val'] !== '-' ? $statisticsData['co']['max_val'] . ' ppm' : '-' }}
                            </div>
                        </div>
                        <div class="text-muted small fw-medium" id="statMaxPad">
                            {{ $statisticsData['co']['max_pad'] }}
                        </div>
                    </div>
                </div>

                <!-- Minimum -->
                <div class="stat-row-v2">
                    <div class="stat-icon-v2" style="background: #f0fdf4; color: #16a34a;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted" style="font-size: 11px;">Minimum</div>
                            <div class="fw-bold text-dark fs-6" id="statMinVal">
                                {{ $statisticsData['co']['min_val'] !== '-' ? $statisticsData['co']['min_val'] . ' ppm' : '-' }}
                            </div>
                        </div>
                        <div class="text-muted small fw-medium" id="statMinPad">
                            {{ $statisticsData['co']['min_pad'] }}
                        </div>
                    </div>
                </div>

                <!-- Jumlah Data -->
                <div class="stat-row-v2">
                    <div class="stat-icon-v2" style="background: #f8fafc; color: #0284c7; border: 1px solid #e2e8f0;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted" style="font-size: 11px;">Jumlah Data</div>
                        <div class="fw-bold text-dark fs-6" id="statCountVal">
                            {{ $statisticsData['co']['count'] }} Well Pad
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Keseluruhan -->
            <div class="p-2 rounded mt-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small fw-semibold text-secondary">Status Keseluruhan</span>
                    <span class="badge {{ $statisticsData['co']['overall_status'] === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}" id="statOverallStatusBadge">
                        {{ $statisticsData['co']['overall_status'] }}
                    </span>
                </div>
                <div class="text-muted" style="font-size: 10px;" id="statOverallDetailNote">
                    {{ $statisticsData['co']['detail_note'] }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===========================================================================
     5. TABEL DATA HASIL PENGUKURAN & TOMBOL EKSPOR LAPORAN
     =========================================================================== -->
<div class="gas-table-box">
    <div class="row align-items-center g-2 mb-3">
        <div class="col-md-5">
            <h5 class="fw-bold text-dark mb-0" style="font-size: 16px;">
                Data Gas Monitoring — {{ $formattedPeriodLabel }}
            </h5>
        </div>
        <div class="col-md-7 text-md-end">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                <!-- Search Box -->
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="liveSearchInput" class="form-control border-start-0" placeholder="Cari well pad / tanggal..." value="{{ $searchQuery }}">
                </div>

                <!-- Export PDF Berdasarkan Filter Aktif -->
                <a href="{{ route('gas-detector.measurements.export', request()->query()) }}" id="btnExportGasPdf" class="btn-export-pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                </a>

                <!-- Export Excel Berdasarkan Filter Aktif -->
                <button type="button" class="btn-export-excel" onclick="exportTableToExcel()">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table class="table table-gas align-middle" id="mainGasMonitoringTable">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">No</th>
                    <th style="width: 105px;">Tanggal</th>
                    <th style="width: 120px;">Well Pad</th>
                    <th class="text-center" style="width: 85px;">CO (ppm)<br><span class="fw-normal text-muted" style="font-size: 10px;">&le; 25</span></th>
                    <th class="text-center" style="width: 85px;">O₂ (%)<br><span class="fw-normal text-muted" style="font-size: 10px;">19.5 - 23.5</span></th>
                    <th class="text-center" style="width: 85px;">H₂S (ppm)<br><span class="fw-normal text-muted" style="font-size: 10px;">&le; 10</span></th>
                    <th class="text-center" style="width: 85px;">LEL (%LEL)<br><span class="fw-normal text-muted" style="font-size: 10px;">&le; 10</span></th>
                    <th class="text-center" style="width: 95px;">Pressure (barg)<br><span class="fw-normal text-muted" style="font-size: 10px;">&le; 20</span></th>
                    <th class="text-center" style="width: 95px;">Status</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width: 105px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tableRecords as $index => $row)
                    @php
                        $coExceeded = ($row->co_level !== null && $row->co_level > 25.0);
                        $o2Exceeded = ($row->o2_level !== null && ($row->o2_level < 19.5 || $row->o2_level > 23.5));
                        $h2sExceeded = ($row->h2s_level !== null && $row->h2s_level > 10.0);
                        $lelExceeded = ($row->lel_level !== null && $row->lel_level > 10.0);
                        $pressExceeded = ($row->pressure !== null && $row->pressure > 20.0);
                        $isRowExceeded = $coExceeded || $o2Exceeded || $h2sExceeded || $lelExceeded || $pressExceeded;
                    @endphp
                    <tr style="{{ $isRowExceeded ? 'background-color: #fef2f2;' : '' }}">
                        <td class="text-center text-muted fw-semibold">
                            {{ $tableRecords->firstItem() + $index }}
                        </td>
                        <td class="text-nowrap text-dark">
                            {{ $row->measurement_date ? $row->measurement_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="fw-bold text-dark">
                            {{ $row->well_pad }}
                        </td>
                        <!-- CO -->
                        <td class="text-center fw-semibold {{ $coExceeded ? 'text-danger bg-danger-subtle' : '' }}">
                            {{ $row->co_level !== null ? number_format($row->co_level, 1) : '-' }}
                        </td>
                        <!-- O2 -->
                        <td class="text-center fw-semibold {{ $o2Exceeded ? 'text-danger bg-danger-subtle' : '' }}">
                            {{ $row->o2_level !== null ? number_format($row->o2_level, 1) : '-' }}
                        </td>
                        <!-- H2S -->
                        <td class="text-center fw-semibold {{ $h2sExceeded ? 'text-danger bg-danger-subtle' : '' }}">
                            {{ $row->h2s_level !== null ? number_format($row->h2s_level, 1) : '-' }}
                        </td>
                        <!-- LEL -->
                        <td class="text-center fw-semibold {{ $lelExceeded ? 'text-danger bg-danger-subtle' : '' }}">
                            {{ $row->lel_level !== null ? number_format($row->lel_level, 1) : '-' }}
                        </td>
                        <!-- Pressure -->
                        <td class="text-center fw-semibold {{ $pressExceeded ? 'text-danger bg-danger-subtle' : '' }}">
                            {{ $row->pressure !== null ? number_format($row->pressure, 1) : '-' }}
                        </td>
                        <!-- Status -->
                        <td class="text-center">
                            <span class="{{ !$isRowExceeded ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                {{ !$isRowExceeded ? 'Sesuai' : 'Tidak Sesuai' }}
                            </span>
                        </td>
                        <!-- Keterangan -->
                        <td class="small text-muted">
                            {{ $row->notes ?: '-' }}
                        </td>
                        <!-- Aksi -->
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn-tbl-action btn-tbl-view" onclick="viewMeasurementDetail({{ $row->id }})" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @adminManagement
                                    <button type="button" class="btn-tbl-action btn-tbl-edit" onclick="editSingleMeasurement({{ $row->id }})" title="Edit Data">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn-tbl-action btn-tbl-del" onclick="deleteMeasurementRecord({{ $row->id }}, '{{ $row->well_pad }}')" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                            Tidak ada data Gas Monitoring pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tableRecords->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="small text-muted">
                Menampilkan {{ $tableRecords->firstItem() }} sampai {{ $tableRecords->lastItem() }} dari {{ $tableRecords->total() }} data
            </div>
            <div>
                {{ $tableRecords->links() }}
            </div>
        </div>
    @endif
</div>


<!-- ===========================================================================
     MODAL 1: FORM TAMBAH DATA GAS MONITORING MASSAL (SESUAI GAMBAR 2)
     =========================================================================== -->
<div class="modal fade" id="modalBatchCreateGas" tabindex="-1" aria-labelledby="modalBatchCreateGasLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: #059669; color: #ffffff; padding: 16px 24px; border: none;">
                <div>
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalBatchCreateGasLabel" style="font-size: 16px;">
                        <i class="bi bi-plus-circle"></i> Tambah Data Gas Monitoring
                    </h5>
                    <div class="small text-white-50" style="font-size: 11.5px;">
                        Masukkan hasil pemantauan parameter gas untuk seluruh Well Pad aktif
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>

            <form id="formBatchCreateGas" onsubmit="submitBatchGasForm(event)">
                <div class="modal-body p-4 bg-white" style="max-height: 75vh; overflow-y: auto;">
                    <!-- Row Input Tanggal & Informasi Massal -->
                    <div class="row g-3 mb-3 pb-3 border-bottom align-items-center">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small mb-1">Tanggal Pengukuran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar3"></i></span>
                                <input type="date" name="measurement_date" id="batchMeasurementDate" class="form-control" value="{{ now()->setTimezone('Asia/Jakarta')->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="p-2 rounded bg-light border d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                <div style="font-size: 11px;">
                                    <strong class="d-block text-dark">Semua Well Pad akan diisi untuk tanggal yang sama</strong>
                                    <span class="text-muted">Isi nilai pengukuran pada setiap Well Pad. Jika tidak ada data, dapat dikosongkan dan beri keterangan.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Judul Form Tabel -->
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">Data Parameter Gas per Well Pad</h6>

                    <!-- Tabel Input Massal 5 Well Pad Aktif Valid -->
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tableBatchInputs" style="font-size: 12px;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 140px;" class="align-middle">Well Pad</th>
                                    <th style="width: 115px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-success fw-bold">
                                            <i class="bi bi-cloud"></i> CO
                                        </div>
                                        <span class="text-muted" style="font-size: 10px;">(ppm)<br>NAB: 25 ppm</span>
                                    </th>
                                    <th style="width: 115px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-primary fw-bold">
                                            <span>O₂</span>
                                        </div>
                                        <span class="text-muted" style="font-size: 10px;">(%)<br>NAB: 19.5 - 23.5 %</span>
                                    </th>
                                    <th style="width: 115px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-danger fw-bold">
                                            <i class="bi bi-radioactive"></i> H₂S
                                        </div>
                                        <span class="text-muted" style="font-size: 10px;">(ppm)<br>NAB: 10 ppm</span>
                                    </th>
                                    <th style="width: 115px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-warning fw-bold">
                                            <i class="bi bi-fire"></i> LEL
                                        </div>
                                        <span class="text-muted" style="font-size: 10px;">(%LEL)<br>NAB: 10 %LEL</span>
                                    </th>
                                    <th style="width: 115px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-purple fw-bold" style="color: #9333ea;">
                                            <i class="bi bi-speedometer2"></i> Pressure
                                        </div>
                                        <span class="text-muted" style="font-size: 10px;">(barg)<br>NAB: 20 barg</span>
                                    </th>
                                    <th>
                                        <div class="d-flex align-items-center gap-1 text-dark fw-bold">
                                            <i class="bi bi-file-text"></i> Keterangan
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeWellPads as $idx => $pad)
                                    <tr>
                                        <td class="fw-bold text-dark bg-light-subtle">
                                            <input type="hidden" name="measurements[{{ $idx }}][well_pad_id]" value="{{ $pad->id }}">
                                            <input type="hidden" name="measurements[{{ $idx }}][well_pad_name]" value="{{ $pad->name }}">
                                            {{ $pad->name }}
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" name="measurements[{{ $idx }}][co_level]" class="form-control form-control-sm text-center" placeholder="Contoh: 12">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="100" name="measurements[{{ $idx }}][o2_level]" class="form-control form-control-sm text-center" placeholder="Contoh: 20.8">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" name="measurements[{{ $idx }}][h2s_level]" class="form-control form-control-sm text-center" placeholder="Contoh: 8">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="100" name="measurements[{{ $idx }}][lel_level]" class="form-control form-control-sm text-center" placeholder="Contoh: 5">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" name="measurements[{{ $idx }}][pressure]" class="form-control form-control-sm text-center" placeholder="Contoh: 18.5">
                                        </td>
                                        <td>
                                            <input type="text" name="measurements[{{ $idx }}][notes]" class="form-control form-control-sm" placeholder="Opsional...">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="resetBatchForm()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border btn-sm px-3" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-1" id="btnSubmitBatchGas" style="background-color: #059669; border: none;">
                            <i class="bi bi-file-earmark-check"></i> Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===========================================================================
     MODAL 2: MANAJEMEN KELOLA WELL PAD (SESUAI GAMBAR 3)
     =========================================================================== -->
<div class="modal fade" id="modalWellPadManagement" tabindex="-1" aria-labelledby="modalWellPadManagementLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: #059669; color: #ffffff; padding: 16px 24px; border: none;">
                <div>
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalWellPadManagementLabel" style="font-size: 16px;">
                        <i class="bi bi-gear-fill"></i> Manajemen Well Pad
                    </h5>
                    <div class="small text-white-50" style="font-size: 11.5px;">
                        Kelola daftar Well Pad yang digunakan pada Gas Monitoring
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                <div class="alert alert-info border-0 d-flex align-items-start gap-2 py-2 px-3 mb-3" style="background-color: #eff6ff; color: #1e40af; border-radius: 10px;">
                    <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                    <div style="font-size: 11.5px;">
                        <strong>Well Pad yang ditambahkan akan muncul pada form input Gas Monitoring, filter, grafik, dan rekapitulasi.</strong><br>
                        Jangan menghapus Well Pad yang sudah memiliki data riwayat. Gunakan status nonaktif jika Well Pad tidak digunakan lagi.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchWellPadInput" class="form-control border-start-0" placeholder="Cari nama well pad..." oninput="filterWellPadList()">
                    </div>
                    <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1" onclick="openAddWellPadForm()" style="background-color: #059669; border: none;">
                        <i class="bi bi-plus-lg"></i> Tambah Well Pad
                    </button>
                </div>

                <div class="collapse mb-3" id="collapseWellPadForm">
                    <div class="card card-body bg-light border p-3" style="border-radius: 10px;">
                        <form id="formWellPadAction" onsubmit="submitWellPadForm(event)">
                            <input type="hidden" id="wpFormId" value="">
                            <h6 class="fw-bold text-dark mb-2" id="wpFormTitle" style="font-size: 13px;">Tambah Well Pad</h6>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1 fw-semibold">Nama Well Pad <span class="text-danger">*</span></label>
                                    <input type="text" id="wpFormName" class="form-control form-control-sm" placeholder="Contoh: Well Pad 11" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-semibold">Kode Well Pad <span class="text-danger">*</span></label>
                                    <input type="text" id="wpFormCode" class="form-control form-control-sm" placeholder="Contoh: WP-11" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-semibold">Status</label>
                                    <select id="wpFormStatus" class="form-select form-select-sm">
                                        <option value="1">Aktif</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-light border" onclick="closeWellPadForm()">Batal</button>
                                <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold" id="btnSaveWp">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tableWellPads">
                        <thead class="bg-light text-center" style="font-size: 11.5px; color: #475569;">
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>Nama Well Pad</th>
                                <th>Kode Well Pad</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 100px;">Jumlah Data</th>
                                <th style="width: 130px;">Tanggal Input Terakhir</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyWellPads" style="font-size: 12px;">
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                    <span id="wpTableCounter">Menampilkan 0 dari 0 Well Pad</span>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ===========================================================================
     MODAL 3: DETAIL SINGLE MEASUREMENT
     =========================================================================== -->
<div class="modal fade" id="modalSingleDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0">Detail Pengukuran Gas</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <table class="table table-sm table-borderless small mb-0">
                    <tr><td class="text-muted" style="width: 130px;">Tanggal</td><td class="fw-bold" id="dtlDate">-</td></tr>
                    <tr><td class="text-muted">Well Pad</td><td class="fw-bold text-dark" id="dtlWellPad">-</td></tr>
                    <tr><td class="text-muted">CO (ppm)</td><td id="dtlCo">-</td></tr>
                    <tr><td class="text-muted">O₂ (%)</td><td id="dtlO2">-</td></tr>
                    <tr><td class="text-muted">H₂S (ppm)</td><td id="dtlH2s">-</td></tr>
                    <tr><td class="text-muted">LEL (%LEL)</td><td id="dtlLel">-</td></tr>
                    <tr><td class="text-muted">Pressure (barg)</td><td id="dtlPress">-</td></tr>
                    <tr><td class="text-muted">Status</td><td id="dtlStatus">-</td></tr>
                    <tr><td class="text-muted">Keterangan</td><td id="dtlNotes">-</td></tr>
                    <tr><td class="text-muted">Petugas Input</td><td id="dtlOfficer">-</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ===========================================================================
     MODAL 4: EDIT SINGLE MEASUREMENT
     =========================================================================== -->
<div class="modal fade" id="modalSingleEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: #059669; color: #ffffff;">
                <h6 class="modal-title fw-bold text-white mb-0">Edit Data Pengukuran Gas</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <form id="formSingleEdit" onsubmit="submitSingleEdit(event)">
                <input type="hidden" id="editMeasurementId" value="">
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Tanggal Pengukuran</label>
                        <input type="date" id="editDate" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Well Pad</label>
                        <input type="text" id="editWellPad" class="form-control form-control-sm bg-light" readonly>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">CO (ppm)</label>
                            <input type="number" step="0.1" min="0" id="editCo" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">O₂ (%)</label>
                            <input type="number" step="0.1" min="0" max="100" id="editO2" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">H₂S (ppm)</label>
                            <input type="number" step="0.1" min="0" id="editH2s" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold mb-1">LEL (%LEL)</label>
                            <input type="number" step="0.1" min="0" max="100" id="editLel" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1">Pressure (barg)</label>
                            <input type="number" step="0.1" min="0" id="editPressure" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Keterangan</label>
                        <textarea id="editNotes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-semibold px-3" style="background-color: #059669; border: none;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartRawData = @json($chartData);
const statsRawData = @json($statisticsData);
const currentStartDate = "{{ $startDate }}";
const currentEndDate = "{{ $endDate }}";
const currentFormattedPeriod = "{{ $formattedPeriodLabel }}";
let activeParamKey = 'co';
let gasBarChart = null;
let wellPadsCache = [];

// Toggler Input Mode Periode Gas Monitoring
function toggleGasPeriodMode(mode) {
    const variants = ['all', 'daily', 'weekly', 'monthly', 'yearly', 'custom'];
    variants.forEach(v => {
        const el = document.getElementById(`gasPeriod_${v}`);
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
    const lbl = document.getElementById('gasPeriodInputLabel');
    if (lbl) {
        lbl.innerHTML = `<i class="bi bi-calendar-event text-success"></i> ${labelMap[mode] || 'Parameter Periode'}`;
    }
}

// Validasi Form Date Range
function validateDateRange() {
    const periodType = document.getElementById('filterPeriodType')?.value || 'daily';
    if (periodType === 'custom') {
        const start = document.getElementById('filterStartDate')?.value;
        const end = document.getElementById('filterEndDate')?.value;

        if (!start || !end) {
            Swal.fire('Validasi Tanggal', 'Harap lengkapi Tanggal Mulai dan Tanggal Akhir.', 'warning');
            return false;
        }

        if (end < start) {
            Swal.fire('Rentang Tidak Valid', 'Tanggal Akhir tidak boleh lebih awal daripada Tanggal Mulai.', 'error');
            return false;
        }
    }
    return true;
}

// Threshold Line Plugin untuk Chart.js
const horizontalThresholdPlugin = {
    id: 'horizontalThreshold',
    afterDraw(chart) {
        const activeKey = chart.config.options.plugins.activeParamKey;
        if (!activeKey) return;

        const ctx = chart.ctx;
        const yAxis = chart.scales.y;
        const xAxis = chart.scales.x;

        if (activeKey === 'o2') {
            [19.5, 23.5].forEach(val => {
                const yPos = yAxis.getPixelForValue(val);
                ctx.save();
                ctx.beginPath();
                ctx.setLineDash([6, 6]);
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 1.5;
                ctx.moveTo(xAxis.left, yPos);
                ctx.lineTo(xAxis.right, yPos);
                ctx.stroke();
                ctx.restore();
            });
        } else {
            const threshMap = { 'co': { val: 25, label: 'Threshold 25 ppm' }, 'h2s': { val: 10, label: 'Threshold 10 ppm' }, 'lel': { val: 10, label: 'Threshold 10 %LEL' }, 'pressure': { val: 20, label: 'Threshold 20 barg' } };
            const threshObj = threshMap[activeKey];
            if (threshObj !== undefined) {
                const yPos = yAxis.getPixelForValue(threshObj.val);
                ctx.save();
                ctx.beginPath();
                ctx.setLineDash([6, 6]);
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 1.5;
                ctx.moveTo(xAxis.left, yPos);
                ctx.lineTo(xAxis.right, yPos);
                ctx.stroke();
                
                // Tampilkan label Threshold merah di atas garis sebelah kanan persis seperti di Gambar Referensi 3
                ctx.fillStyle = '#dc2626';
                ctx.font = 'bold 11px sans-serif';
                ctx.textAlign = 'right';
                ctx.textBaseline = 'bottom';
                ctx.fillText(threshObj.label, xAxis.right, yPos - 4);
                ctx.restore();
            }
        }

        // Gambar Nilai di Atas Setiap Batang (Presisi sesuai Gambar Referensi 3)
        chart.data.datasets.forEach((dataset, i) => {
            const meta = chart.getDatasetMeta(i);
            meta.data.forEach((bar, index) => {
                const val = dataset.data[index];
                if (val !== null && val !== undefined) {
                    ctx.save();
                    ctx.font = 'bold 12px sans-serif';
                    ctx.fillStyle = '#1e293b';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(val, bar.x, bar.y - 6);
                    ctx.restore();
                }
            });
        });
    }
};

Chart.register(horizontalThresholdPlugin);

document.addEventListener('DOMContentLoaded', function() {
    initGasBarChart('co');
    initLiveSearch();
});

// Inisialisasi Grafik Batang
function initGasBarChart(paramKey) {
    activeParamKey = paramKey;
    const canvas = document.getElementById('gasBarChartCanvas');
    if (!canvas) return;

    const dataObj = chartRawData[paramKey] || { data: [], colors: [] };
    const labels = chartRawData.labels || [];
    const noDataAlert = document.getElementById('noDataAlert');

    if (labels.length === 0 || dataObj.data.length === 0) {
        if (noDataAlert) noDataAlert.classList.remove('d-none');
    } else {
        if (noDataAlert) noDataAlert.classList.add('d-none');
    }

    let maxVal = Math.max(...dataObj.data, 10);
    if (paramKey === 'co') maxVal = Math.max(maxVal, 30);
    if (paramKey === 'o2') maxVal = 25;
    if (paramKey === 'h2s') maxVal = Math.max(maxVal, 15);
    if (paramKey === 'lel') maxVal = Math.max(maxVal, 15);
    if (paramKey === 'pressure') maxVal = Math.max(maxVal, 25);

    if (gasBarChart) {
        gasBarChart.destroy();
    }

    const ctx = canvas.getContext('2d');
    gasBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: dataObj.data,
                backgroundColor: dataObj.colors,
                borderRadius: 4,
                barThickness: 45,
                maxBarThickness: 60,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 25,
                    right: 15,
                    left: 5,
                    bottom: 0
                }
            },
            plugins: {
                legend: { display: false },
                activeParamKey: paramKey,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` Rata-rata: ${context.raw} ${dataObj.unit || ''}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maxVal,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11, family: 'sans-serif' },
                        color: '#64748b'
                    },
                    title: {
                        display: true,
                        text: `${paramKey.toUpperCase()} (${dataObj.unit || ''})`,
                        font: { size: 11, weight: 'bold' },
                        color: '#475569'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11.5, weight: 'bold' },
                        color: '#1e293b'
                    }
                }
            }
        }
    });

    updateChartLabelsAndSidebar(paramKey);
}

function switchChartParam(paramKey) {
    document.querySelectorAll('.chart-tab-pill').forEach(btn => btn.classList.remove('active'));
    const btnMap = {
        'co': 'btnTabCo',
        'o2': 'btnTabO2',
        'h2s': 'btnTabH2s',
        'lel': 'btnTabLel',
        'pressure': 'btnTabPressure',
    };
    if (btnMap[paramKey]) {
        document.getElementById(btnMap[paramKey]).classList.add('active');
    }

    initGasBarChart(paramKey);
}

function updateChartLabelsAndSidebar(paramKey) {
    const titlePeriod = currentFormattedPeriod;
    const headerTitle = document.getElementById('chartHeaderTitle');
    const threshBadge = document.getElementById('chartThresholdBadge');

    const legSesuai = document.getElementById('legendSesuai');
    const legTidakSesuai = document.getElementById('legendTidakSesuai');
    const legThreshold = document.getElementById('legendThreshold');

    const statHeader = document.getElementById('statHeaderTitle');
    const statUnit = document.getElementById('statUnitPill');
    const statAvg = document.getElementById('statAvgVal');
    const statMax = document.getElementById('statMaxVal');
    const statMaxPad = document.getElementById('statMaxPad');
    const statMin = document.getElementById('statMinVal');
    const statMinPad = document.getElementById('statMinPad');
    const statCount = document.getElementById('statCountVal');
    const statStatus = document.getElementById('statOverallStatusBadge');
    const statDetail = document.getElementById('statOverallDetailNote');

    const s = statsRawData[paramKey] || {};

    if (paramKey === 'co') {
        headerTitle.textContent = `Grafik CO per Well Pad — ${titlePeriod}`;
        threshBadge.textContent = 'Threshold 25 ppm';
        legSesuai.textContent = 'Sesuai (≤ 25 ppm)';
        legTidakSesuai.textContent = 'Tidak Sesuai (> 25 ppm)';
        legThreshold.textContent = 'Threshold (25 ppm)';
        statHeader.textContent = `Statistik CO — ${titlePeriod}`;
        statUnit.textContent = 'ppm';
        statAvg.textContent = s.avg !== '-' ? `${s.avg} ppm` : '-';
        statMax.textContent = s.max_val !== '-' ? `${s.max_val} ppm` : '-';
        statMin.textContent = s.min_val !== '-' ? `${s.min_val} ppm` : '-';
    } else if (paramKey === 'o2') {
        headerTitle.textContent = `Grafik O₂ per Well Pad — ${titlePeriod}`;
        threshBadge.textContent = 'Threshold 19.5 – 23.5 %';
        legSesuai.textContent = 'Sesuai (19.5 - 23.5 %)';
        legTidakSesuai.textContent = 'Tidak Sesuai (< 19.5 atau > 23.5 %)';
        legThreshold.textContent = 'Threshold (19.5 – 23.5 %)';
        statHeader.textContent = `Statistik O₂ — ${titlePeriod}`;
        statUnit.textContent = '%';
        statAvg.textContent = s.avg !== '-' ? `${s.avg} %` : '-';
        statMax.textContent = s.max_val !== '-' ? `${s.max_val} %` : '-';
        statMin.textContent = s.min_val !== '-' ? `${s.min_val} %` : '-';
    } else if (paramKey === 'h2s') {
        headerTitle.textContent = `Grafik H₂S per Well Pad — ${titlePeriod}`;
        threshBadge.textContent = 'Threshold 10 ppm';
        legSesuai.textContent = 'Sesuai (≤ 10 ppm)';
        legTidakSesuai.textContent = 'Tidak Sesuai (> 10 ppm)';
        legThreshold.textContent = 'Threshold (10 ppm)';
        statHeader.textContent = `Statistik H₂S — ${titlePeriod}`;
        statUnit.textContent = 'ppm';
        statAvg.textContent = s.avg !== '-' ? `${s.avg} ppm` : '-';
        statMax.textContent = s.max_val !== '-' ? `${s.max_val} ppm` : '-';
        statMin.textContent = s.min_val !== '-' ? `${s.min_val} ppm` : '-';
    } else if (paramKey === 'lel') {
        headerTitle.textContent = `Grafik LEL per Well Pad — ${titlePeriod}`;
        threshBadge.textContent = 'Threshold 10 %LEL';
        legSesuai.textContent = 'Sesuai (≤ 10 %LEL)';
        legTidakSesuai.textContent = 'Tidak Sesuai (> 10 %LEL)';
        legThreshold.textContent = 'Threshold (10 %LEL)';
        statHeader.textContent = `Statistik LEL — ${titlePeriod}`;
        statUnit.textContent = '%LEL';
        statAvg.textContent = s.avg !== '-' ? `${s.avg} %LEL` : '-';
        statMax.textContent = s.max_val !== '-' ? `${s.max_val} %LEL` : '-';
        statMin.textContent = s.min_val !== '-' ? `${s.min_val} %LEL` : '-';
    } else if (paramKey === 'pressure') {
        headerTitle.textContent = `Grafik Pressure per Well Pad — ${titlePeriod}`;
        threshBadge.textContent = 'Threshold 20 barg';
        legSesuai.textContent = 'Sesuai (≤ 20 barg)';
        legTidakSesuai.textContent = 'Tidak Sesuai (> 20 barg)';
        legThreshold.textContent = 'Threshold (20 barg)';
        statHeader.textContent = `Statistik Pressure — ${titlePeriod}`;
        statUnit.textContent = 'barg';
        statAvg.textContent = s.avg !== '-' ? `${s.avg} barg` : '-';
        statMax.textContent = s.max_val !== '-' ? `${s.max_val} barg` : '-';
        statMin.textContent = s.min_val !== '-' ? `${s.min_val} barg` : '-';
    }

    statMaxPad.textContent = s.max_pad || '';
    statMinPad.textContent = s.min_pad || '';
    statCount.textContent = `${s.count || 0} Well Pad`;

    if (s.overall_status === 'Sesuai') {
        statStatus.className = 'badge badge-sesuai';
        statStatus.textContent = 'Sesuai';
    } else if (s.overall_status === 'Tidak Sesuai') {
        statStatus.className = 'badge badge-tidaksesuai';
        statStatus.textContent = 'Tidak Sesuai';
    } else {
        statStatus.className = 'badge bg-light text-muted border';
        statStatus.textContent = s.overall_status || 'Belum Ada Data';
    }
    statDetail.textContent = s.detail_note || '-';
}

function initLiveSearch() {
    const input = document.getElementById('liveSearchInput');
    if (!input) return;

    input.addEventListener('keyup', function() {
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('#mainGasMonitoringTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

function exportTableToExcel() {
    const table = document.getElementById('mainGasMonitoringTable');
    if (!table) return;

    const wb = XLSX.utils.table_to_book(table, { sheet: "Gas Monitoring" });
    XLSX.writeFile(wb, `Gas_Monitoring_${currentStartDate}_sampai_${currentEndDate}.xlsx`);
}

function resetBatchForm() {
    const form = document.getElementById('formBatchCreateGas');
    if (form) form.reset();
}

async function submitBatchGasForm(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitBatchGas');
    btn.disabled = true;

    const form = document.getElementById('formBatchCreateGas');
    const formData = new FormData(form);

    const payload = {
        measurement_date: formData.get('measurement_date'),
        measurements: []
    };

    const tableRows = document.querySelectorAll('#tableBatchInputs tbody tr');
    tableRows.forEach((tr, i) => {
        const wpId = tr.querySelector(`input[name="measurements[${i}][well_pad_id]"]`)?.value;
        const wpName = tr.querySelector(`input[name="measurements[${i}][well_pad_name]"]`)?.value;
        const co = tr.querySelector(`input[name="measurements[${i}][co_level]"]`)?.value;
        const o2 = tr.querySelector(`input[name="measurements[${i}][o2_level]"]`)?.value;
        const h2s = tr.querySelector(`input[name="measurements[${i}][h2s_level]"]`)?.value;
        const lel = tr.querySelector(`input[name="measurements[${i}][lel_level]"]`)?.value;
        const press = tr.querySelector(`input[name="measurements[${i}][pressure]"]`)?.value;
        const notes = tr.querySelector(`input[name="measurements[${i}][notes]"]`)?.value;

        payload.measurements.push({
            well_pad_id: wpId,
            well_pad_name: wpName,
            co_level: co !== '' ? co : null,
            o2_level: o2 !== '' ? o2 : null,
            h2s_level: h2s !== '' ? h2s : null,
            lel_level: lel !== '' ? lel : null,
            pressure: press !== '' ? press : null,
            notes: notes || null
        });
    });

    try {
        const res = await fetch("{{ route('gas-detector.measurements.store-batch') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const body = await res.json();
        btn.disabled = false;

        if (res.ok && body.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalBatchCreateGas')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: body.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = `{{ route('gas-detector.index') }}?start_date=${body.startDate}&end_date=${body.endDate}`;
            });
        } else {
            let msg = body.message || 'Gagal menyimpan data.';
            if (body.errors) msg = Object.values(body.errors).flat().join('<br>');
            Swal.fire({ icon: 'error', title: 'Penyimpanan Gagal', html: msg });
        }
    } catch (err) {
        btn.disabled = false;
        Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
    }
}

async function openWellPadModal() {
    await loadWellPads();
    const modalEl = document.getElementById('modalWellPadManagement');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

async function loadWellPads() {
    try {
        const res = await fetch("{{ route('gas-detector.well-pads.data') }}", {
            headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();
        if (json.success) {
            wellPadsCache = json.data;
            renderWellPadsTable(wellPadsCache);
        }
    } catch (err) {
        console.error('Failed to load well pads:', err);
    }
}

function renderWellPadsTable(pads) {
    const tbody = document.getElementById('tbodyWellPads');
    tbody.innerHTML = '';

    if (pads.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada Well Pad yang cocok.</td></tr>';
        document.getElementById('wpTableCounter').textContent = 'Menampilkan 0 dari 0 Well Pad';
        return;
    }

    pads.forEach((wp, idx) => {
        const statusBadge = wp.is_active 
            ? '<span class="badge-sesuai">Aktif</span>'
            : '<span class="badge bg-light text-muted border">Nonaktif</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center text-muted fw-semibold">${idx + 1}</td>
            <td class="fw-bold text-dark">${wp.name}</td>
            <td class="text-center font-monospace">${wp.code}</td>
            <td class="text-center">${statusBadge}</td>
            <td class="text-center fw-semibold">${wp.data_count}</td>
            <td class="text-center text-muted">${wp.last_input_date}</td>
            <td class="text-center">
                <div class="d-inline-flex gap-1">
                    <button type="button" class="btn-tbl-action btn-tbl-edit" onclick="editWellPadItem(${wp.id})" title="Edit Well Pad">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn-tbl-action btn-tbl-del" onclick="deleteWellPadItem(${wp.id}, '${wp.name}', ${wp.data_count})" title="Hapus / Nonaktifkan">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('wpTableCounter').textContent = `Menampilkan ${pads.length} dari ${wellPadsCache.length} Well Pad`;
}

function filterWellPadList() {
    const term = document.getElementById('searchWellPadInput').value.toLowerCase();
    const filtered = wellPadsCache.filter(wp => 
        wp.name.toLowerCase().includes(term) || wp.code.toLowerCase().includes(term)
    );
    renderWellPadsTable(filtered);
}

function openAddWellPadForm() {
    document.getElementById('wpFormId').value = '';
    document.getElementById('wpFormTitle').textContent = 'Tambah Well Pad Baru';
    document.getElementById('wpFormName').value = '';
    document.getElementById('wpFormCode').value = '';
    document.getElementById('wpFormStatus').value = '1';
    
    const collapseEl = document.getElementById('collapseWellPadForm');
    bootstrap.Collapse.getOrCreateInstance(collapseEl).show();
}

function closeWellPadForm() {
    const collapseEl = document.getElementById('collapseWellPadForm');
    bootstrap.Collapse.getOrCreateInstance(collapseEl).hide();
}

function editWellPadItem(id) {
    const wp = wellPadsCache.find(p => p.id === id);
    if (!wp) return;

    document.getElementById('wpFormId').value = wp.id;
    document.getElementById('wpFormTitle').textContent = `Edit Well Pad - ${wp.name}`;
    document.getElementById('wpFormName').value = wp.name;
    document.getElementById('wpFormCode').value = wp.code;
    document.getElementById('wpFormStatus').value = wp.is_active ? '1' : '0';

    const collapseEl = document.getElementById('collapseWellPadForm');
    bootstrap.Collapse.getOrCreateInstance(collapseEl).show();
}

async function submitWellPadForm(e) {
    e.preventDefault();
    const id = document.getElementById('wpFormId').value;
    const isEdit = Boolean(id);
    const url = isEdit ? `/gas-detector/well-pads/${id}` : '/gas-detector/well-pads';
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
        name: document.getElementById('wpFormName').value,
        code: document.getElementById('wpFormCode').value,
        is_active: document.getElementById('wpFormStatus').value === '1',
    };

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.ok && data.success) {
            closeWellPadForm();
            await loadWellPads();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } else {
            let msg = data.message || 'Gagal menyimpan data Well Pad.';
            if (data.errors) msg = Object.values(data.errors).flat().join('<br>');
            Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}

function deleteWellPadItem(id, name, dataCount) {
    let warningText = `Apakah Anda yakin ingin menghapus ${name}?`;
    if (dataCount > 0) {
        warningText = `Well Pad "${name}" memiliki ${dataCount} riwayat data pengukuran gas. Menghapus akan mengubah statusnya menjadi NONAKTIF agar data riwayat masa lalu tetap aman. Lanjutkan?`;
    }

    Swal.fire({
        title: 'Hapus / Nonaktifkan Well Pad?',
        text: warningText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch(`/gas-detector/well-pads/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    await loadWellPads();
                    Swal.fire({
                        icon: 'success',
                        title: data.action === 'deactivated' ? 'Dinonaktifkan!' : 'Terhapus!',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Gagal memproses.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        }
    });
}

async function viewMeasurementDetail(id) {
    try {
        const res = await fetch(`/gas-detector/measurements/${id}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const m = data.data;
            document.getElementById('dtlDate').textContent = m.measurement_date || '-';
            document.getElementById('dtlWellPad').textContent = m.well_pad || '-';
            document.getElementById('dtlCo').textContent = m.co_level !== null ? `${m.co_level} ppm (${m.co_status})` : '-';
            document.getElementById('dtlO2').textContent = m.o2_level !== null ? `${m.o2_level} % (${m.o2_status})` : '-';
            document.getElementById('dtlH2s').textContent = m.h2s_level !== null ? `${m.h2s_level} ppm (${m.h2s_status})` : '-';
            document.getElementById('dtlLel').textContent = m.lel_level !== null ? `${m.lel_level} %LEL (${m.lel_status})` : '-';
            document.getElementById('dtlPress').textContent = m.pressure !== null ? `${m.pressure} barg (${m.pressure_status})` : '-';
            document.getElementById('dtlStatus').innerHTML = m.overall_status === 'Sesuai' ? '<span class="badge-sesuai">Sesuai</span>' : '<span class="badge-tidaksesuai">Tidak Sesuai</span>';
            document.getElementById('dtlNotes').textContent = m.notes || '-';
            document.getElementById('dtlOfficer').textContent = m.recorded_by || '-';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSingleDetail')).show();
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal memuat detail data.', 'error');
    }
}

async function editSingleMeasurement(id) {
    try {
        const res = await fetch(`/gas-detector/measurements/${id}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const m = data.data;
            document.getElementById('editMeasurementId').value = m.id;
            document.getElementById('editDate').value = m.measurement_date;
            document.getElementById('editWellPad').value = m.well_pad;
            document.getElementById('editCo').value = m.co_level;
            document.getElementById('editO2').value = m.o2_level;
            document.getElementById('editH2s').value = m.h2s_level;
            document.getElementById('editLel').value = m.lel_level;
            document.getElementById('editPressure').value = m.pressure;
            document.getElementById('editNotes').value = m.notes || '';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSingleEdit')).show();
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal memuat data pengukuran.', 'error');
    }
}

async function submitSingleEdit(e) {
    e.preventDefault();
    const id = document.getElementById('editMeasurementId').value;
    const payload = {
        measurement_date: document.getElementById('editDate').value,
        well_pad: document.getElementById('editWellPad').value,
        co_level: document.getElementById('editCo').value || null,
        o2_level: document.getElementById('editO2').value || null,
        h2s_level: document.getElementById('editH2s').value || null,
        lel_level: document.getElementById('editLel').value || null,
        pressure: document.getElementById('editPressure').value || null,
        notes: document.getElementById('editNotes').value || null,
    };

    try {
        const res = await fetch(`/gas-detector/measurements/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalSingleEdit')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } else {
            Swal.fire('Error', data.message || 'Gagal menyimpan pembaruan.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}

function deleteMeasurementRecord(id, wellPad) {
    Swal.fire({
        title: 'Hapus Rekaman Pengukuran?',
        text: `Apakah Anda yakin ingin menghapus data pengukuran pada ${wellPad}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch(`/gas-detector/measurements/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Gagal menghapus.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        }
    });
}
// Inisialisasi Tampilan Periode pada Form Load
document.addEventListener('DOMContentLoaded', function () {
    const periodTypeSelect = document.getElementById('filterPeriodType');
    if (periodTypeSelect) {
        toggleGasPeriodMode(periodTypeSelect.value);
    }
});
</script>
@endpush

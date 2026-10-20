@extends('layouts.app', [
    'title' => 'Manpower - Pengelolaan Jumlah Tenaga Kerja',
    'activeModule' => 'workforce'
])

@push('styles')
<style>
    .filter-grid-hsse {
        grid-template-columns: 140px 140px 180px 145px 135px 1fr auto;
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

    .wf-code-badge {
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

    .cat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .cat-gde { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .cat-kodipa { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .cat-utility { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }
    .cat-security { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .cat-contractor { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .cat-kp { background: #fdf2f8; color: #be185d; border-color: #fbcfe8; }
    .cat-default { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }

    .mh-source-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }

    .calc-preview-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%);
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px 16px;
    }

    .detail-grid-wf {
        display: grid;
        grid-template-columns: 155px 12px 1fr;
        gap: 8px 0;
        font-size: 12px;
    }

    .detail-label-wf {
        color: #64748b;
        font-weight: 600;
    }

    .empty-state-wf {
        padding: 44px 20px;
        text-align: center;
    }

    .empty-state-wf-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: grid;
        place-items: center;
        font-size: 26px;
        margin: 0 auto 12px;
    }

    .latest-wf-highlight-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #f8fafc;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 16px;
        border: 1px solid #334155;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    }

    .latest-wf-unit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .latest-wf-unit-box {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 8px;
        padding: 10px 12px;
        transition: all 0.2s ease;
    }

    .latest-wf-unit-box.has-data {
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.35);
    }

    .batch-unit-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
        transition: border-color 0.15s ease;
    }

    .batch-unit-row:hover {
        border-color: #94a3b8;
    }

    @media (max-width: 1200px) {
        .stats-grid-hsse { grid-template-columns: repeat(3, 1fr); }
        .charts-row-hsse { grid-template-columns: 1fr; }
        .filter-grid-hsse { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 768px) {
        .stats-grid-hsse { grid-template-columns: 1fr; }
        .filter-grid-hsse { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <h1 class="page-heading">Manpower <span class="ms-2 fs-6 fw-semibold text-secondary">Pengelolaan Jumlah Tenaga Kerja</span></h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">Data</span>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Manpower</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button type="button" class="btn btn-action-outline" id="btnManageUnits" onclick="openManageUnitsModal()" title="Tambah, Ubah, atau Nonaktifkan Master Unit / Area Operasi">
                <i class="bi bi-building-gear me-1 text-primary"></i> Kelola Unit / Area Operasi
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">{{ isset($activeOperationalUnits) ? $activeOperationalUnits->count() : count($units) }} Aktif</span>
            </button>
        @endadminManagement
        <a href="{{ route('man-hours.index') }}" class="btn btn-action-outline" title="Lihat Hasil Perhitungan Man Hours">
            <i class="bi bi-calendar2-check me-1 text-success"></i> Rekap Man Hours
        </a>
        @adminManagement
            <button type="button" class="btn-action-primary" id="btnTambahWorkforce" onclick="openBatchWorkforceModal()">
                <i class="bi bi-plus-lg"></i> Input Manpower
            </button>
            <button type="button" class="btn btn-action-outline" onclick="openWorkforceModal()" title="Input Satuan Kategori/Unit">
                <i class="bi bi-plus-circle text-secondary"></i> Input Satuan
            </button>
        @endadminManagement
        @canExportPdf
        <a href="{{ route('workforce.export', request()->query()) }}" id="btnExportWorkforce" class="btn-action-outline">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </a>
        @endcanExportPdf
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 small mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- HIGHLIGHT WORKFORCE BERDASARKAN TANGGAL TERBARU -->
<div class="latest-wf-highlight-card" id="latestWorkforceHighlight">
    @if(!empty($latestDateHighlight['has_data']))
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success text-white px-2 py-1" style="font-size: 10.5px;">
                        <i class="bi bi-calendar-check-fill me-1"></i> HIGHLIGHT TANGGAL TERBARU
                    </span>
                    <span class="text-info fw-bold" style="font-size: 13px;" id="highlightLatestDateText">
                        {{ $latestDateHighlight['record_date_long'] }} ({{ $latestDateHighlight['record_date_formatted'] }})
                    </span>
                </div>
                <div class="text-white-50" style="font-size: 11.5px;">
                    Menampilkan rekap Workforce dari tanggal pencatatan paling baru di database. Ketika tanggal baru diinput, highlight otomatis berpindah ke tanggal terbaru dan data sebelumnya tetap tersimpan di tabel riwayat.
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="text-end px-3 py-1 rounded" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);">
                    <div class="text-white-50" style="font-size: 10px; text-transform: uppercase;">Total Pekerja Tanggal Ini</div>
                    <div class="fs-5 fw-bold text-white" id="highlightTotalWorkers">
                        {{ number_format((int) $latestDateHighlight['total_workers'], 0, ',', '.') }} <span class="fs-6 fw-normal text-white-50">Orang</span>
                    </div>
                </div>
                <div class="text-end px-3 py-1 rounded" style="background: rgba(16,185,129,0.16); border: 1px solid rgba(16,185,129,0.4);">
                    <div class="text-white-50" style="font-size: 10px; text-transform: uppercase;">Total Man Hours Harian</div>
                    <div class="fs-5 fw-bold text-success-emphasis" style="color: #34d399 !important;" id="highlightTotalManHours">
                        {{ number_format((float) $latestDateHighlight['total_man_hours'], 1, ',', '.') }} <span class="fs-6 fw-normal text-white-50">Jam</span>
                    </div>
                </div>
                @adminManagement
                    <button type="button" class="btn btn-sm btn-success fw-semibold px-3 py-2" onclick="openBatchWorkforceModal('{{ $latestDateHighlight['record_date'] }}')">
                        <i class="bi bi-pencil-square me-1"></i> Edit Tanggal Ini
                    </button>
                @endadminManagement
            </div>
        </div>

        <div class="latest-wf-unit-grid" id="highlightUnitsGrid">
            @foreach($latestDateHighlight['units'] as $uItem)
                <div class="latest-wf-unit-box {{ $uItem['is_recorded'] ? 'has-data' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-white" style="font-size: 12px;">{{ $uItem['unit_name'] }}</span>
                        @if(!empty($uItem['is_new_unit']))
                            <span class="badge bg-warning text-dark" style="font-size: 9.5px;">Unit Baru</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <div>
                            <span class="fs-6 fw-bold {{ $uItem['worker_count'] > 0 ? 'text-white' : 'text-white-50' }}">
                                {{ number_format((int) $uItem['worker_count'], 0, ',', '.') }}
                            </span>
                            <span class="text-white-50" style="font-size: 10.5px;">Orang</span>
                        </div>
                        <div class="text-end" style="font-size: 11px; color: #6ee7b7;">
                            <i class="bi bi-lightning-charge-fill"></i> {{ number_format((float) $uItem['calculated_man_hours'], 1, ',', '.') }} Jam
                        </div>
                    </div>
                    <div class="text-white-50 mt-1" style="font-size: 10px;">
                        @if($uItem['is_recorded'])
                            {{ $uItem['worker_count'] }} × {{ number_format((float) $uItem['hours_per_worker'], 1, ',', '.') }} Jam/Orang
                        @else
                            Belum tercatat pada tanggal ini (0)
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="badge bg-secondary mb-1">HIGHLIGHT WORKFORCE TERBARU</div>
                <h6 class="fw-bold text-white mb-1">Belum ada data Workforce yang tercatat di database</h6>
                <div class="text-white-50 small">Silakan klik tombol <strong>+ Input Workforce</strong> untuk menginput jumlah pekerja seluruh unit/area operasi pada tanggal pencatatan.</div>
            </div>
            <button type="button" class="btn btn-success btn-sm px-3 py-2 fw-semibold" onclick="openBatchWorkforceModal()">
                <i class="bi bi-plus-lg me-1"></i> Input Workforce Sekarang
            </button>
        </div>
    @endif
</div>

<!-- FILTER BAR -->
<div class="filter-card">
    <div class="filter-grid filter-grid-hsse">
        <!-- Tanggal Mulai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterStart" class="form-control form-control-filter" value="{{ request('start_date') }}" title="Tanggal Mulai">
        </div>

        <!-- Tanggal Selesai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterEnd" class="form-control form-control-filter" value="{{ request('end_date') }}" title="Tanggal Selesai">
        </div>

        <!-- Kategori Pekerja -->
        <div>
            <select id="filterCategory" class="form-select form-select-filter">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <!-- Unit Kerja -->
        <div>
            <select id="filterUnit" class="form-select form-select-filter">
                <option value="">Semua Unit</option>
                @foreach($units as $u)
                    <option value="{{ $u }}" {{ request('unit') === $u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
            </select>
        </div>

        <!-- Periode Bulan/Tahun -->
        <div>
            <input type="month" id="filterPeriod" class="form-control form-control-filter px-2" value="{{ request('period') }}" title="Filter Bulan & Tahun">
        </div>

        <!-- Pencarian -->
        <div class="filter-input-wrap">
            <i class="bi bi-search filter-icon-addon"></i>
            <input type="search" id="filterSearch" class="form-control form-control-filter" value="{{ request('search') }}" placeholder="Cari kategori, kode, unit, perusahaan, catatan...">
        </div>

        <!-- Reset -->
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilters()">
                Reset
            </button>
        </div>
    </div>
</div>

<!-- KARTU RINGKASAN (5 STAT CARDS SERAGAM HSSE PLAN) -->
<div class="stats-grid-hsse">
    <!-- 1. Total Pekerja Hari Ini -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Pekerja Hari Ini</div>
            <div class="stat-number" id="statTotalWorkers">{{ number_format($stats['total_workers'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral" id="statTotalWorkersSub">Catatan per {{ $stats['latest_date'] }} (Terbaru)</span></div>
        </div>
    </div>

    <!-- 2. Internal & Utility (GDE, Kodipa, Utility) -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-building-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">GDE, Kodipa &amp; Utility</div>
            <div class="stat-number text-success" id="statCoreWorkers">{{ number_format($stats['core_workers'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Komponen internal &amp; utilitas</span></div>
        </div>
    </div>

    <!-- 3. Mitra & Kontraktor (Security, Contractor & Sub-Contractor, Kerja Praktik) -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-person-badge-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Contractor, Security &amp; KP</div>
            <div class="stat-number text-warning" id="statPartnerWorkers">{{ number_format($stats['partner_workers'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Mitra kerja &amp; kerja praktik</span></div>
        </div>
    </div>

    <!-- 4. Kontribusi Man Hours (Klik ke Man Hours) -->
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('man-hours.index') }}'" title="Lihat Rekap Perhitungan Man Hours">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Sumber Man Hours</div>
            <div class="stat-number text-primary" id="statTotalManHours">{{ number_format($stats['total_man_hours'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Jam kerja terhitung otomatis</span></div>
        </div>
    </div>

    <!-- 5. Unit Operasi Aktif / Total Entri -->
    <div class="stat-card" style="cursor: pointer;" onclick="openManageUnitsModal()" title="Kelola Master Unit / Area Operasi">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Unit Aktif / Entri</div>
            <div class="stat-number" id="statActiveCategories" style="font-size:20px">{{ $stats['active_units_count'] ?? $stats['active_categories'] }} Unit / {{ $stats['total_records'] }}</div>
            <div class="stat-trend"><span class="stat-trend-caption">Klik untuk kelola unit operasi</span></div>
        </div>
    </div>
</div>

<!-- CHARTS ROW (3 CHARTS SERAGAM HSSE PLAN) -->
<div class="charts-row-hsse">
    <!-- Chart 1: Tren Jumlah Pekerja per Tanggal -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Tren Jumlah Pekerja</h2>
            <p class="chart-subtitle">Perkembangan jumlah tenaga kerja berdasarkan tanggal pencatatan</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="trendChartCanvas"></canvas>
        </div>
    </div>

    <!-- Chart 2: Distribusi Pekerja per Unit / Area Operasi (Donut + Legend) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Distribusi Kategori / Unit Pekerja</h2>
            <p class="chart-subtitle">Proporsi jumlah pekerja per unit / area operasi aktif</p>
        </div>
        <div class="donut-layout-hsse">
            <div style="height:140px; position:relative;">
                <canvas id="categoryChartCanvas"></canvas>
            </div>
            <div class="custom-legend-list" id="categoryLegendList">
                @php
                    $totalUnitWorkersForPct = max(1, (int) collect($unitChart['data'] ?? [])->sum());
                @endphp
                @foreach($unitChart['labels'] as $idx => $uLabel)
                    @php
                        $val = $unitChart['data'][$idx] ?? 0;
                        $pct = $totalUnitWorkersForPct > 0 ? round(($val / $totalUnitWorkersForPct) * 100) : 0;
                        $color = $unitChart['colors'][$idx] ?? '#64748b';
                    @endphp
                    <div class="custom-legend-item">
                        <span class="legend-indicator" style="background:{{ $color }}"></span>
                        <span class="legend-name" title="{{ $uLabel }}">{{ $uLabel }}</span>
                        <span class="legend-stats">{{ $val }} org ({{ $pct }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Chart 3: Sumber Man Hours per Unit / Area Operasi -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Sumber Man Hours per Unit</h2>
            <p class="chart-subtitle">Akumulasi jam kerja aman (Pekerja × Jam/Orang) tiap unit/area operasi</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="manHoursSourceChartCanvas"></canvas>
        </div>
    </div>
</div>

<!-- TABEL REKAP RIWAYAT HARIAN WORKFORCE (PER TANGGAL TERBARU KE TERLAMA) -->
@if(!empty($dailyHistory))
<div class="table-card mb-3">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                <i class="bi bi-calendar-range text-primary me-1"></i> Riwayat Rekap Harian Workforce per Tanggal
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-2" style="font-size:10.5px;">
                    Urutan Tanggal Terbaru ke Terlama
                </span>
            </h2>
        </div>
        <div class="text-muted small">
            Menampilkan total pekerja &amp; rincian unit per tanggal pencatatan
        </div>
    </div>
    <div class="table-responsive-custom">
        <table class="table-custom" id="dailyHistoryWorkforceTable">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="width: 145px;">Tanggal Pencatatan</th>
                    <th>Rincian Jumlah Pekerja per Unit / Area Operasi</th>
                    <th style="width: 145px; text-align: center;">Total Pekerja Harian</th>
                    <th style="width: 165px; text-align: center;">Total Man Hours Harian</th>
                    <th style="width: 120px; text-align: center;">Aksi Tanggal</th>
                </tr>
            </thead>
            <tbody id="dailyHistoryTableBody">
                @foreach($dailyHistory as $dIdx => $day)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $dIdx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $day['record_date_formatted'] }}</div>
                            @if($dIdx === 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px;">Terbaru (Highlight)</span>
                            @else
                                <span class="text-muted" style="font-size: 10px;">Riwayat Sebelumnya</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($day['units'] as $uSummary)
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10.5px; font-weight: 600;">
                                        {{ $uSummary['unit'] }}: <strong class="text-primary">{{ number_format((int) $uSummary['worker_count'], 0, ',', '.') }}</strong> org
                                        <span class="text-muted">({{ number_format((float) $uSummary['man_hours'], 0, ',', '.') }}j)</span>
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-bold text-dark" style="font-size: 13.5px;">{{ number_format((int) $day['total_workers'], 0, ',', '.') }}</span>
                            <span class="text-muted small">Orang</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="mh-source-badge">
                                <i class="bi bi-lightning-charge-fill"></i> {{ number_format((float) $day['total_man_hours'], 1, ',', '.') }} Jam
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @adminManagement
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;"
                                        onclick="openBatchWorkforceModal('{{ $day['record_date'] }}')" title="Edit Seluruh Unit pada Tanggal Ini">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Tanggal
                                </button>
                            @endadminManagement
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- TABEL DATA MANPOWER -->
<div class="table-card">
    <div class="table-topbar">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h2 class="table-section-title mb-0">Daftar Rincian Data Manpower <span class="badge bg-success-subtle text-success border border-success-subtle ms-2" style="font-size:10.5px;">Sumber Otomatis Man Hours</span></h2>
            
            <!-- Bulk Action Toolbar (Hapus Terpilih) -->
            <div id="bulkActionToolbar" class="d-none align-items-center gap-2 p-1 px-2 bg-light border border-danger-subtle rounded shadow-sm ms-2">
                <span class="badge bg-danger text-white" id="selectedCountBadge">0 data terpilih</span>
                <button type="button" class="btn btn-sm btn-danger py-0 px-2 d-flex align-items-center gap-1" id="btnDeleteSelected" onclick="deleteSelectedWorkforce()" style="font-size: 11.5px;">
                    <i class="bi bi-trash-fill"></i> Hapus Terpilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="deselectAllWorkforce()" style="font-size: 11px;">Batal</button>
            </div>
        </div>

        <div class="table-controls">
            <div class="table-search-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" class="form-control table-search-input" id="tableSearch" placeholder="Cari baris tabel...">
            </div>

            <select class="form-select form-select-sm" id="sortBy" style="width:160px; font-size:11.5px">
                <option value="date-desc" {{ request('sort') === 'date-desc' ? 'selected' : '' }}>Tanggal Terbaru</option>
                <option value="date-asc" {{ request('sort') === 'date-asc' ? 'selected' : '' }}>Tanggal Terlama</option>
                <option value="workers-desc" {{ request('sort') === 'workers-desc' ? 'selected' : '' }}>Pekerja Terbanyak</option>
                <option value="workers-asc" {{ request('sort') === 'workers-asc' ? 'selected' : '' }}>Pekerja Tersedikit</option>
                <option value="category" {{ request('sort') === 'category' ? 'selected' : '' }}>Urut Kategori</option>
            </select>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="workforceTable">
            <thead>
                <tr>
                    <th style="width: 38px; text-align: center;">
                        <input type="checkbox" id="selectAllWf" class="form-check-input" title="Pilih Semua Halaman Aktif" onchange="toggleSelectAllWf(this)">
                    </th>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 110px;">Tanggal</th>
                    <th style="width: 130px;">Kode Sumber</th>
                    <th>Kategori / Komponen</th>
                    <th>Unit &amp; Keterangan</th>
                    <th style="width: 130px; text-align: center;">Jumlah Pekerja</th>
                    <th style="width: 120px; text-align: center;">Jam / Orang</th>
                    <th style="width: 165px; text-align: center;">Hasil Man Hours</th>
                    <th style="width: 105px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="workforceTableBody">
                @forelse($workforces as $index => $wf)
                    @php
                        $catClass = match($wf->category) {
                            'GDE' => 'cat-gde',
                            'Kodipa' => 'cat-kodipa',
                            'Utility' => 'cat-utility',
                            'Security' => 'cat-security',
                            'Contractor & Sub-Contractor' => 'cat-contractor',
                            'Kerja Praktik' => 'cat-kp',
                            default => 'cat-default',
                        };
                    @endphp
                    <tr data-id="{{ $wf->id }}">
                        <td style="text-align: center;">
                            <input type="checkbox" class="form-check-input wf-row-checkbox" value="{{ $wf->id }}" data-id="{{ $wf->id }}" onchange="handleRowCheckChange()">
                        </td>
                        <td style="text-align: center; color: #64748b;">{{ $workforces->firstItem() + $index }}</td>
                        <td class="fw-semibold">{{ $wf->record_date ? $wf->record_date->format('d/m/Y') : '-' }}</td>
                        <td><span class="wf-code-badge">{{ $wf->employee_code }}</span></td>
                        <td>
                            <span class="cat-pill {{ $catClass }}">{{ $wf->category }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $wf->unit }}</div>
                            @if($wf->department || $wf->notes)
                                <div class="text-muted" style="font-size: 10.5px;">{{ $wf->department ?: $wf->notes }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-bold text-dark" style="font-size: 13px;">{{ number_format($wf->worker_count, 0, ',', '.') }}</span>
                            <span class="text-muted small ms-1">Orang</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="text-secondary fw-semibold">{{ number_format((float) $wf->hours_per_worker, 1, ',', '.') }}</span>
                            <span class="text-muted small">Jam</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="mh-source-badge" title="{{ $wf->worker_count }} Pekerja × {{ $wf->hours_per_worker }} Jam">
                                <i class="bi bi-lightning-charge-fill"></i>
                                {{ number_format((float) $wf->calculated_man_hours, 1, ',', '.') }} Jam
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-action-icon btn-action-view" onclick="viewWorkforce({{ $wf->id }})" title="Lihat Detail & Perhitungan">
                                <i class="bi bi-eye"></i>
                            </button>
                            @adminManagement
                                <button type="button" class="btn-action-icon btn-action-edit" onclick="editWorkforce({{ $wf->id }})" title="Edit Data Manpower">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn-action-icon btn-action-delete" onclick="deleteWorkforce({{ $wf->id }}, '{{ addslashes($wf->category) }}', '{{ $wf->record_date ? $wf->record_date->format('d/m/Y') : '-' }}')" title="Hapus Data Manpower">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endadminManagement
                        </td>
                    </tr>
                @empty
                    <tr id="emptyWorkforceRow">
                        <td colspan="10" class="p-0">
                            <div class="empty-state-wf">
                                <div class="empty-state-wf-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h6 class="fw-bold text-secondary mb-1">Belum ada data Manpower yang tercatat</h6>
                                <p class="text-muted small mb-3">
                                    Data jumlah pekerja belum tersedia pada periode atau filter yang dipilih. Tambahkan data Manpower sebagai sumber perhitungan otomatis Man Hours.
                                </p>
                                @adminManagement
                                <button type="button" class="btn-action-primary mx-auto" onclick="openBatchWorkforceModal()">
                                    <i class="bi bi-plus-lg"></i> Input Manpower
                                </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($workforces->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="text-muted small">
                Menampilkan {{ $workforces->firstItem() }}–{{ $workforces->lastItem() }} dari {{ $workforces->total() }} data Workforce
            </div>
            <div>
                {{ $workforces->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- MODAL DETAIL WORKFORCE -->
<div class="modal fade" id="workforceDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold">Detail Data Workforce &amp; Sumber Man Hours</h5>
                        <div class="text-muted" style="font-size:11px;">Rincian jumlah pekerja dan audit perhitungan otomatis jam kerja</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="detail-grid-wf">
                    <div class="detail-label-wf">Kode Referensi Sumber</div><div>:</div>
                    <div><span id="detailWfCode" class="wf-code-badge">-</span></div>

                    <div class="detail-label-wf">Tanggal Pencatatan</div><div>:</div>
                    <div id="detailWfDate" class="fw-bold text-dark">-</div>

                    <div class="detail-label-wf">Kategori / Komponen</div><div>:</div>
                    <div id="detailWfCategory">-</div>

                    <div class="detail-label-wf">Unit / Area Kerja</div><div>:</div>
                    <div id="detailWfUnit" class="fw-semibold">-</div>

                    <div class="detail-label-wf">Sub-Unit / Perusahaan</div><div>:</div>
                    <div id="detailWfDepartment">-</div>

                    <div class="detail-label-wf">Jumlah Pekerja Sumber</div><div>:</div>
                    <div id="detailWfWorkers" class="fw-bold text-primary">-</div>

                    <div class="detail-label-wf">Jam Kerja per Orang</div><div>:</div>
                    <div id="detailWfHoursPerWorker">-</div>

                    <div class="detail-label-wf">Hasil Perhitungan Man Hours</div><div>:</div>
                    <div id="detailWfTotalManHours" class="fw-bold text-success">-</div>

                    <div class="detail-label-wf">Catatan / Keterangan</div><div>:</div>
                    <div id="detailWfNotes" class="text-secondary">-</div>
                </div>

                <div class="calc-preview-card mt-3">
                    <div class="small fw-bold text-success mb-1">
                        <i class="bi bi-calculator me-1"></i> Audit Rumus Integrasi Man Hours
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="small text-dark" id="detailWfFormulaText">
                            Man Hours = Jumlah Pekerja × Jam Kerja per Orang
                        </div>
                        <a href="{{ route('man-hours.index') }}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size:11px;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat di Menu Man Hours
                        </a>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold mb-2" style="font-size: 12.5px;">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Aktivitas Data Ini
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 11px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:130px;">Waktu</th>
                                <th style="width:140px;">Pengguna</th>
                                <th style="width:80px; text-align:center;">Aksi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="detailWfActivities">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-2">Belum ada riwayat aktivitas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning btn-sm px-3" id="btnEditFromDetailWf">
                    <i class="bi bi-pencil me-1"></i> Edit Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT WORKFORCE -->
<div class="modal fade" id="workforceFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="workforceForm" method="POST" action="{{ route('workforce.store') }}" onsubmit="saveWorkforce(event)">
                @csrf
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="wfModalTitle">Tambah Data Workforce</h5>
                            <div class="text-muted" style="font-size:11px;">Input jumlah tenaga kerja harian/periode sebagai sumber perhitungan Man Hours</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="formWfId" name="id">

                    <!-- Peringatan Duplikasi Tanggal + Kategori -->
                    <div id="wfDuplicateWarning" class="alert alert-warning py-2 px-3 small d-none mb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <span id="wfDuplicateText"></span>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm fw-semibold py-1 px-2" style="font-size:11px;" onclick="switchToEditDuplicate()">
                                <i class="bi bi-pencil-square me-1"></i> Edit Data yang Ada
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Tanggal -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Pencatatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="formWfDate" name="record_date" required
                                   value="{{ date('Y-m-d') }}" onchange="checkWfDuplicate()">
                        </div>

                        <!-- Kategori / Komponen Pekerja -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Kategori / Komponen Pekerja <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="formWfCategory" name="category" required onchange="checkWfDuplicate()">
                                @foreach(\App\Models\Workforce::CATEGORIES as $stdCat)
                                    <option value="{{ $stdCat }}">{{ $stdCat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit / Area -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Unit / Area Operasi</label>
                            <select class="form-select form-select-sm" id="formWfUnit" name="unit">
                                @foreach($units as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub-Unit / Perusahaan -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Sub-Unit / Nama Perusahaan / Kelompok</label>
                            <input type="text" class="form-control form-control-sm" id="formWfDepartment" name="department"
                                   placeholder="Contoh: Divisi Operasi / PT Kontraktor Mitra">
                        </div>

                        <!-- Jumlah Pekerja -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Jumlah Pekerja (Orang) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" id="formWfWorkerCount" name="worker_count"
                                   min="0" max="100000" step="1" required placeholder="0" oninput="updateLiveManHoursPreview()">
                            <div class="form-text" style="font-size:10.5px;">Wajib berupa bilangan bulat positif atau 0 (tidak boleh negatif).</div>
                        </div>

                        <!-- Jam Kerja per Orang -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Jam Kerja per Orang (Jam/Hari)</label>
                            <input type="number" class="form-control form-control-sm" id="formWfHoursPerWorker" name="hours_per_worker"
                                   min="0" max="24" step="0.5" value="8" oninput="updateLiveManHoursPreview()">
                            <div class="form-text" style="font-size:10.5px;">Default: 8 jam/orang/hari (otomatis menghitung Man Hours).</div>
                        </div>

                        <!-- Preview Perhitungan Otomatis Man Hours -->
                        <div class="col-12">
                            <div class="calc-preview-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-success" style="font-size: 11.5px;">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Pratinjau Perhitungan Otomatis Man Hours
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;" id="liveFormulaBreakdown">
                                        0 Pekerja × 8 Jam/Orang
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-success" id="liveTotalManHours">0</span>
                                    <span class="small fw-semibold text-secondary">Man Hours</span>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Catatan / Keterangan Tambahan (Opsional)</label>
                            <textarea class="form-control form-control-sm" id="formWfNotes" name="notes" rows="2"
                                      placeholder="Tambahkan keterangan shift, proyek, atau rincian kelompok kerja jika diperlukan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitWf">
                        <i class="bi bi-save me-1"></i> Simpan Data Workforce
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL INPUT WORKFORCE DINAMIS (SELURUH UNIT AKTIF SEKALIGUS) -->
<div class="modal fade" id="workforceBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form id="workforceBatchForm" method="POST" action="{{ route('workforce.store-batch') }}" onsubmit="saveBatchWorkforce(event)">
                @csrf
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:36px; height:36px; font-size:16px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="batchWfModalTitle">Input Workforce Seluruh Unit / Area Operasi</h5>
                            <div class="text-muted" style="font-size:11.5px;">Input jumlah pekerja untuk seluruh unit/area operasi aktif sekaligus pada tanggal pencatatan</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 align-items-end mb-3 pb-3 border-bottom">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Pencatatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="batchRecordDate" name="record_date"
                                   value="{{ date('Y-m-d') }}" required onchange="loadBatchUnitsByDate(this.value)">
                        </div>
                        <div class="col-md-5">
                            <div class="text-muted" style="font-size:11.5px;" id="batchDateStatusInfo">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                Pilih tanggal pencatatan. Jika tanggal tersebut sudah memiliki data, nilai pekerja akan dimuat otomatis untuk diperbarui.
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchFromBatchToManageUnits()">
                                <i class="bi bi-building-add me-1"></i> + Tambah Unit / Area Baru
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Dinamis Seluruh Unit Aktif dari Database -->
                    <div class="table-responsive" style="max-height: 410px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px; text-align: center;">No</th>
                                    <th style="width: 240px;">Unit / Area Operasi Aktif</th>
                                    <th style="width: 150px;">Jumlah Pekerja (Orang) <span class="text-danger">*</span></th>
                                    <th style="width: 140px;">Jam Kerja / Orang</th>
                                    <th style="width: 155px; text-align: center;">Hasil Man Hours Unit</th>
                                    <th>Catatan / Keterangan Unit (Opsional)</th>
                                </tr>
                            </thead>
                            <tbody id="batchUnitsTableBody">
                                @php
                                    $batchActiveUnits = isset($activeOperationalUnits) && $activeOperationalUnits->isNotEmpty()
                                        ? $activeOperationalUnits
                                        : collect($units)->map(fn($u, $i) => (object)['id' => null, 'name' => $u, 'is_initial' => true]);
                                @endphp
                                @foreach($batchActiveUnits as $bIdx => $opUnit)
                                    <tr class="batch-unit-tr" data-unit-name="{{ $opUnit->name }}" data-unit-id="{{ $opUnit->id }}">
                                        <td class="text-center text-muted fw-semibold">{{ $bIdx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                <span>{{ $opUnit->name }}</span>
                                                @if(isset($opUnit->is_initial) && !$opUnit->is_initial)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 9.5px;">Unit Baru</span>
                                                @endif
                                            </div>
                                            <input type="hidden" name="units[{{ $bIdx }}][operational_unit_id]" value="{{ $opUnit->id }}">
                                            <input type="hidden" name="units[{{ $bIdx }}][unit]" value="{{ $opUnit->name }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm batch-worker-input"
                                                   name="units[{{ $bIdx }}][worker_count]"
                                                   min="0" max="100000" step="1" value="0" required
                                                   oninput="recalcBatchPreview()">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm batch-hours-input"
                                                   name="units[{{ $bIdx }}][hours_per_worker]"
                                                   min="0" max="24" step="0.5" value="8" required
                                                   oninput="recalcBatchPreview()">
                                        </td>
                                        <td class="text-center">
                                            <span class="mh-source-badge batch-unit-mh-preview">
                                                <i class="bi bi-lightning-charge-fill"></i> <span class="unit-mh-val">0</span> Jam
                                            </span>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm batch-notes-input"
                                                   name="units[{{ $bIdx }}][notes]"
                                                   placeholder="Catatan unit {{ $opUnit->name }} (opsional)...">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Ringkasan Live Total Pekerja & Total Man Hours -->
                    <div class="calc-preview-card mt-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-bold text-success" style="font-size: 12px;">
                                <i class="bi bi-calculator-fill me-1"></i> Ringkasan Total Harian ({{ isset($activeOperationalUnits) ? $activeOperationalUnits->count() : count($units) }} Unit Aktif)
                            </div>
                            <div class="text-muted" style="font-size: 11px;">
                                Rumus: <strong class="text-dark">Total Man Hours Harian = Σ (Jumlah Pekerja Unit × Jam Kerja/Orang)</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end">
                                <div class="text-muted" style="font-size: 10.5px;">Total Pekerja Seluruh Unit</div>
                                <div class="fs-5 fw-bold text-primary"><span id="batchLiveTotalWorkers">0</span> <small class="fs-6 fw-normal text-secondary">Orang</small></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted" style="font-size: 10.5px;">Total Man Hours Harian</div>
                                <div class="fs-5 fw-bold text-success"><span id="batchLiveTotalManHours">0</span> <small class="fs-6 fw-normal text-secondary">Man Hours</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitBatchWf">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Seluruh Unit pada Tanggal Ini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KELOLA MASTER UNIT / AREA OPERASI -->
<div class="modal fade" id="manageUnitsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:36px; height:36px; font-size:16px;">
                        <i class="bi bi-building-gear"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold">Kelola Master Unit / Area Operasi</h5>
                        <div class="text-muted" style="font-size:11.5px;">Tambah unit baru, ubah nama unit, atau aktifkan/nonaktifkan unit tanpa menghapus data historis</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Form Tambah Unit Baru -->
                <form id="addOperationalUnitForm" onsubmit="submitNewOperationalUnit(event)" class="p-3 bg-light border rounded mb-3">
                    <div class="fw-bold text-dark mb-2" style="font-size: 12px;">
                        <i class="bi bi-plus-circle-fill text-success me-1"></i> Tambah Unit / Area Operasi Baru
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Nama Unit / Area Operasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="newUnitName" required maxlength="100"
                                   placeholder="Contoh: Dieng Unit 3 / Rig Drilling">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Keterangan / Deskripsi (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="newUnitDescription" maxlength="255"
                                   placeholder="Contoh: Area pengembangan sumur baru">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold" id="btnAddOperationalUnit">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="form-text mt-1" style="font-size: 10.5px;">
                        Unit baru yang ditambahkan akan langsung muncul pada form Input Workforce dan disertakan dalam perhitungan Man Hours.
                    </div>
                </form>

                <!-- Tabel Daftar Master Unit -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Nama Unit / Area Operasi</th>
                                <th>Keterangan</th>
                                <th style="width: 110px; text-align: center;">Tipe</th>
                                <th style="width: 100px; text-align: center;">Status</th>
                                <th style="width: 165px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="operationalUnitsTableBody">
                            @if(isset($operationalUnits))
                                @foreach($operationalUnits as $uIdx => $opU)
                                    <tr data-unit-id="{{ $opU->id }}">
                                        <td class="text-center text-muted">{{ $uIdx + 1 }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm fw-semibold unit-edit-name"
                                                   id="unitNameInput_{{ $opU->id }}" value="{{ $opU->name }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm unit-edit-desc"
                                                   id="unitDescInput_{{ $opU->id }}" value="{{ $opU->description }}" placeholder="-">
                                        </td>
                                        <td class="text-center">
                                            @if($opU->is_initial)
                                                <span class="badge bg-secondary-subtle text-secondary border">Unit Awal</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Unit Baru</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($opU->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;"
                                                    onclick="updateOperationalUnit({{ $opU->id }})" title="Simpan Perubahan Nama/Deskripsi">
                                                <i class="bi bi-check2"></i> Simpan
                                            </button>
                                            <button type="button" class="btn btn-sm {{ $opU->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} py-0 px-2" style="font-size: 11px;"
                                                    onclick="toggleOperationalUnit({{ $opU->id }})"
                                                    title="{{ $opU->is_active ? 'Nonaktifkan Unit (Data historis tetap aman)' : 'Aktifkan Unit Kembali' }}">
                                                <i class="bi {{ $opU->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                {{ $opU->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const WF_BASE_URL = "{{ url('/workforce') }}";

    let trendChartInstance = null;
    let categoryChartInstance = null;
    let mhSourceChartInstance = null;
    let detectedDuplicateRecord = null;

    const initialTrendData = @json($trendChart);
    const initialUnitData = @json($unitChart);
    const initialCategoryData = @json($categoryChart);

    document.addEventListener('DOMContentLoaded', function () {
        initWorkforceCharts(initialTrendData, initialUnitData);

        document.getElementById('filterStart')?.addEventListener('change', applyWorkforceFilters);
        document.getElementById('filterEnd')?.addEventListener('change', applyWorkforceFilters);
        document.getElementById('filterCategory')?.addEventListener('change', applyWorkforceFilters);
        document.getElementById('filterUnit')?.addEventListener('change', applyWorkforceFilters);
        document.getElementById('filterPeriod')?.addEventListener('change', applyWorkforceFilters);
        document.getElementById('sortBy')?.addEventListener('change', applyWorkforceFilters);

        let searchTimer;
        document.getElementById('filterSearch')?.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(applyWorkforceFilters, 350);
        });

        document.getElementById('tableSearch')?.addEventListener('input', function (e) {
            const q = (e.target.value || '').toLowerCase().trim();
            document.querySelectorAll('#workforceTableBody tr:not(#emptyWorkforceRow)').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });

    function initWorkforceCharts(trendData, unitData) {
        // 1. Trend Chart
        const trendCtx = document.getElementById('trendChartCanvas')?.getContext('2d');
        if (trendCtx) {
            if (trendChartInstance) trendChartInstance.destroy();
            trendChartInstance = new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendData.labels.length ? trendData.labels : ['Belum Ada Data'],
                    datasets: [{
                        label: 'Jumlah Pekerja (Orang)',
                        data: trendData.workers.length ? trendData.workers : [0],
                        backgroundColor: '#059669',
                        borderRadius: 4,
                        barPercentage: 0.55
                    }]
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

        // 2. Unit Distribution Donut Chart
        const catCtx = document.getElementById('categoryChartCanvas')?.getContext('2d');
        if (catCtx) {
            if (categoryChartInstance) categoryChartInstance.destroy();
            const totalWorkers = (unitData.data || []).reduce((a, b) => a + b, 0);
            categoryChartInstance = new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: unitData.labels,
                    datasets: [{
                        data: totalWorkers > 0 ? unitData.data : [1],
                        backgroundColor: totalWorkers > 0 ? unitData.colors : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (totalWorkers === 0) return 'Belum ada data (0)';
                                    const val = context.raw || 0;
                                    const pct = Math.round((val / totalWorkers) * 100);
                                    return ` ${context.label}: ${val} orang (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Man Hours Source by Unit Chart
        const mhCtx = document.getElementById('manHoursSourceChartCanvas')?.getContext('2d');
        if (mhCtx) {
            if (mhSourceChartInstance) mhSourceChartInstance.destroy();
            mhSourceChartInstance = new Chart(mhCtx, {
                type: 'bar',
                data: {
                    labels: unitData.labels,
                    datasets: [{
                        label: 'Man Hours (Jam)',
                        data: unitData.man_hours || [],
                        backgroundColor: unitData.colors || '#2563eb',
                        borderRadius: 4,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        y: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    }

    function applyWorkforceFilters() {
        const params = new URLSearchParams();
        const start = document.getElementById('filterStart')?.value || '';
        const end = document.getElementById('filterEnd')?.value || '';
        const category = document.getElementById('filterCategory')?.value || '';
        const unit = document.getElementById('filterUnit')?.value || '';
        const period = document.getElementById('filterPeriod')?.value || '';
        const search = document.getElementById('filterSearch')?.value || '';
        const sort = document.getElementById('sortBy')?.value || 'date-desc';

        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);
        if (category) params.append('category', category);
        if (unit) params.append('unit', unit);
        if (period) params.append('period', period);
        if (search) params.append('search', search);
        if (sort) params.append('sort', sort);

        const exportBtn = document.getElementById('btnExportWorkforce');
        if (exportBtn) {
            exportBtn.href = `${WF_BASE_URL}/export?${params.toString()}`;
        }

        fetch(`${WF_BASE_URL}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            document.getElementById('statTotalWorkers').textContent = Number(data.stats.total_workers).toLocaleString('id-ID');
            const subEl = document.getElementById('statTotalWorkersSub');
            if (subEl && data.stats.latest_date) {
                subEl.textContent = `Catatan per ${data.stats.latest_date} (Terbaru)`;
            }
            document.getElementById('statCoreWorkers').textContent = Number(data.stats.core_workers).toLocaleString('id-ID');
            document.getElementById('statPartnerWorkers').textContent = Number(data.stats.partner_workers).toLocaleString('id-ID');
            document.getElementById('statTotalManHours').textContent = Number(data.stats.total_man_hours).toLocaleString('id-ID');
            document.getElementById('statActiveCategories').textContent = `${data.stats.active_categories} / ${data.stats.total_records}`;

            // Update Legend Unit
            const legendList = document.getElementById('categoryLegendList');
            const targetUnitData = data.unitChart || data.categoryChart;
            if (legendList && targetUnitData) {
                const totalW = Math.max(1, (targetUnitData.data || []).reduce((a, b) => a + b, 0));
                legendList.innerHTML = targetUnitData.labels.map((lbl, idx) => {
                    const val = targetUnitData.data[idx] || 0;
                    const pct = totalW > 0 ? Math.round((val / totalW) * 100) : 0;
                    const color = targetUnitData.colors[idx] || '#64748b';
                    return `
                        <div class="custom-legend-item">
                            <span class="legend-indicator" style="background:${color}"></span>
                            <span class="legend-name" title="${escapeHtml(lbl)}">${escapeHtml(lbl)}</span>
                            <span class="legend-stats">${val} org (${pct}%)</span>
                        </div>
                    `;
                }).join('');
            }

            initWorkforceCharts(data.trendChart, data.unitChart || data.categoryChart);
            renderWorkforceRows(data.workforces);
        })
        .catch(err => console.error('Gagal memuat filter Workforce:', err));
    }

    function resetFilters() {
        document.getElementById('filterStart').value = '';
        document.getElementById('filterEnd').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterUnit').value = '';
        document.getElementById('filterPeriod').value = '';
        document.getElementById('filterSearch').value = '';
        document.getElementById('tableSearch').value = '';
        document.getElementById('sortBy').value = 'date-desc';
        applyWorkforceFilters();
    }

    function getCategoryBadgeClass(cat) {
        switch (cat) {
            case 'GDE': return 'cat-gde';
            case 'Kodipa': return 'cat-kodipa';
            case 'Utility': return 'cat-utility';
            case 'Security': return 'cat-security';
            case 'Contractor & Sub-Contractor': return 'cat-contractor';
            case 'Kerja Praktik': return 'cat-kp';
            default: return 'cat-default';
        }
    }

    function renderWorkforceRows(rows) {
        const tbody = document.getElementById('workforceTableBody');
        if (!tbody) return;

        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyWorkforceRow">
                    <td colspan="9" class="p-0">
                        <div class="empty-state-wf">
                            <div class="empty-state-wf-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="fw-bold text-secondary mb-1">Belum ada data Workforce yang tercatat</h6>
                            <p class="text-muted small mb-3">
                                Data jumlah pekerja belum tersedia pada periode atau filter yang dipilih.
                            </p>
                            <button type="button" class="btn-action-primary mx-auto" onclick="openWorkforceModal()">
                                <i class="bi bi-plus-lg"></i> Tambah Workforce
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = rows.map((wf, idx) => {
            const catClass = getCategoryBadgeClass(wf.category);
            const dateStr = formatDate(wf.record_date);
            const subInfo = wf.department || wf.notes || '';
            const workersFormatted = Number(wf.worker_count || 0).toLocaleString('id-ID');
            const hoursFormatted = Number(wf.hours_per_worker || 8).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const mhFormatted = Number(wf.calculated_man_hours || 0).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });

            return `
                <tr data-id="${wf.id}">
                    <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                    <td class="fw-semibold">${dateStr}</td>
                    <td><span class="wf-code-badge">${escapeHtml(wf.employee_code)}</span></td>
                    <td><span class="cat-pill ${catClass}">${escapeHtml(wf.category)}</span></td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHtml(wf.unit)}</div>
                        ${subInfo ? `<div class="text-muted" style="font-size: 10.5px;">${escapeHtml(subInfo)}</div>` : ''}
                    </td>
                    <td style="text-align: center;">
                        <span class="fw-bold text-dark" style="font-size: 13px;">${workersFormatted}</span>
                        <span class="text-muted small ms-1">Orang</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="text-secondary fw-semibold">${hoursFormatted}</span>
                        <span class="text-muted small">Jam</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="mh-source-badge">
                            <i class="bi bi-lightning-charge-fill"></i> ${mhFormatted} Jam
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-action-icon btn-action-view" onclick="viewWorkforce(${wf.id})" title="Lihat Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn-action-icon btn-action-edit" onclick="editWorkforce(${wf.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn-action-icon btn-action-delete" onclick="deleteWorkforce(${wf.id}, '${escapeHtml(wf.category)}', '${dateStr}')" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateLiveManHoursPreview() {
        const workers = Math.max(0, parseInt(document.getElementById('formWfWorkerCount')?.value || '0', 10) || 0);
        const hours = Math.max(0, parseFloat(document.getElementById('formWfHoursPerWorker')?.value || '8') || 0);
        const total = Math.round(workers * hours * 100) / 100;

        const breakdownEl = document.getElementById('liveFormulaBreakdown');
        const totalEl = document.getElementById('liveTotalManHours');
        if (breakdownEl) {
            breakdownEl.textContent = `${workers.toLocaleString('id-ID')} Pekerja × ${hours.toLocaleString('id-ID')} Jam/Orang`;
        }
        if (totalEl) {
            totalEl.textContent = total.toLocaleString('id-ID');
        }
    }

    function checkWfDuplicate() {
        const recordDate = document.getElementById('formWfDate')?.value || '';
        const category = document.getElementById('formWfCategory')?.value || '';
        const ignoreId = document.getElementById('formWfId')?.value || '';
        const warningBox = document.getElementById('wfDuplicateWarning');
        const warningText = document.getElementById('wfDuplicateText');

        detectedDuplicateRecord = null;

        if (!recordDate || !category) {
            warningBox?.classList.add('d-none');
            return;
        }

        const params = new URLSearchParams({ record_date: recordDate, category: category });
        if (ignoreId) params.append('ignore_id', ignoreId);

        fetch(`${WF_BASE_URL}/check-duplicate?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.duplicate && warningBox && warningText) {
                detectedDuplicateRecord = data.data || data.record || null;
                warningText.textContent = data.message;
                warningBox.classList.remove('d-none');
            } else {
                warningBox?.classList.add('d-none');
            }
        })
        .catch(() => {});
    }

    function switchToEditDuplicate() {
        if (!detectedDuplicateRecord) return;
        populateFormForEdit(detectedDuplicateRecord);
    }

    function populateFormForEdit(w) {
        document.getElementById('formWfId').value = w.id;
        document.getElementById('formWfDate').value = w.record_date ? String(w.record_date).substring(0, 10) : '';
        document.getElementById('formWfCategory').value = w.category || 'GDE';
        document.getElementById('formWfUnit').value = w.unit || 'PMU';
        document.getElementById('formWfDepartment').value = w.department || '';
        document.getElementById('formWfWorkerCount').value = w.worker_count ?? 0;
        document.getElementById('formWfHoursPerWorker').value = w.hours_per_worker ?? 8;
        document.getElementById('formWfNotes').value = w.notes || '';

        document.getElementById('wfModalTitle').textContent = `Edit Data Workforce (${w.category})`;
        document.getElementById('wfDuplicateWarning')?.classList.add('d-none');
        detectedDuplicateRecord = null;
        updateLiveManHoursPreview();
    }

    function openWorkforceModal() {
        const form = document.getElementById('workforceForm');
        if (form) form.reset();

        document.getElementById('formWfId').value = '';
        document.getElementById('formWfDate').value = "{{ date('Y-m-d') }}";
        document.getElementById('formWfHoursPerWorker').value = '8';
        document.getElementById('wfModalTitle').textContent = 'Tambah Data Workforce';
        document.getElementById('wfDuplicateWarning')?.classList.add('d-none');
        detectedDuplicateRecord = null;
        updateLiveManHoursPreview();
        checkWfDuplicate();

        const modalEl = document.getElementById('workforceFormModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function viewWorkforce(id) {
        fetch(`${WF_BASE_URL}/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Error', data.message || 'Data Workforce tidak ditemukan.', 'error');
                return;
            }

            const w = data.workforce;
            document.getElementById('detailWfCode').textContent = w.employee_code || '-';
            document.getElementById('detailWfDate').textContent = formatDate(w.record_date);
            document.getElementById('detailWfCategory').innerHTML = `<span class="cat-pill ${getCategoryBadgeClass(w.category)}">${escapeHtml(w.category)}</span>`;
            document.getElementById('detailWfUnit').textContent = w.unit || '-';
            document.getElementById('detailWfDepartment').textContent = w.department || w.name || '-';
            document.getElementById('detailWfWorkers').textContent = `${Number(w.worker_count).toLocaleString('id-ID')} Orang`;
            document.getElementById('detailWfHoursPerWorker').textContent = `${Number(w.hours_per_worker).toLocaleString('id-ID')} Jam / Orang`;
            document.getElementById('detailWfTotalManHours').textContent = `${Number(w.calculated_man_hours).toLocaleString('id-ID')} Man Hours`;
            document.getElementById('detailWfNotes').textContent = w.notes || '-';
            document.getElementById('detailWfFormulaText').textContent = `${w.worker_count} Pekerja × ${w.hours_per_worker} Jam/Orang = ${w.calculated_man_hours} Man Hours`;

            const actBody = document.getElementById('detailWfActivities');
            if (data.activities && data.activities.length > 0) {
                actBody.innerHTML = data.activities.map(act => `
                    <tr>
                        <td class="text-muted">${formatDateTime(act.created_at)}</td>
                        <td class="fw-semibold">${escapeHtml(act.user_name || '-')}</td>
                        <td class="text-center"><span class="badge bg-secondary">${escapeHtml(act.action)}</span></td>
                        <td>${escapeHtml(act.description || '-')}</td>
                    </tr>
                `).join('');
            } else {
                actBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-2">Belum ada riwayat aktivitas.</td></tr>`;
            }

            const btnEdit = document.getElementById('btnEditFromDetailWf');
            if (btnEdit) {
                btnEdit.onclick = function () {
                    bootstrap.Modal.getInstance(document.getElementById('workforceDetailModal'))?.hide();
                    editWorkforce(w.id);
                };
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('workforceDetailModal')).show();
        })
        .catch(() => Swal.fire('Error', 'Gagal memuat detail Workforce.', 'error'));
    }

    function editWorkforce(id) {
        fetch(`${WF_BASE_URL}/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Error', data.message || 'Data tidak ditemukan.', 'error');
                return;
            }

            populateFormForEdit(data.workforce);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('workforceFormModal')).show();
        })
        .catch(() => Swal.fire('Error', 'Gagal memuat data Workforce.', 'error'));
    }

    function saveWorkforce(event) {
        event.preventDefault();

        const id = document.getElementById('formWfId')?.value;
        const isEdit = Boolean(id);
        const url = isEdit ? `${WF_BASE_URL}/${id}` : WF_BASE_URL;
        const method = isEdit ? 'PUT' : 'POST';

        const rawWorkers = document.getElementById('formWfWorkerCount').value;
        const workerCountVal = Number(rawWorkers);
        if (rawWorkers === '' || !Number.isInteger(workerCountVal) || workerCountVal < 0) {
            Swal.fire('Validasi Gagal', 'Jumlah pekerja wajib berupa bilangan bulat yang tidak negatif.', 'warning');
            return;
        }

        const payload = {
            record_date: document.getElementById('formWfDate').value,
            category: document.getElementById('formWfCategory').value,
            unit: document.getElementById('formWfUnit').value,
            department: document.getElementById('formWfDepartment').value,
            worker_count: workerCountVal,
            hours_per_worker: parseFloat(document.getElementById('formWfHoursPerWorker').value || '8'),
            notes: document.getElementById('formWfNotes').value,
        };

        const btn = document.getElementById('btnSubmitWf');
        btn.disabled = true;

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            btn.disabled = false;

            if (status >= 400 || !body.success) {
                // Jika terdeteksi duplikasi tanggal + kategori, tawarkan untuk mengedit data yang sudah ada
                if (body.duplicate && body.existing) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Sudah Terdaftar',
                        html: `${escapeHtml(body.message)}<br><br>Apakah Anda ingin beralih ke mode <strong>Edit</strong> untuk memperbarui data tersebut?`,
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-pencil-square me-1"></i> Ya, Edit Data yang Ada',
                        cancelButtonText: 'Batal'
                    }).then(res => {
                        if (res.isConfirmed) {
                            populateFormForEdit(body.existing);
                        }
                    });
                    return;
                }

                let msg = body.message || 'Gagal menyimpan data Workforce.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: msg });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('workforceFormModal'))?.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan',
                text: body.message,
                timer: 1600,
                showConfirmButton: false
            });
            applyWorkforceFilters();
        })
        .catch(() => {
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan jaringan atau server.', 'error');
        });
    }

    function deleteWorkforce(id, category, dateStr) {
        Swal.fire({
            title: 'Hapus Data Workforce?',
            html: `Hapus data <strong>${escapeHtml(category)}</strong> tanggal <strong>${escapeHtml(dateStr)}</strong>?<br><small class="text-muted">Data Man Hours yang bersumber dari catatan ini juga akan diperbarui secara otomatis.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(`${WF_BASE_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                if (status >= 400 || !body.success) {
                    Swal.fire('Error', body.message || 'Gagal menghapus data Workforce.', 'error');
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus',
                    text: body.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                applyWorkforceFilters();
            })
            .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat menghapus data.', 'error'));
        });
    }

    // =========================================================================
    // HAPUS MASSAL (BULK DELETE) MANPOWER
    // =========================================================================
    function toggleSelectAllWf(master) {
        const checkboxes = document.querySelectorAll('.wf-row-checkbox');
        checkboxes.forEach(cb => { cb.checked = master.checked; });
        updateBulkToolbar();
    }

    function handleRowCheckChange() {
        const checkboxes = document.querySelectorAll('.wf-row-checkbox');
        const checkedCount = document.querySelectorAll('.wf-row-checkbox:checked').length;
        const master = document.getElementById('selectAllWf');
        if (master) {
            master.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            master.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const checkedBoxes = document.querySelectorAll('.wf-row-checkbox:checked');
        const count = checkedBoxes.length;
        const toolbar = document.getElementById('bulkActionToolbar');
        const badge = document.getElementById('selectedCountBadge');

        if (count > 0) {
            toolbar?.classList.remove('d-none');
            toolbar?.classList.add('d-flex');
            if (badge) badge.textContent = `${count} data terpilih`;
        } else {
            toolbar?.classList.add('d-none');
            toolbar?.classList.remove('d-flex');
        }
    }

    function deselectAllWorkforce() {
        const checkboxes = document.querySelectorAll('.wf-row-checkbox');
        checkboxes.forEach(cb => { cb.checked = false; });
        const master = document.getElementById('selectAllWf');
        if (master) {
            master.checked = false;
            master.indeterminate = false;
        }
        updateBulkToolbar();
    }

    function deleteSelectedWorkforce() {
        const checkedBoxes = document.querySelectorAll('.wf-row-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value)).filter(Boolean);

        if (ids.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal satu data Manpower untuk dihapus.', 'warning');
            return;
        }

        Swal.fire({
            title: `Hapus ${ids.length} Data Manpower Terpilih?`,
            html: `Apakah Anda yakin ingin menghapus <strong>${ids.length} data Manpower</strong> yang telah dipilih?<br><small class="text-muted">Data Man Hours yang bersumber dari data ini akan disinkronkan dan diperbarui secara otomatis.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: `<i class="bi bi-trash-fill me-1"></i> Ya, Hapus ${ids.length} Data`,
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById('btnDeleteSelected');
            if (btn) btn.disabled = true;

            fetch('{{ route('workforce.destroy-bulk') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                if (btn) btn.disabled = false;
                if (status >= 400 || !body.success) {
                    Swal.fire('Gagal', body.message || 'Gagal menghapus data Manpower terpilih.', 'error');
                    return;
                }

                deselectAllWorkforce();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Dihapus',
                    text: body.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(() => {
                if (btn) btn.disabled = false;
                Swal.fire('Error', 'Terjadi kesalahan sistem saat memproses penghapusan massal.', 'error');
            });
        });
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

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    // =========================================================================
    // INPUT WORKFORCE DINAMIS (SELURUH UNIT AKTIF SEKALIGUS)
    // =========================================================================
    function openBatchWorkforceModal(targetDate = null) {
        const dateInput = document.getElementById('batchRecordDate');
        const defaultDate = targetDate || "{{ date('Y-m-d') }}";
        if (dateInput) {
            dateInput.value = defaultDate;
        }

        loadBatchUnitsByDate(defaultDate);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('workforceBatchModal')).show();
    }

    function loadBatchUnitsByDate(dateVal) {
        if (!dateVal) return;

        fetch(`${WF_BASE_URL}/by-date?record_date=${encodeURIComponent(dateVal)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.units)) return;

            const tbody = document.getElementById('batchUnitsTableBody');
            if (!tbody) return;

            let recordedCount = 0;
            tbody.innerHTML = data.units.map((u, idx) => {
                if (u.has_existing_record) recordedCount++;
                const workers = Number(u.worker_count ?? 0);
                const hours = Number(u.hours_per_worker ?? 8);
                const mh = Math.round(workers * hours * 100) / 100;
                const badgeNew = u.is_initial === false
                    ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 9.5px;">Unit Baru</span>`
                    : '';

                return `
                    <tr class="batch-unit-tr" data-unit-name="${escapeHtml(u.unit_name)}" data-unit-id="${u.operational_unit_id || ''}">
                        <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center">
                                <span>${escapeHtml(u.unit_name)}</span>
                                ${badgeNew}
                            </div>
                            <input type="hidden" name="units[${idx}][operational_unit_id]" value="${u.operational_unit_id || ''}">
                            <input type="hidden" name="units[${idx}][unit]" value="${escapeHtml(u.unit_name)}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm batch-worker-input"
                                   name="units[${idx}][worker_count]"
                                   min="0" max="100000" step="1" value="${workers}" required
                                   oninput="recalcBatchPreview()">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm batch-hours-input"
                                   name="units[${idx}][hours_per_worker]"
                                   min="0" max="24" step="0.5" value="${hours}" required
                                   oninput="recalcBatchPreview()">
                        </td>
                        <td class="text-center">
                            <span class="mh-source-badge batch-unit-mh-preview">
                                <i class="bi bi-lightning-charge-fill"></i> <span class="unit-mh-val">${mh.toLocaleString('id-ID')}</span> Jam
                            </span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm batch-notes-input"
                                   name="units[${idx}][notes]"
                                   value="${escapeHtml(u.notes || '')}"
                                   placeholder="Catatan unit ${escapeHtml(u.unit_name)} (opsional)...">
                        </td>
                    </tr>
                `;
            }).join('');

            const infoEl = document.getElementById('batchDateStatusInfo');
            if (infoEl) {
                if (recordedCount > 0) {
                    infoEl.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Ditemukan ${recordedCount} unit yang sudah tercatat pada tanggal ${formatDate(dateVal)}. Perubahan akan memperbarui data tanggal tersebut.</span>`;
                } else {
                    infoEl.innerHTML = `<span class="text-secondary"><i class="bi bi-calendar-plus me-1"></i> Belum ada data Workforce pada tanggal ${formatDate(dateVal)}. Silakan isi jumlah pekerja untuk tiap unit aktif.</span>`;
                }
            }

            recalcBatchPreview();
        })
        .catch(() => recalcBatchPreview());
    }

    function recalcBatchPreview() {
        let totalWorkers = 0;
        let totalManHours = 0;

        document.querySelectorAll('#batchUnitsTableBody tr.batch-unit-tr').forEach(tr => {
            const wInput = tr.querySelector('.batch-worker-input');
            const hInput = tr.querySelector('.batch-hours-input');
            const mhSpan = tr.querySelector('.unit-mh-val');

            const w = Math.max(0, parseInt(wInput?.value || '0', 10) || 0);
            const h = Math.max(0, parseFloat(hInput?.value || '8') || 0);
            const mh = Math.round(w * h * 100) / 100;

            totalWorkers += w;
            totalManHours += mh;

            if (mhSpan) {
                mhSpan.textContent = mh.toLocaleString('id-ID');
            }
        });

        const totalWEl = document.getElementById('batchLiveTotalWorkers');
        const totalMhEl = document.getElementById('batchLiveTotalManHours');
        if (totalWEl) totalWEl.textContent = totalWorkers.toLocaleString('id-ID');
        if (totalMhEl) totalMhEl.textContent = (Math.round(totalManHours * 100) / 100).toLocaleString('id-ID');
    }

    function saveBatchWorkforce(event) {
        event.preventDefault();

        const recordDate = document.getElementById('batchRecordDate')?.value;
        if (!recordDate) {
            Swal.fire('Validasi Gagal', 'Tanggal pencatatan wajib diisi.', 'warning');
            return;
        }

        const unitsPayload = [];
        let hasInvalid = false;

        document.querySelectorAll('#batchUnitsTableBody tr.batch-unit-tr').forEach(tr => {
            const unitName = tr.getAttribute('data-unit-name') || '';
            const unitId = tr.getAttribute('data-unit-id') || null;
            const wRaw = tr.querySelector('.batch-worker-input')?.value;
            const hRaw = tr.querySelector('.batch-hours-input')?.value;
            const notesVal = tr.querySelector('.batch-notes-input')?.value || '';

            const wVal = Number(wRaw);
            const hVal = parseFloat(hRaw || '8');

            if (wRaw === '' || !Number.isInteger(wVal) || wVal < 0) {
                hasInvalid = true;
            }

            unitsPayload.push({
                operational_unit_id: unitId ? Number(unitId) : null,
                unit: unitName,
                worker_count: wVal,
                hours_per_worker: isNaN(hVal) ? 8 : hVal,
                notes: notesVal
            });
        });

        if (hasInvalid) {
            Swal.fire('Validasi Gagal', 'Jumlah pekerja setiap unit wajib berupa bilangan bulat >= 0.', 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitBatchWf');
        if (btn) btn.disabled = true;

        fetch(`${WF_BASE_URL}/batch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                record_date: recordDate,
                units: unitsPayload
            })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            if (btn) btn.disabled = false;

            if (status >= 400 || !body.success) {
                let msg = body.message || 'Gagal menyimpan data Workforce.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: msg });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('workforceBatchModal'))?.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan',
                text: body.message,
                timer: 1400,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan jaringan atau server.', 'error');
        });
    }

    // =========================================================================
    // KELOLA MASTER UNIT / AREA OPERASI
    // =========================================================================
    function openManageUnitsModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('manageUnitsModal')).show();
    }

    function switchFromBatchToManageUnits() {
        bootstrap.Modal.getInstance(document.getElementById('workforceBatchModal'))?.hide();
        setTimeout(() => openManageUnitsModal(), 200);
    }

    function submitNewOperationalUnit(event) {
        event.preventDefault();
        const nameEl = document.getElementById('newUnitName');
        const descEl = document.getElementById('newUnitDescription');
        const btn = document.getElementById('btnAddOperationalUnit');

        const name = (nameEl?.value || '').trim();
        const description = (descEl?.value || '').trim();

        if (!name) {
            Swal.fire('Validasi Gagal', 'Nama Unit / Area Operasi wajib diisi.', 'warning');
            return;
        }

        if (btn) btn.disabled = true;

        fetch(`${WF_BASE_URL}/units`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, description })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            if (btn) btn.disabled = false;

            if (status >= 400 || !body.success) {
                let msg = body.message || 'Gagal menambahkan unit operasi.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Menambahkan Unit', html: msg });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Unit Baru Ditambahkan',
                text: body.message,
                timer: 1400,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan saat menambahkan unit.', 'error');
        });
    }

    function updateOperationalUnit(id) {
        const name = (document.getElementById(`unitNameInput_${id}`)?.value || '').trim();
        const description = (document.getElementById(`unitDescInput_${id}`)?.value || '').trim();

        if (!name) {
            Swal.fire('Validasi Gagal', 'Nama unit/area operasi tidak boleh kosong.', 'warning');
            return;
        }

        fetch(`${WF_BASE_URL}/units/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, description })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            if (status >= 400 || !body.success) {
                let msg = body.message || 'Gagal memperbarui unit.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Gagal Memperbarui', html: msg });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil Diperbarui',
                text: body.message,
                timer: 1300,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat memperbarui unit.', 'error'));
    }

    function toggleOperationalUnit(id) {
        fetch(`${WF_BASE_URL}/units/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            if (status >= 400 || !body.success) {
                Swal.fire('Error', body.message || 'Gagal mengubah status unit.', 'error');
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Status Unit Diperbarui',
                text: body.message,
                timer: 1300,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat mengubah status unit.', 'error'));
    }
</script>
@endpush


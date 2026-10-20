@extends('layouts.app', [
    'title' => 'HSSE Plan - KPI Kontraktor',
    'activeModule' => 'hsse-plan'
])

@push('styles')
<style>
    .filter-grid-hsse {
        grid-template-columns: 140px 140px 140px 140px 120px 1fr auto;
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

    .bottom-grid-hsse {
        display: grid;
        grid-template-columns: 1.4fr 1fr 0.85fr;
        gap: 14px;
        margin-top: 14px;
    }

    .text-leading { color: #1d4ed8; font-weight: 600; }
    .text-lagging { color: #7c3aed; font-weight: 600; }

    .progress-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 85px;
    }

    .progress-custom {
        height: 6px;
        flex-grow: 1;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 4px;
    }

    .progress-green { background: #10b981; }
    .progress-orange { background: #f59e0b; }
    .progress-red { background: #ef4444; }
    .progress-label { min-width: 28px; font-size: 10px; text-align: right; font-weight: 600; }

    .kpi-row-selected {
        background: #ecfdf5 !important;
    }

    .doc-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
    }

    .doc-link:hover {
        text-decoration: underline;
    }

    .detail-fields-grid {
        display: grid;
        grid-template-columns: 90px 10px 1fr;
        gap: 5px 0;
        font-size: 11px;
    }

    .thumb-preview-box {
        width: 44px;
        height: 40px;
        display: grid;
        place-items: center;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        color: #475569;
        font-size: 16px;
    }

    .history-table-custom {
        font-size: 10.5px;
        width: 100%;
    }

    .history-table-custom th {
        background: #f8fafc;
        padding: 6px 8px;
        border-bottom: 1px solid var(--border-color);
    }

    .history-table-custom td {
        padding: 6px 8px;
        border-bottom: 1px solid #f1f5f9;
    }

    .note-item-box {
        background: #f8fafc;
        border-left: 3px solid var(--green-primary);
        border-radius: 4px;
        padding: 7px 9px;
        margin-bottom: 6px;
        font-size: 11px;
        transition: all 0.15s ease-in-out;
    }

    .note-item-box:hover {
        background: #f1f5f9;
    }

    @media (max-width: 1200px) {
        .stats-grid-hsse { grid-template-columns: repeat(3, 1fr); }
        .charts-row-hsse { grid-template-columns: 1fr; }
        .bottom-grid-hsse { grid-template-columns: 1fr; }
        .filter-grid-hsse { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-bullseye"></i>
        </div>
        <div>
            <h1 class="page-heading">HSSE Plan <span class="ms-2 fs-6 fw-semibold text-secondary">KPI Kontraktor</span></h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">HSSE Plan</span>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">KPI Kontraktor</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('hsse-plan.achieved') }}" class="btn btn-action-outline">
            <i class="bi bi-clock-history me-1 text-success"></i> Riwayat Tercapai
        </a>
        @adminManagement
            <button class="btn-action-primary" onclick="openKPIModal()">
                <i class="bi bi-plus-lg"></i> Tambah KPI
            </button>
        @endadminManagement
        @canExportPdf
        <button class="btn-action-outline" onclick="exportPDF()">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </button>
        @endcanExportPdf
    </div>
</div>

<!-- FILTER BAR -->
<div class="filter-card">
    <div class="filter-grid filter-grid-hsse">
        <!-- Tanggal Mulai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterStart" class="form-control form-control-filter" title="Tanggal Mulai">
        </div>

        <!-- Tanggal Selesai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterEnd" class="form-control form-control-filter" title="Tanggal Selesai">
        </div>

        <!-- Filter Kontraktor (Dari Database) -->
        <div>
            <select id="filterContractor" class="form-select form-select-filter">
                <option value="">Semua Kontraktor</option>
                <option value="unassigned">Belum Ditentukan</option>
                @if(isset($contractors))
                    @foreach($contractors as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Jenis KPI -->
        <div>
            <select id="filterType" class="form-select form-select-filter">
                <option value="">Semua Jenis</option>
                <option value="Leading">Leading</option>
                <option value="Lagging">Lagging</option>
            </select>
        </div>

        <!-- Status Pemenuhan -->
        <div>
            <select id="filterStatus" class="form-select form-select-filter">
                <option value="">Semua Status</option>
                <option value="Tercapai">Tercapai</option>
                <option value="Dalam Progress">Dalam Progress</option>
                <option value="Belum Tercapai">Belum Tercapai</option>
            </select>
        </div>

        <!-- Periode Tahun -->
        <div>
            <select id="filterPeriod" class="form-select form-select-filter">
                <option value="">Semua Tahun</option>
                <option value="2025" selected>2025</option>
                <option value="2026">2026</option>
            </select>
        </div>

        <!-- Pencarian -->
        <div class="filter-input-wrap">
            <i class="bi bi-search filter-icon-addon"></i>
            <input type="search" id="globalSearch" class="form-control form-control-filter" placeholder="Cari nama KPI atau keterangan...">
        </div>

        <!-- Reset -->
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilters()">
                Reset
            </button>
        </div>
    </div>
</div>

<!-- KARTU RINGKASAN (5 KPI CARDS) -->
<div class="stats-grid-hsse">
    <!-- Total KPI -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-bullseye"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total KPI</div>
            <div class="stat-number" id="statTotal">0</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Indikator kontraktor</span></div>
        </div>
    </div>

    <!-- Tercapai -->
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('hsse-plan.achieved') }}'" title="Buka Halaman Riwayat Tercapai">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Tercapai</div>
            <div class="stat-number text-success" id="statAchieved">0</div>
            <div class="stat-trend"><span class="stat-trend-up">KPI terpenuhi</span></div>
        </div>
    </div>

    <!-- Dalam Progress -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Dalam Progress</div>
            <div class="stat-number text-warning" id="statProgress">0</div>
            <div class="stat-trend"><span class="stat-trend-down">Sedang berjalan</span></div>
        </div>
    </div>

    <!-- Belum Tercapai -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-cancelled rounded-circle">
            <i class="bi bi-x-lg"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Belum Tercapai</div>
            <div class="stat-number text-danger" id="statNot">0</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Perlu tindak lanjut</span></div>
        </div>
    </div>

    <!-- Leading / Lagging -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Leading / Lagging</div>
            <div class="stat-number" id="statTypes" style="font-size:20px">0 / 0</div>
            <div class="stat-trend"><span class="stat-trend-caption">Jumlah indikator</span></div>
        </div>
    </div>
</div>

<!-- CHARTS ROW (3 CHARTS) -->
<div class="charts-row-hsse">
    <!-- Chart 1: Capaian KPI per Bulan -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Capaian KPI per Bulan</h2>
            <p class="chart-subtitle">Perbandingan target akumulatif dan realisasi bulanan</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Chart 2: Status Pemenuhan KPI (Donut) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Status Pemenuhan KPI</h2>
            <p class="chart-subtitle">Proporsi pencapaian target kinerja</p>
        </div>
        <div class="donut-layout-hsse">
            <div style="height:140px; position:relative;">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="custom-legend-list">
                <div class="custom-legend-item">
                    <span class="legend-indicator" style="background:#10b981"></span>
                    <span class="legend-name">Tercapai</span>
                    <span class="legend-stats" id="legendAchieved">0 (0%)</span>
                </div>
                <div class="custom-legend-item">
                    <span class="legend-indicator" style="background:#f59e0b"></span>
                    <span class="legend-name">Progress</span>
                    <span class="legend-stats" id="legendProgress">0 (0%)</span>
                </div>
                <div class="custom-legend-item">
                    <span class="legend-indicator" style="background:#ef4444"></span>
                    <span class="legend-name">Belum</span>
                    <span class="legend-stats" id="legendNot">0 (0%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 3: Jumlah KPI per Jenis -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Jumlah KPI per Jenis</h2>
            <p class="chart-subtitle">Leading vs Lagging Indicator</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="typeChart"></canvas>
        </div>
    </div>
</div>

<!-- TABEL DAFTAR KPI -->
<div class="table-card">
    <div class="table-topbar">
        <h2 class="table-section-title">Daftar KPI Kontraktor</h2>

        <div class="table-controls">
            <div class="table-search-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" class="form-control table-search-input" id="tableSearch" placeholder="Cari KPI...">
            </div>

            <select class="form-select form-select-sm" id="sortBy" style="width:145px; font-size:11.5px">
                <option value="name">Urut Nama</option>
                <option value="progress-desc">Progress Tertinggi</option>
                <option value="progress-asc">Progress Terendah</option>
                <option value="date">Tanggal Mulai</option>
            </select>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama KPI</th>
                    <th style="width: 130px;">Kontraktor</th>
                    <th style="width: 90px;">Jenis</th>
                    <th style="width: 100px; text-align: center;">Target</th>
                    <th style="width: 100px; text-align: center;">Realisasi</th>
                    <th style="width: 140px;">Persentase</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 70px; text-align: center;">Periode</th>
                    <th>Keterangan</th>
                    <th style="width: 90px; text-align: center;">Dokumen</th>
                    <th style="width: 105px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="kpiTableBody"></tbody>
        </table>
    </div>

    <div class="table-bottom-bar">
        <div id="tableInfo">Memuat data...</div>
        <div>
            <ul class="pagination pagination-sm m-0" id="pagination"></ul>
        </div>
    </div>
</div>

<!-- BOTTOM PANELS: DETAIL KPI, RIWAYAT CAPAIAN, CATATAN -->
<div class="bottom-grid-hsse">
    <!-- Panel 1: Detail KPI Terpilih -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Detail KPI Terpilih</h2>
            <p class="chart-subtitle">Klik baris tabel untuk memilih KPI</p>
        </div>
        <div id="detailPanel" class="p-2">
            <div class="text-muted text-center py-4">Pilih baris pada tabel untuk melihat detail.</div>
        </div>
    </div>

    <!-- Panel 2: Riwayat Capaian -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Riwayat Capaian</h2>
            <p class="chart-subtitle">Log perkembangan realisasi indikator</p>
        </div>
        <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
            <table class="history-table-custom">
                <thead>
                    <tr><th>Tanggal</th><th>Realisasi</th><th>Keterangan</th><th>Dokumen</th></tr>
                </thead>
                <tbody id="historyBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Panel 3: Catatan Evaluasi & Tindak Lanjut -->
    <div class="chart-box-card">
        <div class="chart-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="chart-title">Catatan KPI</h2>
                <p class="chart-subtitle">Evaluasi & tindak lanjut</p>
            </div>
            @adminManagement
                <button class="btn btn-action-outline btn-sm py-1 px-2" style="font-size:11px" onclick="openNoteModal()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            @endadminManagement
        </div>
        <div id="notesPanel" style="max-height: 170px; overflow-y: auto;">
            <div class="text-muted text-center py-3" style="font-size:11px">Pilih KPI untuk melihat catatan.</div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL KPI (DIPICU DARI TOMBOL VIEW DI KOLOM AKSI) -->
<div class="modal fade" id="kpiDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed green">
                <h5 class="modal-title" id="kpiDetailModalTitle">
                    <i class="bi bi-bullseye me-1"></i> Detail KPI Kontraktor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="kpiDetailModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                    Memuat data detail KPI dari database...
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                <a href="#" id="kpiDetailPermalink" class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Penuh
                </a>
                <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT KPI -->
<div class="modal fade" id="kpiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <form id="kpiForm">
                <div class="modal-header modal-header-themed green">
                    <h5 class="modal-title" id="kpiModalTitle">
                        <i class="bi bi-bullseye me-1"></i> Tambah KPI Kontraktor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="kpiId">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:12px">Nama KPI <span class="text-danger">*</span></label>
                            <input type="text" id="kpiName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Kontraktor Pelaksana</label>
                            <select id="kpiContractorId" class="form-select form-select-sm">
                                <option value="">-- Belum Ditentukan --</option>
                                @if(isset($contractors))
                                    @foreach($contractors as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Jenis KPI <span class="text-danger">*</span></label>
                            <select id="kpiType" class="form-select form-select-sm" required>
                                <option value="Leading">Leading</option>
                                <option value="Lagging">Lagging</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Unit / Lokasi <span class="text-danger">*</span></label>
                            <input type="text" id="kpiUnit" class="form-control form-control-sm" value="Dieng Unit 1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Nilai Target <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="kpiTargetValue" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Satuan Pengukuran</label>
                            <input type="text" id="kpiUnitMeasure" class="form-control form-control-sm" placeholder="Contoh: %, kali/tahun, index">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Realisasi Aktual <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="kpiActual" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Tahun Periode <span class="text-danger">*</span></label>
                            <input type="number" id="kpiPeriod" class="form-control form-control-sm" value="2025" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" id="kpiStart" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" id="kpiEnd" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Status Pemenuhan</label>
                            <select id="kpiStatus" class="form-select form-select-sm">
                                <option value="Tercapai">Tercapai</option>
                                <option value="Dalam Progress">Dalam Progress</option>
                                <option value="Belum Tercapai">Belum Tercapai</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Keterangan Target</label>
                            <textarea id="kpiDescription" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH CATATAN -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <form id="noteForm">
                <div class="modal-header modal-header-themed green">
                    <h5 class="modal-title"><i class="bi bi-chat-left-text me-1"></i> Tambah Catatan KPI</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px">KPI Terpilih</label>
                        <input type="text" id="noteKpiName" class="form-control form-control-sm bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px">Catatan Tindak Lanjut <span class="text-danger">*</span></label>
                        <textarea id="noteText" class="form-control form-control-sm" rows="3" placeholder="Tuliskan catatan evaluasi atau tindak lanjut..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:12px">Penulis / PIC (Opsional)</label>
                        <input type="text" id="noteAuthor" class="form-control form-control-sm" placeholder="Nama pembuat catatan (default: Admin HSSE Dieng)">
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT CATATAN -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <form id="editNoteForm">
                <div class="modal-header modal-header-themed green">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit Catatan KPI</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="editNoteId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px">Catatan Tindak Lanjut <span class="text-danger">*</span></label>
                        <textarea id="editNoteText" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DOKUMEN KPI -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed green">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text me-1"></i> Dokumen & Bukti Capaian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="documentList" class="mb-4"></div>
                @adminManagement
                    <div class="border-top pt-3">
                        <h6 class="fw-bold mb-2" style="font-size:12px">Upload Dokumen Baru</h6>
                        <form id="docUploadForm" class="row g-2">
                            <div class="col-md-7">
                                <input type="text" id="docName" class="form-control form-control-sm" placeholder="Nama Dokumen" required>
                            </div>
                            <div class="col-md-5">
                                <input type="file" id="docFile" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-sm btn-action-primary">
                                    <i class="bi bi-upload me-1"></i> Upload Dokumen
                                </button>
                            </div>
                        </form>
                    </div>
                @endadminManagement
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CSRF Token for Secure AJAX/Fetch calls
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const canManageKPI = {{ Auth::user()?->isAdminManagement() ? 'true' : 'false' }};

    // Data KPI Kontraktor dari Database
    let kpiData = @json($kpis ?? []);

    let selectedId = kpiData.length > 0 ? kpiData[0].id : null;
    let currentPage = 1;
    const pageSize = 10;
    let monthlyChart = null;
    let statusChart = null;
    let typeChart = null;

    const $ = id => document.getElementById(id);

    const kpiModal = new bootstrap.Modal($('kpiModal'));
    const kpiDetailModal = new bootstrap.Modal($('kpiDetailModal'));
    const noteModal = new bootstrap.Modal($('noteModal'));
    const editNoteModal = new bootstrap.Modal($('editNoteModal'));
    const documentModal = new bootstrap.Modal($('documentModal'));

    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
        })[ch]);
    }

    function percentage(item) {
        if (typeof item.percentage !== 'undefined') return Number(item.percentage);
        const target = Number(item.targetValue ?? item.target ?? 0);
        const actual = Number(item.actual ?? item.realization ?? 0);
        if (target <= 0) return actual <= 0 ? 100 : 0;
        return Math.max(0, Math.min(100, Math.round(actual / target * 100)));
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID', {maximumFractionDigits:2});
    }

    function formatDate(value) {
        if (!value) return '-';
        const strVal = String(value);
        const date = new Date(strVal.includes('T') ? strVal : strVal + 'T00:00:00');
        if (isNaN(date.getTime())) return strVal;
        return date.toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'});
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (isNaN(date.getTime())) return String(value);
        return date.toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) + ', ' +
               date.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});
    }

    function statusBadgeClass(status) {
        if (status === 'Tercapai') return 'status-achieved';
        if (status === 'Belum Tercapai' || status === 'Tidak Tercapai' || status === 'Belum Mulai') return 'status-not';
        return 'status-progress';
    }

    function progressColorClass(item) {
        const st = item.status || '';
        if (st === 'Tercapai') return 'progress-green';
        if (st === 'Belum Tercapai' || st === 'Tidak Tercapai' || st === 'Belum Mulai') return 'progress-red';
        return 'progress-orange';
    }

    function getFilteredData() {
        const start = $('filterStart').value;
        const end = $('filterEnd').value;
        const contractor = $('filterContractor') ? $('filterContractor').value : '';
        const type = $('filterType').value;
        const status = $('filterStatus').value;
        const period = $('filterPeriod').value;
        const search = ($('globalSearch').value + ' ' + $('tableSearch').value).trim().toLowerCase();

        let rows = kpiData.filter(item => {
            const title = (item.title || item.name || '').toLowerCase();
            const desc = (item.description || '').toLowerCase();
            const kpiType = item.category || item.type || '';
            const cName = (item.contractor ? item.contractor.name : (item.contractor_name || '')).toLowerCase();
            const searchable = title + ' ' + desc + ' ' + kpiType.toLowerCase() + ' ' + cName;
            const searchMatch = !search || searchable.includes(search);

            const typeMatch = !type || kpiType === type;

            let statusMatch = true;
            if (status) {
                if (status === 'Dalam Progress') {
                    statusMatch = (item.status === 'Dalam Progress' || item.status === 'Berlangsung');
                } else if (status === 'Belum Tercapai') {
                    statusMatch = (item.status === 'Belum Tercapai' || item.status === 'Tidak Tercapai' || item.status === 'Belum Mulai');
                } else {
                    statusMatch = (item.status === status);
                }
            }

            const kpiPeriod = String(item.year || item.period || '');
            const periodMatch = !period || kpiPeriod === period;

            const kpiStart = item.start_date || item.start || '';
            const kpiEnd = item.end_date || item.end || '';
            const dateMatch = (!start || (kpiEnd && kpiEnd >= start)) && (!end || (kpiStart && kpiStart <= end));

            let contractorMatch = true;
            if (contractor === 'unassigned') {
                contractorMatch = !item.contractor_id && !item.contractor;
            } else if (contractor) {
                contractorMatch = (String(item.contractor_id) === String(contractor));
            }

            return searchMatch && typeMatch && statusMatch && periodMatch && dateMatch && contractorMatch;
        });

        const sort = $('sortBy').value;
        if (sort === 'progress-desc') rows.sort((a,b) => percentage(b) - percentage(a));
        else if (sort === 'progress-asc') rows.sort((a,b) => percentage(a) - percentage(b));
        else if (sort === 'date') rows.sort((a,b) => (a.start_date || a.start || '').localeCompare(b.start_date || b.start || ''));
        else rows.sort((a,b) => (a.title || a.name || '').localeCompare(b.title || b.name || ''));

        return rows;
    }

    function renderTable() {
        const rows = getFilteredData();
        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);

        const startIndex = (currentPage - 1) * pageSize;
        const visible = rows.slice(startIndex, startIndex + pageSize);

        $('kpiTableBody').innerHTML = visible.map((item, index) => {
            const pct = percentage(item);
            const kpiType = item.category || item.type || 'Leading';
            const typeClass = kpiType === 'Leading' ? 'text-leading' : 'text-lagging';
            const docs = item.documents || [];
            const targetLabel = item.target_label || (item.target ? `${item.target} ${item.unit_measure || ''}` : '-');
            const actualVal = item.realization ?? item.actual ?? 0;
            const contractorDisplay = item.contractor ? item.contractor.name : (item.contractor_name || 'Belum Ditentukan');

            return `
                <tr class="${item.id === selectedId ? 'kpi-row-selected' : ''}" onclick="selectKPI(${item.id})">
                    <td class="text-center">${startIndex + index + 1}</td>
                    <td><div class="fw-semibold text-dark">${escapeHTML(item.title || item.name)}</div></td>
                    <td><span class="badge bg-light text-secondary border fw-medium" style="font-size:11px">${escapeHTML(contractorDisplay)}</span></td>
                    <td><span class="${typeClass}">${escapeHTML(kpiType)}</span></td>
                    <td class="text-center">${escapeHTML(targetLabel)}</td>
                    <td class="text-center">${escapeHTML(formatNumber(actualVal))}</td>
                    <td>
                        <div class="progress-wrap">
                            <div class="progress-custom"><div class="progress-bar-custom ${progressColorClass(item)}" style="width:${pct}%"></div></div>
                            <span class="progress-label">${pct}%</span>
                        </div>
                    </td>
                    <td class="text-center"><span class="badge-status ${statusBadgeClass(item.status)}">${escapeHTML(item.status)}</span></td>
                    <td class="text-center">${item.year || item.period || '-'}</td>
                    <td>${escapeHTML(item.description || '-')}</td>
                    <td class="text-center">
                        <a href="javascript:void(0)" class="doc-link" onclick="showDocuments(event,${item.id})">
                            <i class="bi bi-file-earmark-text"></i> (${docs.length})
                        </a>
                    </td>
                    <td class="text-center">
                        <button class="btn-action-icon btn-action-view" title="Detail KPI" onclick="event.stopPropagation();viewKPIDetail(${item.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${canManageKPI ? `
                            <button class="btn-action-icon btn-action-edit" title="Edit KPI" onclick="event.stopPropagation();editKPI(${item.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-action-icon btn-action-delete" title="Hapus KPI" onclick="event.stopPropagation();deleteKPI(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : ''}
                    </td>
                </tr>`;
        }).join('');

        if (!visible.length) {
            $('kpiTableBody').innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted">Tidak ada data KPI yang sesuai filter.</td></tr>';
        }

        $('tableInfo').textContent = rows.length
            ? `Menampilkan ${startIndex + 1} - ${Math.min(startIndex + pageSize, rows.length)} dari ${rows.length} data`
            : 'Tidak ada data';

        let pages = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><button class="page-link" onclick="goPage(${currentPage-1})">‹</button></li>`;
        for (let p = 1; p <= totalPages; p++) {
            pages += `<li class="page-item ${p === currentPage ? 'active' : ''}"><button class="page-link" onclick="goPage(${p})">${p}</button></li>`;
        }
        pages += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><button class="page-link" onclick="goPage(${currentPage+1})">›</button></li>`;
        $('pagination').innerHTML = pages;
    }

    function goPage(page) {
        const maxPage = Math.max(1, Math.ceil(getFilteredData().length / pageSize));
        if (page < 1 || page > maxPage) return;
        currentPage = page;
        renderTable();
    }

    function updateStats() {
        const rows = getFilteredData();
        const achieved = rows.filter(x => x.status === 'Tercapai').length;
        const progress = rows.filter(x => x.status === 'Berlangsung' || x.status === 'Dalam Progress').length;
        const notAchieved = rows.filter(x => x.status === 'Tidak Tercapai' || x.status === 'Belum Tercapai' || x.status === 'Belum Mulai').length;
        const leading = rows.filter(x => (x.category || x.type) === 'Leading').length;
        const lagging = rows.filter(x => (x.category || x.type) === 'Lagging').length;
        const total = rows.length;
        const pct = n => total ? Math.round(n / total * 100) : 0;

        $('statTotal').textContent = total;
        $('statAchieved').textContent = achieved;
        $('statProgress').textContent = progress;
        $('statNot').textContent = notAchieved;
        $('statTypes').textContent = `${leading} / ${lagging}`;

        $('legendAchieved').textContent = `${achieved} (${pct(achieved)}%)`;
        $('legendProgress').textContent = `${progress} (${pct(progress)}%)`;
        $('legendNot').textContent = `${notAchieved} (${pct(notAchieved)}%)`;
    }

    function initCharts() {
        monthlyChart = new Chart($('monthlyChart'), {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'],
                datasets: [
                    {label:'Target',data:Array(12).fill(0),backgroundColor:'#3b82f6',borderRadius:2},
                    {label:'Realisasi',data:Array(12).fill(0),backgroundColor:'#10b981',borderRadius:2}
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},
                scales:{
                    x:{grid:{display:false},ticks:{font:{size:9}}},
                    y:{beginAtZero:true,ticks:{precision:0,font:{size:9}},grid:{color:'#f1f5f9'}}
                }
            }
        });

        statusChart = new Chart($('statusChart'), {
            type:'doughnut',
            data:{
                labels:['Tercapai','Dalam Progress','Belum Tercapai'],
                datasets:[{data:[0,0,0],backgroundColor:['#10b981','#f59e0b','#ef4444'],borderWidth:0,hoverOffset:4}]
            },
            options:{
                responsive:true,maintainAspectRatio:false,cutout:'68%',
                plugins:{legend:{display:false}}
            }
        });

        typeChart = new Chart($('typeChart'), {
            type:'bar',
            data:{
                labels:['Leading','Lagging'],
                datasets:[{data:[0,0],backgroundColor:['#3b82f6','#8b5cf6'],borderRadius:4}]
            },
            options:{
                responsive:true,maintainAspectRatio:false,
                plugins:{legend:{display:false}},
                scales:{
                    x:{grid:{display:false},ticks:{font:{size:10}}},
                    y:{beginAtZero:true,ticks:{precision:0,font:{size:9}},grid:{color:'#f1f5f9'}}
                }
            }
        });
    }

    function updateCharts() {
        const rows = getFilteredData();
        const achieved = rows.filter(x => x.status === 'Tercapai').length;
        const progress = rows.filter(x => x.status === 'Berlangsung' || x.status === 'Dalam Progress').length;
        const notAchieved = rows.filter(x => x.status === 'Tidak Tercapai' || x.status === 'Belum Tercapai' || x.status === 'Belum Mulai').length;
        const leading = rows.filter(x => (x.category || x.type) === 'Leading').length;
        const lagging = rows.filter(x => (x.category || x.type) === 'Lagging').length;

        statusChart.data.datasets[0].data = [achieved, progress, notAchieved];
        statusChart.update();

        typeChart.data.datasets[0].data = [leading, lagging];
        typeChart.update();

        const targets = Array(12).fill(0);
        const actuals = Array(12).fill(0);
        rows.forEach(item => {
            const startDate = item.start_date || item.start;
            const endDate = item.end_date || item.end;
            const mStart = startDate ? new Date(startDate.includes('T') ? startDate : startDate + 'T00:00:00').getMonth() : 0;
            const mEnd = endDate ? new Date(endDate.includes('T') ? endDate : endDate + 'T00:00:00').getMonth() : 11;
            const val = Number(item.targetValue ?? item.target ?? 1);
            const act = Number(item.actual ?? item.realization ?? 0);
            const span = Math.max(1, mEnd - mStart + 1);
            for(let i = mStart; i <= mEnd; i++) {
                targets[i] += val / span;
                actuals[i] += act / span;
            }
        });

        monthlyChart.data.datasets[0].data = targets.map(v => Math.round(v * 10) / 10);
        monthlyChart.data.datasets[1].data = actuals.map(v => Math.round(v * 10) / 10);
        monthlyChart.update();
    }

    function selectKPI(id) {
        selectedId = id;
        renderDetail();
        renderTable();
    }

    function renderDetail() {
        const item = kpiData.find(x => x.id === selectedId) || kpiData[0];
        if (!item) {
            $('detailPanel').innerHTML = '<div class="text-muted text-center py-4">Pilih KPI untuk melihat detail.</div>';
            $('historyBody').innerHTML = '';
            $('notesPanel').innerHTML = '<div class="text-muted text-center py-3" style="font-size:11px">Belum ada catatan evaluasi.</div>';
            return;
        }

        const pct = percentage(item);
        const docs = item.documents || [];
        const thumbs = docs.map(d => `<span class="thumb-preview-box" title="${escapeHTML(d)}"><i class="bi bi-file-earmark-pdf"></i></span>`).join('');
        const kpiType = item.category || item.type || 'Leading';
        const targetLabel = item.target_label || (item.target ? `${item.target} ${item.unit_measure || ''}` : '-');
        const actualVal = item.realization ?? item.actual ?? 0;

        $('detailPanel').innerHTML = `
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge-status ${statusBadgeClass(item.status)}">${escapeHTML(item.status)}</span>
                    <span class="ms-2 fw-semibold ${kpiType === 'Leading' ? 'text-leading' : 'text-lagging'}">${escapeHTML(kpiType)}</span>
                </div>
                <button class="btn btn-action-outline btn-sm py-0 px-2" style="font-size:11px" onclick="viewKPIDetail(${item.id})">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar
                </button>
            </div>
            <div class="detail-fields-grid mb-2">
                <span class="text-muted">Nama KPI</span><span>:</span><strong class="text-dark">${escapeHTML(item.title || item.name)}</strong>
                <span class="text-muted">Target</span><span>:</span><span>${escapeHTML(targetLabel)}</span>
                <span class="text-muted">Realisasi</span><span>:</span><span>${escapeHTML(formatNumber(actualVal))} ${escapeHTML(item.unit_measure || '')}</span>
                <span class="text-muted">Periode</span><span>:</span><span>${formatDate(item.start_date || item.start)} s/d ${formatDate(item.end_date || item.end)}</span>
                <span class="text-muted">Keterangan</span><span>:</span><span>${escapeHTML(item.description || '-')}</span>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-muted" style="font-size:11px">Persentase Pemenuhan</span>
                    <span class="fw-bold text-dark" style="font-size:11px">${pct}%</span>
                </div>
                <div class="progress-custom" style="height:8px">
                    <div class="progress-bar-custom ${progressColorClass(item)}" style="width:${pct}%"></div>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top">
                <span class="fw-semibold text-muted d-block mb-1" style="font-size:10.5px">Dokumen Terlampir (${docs.length})</span>
                <div class="d-flex gap-1 flex-wrap">${thumbs || '<span class="text-muted" style="font-size:10px">Belum ada dokumen.</span>'}</div>
            </div>
        `;

        const history = item.history || [];
        $('historyBody').innerHTML = history.length
            ? history.map(h => `<tr><td>${formatDate(h.date)}</td><td>${formatNumber(h.actual)}</td><td>${escapeHTML(h.note)}</td><td>${h.doc ? `<a href="#" class="doc-link">${escapeHTML(h.doc)}</a>` : '-'}</td></tr>`).join('')
            : '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat capaian.</td></tr>';

        renderNotesList(item);
    }

    function renderNotesList(item) {
        const notes = item.notes || [];
        if (!notes.length) {
            $('notesPanel').innerHTML = '<div class="text-muted text-center py-3" style="font-size:11px"><i class="bi bi-chat-left-dots d-block mb-1 opacity-50"></i>Belum ada catatan evaluasi.</div>';
            return;
        }

        $('notesPanel').innerHTML = notes.map(n => {
            const id = n.id || 0;
            const text = typeof n === 'string' ? n : (n.note || '');
            const author = typeof n === 'object' && n.author ? n.author : 'Admin HSSE';
            const dateStr = typeof n === 'object' && n.created_at ? formatDateTime(n.created_at) : '';

            return `
                <div class="note-item-box d-flex justify-content-between align-items-start gap-2 mb-2 p-2">
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size:10.5px">
                                <i class="bi bi-person-fill text-muted me-1"></i>${escapeHTML(author)}
                            </span>
                            ${dateStr ? `<span class="text-muted" style="font-size:9.5px">${dateStr}</span>` : ''}
                        </div>
                        <div class="note-text text-secondary" style="font-size:11px; line-height:1.35; word-break:break-word;">
                            ${escapeHTML(text)}
                        </div>
                    </div>
                    <div class="note-actions d-flex gap-1 flex-shrink-0">
                        ${id ? `
                            <button type="button" class="btn btn-link text-primary p-0" title="Edit Catatan" onclick="openEditNoteModal(${id}, ${escapeHTML(JSON.stringify(text))})">
                                <i class="bi bi-pencil" style="font-size:11px"></i>
                            </button>
                            <button type="button" class="btn btn-link text-danger p-0" title="Hapus Catatan" onclick="deleteNote(${id})">
                                <i class="bi bi-trash" style="font-size:11px"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    // ==========================================
    // VIEW ACTION: MODAL DETAIL KPI (REAL DB DATA)
    // ==========================================
    function viewKPIDetail(id) {
        selectedId = id;
        renderDetail();
        renderTable();

        // Tampilkan loading di modal
        $('kpiDetailModalTitle').innerHTML = '<i class="bi bi-bullseye me-1"></i> Memuat Detail KPI...';
        $('kpiDetailModalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <div class="mt-2 text-muted fw-semibold" style="font-size:12px">Mengambil data KPI dari database...</div>
            </div>
        `;
        kpiDetailModal.show();

        // Fetch dari endpoint /hsse-plan/{kpi} untuk memuat data database nyata
        fetch(`/hsse-plan/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(res.status === 404 ? 'Data KPI tidak ditemukan di database.' : 'Gagal memuat detail KPI.');
            }
            return res.json();
        })
        .then(data => {
            if (!data.success || !data.kpi) {
                throw new Error(data.message || 'Data KPI tidak ditemukan di database.');
            }
            const kpi = data.kpi;

            // Perbarui data lokal
            const idx = kpiData.findIndex(x => x.id === kpi.id);
            if (idx !== -1) {
                kpiData[idx] = kpi;
            }

            renderKPIDetailModal(kpi);
        })
        .catch(err => {
            kpiDetailModal.hide();
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Ditemukan',
                text: err.message || 'Data KPI yang dipilih tidak ditemukan pada sistem.'
            });
        });
    }

    function renderKPIDetailModal(item) {
        const pct = percentage(item);
        const notes = item.notes || [];
        const kpiType = item.category || item.type || 'Leading';
        const targetLabel = item.target_label || (item.target ? `${item.target} ${item.unit_measure || ''}` : '-');
        const actualVal = item.realization ?? item.actual ?? 0;
        const actualFormatted = `${formatNumber(actualVal)} ${item.unit_measure || ''}`;
        const permalink = `/hsse-plan/${item.id}`;

        $('kpiDetailPermalink').href = permalink;
        $('kpiDetailModalTitle').innerHTML = `<i class="bi bi-bullseye me-1"></i> Detail: ${escapeHTML(item.title || item.name)}`;

        $('kpiDetailModalBody').innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <span class="badge-status ${statusBadgeClass(item.status)} px-3 py-1 fs-6">${escapeHTML(item.status)}</span>
                    <span class="badge ms-2 ${kpiType === 'Leading' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-purple-subtle text-purple border'}" style="font-size:12px">
                        <i class="bi bi-tag-fill me-1"></i>${escapeHTML(kpiType)} Indicator
                    </span>
                </div>
                <span class="text-muted" style="font-size:11.5px">Unit: <strong>${escapeHTML(item.unit || 'Dieng Unit 1')}</strong></span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted" style="font-size:11px">Nama Indikator KPI</div>
                        <div class="fw-bold text-dark mt-1" style="font-size:13px">${escapeHTML(item.title || item.name)}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted" style="font-size:11px">Target Kinerja</div>
                        <div class="fw-bold text-primary mt-1" style="font-size:13px">${escapeHTML(targetLabel)}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted" style="font-size:11px">Realisasi Aktual</div>
                        <div class="fw-bold text-success mt-1" style="font-size:13px">${escapeHTML(actualFormatted)}</div>
                    </div>
                </div>
            </div>

            <div class="p-3 border rounded mb-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-dark" style="font-size:11.5px">Persentase Pemenuhan Target</span>
                    <span class="fw-bold text-dark" style="font-size:13px">${pct}%</span>
                </div>
                <div class="progress-custom" style="height:10px">
                    <div class="progress-bar-custom ${progressColorClass(item)}" style="width:${pct}%"></div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light h-100">
                        <div class="text-muted" style="font-size:11px">Periode Pelaksanaan</div>
                        <div class="fw-semibold text-dark mt-1" style="font-size:12px">
                            Tahun ${item.year || item.period || '2025'} <br>
                            <small class="text-muted">(${formatDate(item.start_date || item.start)} s/d ${formatDate(item.end_date || item.end)})</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light h-100">
                        <div class="text-muted" style="font-size:11px">Keterangan / Deskripsi</div>
                        <div class="text-dark mt-1" style="font-size:12px">${escapeHTML(item.description || '-')}</div>
                    </div>
                </div>
            </div>

            <!-- Section Catatan di dalam Modal Detail -->
            <div class="border-top pt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0" style="font-size:12.5px">
                        <i class="bi bi-chat-left-text me-1 text-success"></i> Catatan & Tindak Lanjut (${notes.length})
                    </h6>
                    <button type="button" class="btn btn-action-outline btn-sm py-1 px-2" style="font-size:11px" onclick="openNoteModal();">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Catatan
                    </button>
                </div>
                <div class="notes-modal-list" style="max-height:160px; overflow-y:auto;">
                    ${notes.length ? notes.map(n => {
                        const id = n.id || 0;
                        const text = typeof n === 'string' ? n : (n.note || '');
                        const author = typeof n === 'object' && n.author ? n.author : 'Admin HSSE';
                        const dateStr = typeof n === 'object' && n.created_at ? formatDateTime(n.created_at) : '';
                        return `
                            <div class="note-item-box p-2 mb-2 bg-light border-start border-3 border-success rounded d-flex justify-content-between align-items-start gap-2">
                                <div class="flex-grow-1" style="min-width:0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold text-dark" style="font-size:11px">
                                            <i class="bi bi-person-fill text-muted me-1"></i>${escapeHTML(author)}
                                        </span>
                                        ${dateStr ? `<span class="text-muted" style="font-size:10px">${dateStr}</span>` : ''}
                                    </div>
                                    <div class="text-secondary" style="font-size:11.5px; line-height:1.35; word-break:break-word;">
                                        ${escapeHTML(text)}
                                    </div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    ${id ? `
                                        <button type="button" class="btn btn-link text-primary p-0" title="Edit" onclick="openEditNoteModal(${id}, ${escapeHTML(JSON.stringify(text))})">
                                            <i class="bi bi-pencil" style="font-size:11px"></i>
                                        </button>
                                        <button type="button" class="btn btn-link text-danger p-0" title="Hapus" onclick="deleteNote(${id})">
                                            <i class="bi bi-trash" style="font-size:11px"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    }).join('') : '<div class="text-muted text-center py-3 bg-light rounded" style="font-size:11px">Belum ada catatan evaluasi untuk KPI ini.</div>'}
                </div>
            </div>
        `;
    }

    // ==========================================
    // CRUD CATATAN (NOTES) DENGAN SERVER-SIDE SYNC
    // ==========================================
    function openNoteModal() {
        if (!selectedId) {
            Swal.fire('Perhatian', 'Pilih salah satu KPI terlebih dahulu.', 'info');
            return;
        }
        const item = kpiData.find(x => x.id === selectedId);
        $('noteForm').reset();
        $('noteKpiName').value = item ? (item.title || item.name) : '-';
        noteModal.show();
    }

    $('noteForm').addEventListener('submit', e => {
        e.preventDefault();
        if (!selectedId) return;

        const noteText = $('noteText').value.trim();
        const noteAuthor = $('noteAuthor').value.trim();
        if (!noteText) return;

        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch(`/hsse-plan/${selectedId}/notes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                note: noteText,
                author: noteAuthor || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success && data.note) {
                const item = kpiData.find(x => x.id === selectedId);
                if (item) {
                    if (!item.notes) item.notes = [];
                    item.notes.unshift(data.note);
                }
                noteModal.hide();
                $('noteText').value = '';
                $('noteAuthor').value = '';
                renderDetail();

                // Refresh modal detail jika sedang terbuka
                if ($('kpiDetailModal').classList.contains('show') && item) {
                    renderKPIDetailModal(item);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message || 'Catatan berhasil ditambahkan.',
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menyimpan catatan.', 'error');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan catatan.', 'error');
        });
    });

    function openEditNoteModal(noteId, currentText) {
        $('editNoteId').value = noteId;
        $('editNoteText').value = currentText;
        editNoteModal.show();
    }

    $('editNoteForm').addEventListener('submit', e => {
        e.preventDefault();
        if (!selectedId) return;

        const noteId = $('editNoteId').value;
        const noteText = $('editNoteText').value.trim();
        if (!noteId || !noteText) return;

        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch(`/hsse-plan/${selectedId}/notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ note: noteText })
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success && data.note) {
                const item = kpiData.find(x => x.id === selectedId);
                if (item && item.notes) {
                    const idx = item.notes.findIndex(n => (n.id || n) == noteId);
                    if (idx !== -1) {
                        item.notes[idx] = data.note;
                    }
                }
                editNoteModal.hide();
                renderDetail();

                // Refresh modal detail jika sedang terbuka
                if ($('kpiDetailModal').classList.contains('show') && item) {
                    renderKPIDetailModal(item);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message || 'Catatan berhasil diperbarui.',
                    timer: 1800,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal memperbarui catatan.', 'error');
            }
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan sistem saat memperbarui catatan.', 'error');
        });
    });

    function deleteNote(noteId) {
        if (!selectedId || !noteId) return;

        Swal.fire({
            title: 'Hapus Catatan?',
            text: 'Apakah Anda yakin ingin menghapus catatan evaluasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/hsse-plan/${selectedId}/notes/${noteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const item = kpiData.find(x => x.id === selectedId);
                        if (item && item.notes) {
                            item.notes = item.notes.filter(n => (n.id || n) != noteId);
                        }
                        renderDetail();

                        // Refresh modal detail jika sedang terbuka
                        if ($('kpiDetailModal').classList.contains('show') && item) {
                            renderKPIDetailModal(item);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: data.message || 'Catatan berhasil dihapus.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Gagal', data.message || 'Tidak dapat menghapus catatan.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus catatan.', 'error');
                });
            }
        });
    }

    // ==========================================
    // MODAL TAMBAH / EDIT KPI
    // ==========================================
    function openKPIModal(data = null) {
        $('kpiForm').reset();
        if (data) {
            $('kpiModalTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Edit KPI Kontraktor';
            $('kpiId').value = data.id;
            $('kpiName').value = data.title || data.name || '';
            if ($('kpiContractorId')) $('kpiContractorId').value = data.contractor_id || '';
            $('kpiType').value = data.category || data.type || 'Leading';
            $('kpiUnit').value = data.unit || 'Dieng Unit 1';
            $('kpiTargetValue').value = data.target ?? data.targetValue ?? 0;
            $('kpiUnitMeasure').value = data.unit_measure || '';
            $('kpiActual').value = data.realization ?? data.actual ?? 0;
            $('kpiPeriod').value = data.year || data.period || '2025';
            $('kpiStart').value = data.start_date || data.start || '2025-01-01';
            $('kpiEnd').value = data.end_date || data.end || '2025-12-31';
            $('kpiStatus').value = data.status === 'Berlangsung' ? 'Dalam Progress' : (data.status === 'Tidak Tercapai' ? 'Belum Tercapai' : data.status);
            $('kpiDescription').value = data.description || '';
        } else {
            $('kpiModalTitle').innerHTML = '<i class="bi bi-bullseye me-1"></i> Tambah KPI Kontraktor';
            $('kpiId').value = '';
            if ($('kpiContractorId')) $('kpiContractorId').value = '';
            $('kpiPeriod').value = '2025';
            $('kpiStart').value = '2025-01-01';
            $('kpiEnd').value = '2025-12-31';
            $('kpiStatus').value = 'Dalam Progress';
            $('kpiUnit').value = 'Dieng Unit 1';
        }
        kpiModal.show();
    }

    function editKPI(id) {
        const item = kpiData.find(x => x.id === id);
        if (item) openKPIModal(item);
    }

    function deleteKPI(id) {
        const item = kpiData.find(x => x.id === id);
        Swal.fire({
            title: 'Hapus KPI?',
            html: `Apakah Anda yakin ingin menghapus indikator <b>"${escapeHTML(item ? (item.title || item.name) : '')}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/hsse-plan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        kpiData = kpiData.filter(x => x.id !== id);
                        if (selectedId === id) selectedId = kpiData[0] ? kpiData[0].id : null;
                        applyAll();
                        Swal.fire('Terhapus!', 'Indikator KPI berhasil dihapus.', 'success');
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menghapus KPI.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus KPI.', 'error');
                });
            }
        });
    }

    $('kpiForm').addEventListener('submit', e => {
        e.preventDefault();
        const id = $('kpiId').value;
        const payload = {
            title: $('kpiName').value,
            contractor_id: $('kpiContractorId') ? ($('kpiContractorId').value || null) : null,
            category: $('kpiType').value,
            unit: $('kpiUnit').value,
            target: Number($('kpiTargetValue').value),
            unit_measure: $('kpiUnitMeasure').value,
            realization: Number($('kpiActual').value),
            year: Number($('kpiPeriod').value),
            start_date: $('kpiStart').value,
            end_date: $('kpiEnd').value,
            status: $('kpiStatus').value,
            description: $('kpiDescription').value
        };

        const isUpdate = !!id;
        const url = isUpdate ? `/hsse-plan/${id}` : '/hsse-plan';
        const method = isUpdate ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.kpi) {
                const kpi = data.kpi;
                if (isUpdate) {
                    const idx = kpiData.findIndex(x => x.id == id);
                    if (idx !== -1) kpiData[idx] = kpi;
                } else {
                    kpiData.unshift(kpi);
                    selectedId = kpi.id;
                }
                kpiModal.hide();
                applyAll();
                Swal.fire('Berhasil!', data.message || 'Data KPI berhasil disimpan.', 'success');
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menyimpan KPI.', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan KPI.', 'error');
        });
    });

    // Dokumen Mockup Handlers
    function showDocuments(event, id) {
        event.preventDefault();
        event.stopPropagation();
        selectedId = id;
        const item = kpiData.find(x => x.id === id);
        if (!item) return;

        const docs = item.documents || [];
        $('documentList').innerHTML = docs.length
            ? docs.map(d => `<div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-light"><div class="d-flex align-items-center gap-2"><i class="bi bi-file-earmark-pdf fs-5 text-danger"></i><span class="fw-semibold">${escapeHTML(d)}</span></div><a href="#" class="btn btn-sm btn-outline-primary">Unduh</a></div>`).join('')
            : '<div class="text-muted text-center py-3">Belum ada dokumen yang diunggah.</div>';

        documentModal.show();
    }

    $('docUploadForm').addEventListener('submit', e => {
        e.preventDefault();
        const item = kpiData.find(x => x.id === selectedId);
        if (!item) return;

        item.documents = item.documents || [];
        item.documents.push($('docName').value);
        $('docName').value = '';
        $('docFile').value = '';
        showDocuments(e, selectedId);
        renderTable();
        renderDetail();
        Swal.fire('Berhasil!', 'Dokumen berhasil diunggah.', 'success');
    });

    function resetFilters() {
        $('filterStart').value = '';
        $('filterEnd').value = '';
        if ($('filterContractor')) $('filterContractor').value = '';
        $('filterType').value = '';
        $('filterStatus').value = '';
        $('filterPeriod').value = '2025';
        $('globalSearch').value = '';
        $('tableSearch').value = '';
        $('sortBy').value = 'name';
        applyAll();
    }

    function exportPDF() {
        const params = new URLSearchParams();
        if ($('filterStart') && $('filterStart').value) params.set('start_date', $('filterStart').value);
        if ($('filterEnd') && $('filterEnd').value) params.set('end_date', $('filterEnd').value);
        if ($('filterContractor') && $('filterContractor').value) params.set('contractor_id', $('filterContractor').value);
        if ($('filterType') && $('filterType').value) params.set('type', $('filterType').value);
        if ($('filterStatus') && $('filterStatus').value) params.set('status', $('filterStatus').value);
        if ($('filterPeriod') && $('filterPeriod').value) params.set('period', $('filterPeriod').value);
        if ($('globalSearch') && $('globalSearch').value) params.set('search', $('globalSearch').value);
        window.location.href = '{{ route('hsse-plan.export') }}?' + params.toString();
    }

    function applyAll() {
        renderTable();
        updateStats();
        updateCharts();
        renderDetail();
    }

    // Filter Listeners
    ['filterStart','filterEnd','filterContractor','filterType','filterStatus','filterPeriod','sortBy'].forEach(id => {
        if ($(id)) $(id).addEventListener('change', () => { currentPage = 1; applyAll(); });
    });

    ['globalSearch','tableSearch'].forEach(id => {
        $(id).addEventListener('input', () => { currentPage = 1; applyAll(); });
    });

    // Inisialisasi awal
    initCharts();
    applyAll();
</script>
@endpush

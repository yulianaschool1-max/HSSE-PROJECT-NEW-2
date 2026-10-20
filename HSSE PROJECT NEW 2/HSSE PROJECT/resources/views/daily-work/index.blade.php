@extends('layouts.app', [
    'title' => 'Daily Work',
    'activeModule' => 'daily-work'
])

@push('styles')
<style>
    .stats-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 16px;
    }

    @media (max-width: 900px) {
        .stats-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-clickable {
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        user-select: none;
        position: relative;
    }

    .stat-card-clickable:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        border-color: #0d9488;
    }

    .stat-card-clickable:hover .stat-arrow {
        color: #0d9488 !important;
        transform: translateX(3px);
    }

    .stat-arrow {
        transition: all 0.18s ease;
        font-size: 15px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        padding-left: 8px;
    }

    .stat-card-clickable:focus-visible {
        outline: 2px solid #0d9488;
        outline-offset: 2px;
    }

    .daily-grid-top {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .filter-grid-daily {
        display: grid;
        grid-template-columns: 180px 150px 150px 140px 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .mode-pill-group {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
        gap: 3px;
    }

    .mode-pill-btn {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 11.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .mode-pill-btn.active {
        background: #0d9488;
        color: #ffffff;
        box-shadow: 0 1px 3px rgba(13, 148, 136, 0.3);
    }

    .pie-chart-wrap {
        position: relative;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pie-empty-state {
        text-align: center;
        color: #94a3b8;
        padding: 28px 16px;
    }

    .pie-legend-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed #e2e8f0;
    }

    .pie-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11.5px;
        color: #334155;
    }

    .pie-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .pie-legend-val {
        margin-left: auto;
        font-weight: 600;
        color: #0f172a;
    }

    .badge-ptw {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 5px;
        display: inline-block;
    }

    .badge-sikka {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 5px;
        display: inline-block;
    }

    .badge-company {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 5px;
        display: inline-block;
    }

    .master-tab-btn {
        border: none;
        background: transparent;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        padding: 8px 16px;
        border-bottom: 2px solid transparent;
        transition: all 0.15s;
    }

    .master-tab-btn.active {
        color: #0d9488;
        border-bottom-color: #0d9488;
        background: #f0fdfa;
    }

    @media (max-width: 1100px) {
        .daily-grid-top {
            grid-template-columns: 1fr;
        }
        .filter-grid-daily {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .filter-grid-daily {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box teal">
            <i class="bi bi-calendar2-week"></i>
        </div>
        <div>
            <h1 class="page-heading">Daily Work</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Daily Work</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button class="btn-action-primary" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i> Tambah Pekerjaan
            </button>
            <button class="btn-action-outline" onclick="openMasterModal()">
                <i class="bi bi-sliders2"></i> Kelola Pilihan Master
            </button>
        @endadminManagement
        @canExportPdf
        <button class="btn-action-outline" onclick="exportPDF()">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </button>
        @endcanExportPdf
    </div>
</div>

<!-- STATS CARDS (INTERAKTIF & RINGKAS) -->
<div class="stats-grid-3">
    <!-- Card 1: Total Pekerjaan Hari Ini -->
    <div class="stat-card stat-card-clickable" id="cardStatToday" onclick="filterByCardToday()" onkeydown="handleCardKeydown(event, filterByCardToday)" role="button" tabindex="0" title="Klik untuk melihat pekerjaan hari ini">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-journal-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title" id="statTitleToday">Total Pekerjaan Hari Ini</div>
            <div class="stat-number" id="statTotalToday">{{ $stats['total_today'] ?? 0 }}</div>
        </div>
        <div class="stat-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>

    <!-- Card 2: Perusahaan Terlibat -->
    <div class="stat-card stat-card-clickable" id="cardStatCompanies" onclick="filterByCardCompanies()" onkeydown="handleCardKeydown(event, filterByCardCompanies)" role="button" tabindex="0" title="Klik untuk melihat data per perusahaan hari ini">
        <div class="stat-icon-wrapper rounded-circle" style="background:#dcfce7; color:#15803d;">
            <i class="bi bi-building-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Perusahaan Terlibat</div>
            <div class="stat-number text-success" id="statCompaniesToday">{{ $stats['companies_today'] ?? 0 }}</div>
        </div>
        <div class="stat-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>

    <!-- Card 3: Total Seluruh Data -->
    <div class="stat-card stat-card-clickable" id="cardStatHistory" onclick="filterByCardHistory()" onkeydown="handleCardKeydown(event, filterByCardHistory)" role="button" tabindex="0" title="Klik untuk melihat seluruh riwayat pekerjaan">
        <div class="stat-icon-wrapper rounded-circle" style="background:#f1f5f9; color:#475569;">
            <i class="bi bi-database"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title" id="statTitleHistory">Total Seluruh Data</div>
            <div class="stat-number text-secondary" id="statTotalHistory">{{ $stats['total_all'] ?? ($stats['total_history'] ?? 0) }}</div>
        </div>
        <div class="stat-arrow">
            <i class="bi bi-chevron-right"></i>
        </div>
    </div>
</div>

<!-- CHARTS ROW: PIE CHART PER PERUSAHAAN (HARI INI) & RINGKASAN KLASIFIKASI -->
<div class="daily-grid-top">
    <!-- Diagram Lingkaran Pekerjaan per Perusahaan -->
    <div class="chart-box-card">
        <div class="chart-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="chart-title">Distribusi Pekerjaan per Perusahaan</h2>
                <p class="chart-subtitle mb-0" id="pieChartSubtitle">Pekerjaan mulai hari ini ({{ \Carbon\Carbon::parse($today)->locale('id')->isoFormat('D MMMM Y') }})</p>
            </div>
            <span class="badge bg-teal-subtle text-teal border border-teal px-2 py-1" style="font-size:11px" id="todayPieBadge">
                {{ $stats['total_today'] ?? 0 }} Pekerjaan
            </span>
        </div>

        <div class="chart-canvas-container" style="height: 240px; position:relative;">
            <div id="pieEmptyState" class="pie-empty-state {{ ($stats['total_today'] ?? 0) > 0 ? 'd-none' : '' }}">
                <div class="mb-2"><i class="bi bi-pie-chart text-muted" style="font-size: 38px;"></i></div>
                <div class="fw-semibold text-dark" id="pieEmptyTitle" style="font-size: 13px;">Tidak Ada Pekerjaan yang Dimulai Hari Ini</div>
                <div class="text-muted" id="pieEmptyDesc" style="font-size: 11.5px;">Belum ada pekerjaan dengan tanggal mulai hari ini.</div>
            </div>
            <canvas id="companyPieChart" class="{{ ($stats['total_today'] ?? 0) > 0 ? '' : 'd-none' }}"></canvas>
        </div>

        <div class="pie-legend-grid" id="pieLegendGrid"></div>
    </div>

    <!-- Ringkasan Klasifikasi PTW & SIKKA Hari Ini -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Klasifikasi PTW & Izin SIKKA Hari Ini</h2>
        </div>

        <div class="p-3">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11.5px;">PTW: Cold Work</span>
                    <span class="fw-bold text-dark" id="countPtwCold">0</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div id="progPtwCold" class="progress-bar bg-info" style="width: 0%;"></div>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11.5px;">PTW: Hot Work</span>
                    <span class="fw-bold text-dark" id="countPtwHot">0</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div id="progPtwHot" class="progress-bar bg-danger" style="width: 0%;"></div>
                </div>
            </div>

            <hr class="my-3">

            <div class="fw-semibold text-dark mb-2" style="font-size: 11.5px;">Distribusi Izin SIKKA Hari Ini:</div>
            <div id="sikkaChipsContainer" class="d-flex flex-wrap gap-1"></div>
        </div>
    </div>
</div>

<!-- SECTION: TABEL PEKERJAAN HARI INI & RIWAYAT -->
<div class="table-card">
    <div class="table-topbar flex-wrap gap-2">
        <div>
            <h2 class="table-section-title mb-1" id="tableHeaderTitle">Pekerjaan Hari Ini</h2>
            <div class="text-muted" style="font-size: 11.5px;" id="tableHeaderSubtitle">
                Menampilkan daftar pekerjaan yang aktif pada tanggal {{ \Carbon\Carbon::parse($today)->locale('id')->isoFormat('D MMMM Y') }}
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
            <!-- Mode Switcher: Hari Ini vs Semua Riwayat -->
            <div class="mode-pill-group">
                <button type="button" class="mode-pill-btn active" id="btnModeToday" onclick="setMode('today')">
                    <i class="bi bi-calendar-check"></i> Hari Ini
                </button>
                <button type="button" class="mode-pill-btn" id="btnModeAll" onclick="setMode('all')">
                    <i class="bi bi-clock-history"></i> Semua Riwayat
                </button>
            </div>

            <!-- Page Size -->
            <div class="d-flex align-items-center gap-1">
                <span class="text-muted" style="font-size:11.5px">Baris:</span>
                <select id="perPage" class="form-select form-select-sm" style="width: 65px; font-size:11.5px">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="p-3 bg-light border-bottom">
        <div class="filter-grid-daily">
            <!-- Filter Perusahaan -->
            <div>
                <select id="companyFilter" class="form-select form-select-filter">
                    <option value="">Semua Perusahaan</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter PTW Classification -->
            <div>
                <select id="ptwFilter" class="form-select form-select-filter">
                    <option value="">Semua PTW</option>
                    @foreach($ptwClassifications as $p)
                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter SIKKA -->
            <div>
                <select id="sikkaFilter" class="form-select form-select-filter">
                    <option value="">Semua SIKKA</option>
                    @foreach($sikkas as $s)
                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal -->
            <div class="filter-input-wrap" id="dateFilterWrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="date" id="dateFilter" class="form-control form-control-filter" title="Pilih Tanggal Pekerjaan" value="{{ $today }}">
            </div>

            <!-- Search Bar -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" id="searchInput" class="form-control form-control-filter" placeholder="Cari nama pekerjaan, lokasi...">
            </div>

            <!-- Reset Filter -->
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilters()">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR PEKERJAAN -->
    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No.</th>
                    <th>Nama Pekerjaan</th>
                    <th style="width: 140px;">Perusahaan</th>
                    <th style="width: 130px;">PTW Classification</th>
                    <th style="width: 130px;">SIKKA</th>
                    <th style="width: 95px;">Tgl Mulai</th>
                    <th style="width: 95px;">Tgl Selesai</th>
                    <th style="width: 130px;">Lokasi</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="workTableBody"></tbody>
        </table>
    </div>

    <div class="table-bottom-bar">
        <div id="tableInfo">Memuat data...</div>
        <div id="pagination" class="d-flex gap-1"></div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH / EDIT PEKERJAAN -->
<!-- ========================================================================= -->
<div class="modal fade" id="workModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <form id="workForm">
                <div class="modal-header modal-header-themed teal">
                    <h5 class="modal-title" id="workModalTitle">
                        <i class="bi bi-calendar2-check me-1"></i> Tambah Pekerjaan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="workEditId">
                    <div class="row g-3">
                        <!-- Nama Pekerjaan -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Nama Pekerjaan <span class="text-danger">*</span></label>
                            <input id="workName" class="form-control form-control-sm" placeholder="Contoh: Pengecekan Turbin Unit 1" required>
                        </div>

                        <!-- Perusahaan -->
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px">Perusahaan <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" onclick="openMasterModal('company')" class="text-decoration-none" style="font-size: 11px;">+ Kelola</a>
                            </div>
                            <select id="workCompany" class="form-select form-select-sm" required>
                                <option value="">Pilih Perusahaan</option>
                                @foreach($companies as $c)
                                    @if($c->is_active)
                                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- PTW Classification -->
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px">PTW Classification <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" onclick="openMasterModal('ptw')" class="text-decoration-none" style="font-size: 11px;">+ Kelola</a>
                            </div>
                            <select id="workPtw" class="form-select form-select-sm" required>
                                <option value="">Pilih PTW</option>
                                @foreach($ptwClassifications as $p)
                                    @if($p->is_active)
                                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- SIKKA -->
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px">SIKKA <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" onclick="openMasterModal('sikka')" class="text-decoration-none" style="font-size: 11px;">+ Kelola</a>
                            </div>
                            <select id="workSikka" class="form-select form-select-sm" required>
                                <option value="">Pilih SIKKA</option>
                                @foreach($sikkas as $s)
                                    @if($s->is_active)
                                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" id="workStart" class="form-control form-control-sm" required value="{{ $today }}">
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" id="workEnd" class="form-control form-control-sm" required value="{{ $today }}">
                        </div>

                        <!-- Lokasi -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Lokasi Pekerjaan <span class="text-danger">*</span></label>
                            <input id="workLocation" class="form-control form-control-sm" placeholder="Contoh: Area Plant Utama / Wellpad 28" required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Deskripsi / Keterangan</label>
                            <textarea id="workDescription" class="form-control form-control-sm" rows="3" placeholder="Rincian catatan kegiatan pekerjaan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4" id="btnSubmitWork">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL PEKERJAAN -->
<!-- ========================================================================= -->
<div class="modal fade" id="workDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed teal">
                <h5 class="modal-title" id="workDetailModalTitle">
                    <i class="bi bi-calendar2-week me-1"></i> Detail Pekerjaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="workDetailModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-teal me-2" role="status"></div>
                    Memuat data detail pekerjaan...
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                <a href="#" id="workDetailPermalink" class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Penuh
                </a>
                <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: KELOLA PILIHAN MASTER (PERUSAHAAN, PTW, SIKKA) -->
<!-- ========================================================================= -->
<div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed teal">
                <h5 class="modal-title">
                    <i class="bi bi-sliders2 me-1"></i> Kelola Pilihan Master Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Master Tabs -->
            <div class="border-bottom bg-light px-3 d-flex gap-2">
                <button type="button" class="master-tab-btn active" id="tabBtnCompany" onclick="switchMasterTab('company')">
                    <i class="bi bi-building me-1"></i> Perusahaan
                </button>
                <button type="button" class="master-tab-btn" id="tabBtnPtw" onclick="switchMasterTab('ptw')">
                    <i class="bi bi-shield-check me-1"></i> PTW Classification
                </button>
                <button type="button" class="master-tab-btn" id="tabBtnSikka" onclick="switchMasterTab('sikka')">
                    <i class="bi bi-file-earmark-check me-1"></i> SIKKA
                </button>
            </div>

            <div class="modal-body p-4">
                <!-- TAB 1: PERUSAHAAN -->
                <div id="tabContentCompany" class="master-tab-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Daftar Perusahaan (Internal & Mitra)</h6>
                            <small class="text-muted">Perusahaan yang terikat pada data pekerjaan tidak dapat dihapus sembarangan.</small>
                        </div>
                    </div>

                    <!-- Form Tambah Perusahaan -->
                    <div class="input-group mb-3">
                        <input type="text" id="newCompanyName" class="form-control form-control-sm" placeholder="Nama perusahaan baru...">
                        <button type="button" class="btn btn-sm btn-teal" onclick="submitAddCompany()">
                            <i class="bi bi-plus-lg"></i> Tambah Perusahaan
                        </button>
                    </div>

                    <!-- Table Perusahaan -->
                    <div class="table-responsive border rounded" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nama Perusahaan</th>
                                    <th style="width: 100px; text-align: center;">Status</th>
                                    <th style="width: 120px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="companyMasterTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: PTW CLASSIFICATION -->
                <div id="tabContentPtw" class="master-tab-content d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Daftar Pilihan PTW Classification</h6>
                            <small class="text-muted">Klasifikasi izin kerja utama (contoh: Cold Work, Hot Work).</small>
                        </div>
                    </div>

                    <!-- Form Tambah PTW -->
                    <div class="input-group mb-3">
                        <input type="text" id="newPtwName" class="form-control form-control-sm" placeholder="Nama klasifikasi PTW baru...">
                        <button type="button" class="btn btn-sm btn-teal" onclick="submitAddPtw()">
                            <i class="bi bi-plus-lg"></i> Tambah PTW
                        </button>
                    </div>

                    <!-- Table PTW -->
                    <div class="table-responsive border rounded" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nama Klasifikasi</th>
                                    <th style="width: 120px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="ptwMasterTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: SIKKA -->
                <div id="tabContentSikka" class="master-tab-content d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Daftar Pilihan Izin SIKKA</h6>
                            <small class="text-muted">Surat Izin Keselamatan Kerja Khusus (Ketinggian, Penggalian, dll).</small>
                        </div>
                    </div>

                    <!-- Form Tambah SIKKA -->
                    <div class="input-group mb-3">
                        <input type="text" id="newSikkaName" class="form-control form-control-sm" placeholder="Nama jenis SIKKA baru...">
                        <button type="button" class="btn btn-sm btn-teal" onclick="submitAddSikka()">
                            <i class="bi bi-plus-lg"></i> Tambah SIKKA
                        </button>
                    </div>

                    <!-- Table SIKKA -->
                    <div class="table-responsive border rounded" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Jenis SIKKA</th>
                                    <th style="width: 120px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="sikkaMasterTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PILIH PERUSAHAAN TERLIBAT HARI INI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalSelectCompanyToday" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content shadow-sm border-0">
            <div class="modal-header py-3 px-4 border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px; height:34px; background:#dcfce7; color:#15803d; font-size:16px;">
                        <i class="bi bi-building-check"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="modalCompanyDateTitle">Perusahaan Terlibat Hari Ini</h6>
                        <small class="text-muted" id="modalCompanyDateSubtitle">Pilih perusahaan untuk menyaring pekerjaan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="list-group list-group-flush border rounded-3 overflow-hidden" id="modalCompanyListContainer">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-teal" onclick="selectCompanyAndFilter('')">
                    <i class="bi bi-grid me-1"></i> Tampilkan Semua Perusahaan
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const todayStr = '{{ $today }}';
    const canManageDailyWork = {{ Auth::user()?->isAdminManagement() ? 'true' : 'false' }};

    // Master data in memory
    let masterCompanies = @json($companies ?? []);
    let masterPtws = @json($ptwClassifications ?? []);
    let masterSikkas = @json($sikkas ?? []);
    let currentCompaniesToday = @json($stats['companies_list_today'] ?? []);

    // Current works list & pagination
    let currentWorks = @json($works ?? []);
    let currentPage = 1;
    let currentMode = 'today'; // 'today' or 'all'
    let companyPieChart = null;

    const $ = id => document.getElementById(id);

    const workModal = new bootstrap.Modal($('workModal'));
    const workDetailModal = new bootstrap.Modal($('workDetailModal'));
    const masterModal = new bootstrap.Modal($('masterModal'));
    const companySelectModal = $('modalSelectCompanyToday') ? new bootstrap.Modal($('modalSelectCompanyToday')) : null;

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }

    function formatDate(date) {
        if (!date) return '-';
        const strVal = String(date).slice(0, 10);
        const parts = strVal.split('-');
        if (parts.length === 3) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const day = parts[2];
            const mIdx = parseInt(parts[1], 10) - 1;
            const year = parts[0];
            return `${day} ${months[mIdx] || parts[1]} ${year}`;
        }
        return strVal;
    }

    // =========================================================================
    // MODE SWITCHING & FILTERING
    // =========================================================================
    function setMode(mode) {
        currentMode = mode;
        if (mode === 'today') {
            $('btnModeToday').classList.add('active');
            $('btnModeAll').classList.remove('active');
            // Mode Hari Ini: set input tanggal ke default tanggal hari ini
            $('dateFilter').value = todayStr;
            updateHeaderLabels(todayStr);
        } else {
            $('btnModeAll').classList.add('active');
            $('btnModeToday').classList.remove('active');
            // Mode Semua Riwayat: kosongkan input tanggal agar memuat seluruh riwayat
            $('dateFilter').value = '';
            updateHeaderLabels(null);
        }
        currentPage = 1;
        fetchWorks();
    }

    function resetFilters() {
        $('companyFilter').value = '';
        $('ptwFilter').value = '';
        $('sikkaFilter').value = '';
        $('searchInput').value = '';

        // Reset filter tanggal sesuai mode yang sedang aktif
        if (currentMode === 'today') {
            $('dateFilter').value = todayStr;
            updateHeaderLabels(todayStr);
        } else {
            $('dateFilter').value = '';
            updateHeaderLabels(null);
        }

        currentPage = 1;
        fetchWorks();
    }

    function updateHeaderLabels(selectedDate) {
        if (currentMode === 'today') {
            const d = selectedDate || todayStr;
            const dFormatted = formatDate(d);
            const isToday = (d === todayStr);

            $('tableHeaderTitle').textContent = isToday ? 'Pekerjaan Hari Ini' : `Pekerjaan Tanggal ${dFormatted}`;
            $('tableHeaderSubtitle').textContent = `Tanggal: ${dFormatted}`;

            if ($('pieChartSubtitle')) {
                $('pieChartSubtitle').textContent = isToday
                    ? `Pekerjaan mulai hari ini (${dFormatted})`
                    : `Pekerjaan mulai tanggal ${dFormatted}`;
            }
            if ($('statTitleToday')) {
                $('statTitleToday').textContent = isToday
                    ? 'Total Pekerjaan Hari Ini'
                    : `Pekerjaan Tanggal ${dFormatted}`;
            }
        } else {
            if (selectedDate) {
                const dFormatted = formatDate(selectedDate);
                $('tableHeaderTitle').textContent = `Riwayat Pekerjaan (${dFormatted})`;
                $('tableHeaderSubtitle').textContent = `Tanggal: ${dFormatted}`;
                if ($('pieChartSubtitle')) {
                    $('pieChartSubtitle').textContent = `Distribusi perusahaan pada tanggal ${dFormatted}`;
                }
            } else {
                $('tableHeaderTitle').textContent = 'Semua Riwayat Pekerjaan';
                $('tableHeaderSubtitle').textContent = 'Seluruh data riwayat pekerjaan';
                if ($('pieChartSubtitle')) {
                    $('pieChartSubtitle').textContent = 'Distribusi perusahaan dari seluruh riwayat';
                }
            }
            if ($('statTitleToday')) {
                $('statTitleToday').textContent = 'Total Pekerjaan Hari Ini';
            }
        }
    }

    function fetchWorks() {
        const params = new URLSearchParams();
        params.set('date_mode', currentMode);

        const comp = $('companyFilter')?.value;
        const ptw = $('ptwFilter')?.value;
        const sikka = $('sikkaFilter')?.value;
        const searchVal = $('searchInput')?.value?.trim();
        const dateVal = $('dateFilter')?.value;

        if (comp) params.set('company', comp);
        if (ptw) params.set('ptw_classification', ptw);
        if (sikka) params.set('sikka', sikka);
        if (searchVal) params.set('search', searchVal);
        if (dateVal) params.set('date', dateVal);

        fetch(`/daily-work?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentWorks = data.works || [];
                renderTable();
                if (data.stats) updateStatsDisplay(data.stats);
                if (data.company_distribution) renderPieChart(data.company_distribution);
                updateClassificationSummary(currentWorks);
                updateHeaderLabels(dateVal);
            }
        })
        .catch(err => {
            console.error('Failed to fetch works:', err);
        });
    }

    // =========================================================================
    // RENDERING TABLE & PAGINATION
    // =========================================================================
    function renderTable() {
        const perPage = Number($('perPage').value) || 10;
        const totalItems = currentWorks.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / perPage));

        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * perPage;
        const pageItems = currentWorks.slice(startIndex, startIndex + perPage);

        if (pageItems.length === 0) {
            const curDate = $('dateFilter')?.value || todayStr;
            const dFormatted = formatDate(curDate);
            const emptyMessage = currentMode === 'today'
                ? (curDate === todayStr
                    ? `Tidak ada pekerjaan yang dimulai pada hari ini (${dFormatted}).`
                    : `Tidak ada pekerjaan yang dimulai pada tanggal ${dFormatted}.`)
                : 'Tidak ada data pekerjaan yang sesuai dengan filter.';
            $('workTableBody').innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                        ${escapeHtml(emptyMessage)}
                    </td>
                </tr>
            `;
            $('tableInfo').textContent = 'Tidak ada data';
            $('pagination').innerHTML = '';
            return;
        }

        $('workTableBody').innerHTML = pageItems.map((w, idx) => {
            const no = startIndex + idx + 1;
            const companyName = w.unit || w.company || '-';
            const ptwName = w.ptw_classification || '-';
            const sikkaName = w.sikka || '-';

            return `
                <tr>
                    <td class="text-center fw-semibold text-muted">${no}</td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHtml(w.name)}</div>
                        <small class="text-muted">#DW-${String(w.id).padStart(4, '0')}</small>
                    </td>
                    <td><span class="badge-company">${escapeHtml(companyName)}</span></td>
                    <td><span class="badge-ptw">${escapeHtml(ptwName)}</span></td>
                    <td><span class="badge-sikka">${escapeHtml(sikkaName)}</span></td>
                    <td style="font-size:11.5px;">${formatDate(w.start_date || w.start)}</td>
                    <td style="font-size:11.5px;">${formatDate(w.end_date || w.end)}</td>
                    <td style="font-size:12px;">${escapeHtml(w.location || '-')}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn-action-icon btn-action-view" title="Detail Pekerjaan" onclick="viewWorkDetail(${w.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            ${canManageDailyWork ? `
                                <button type="button" class="btn-action-icon btn-action-edit" title="Edit Pekerjaan" onclick="editWork(${w.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn-action-icon btn-action-delete" title="Hapus Pekerjaan" onclick="deleteWork(${w.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        const endCount = Math.min(startIndex + perPage, totalItems);
        $('tableInfo').textContent = `Menampilkan ${startIndex + 1} - ${endCount} dari ${totalItems} data`;

        let paginationHtml = '';
        for (let p = 1; p <= totalPages; p++) {
            const activeStyle = p === currentPage ? 'background:#0d9488; border-color:#0d9488; color:white;' : '';
            paginationHtml += `
                <button class="btn btn-sm ${p === currentPage ? 'btn-teal' : 'btn-outline-secondary'}" style="${activeStyle}" onclick="goToPage(${p})">
                    ${p}
                </button>
            `;
        }
        $('pagination').innerHTML = paginationHtml;
    }

    function goToPage(page) {
        currentPage = page;
        renderTable();
    }

    // =========================================================================
    // STATS & CHARTS UPDATE
    // =========================================================================
    function updateStatsDisplay(stats) {
        if ($('statTotalToday')) $('statTotalToday').textContent = stats.total_today ?? 0;
        if ($('statCompaniesToday')) $('statCompaniesToday').textContent = stats.companies_today ?? 0;
        if ($('statTotalHistory')) $('statTotalHistory').textContent = stats.total_all ?? (stats.total_history ?? 0);
        
        if (stats.companies_list_today) {
            currentCompaniesToday = stats.companies_list_today;
        }

        if ($('todayPieBadge')) {
            $('todayPieBadge').textContent = currentMode === 'today'
                ? `${stats.total_today ?? 0} Pekerjaan`
                : `${currentWorks.length} Pekerjaan (Riwayat)`;
        }
    }

    // =========================================================================
    // INTERAKSI KARTU STATISTIK (HYPERLINK & FILTER OTOMATIS)
    // =========================================================================
    function scrollToTable() {
        const tableCard = document.querySelector('.table-card');
        if (tableCard) {
            tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function handleCardKeydown(e, callback) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            callback();
        }
    }

    // Kartu 1: Total Pekerjaan Hari Ini
    function filterByCardToday() {
        currentMode = 'today';
        $('btnModeToday')?.classList.add('active');
        $('btnModeAll')?.classList.remove('active');

        // Pastikan tanggal default hari ini
        if ($('dateFilter')) {
            $('dateFilter').value = todayStr;
        }

        // Reset filter lain agar menampilkan semua pekerjaan hari ini
        if ($('companyFilter')) $('companyFilter').value = '';
        if ($('ptwFilter')) $('ptwFilter').value = '';
        if ($('sikkaFilter')) $('sikkaFilter').value = '';
        if ($('searchInput')) $('searchInput').value = '';

        currentPage = 1;
        fetchWorks();
        scrollToTable();
    }

    // Kartu 2: Perusahaan Terlibat (Interaktif dengan pilihan perusahaan jika >1)
    function filterByCardCompanies() {
        const companies = currentCompaniesToday || [];
        const curDate = $('dateFilter')?.value || todayStr;
        const isToday = (curDate === todayStr);
        const dFormatted = formatDate(curDate);

        if (companies.length === 0) {
            filterByCardToday();
            return;
        }

        if (companies.length === 1) {
            // Jika hanya 1 perusahaan, langsung filter ke perusahaan tersebut
            selectCompanyAndFilter(companies[0].name);
            return;
        }

        // Jika terdapat beberapa perusahaan, tampilkan modal pilihan perusahaan
        renderCompanyPickerModal(companies, curDate, isToday, dFormatted);
        const modalEl = $('modalSelectCompanyToday');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function renderCompanyPickerModal(companies, curDate, isToday, dFormatted) {
        const titleEl = $('modalCompanyDateTitle');
        const subtitleEl = $('modalCompanyDateSubtitle');
        const container = $('modalCompanyListContainer');

        if (titleEl) {
            titleEl.textContent = isToday ? 'Perusahaan Terlibat Hari Ini' : `Perusahaan Terlibat (${dFormatted})`;
        }
        if (subtitleEl) {
            subtitleEl.textContent = `${companies.length} perusahaan aktif dengan pekerjaan mulai ${isToday ? 'hari ini' : dFormatted}`;
        }

        if (!container) return;

        let html = '';
        companies.forEach(c => {
            const safeName = escapeHtml(c.name);
            const count = c.count || 0;
            const jsArg = safeName.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
            html += `
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2.5 px-3 border-0 border-bottom" onclick="selectCompanyAndFilter('${jsArg}')">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:28px; height:28px; background:#f1f5f9; color:#475569; font-size:13px;">
                            <i class="bi bi-building"></i>
                        </div>
                        <span class="fw-semibold text-dark" style="font-size:13px;">${safeName}</span>
                    </div>
                    <span class="badge rounded-pill bg-teal-subtle text-teal border border-teal" style="font-size:11px; padding: 4px 10px;">
                        ${count} Pekerjaan
                    </span>
                </button>
            `;
        });

        container.innerHTML = html;
    }

    function selectCompanyAndFilter(companyName) {
        const modalEl = $('modalSelectCompanyToday');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        currentMode = 'today';
        $('btnModeToday')?.classList.add('active');
        $('btnModeAll')?.classList.remove('active');

        // Pastikan filter tanggal aktif
        if ($('dateFilter') && !$('dateFilter').value) {
            $('dateFilter').value = todayStr;
        }

        // Set filter perusahaan
        if ($('companyFilter')) {
            $('companyFilter').value = companyName || '';
        }

        currentPage = 1;
        fetchWorks();
        scrollToTable();
    }

    // Kartu 3: Total Seluruh Data (Hyperlink ke Semua Riwayat)
    function filterByCardHistory() {
        setMode('all');

        // Kosongkan seluruh filter agar benar-benar menampilkan semua data
        if ($('dateFilter')) $('dateFilter').value = '';
        if ($('companyFilter')) $('companyFilter').value = '';
        if ($('ptwFilter')) $('ptwFilter').value = '';
        if ($('sikkaFilter')) $('sikkaFilter').value = '';
        if ($('searchInput')) $('searchInput').value = '';

        currentPage = 1;
        fetchWorks();
        scrollToTable();
    }

    const pieColors = [
        '#0d9488', '#0284c7', '#f59e0b', '#8b5cf6', '#ec4899', '#10b981', '#f97316', '#64748b'
    ];

    function renderPieChart(distribution) {
        const pieCanvas = $('companyPieChart');
        const emptyState = $('pieEmptyState');
        const legendGrid = $('pieLegendGrid');

        const curDate = $('dateFilter')?.value || todayStr;
        const dFormatted = formatDate(curDate);

        // Update empty state text based on mode
        if ($('pieEmptyTitle')) {
            $('pieEmptyTitle').textContent = currentMode === 'today'
                ? (curDate === todayStr ? 'Tidak Ada Pekerjaan yang Dimulai Hari Ini' : `Tidak Ada Pekerjaan Mulai ${dFormatted}`)
                : 'Tidak Ada Data Pekerjaan';
        }
        if ($('pieEmptyDesc')) {
            $('pieEmptyDesc').textContent = currentMode === 'today'
                ? `Belum ada pekerjaan dengan tanggal mulai ${dFormatted}.`
                : 'Tidak ditemukan pekerjaan yang cocok dengan filter.';
        }

        if ($('todayPieBadge')) {
            $('todayPieBadge').textContent = currentMode === 'today'
                ? `${currentWorks.length} Pekerjaan`
                : `${currentWorks.length} Pekerjaan (Riwayat)`;
        }

        if (!distribution || distribution.length === 0) {
            if (companyPieChart) {
                companyPieChart.destroy();
                companyPieChart = null;
            }
            pieCanvas.classList.add('d-none');
            emptyState.classList.remove('d-none');
            legendGrid.innerHTML = '';
            return;
        }

        emptyState.classList.add('d-none');
        pieCanvas.classList.remove('d-none');

        const labels = distribution.map(d => d.name);
        const dataVals = distribution.map(d => d.count);
        const bgColors = labels.map((_, i) => pieColors[i % pieColors.length]);

        // Render custom legend
        legendGrid.innerHTML = distribution.map((d, i) => `
            <div class="pie-legend-item">
                <span class="pie-legend-dot" style="background: ${bgColors[i]};"></span>
                <span class="text-truncate" title="${escapeHtml(d.name)}">${escapeHtml(d.name)}</span>
                <span class="pie-legend-val">${d.count} (${d.percentage}%)</span>
            </div>
        `).join('');

        if (companyPieChart) {
            companyPieChart.data.labels = labels;
            companyPieChart.data.datasets[0].data = dataVals;
            companyPieChart.data.datasets[0].backgroundColor = bgColors;
            companyPieChart.update();
            return;
        }

        companyPieChart = new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dataVals,
                    backgroundColor: bgColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Menggunakan custom legend di bawah grafik
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                const item = distribution[idx];
                                return ` ${item.name}: ${item.count} pekerjaan (${item.percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateClassificationSummary(worksList) {
        // Hitung PTW hari ini
        const coldCount = worksList.filter(w => (w.ptw_classification || '').toLowerCase().includes('cold')).length;
        const hotCount = worksList.filter(w => (w.ptw_classification || '').toLowerCase().includes('hot')).length;
        const totalPtw = Math.max(1, coldCount + hotCount);

        $('countPtwCold').textContent = `${coldCount} (${Math.round((coldCount / totalPtw) * 100)}%)`;
        $('countPtwHot').textContent = `${hotCount} (${Math.round((hotCount / totalPtw) * 100)}%)`;

        $('progPtwCold').style.width = `${Math.round((coldCount / totalPtw) * 100)}%`;
        $('progPtwHot').style.width = `${Math.round((hotCount / totalPtw) * 100)}%`;

        // Hitung SIKKA hari ini
        const sikkaCounts = {};
        worksList.forEach(w => {
            if (w.sikka) {
                sikkaCounts[w.sikka] = (sikkaCounts[w.sikka] || 0) + 1;
            }
        });

        const keys = Object.keys(sikkaCounts);
        if (keys.length === 0) {
            $('sikkaChipsContainer').innerHTML = '<span class="text-muted" style="font-size:11px;">Belum ada izin SIKKA terdaftar.</span>';
        } else {
            $('sikkaChipsContainer').innerHTML = keys.map(k => `
                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                    <strong>${escapeHtml(k)}:</strong> ${sikkaCounts[k]}
                </span>
            `).join('');
        }
    }

    // =========================================================================
    // ADD / EDIT WORK MODAL & SUBMIT
    // =========================================================================
    function openAddModal() {
        $('workForm').reset();
        $('workEditId').value = '';
        $('workStart').value = todayStr;
        $('workEnd').value = todayStr;
        $('workModalTitle').innerHTML = '<i class="bi bi-calendar2-check me-1"></i> Tambah Pekerjaan Baru';
        syncSelectDropdowns();
        workModal.show();
    }

    function editWork(id) {
        const w = currentWorks.find(item => item.id === Number(id));
        if (!w) return;

        $('workForm').reset();
        $('workEditId').value = w.id;
        $('workModalTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Edit Pekerjaan';

        $('workName').value = w.name || '';
        $('workStart').value = (w.start_date || w.start || '').slice(0, 10);
        $('workEnd').value = (w.end_date || w.end || '').slice(0, 10);
        $('workLocation').value = w.location || '';
        $('workDescription').value = w.description || '';

        syncSelectDropdowns();

        // Set Perusahaan (fallback jika unit lama belum ada di master)
        const companySelect = $('workCompany');
        const companyVal = w.unit || w.company;
        let compFound = false;
        for (let i = 0; i < companySelect.options.length; i++) {
            if (companySelect.options[i].value === companyVal) {
                compFound = true;
                break;
            }
        }
        if (!compFound && companyVal) {
            const opt = document.createElement('option');
            opt.value = companyVal;
            opt.textContent = `${companyVal} (Legacy)`;
            companySelect.appendChild(opt);
        }
        companySelect.value = companyVal;

        // Set PTW
        $('workPtw').value = w.ptw_classification || '';
        // Set SIKKA
        $('workSikka').value = w.sikka || '';

        workModal.show();
    }

    $('workForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = $('workEditId').value;
        const isUpdate = !!id;

        const payload = {
            name: $('workName').value.trim(),
            unit: $('workCompany').value,
            ptw_classification: $('workPtw').value,
            sikka: $('workSikka').value,
            start_date: $('workStart').value,
            end_date: $('workEnd').value,
            location: $('workLocation').value.trim(),
            description: $('workDescription').value.trim(),
        };

        const url = isUpdate ? `/daily-work/${id}` : '/daily-work';
        const method = isUpdate ? 'PUT' : 'POST';

        const submitBtn = $('btnSubmitWork');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

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
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan';

            if (data.success) {
                workModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message || 'Pekerjaan berhasil disimpan.',
                    timer: 1800,
                    showConfirmButton: false
                });
                fetchWorks();
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menyimpan data pekerjaan.', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan';
            Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
        });
    });

    // =========================================================================
    // VIEW WORK DETAIL
    // =========================================================================
    function viewWorkDetail(id) {
        $('workDetailPermalink').href = `/daily-work/${id}`;
        $('workDetailModalTitle').innerHTML = '<i class="bi bi-calendar2-week me-1"></i> Memuat Detail...';
        $('workDetailModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-teal" role="status"></div>
                <div class="mt-2 text-muted fw-semibold" style="font-size:12px">Mengambil data pekerjaan...</div>
            </div>
        `;
        workDetailModal.show();

        fetch(`/daily-work/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.work) {
                throw new Error(data.message || 'Data tidak ditemukan.');
            }
            const w = data.work;
            $('workDetailModalTitle').innerHTML = `<i class="bi bi-calendar2-week me-1"></i> Detail: ${escapeHtml(w.name)}`;

            $('workDetailModalBody').innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge-company fs-6 px-3 py-1">
                            <i class="bi bi-building me-1"></i>${escapeHtml(w.unit || w.company)}
                        </span>
                        <span class="badge-ptw fs-6 px-3 py-1">
                            ${escapeHtml(w.ptw_classification || '-')}
                        </span>
                        <span class="badge-sikka fs-6 px-3 py-1">
                            ${escapeHtml(w.sikka || '-')}
                        </span>
                    </div>
                    <span class="text-muted fw-semibold" style="font-size:11.5px">
                        ID: #DW-${String(w.id).padStart(4, '0')}
                    </span>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-12">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted" style="font-size:11px">Nama Pekerjaan</div>
                            <div class="fw-bold text-dark mt-1" style="font-size:13.5px">${escapeHtml(w.name)}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted" style="font-size:11px">Periode Pelaksanaan</div>
                            <div class="fw-semibold text-dark mt-1" style="font-size:12px">
                                <i class="bi bi-calendar3 text-primary me-1"></i>${formatDate(w.start_date || w.start)} s/d ${formatDate(w.end_date || w.end)}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted" style="font-size:11px">Lokasi Pekerjaan</div>
                            <div class="fw-semibold text-dark mt-1" style="font-size:12px">
                                <i class="bi bi-geo-alt text-danger me-1"></i>${escapeHtml(w.location || '-')}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 border rounded bg-light">
                            <div class="text-muted mb-1" style="font-size:11px">Keterangan / Deskripsi Pekerjaan</div>
                            <div class="text-secondary" style="font-size:12px; line-height:1.5;">
                                ${escapeHtml(w.description || 'Tidak ada catatan atau deskripsi tambahan.')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            workDetailModal.hide();
            Swal.fire('Data Tidak Ditemukan', err.message, 'error');
        });
    }

    // =========================================================================
    // DELETE WORK
    // =========================================================================
    function deleteWork(id) {
        const w = currentWorks.find(item => item.id === Number(id));
        Swal.fire({
            title: 'Hapus Pekerjaan?',
            html: `Apakah Anda yakin ingin menghapus pekerjaan <b>"${w ? escapeHtml(w.name) : ''}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/${id}`, {
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Pekerjaan berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        fetchWorks();
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menghapus pekerjaan.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus.', 'error');
                });
            }
        });
    }

    // =========================================================================
    // CRUD MASTER DATA MODAL (PERUSAHAAN, PTW, SIKKA)
    // =========================================================================
    function openMasterModal(tab = 'company') {
        switchMasterTab(tab);
        loadMasterData();
        masterModal.show();
    }

    function switchMasterTab(tab) {
        ['company', 'ptw', 'sikka'].forEach(t => {
            const btn = $(`tabBtn${t.charAt(0).toUpperCase() + t.slice(1)}`);
            const content = $(`tabContent${t.charAt(0).toUpperCase() + t.slice(1)}`);
            if (t === tab) {
                btn?.classList.add('active');
                content?.classList.remove('d-none');
            } else {
                btn?.classList.remove('active');
                content?.classList.add('d-none');
            }
        });
    }

    function loadMasterData() {
        fetch('/daily-work/masters/all', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                masterCompanies = data.companies || [];
                masterPtws = data.ptw_classifications || [];
                masterSikkas = data.sikkas || [];
                renderMasterTables();
                syncSelectDropdowns();
            }
        })
        .catch(err => console.error('Failed to load masters:', err));
    }

    function renderMasterTables() {
        // Render Companies Table
        $('companyMasterTableBody').innerHTML = masterCompanies.map(c => `
            <tr>
                <td class="align-middle fw-semibold text-dark">${escapeHtml(c.name)}</td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-sm ${c.is_active ? 'btn-success' : 'btn-outline-secondary'} py-0 px-2" style="font-size:11px;" onclick="toggleCompanyActive(${c.id})">
                        ${c.is_active ? 'Aktif' : 'Nonaktif'}
                    </button>
                </td>
                <td class="align-middle text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" onclick="editCompanyName(${c.id}, '${escapeHtml(c.name)}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="deleteCompany(${c.id}, '${escapeHtml(c.name)}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="3" class="text-center text-muted py-3">Belum ada perusahaan.</td></tr>';

        // Render PTW Table
        $('ptwMasterTableBody').innerHTML = masterPtws.map(p => `
            <tr>
                <td class="align-middle fw-semibold text-dark">${escapeHtml(p.name)}</td>
                <td class="align-middle text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" onclick="editPtwName(${p.id}, '${escapeHtml(p.name)}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="deletePtw(${p.id}, '${escapeHtml(p.name)}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="2" class="text-center text-muted py-3">Belum ada pilihan PTW.</td></tr>';

        // Render SIKKA Table
        $('sikkaMasterTableBody').innerHTML = masterSikkas.map(s => `
            <tr>
                <td class="align-middle fw-semibold text-dark">${escapeHtml(s.name)}</td>
                <td class="align-middle text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" onclick="editSikkaName(${s.id}, '${escapeHtml(s.name)}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="deleteSikka(${s.id}, '${escapeHtml(s.name)}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="2" class="text-center text-muted py-3">Belum ada pilihan SIKKA.</td></tr>';
    }

    function syncSelectDropdowns() {
        // Sync Work Modal Dropdowns
        const curWorkComp = $('workCompany').value;
        const curWorkPtw = $('workPtw').value;
        const curWorkSikka = $('workSikka').value;

        // Sync Company Select
        $('workCompany').innerHTML = '<option value="">Pilih Perusahaan</option>' + masterCompanies.filter(c => c.is_active).map(c => `
            <option value="${escapeHtml(c.name)}">${escapeHtml(c.name)}</option>
        `).join('');
        if (curWorkComp) $('workCompany').value = curWorkComp;

        // Sync PTW Select
        $('workPtw').innerHTML = '<option value="">Pilih PTW</option>' + masterPtws.filter(p => p.is_active).map(p => `
            <option value="${escapeHtml(p.name)}">${escapeHtml(p.name)}</option>
        `).join('');
        if (curWorkPtw) $('workPtw').value = curWorkPtw;

        // Sync SIKKA Select
        $('workSikka').innerHTML = '<option value="">Pilih SIKKA</option>' + masterSikkas.filter(s => s.is_active).map(s => `
            <option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>
        `).join('');
        if (curWorkSikka) $('workSikka').value = curWorkSikka;

        // Sync Filter Dropdowns
        const curFilterComp = $('companyFilter').value;
        const curFilterPtw = $('ptwFilter').value;
        const curFilterSikka = $('sikkaFilter').value;

        $('companyFilter').innerHTML = '<option value="">Semua Perusahaan</option>' + masterCompanies.map(c => `
            <option value="${escapeHtml(c.name)}">${escapeHtml(c.name)}</option>
        `).join('');
        if (curFilterComp) $('companyFilter').value = curFilterComp;

        $('ptwFilter').innerHTML = '<option value="">Semua PTW</option>' + masterPtws.map(p => `
            <option value="${escapeHtml(p.name)}">${escapeHtml(p.name)}</option>
        `).join('');
        if (curFilterPtw) $('ptwFilter').value = curFilterPtw;

        $('sikkaFilter').innerHTML = '<option value="">Semua SIKKA</option>' + masterSikkas.map(s => `
            <option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>
        `).join('');
        if (curFilterSikka) $('sikkaFilter').value = curFilterSikka;
    }

    // --- Master Company Actions ---
    function submitAddCompany() {
        const name = $('newCompanyName').value.trim();
        if (!name) return;
        if (name.toUpperCase() === 'SOKKO') {
            Swal.fire('Perhatian', 'Perusahaan SOKKO sudah tidak diizinkan.', 'warning');
            return;
        }

        fetch('/daily-work/masters/companies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $('newCompanyName').value = '';
                masterCompanies = data.companies || masterCompanies;
                renderMasterTables();
                syncSelectDropdowns();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menambahkan perusahaan.', 'error');
            }
        });
    }

    function editCompanyName(id, oldName) {
        Swal.fire({
            title: 'Edit Nama Perusahaan',
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val || !val.trim()) return 'Nama perusahaan tidak boleh kosong!';
                if (val.trim().toUpperCase() === 'SOKKO') return 'Perusahaan SOKKO sudah tidak diizinkan!';
            }
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/companies/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name: result.value.trim() })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        masterCompanies = data.companies || masterCompanies;
                        renderMasterTables();
                        syncSelectDropdowns();
                        fetchWorks();
                        Swal.fire({ icon: 'success', title: 'Tersimpan', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal memperbarui nama.', 'error');
                    }
                });
            }
        });
    }

    function toggleCompanyActive(id) {
        fetch(`/daily-work/masters/companies/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                masterCompanies = data.companies || masterCompanies;
                renderMasterTables();
                syncSelectDropdowns();
            }
        });
    }

    function deleteCompany(id, name) {
        Swal.fire({
            title: 'Hapus Perusahaan?',
            html: `Apakah Anda yakin ingin menghapus <b>"${name}"</b>?<br><small class="text-muted">Perusahaan yang sudah memiliki data pekerjaan akan ditolak penghapusannya.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/companies/${id}`, {
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
                        masterCompanies = data.companies || masterCompanies;
                        renderMasterTables();
                        syncSelectDropdowns();
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Tidak Dapat Dihapus', data.message || 'Gagal menghapus.', 'warning');
                    }
                });
            }
        });
    }

    // --- Master PTW Actions ---
    function submitAddPtw() {
        const name = $('newPtwName').value.trim();
        if (!name) return;

        fetch('/daily-work/masters/ptw', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $('newPtwName').value = '';
                masterPtws = data.ptw_classifications || masterPtws;
                renderMasterTables();
                syncSelectDropdowns();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menambahkan PTW.', 'error');
            }
        });
    }

    function editPtwName(id, oldName) {
        Swal.fire({
            title: 'Edit PTW Classification',
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val || !val.trim()) return 'Nama klasifikasi tidak boleh kosong!';
            }
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/ptw/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name: result.value.trim() })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        masterPtws = data.ptw_classifications || masterPtws;
                        renderMasterTables();
                        syncSelectDropdowns();
                        fetchWorks();
                        Swal.fire({ icon: 'success', title: 'Tersimpan', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal memperbarui.', 'error');
                    }
                });
            }
        });
    }

    function deletePtw(id, name) {
        Swal.fire({
            title: 'Hapus PTW Classification?',
            html: `Apakah Anda yakin ingin menghapus <b>"${name}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/ptw/${id}`, {
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
                        masterPtws = data.ptw_classifications || masterPtws;
                        renderMasterTables();
                        syncSelectDropdowns();
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Tidak Dapat Dihapus', data.message || 'Gagal menghapus.', 'warning');
                    }
                });
            }
        });
    }

    // --- Master SIKKA Actions ---
    function submitAddSikka() {
        const name = $('newSikkaName').value.trim();
        if (!name) return;

        fetch('/daily-work/masters/sikka', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                $('newSikkaName').value = '';
                masterSikkas = data.sikkas || masterSikkas;
                renderMasterTables();
                syncSelectDropdowns();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal menambahkan SIKKA.', 'error');
            }
        });
    }

    function editSikkaName(id, oldName) {
        Swal.fire({
            title: 'Edit Jenis SIKKA',
            input: 'text',
            inputValue: oldName,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val || !val.trim()) return 'Nama SIKKA tidak boleh kosong!';
            }
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/sikka/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name: result.value.trim() })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        masterSikkas = data.sikkas || masterSikkas;
                        renderMasterTables();
                        syncSelectDropdowns();
                        fetchWorks();
                        Swal.fire({ icon: 'success', title: 'Tersimpan', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal memperbarui.', 'error');
                    }
                });
            }
        });
    }

    function deleteSikka(id, name) {
        Swal.fire({
            title: 'Hapus Pilihan SIKKA?',
            html: `Apakah Anda yakin ingin menghapus <b>"${name}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/daily-work/masters/sikka/${id}`, {
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
                        masterSikkas = data.sikkas || masterSikkas;
                        renderMasterTables();
                        syncSelectDropdowns();
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Tidak Dapat Dihapus', data.message || 'Gagal menghapus.', 'warning');
                    }
                });
            }
        });
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================
    function exportPDF() {
        const params = new URLSearchParams();
        params.set('date_mode', currentMode);

        const comp = $('companyFilter')?.value;
        const ptw = $('ptwFilter')?.value;
        const sikka = $('sikkaFilter')?.value;
        const dateVal = $('dateFilter')?.value;
        const searchVal = $('searchInput')?.value?.trim();

        if (comp) params.set('unit', comp);
        if (ptw) params.set('ptw_classification', ptw);
        if (sikka) params.set('sikka', sikka);
        if (dateVal) params.set('date', dateVal);
        if (searchVal) params.set('search', searchVal);

        window.location.href = '{{ route('daily-work.export') }}?' + params.toString();
    }

    // =========================================================================
    // INITIALIZATION & EVENT LISTENERS
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Filter event listeners
        ['companyFilter', 'ptwFilter', 'sikkaFilter', 'dateFilter'].forEach(id => {
            $(id)?.addEventListener('change', () => {
                currentPage = 1;
                fetchWorks();
            });
        });

        $('dateFilter')?.addEventListener('input', () => {
            currentPage = 1;
            fetchWorks();
        });

        $('searchInput')?.addEventListener('input', () => {
            currentPage = 1;
            fetchWorks();
        });

        $('perPage')?.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });

        // Initialize table and pie chart with initial PHP-rendered data
        renderTable();
        renderPieChart(@json($companyDistribution ?? []));
        updateClassificationSummary(currentWorks);
    });
</script>
@endpush

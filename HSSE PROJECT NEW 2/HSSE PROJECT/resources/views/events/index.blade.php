@extends('layouts.app', [
    'title' => 'Events',
    'activeModule' => 'events'
])

@push('styles')
<style>
    /* Monthly Chart Specific Layout */
    .monthly-chart-layout {
        display: grid;
        grid-template-columns: 1fr 105px;
        gap: 10px;
        align-items: center;
        height: 175px;
    }

    .donut-chart-layout {
        display: grid;
        grid-template-columns: 125px 1fr;
        gap: 12px;
        align-items: center;
        height: 175px;
    }

    .donut-relative {
        position: relative;
        width: 125px;
        height: 125px;
        margin: auto;
    }

    .donut-center-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    .donut-center-number {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .donut-center-label {
        font-size: 9.5px;
        color: #64748b;
        font-weight: 600;
    }

    .custom-legend-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 11px;
        max-height: 170px;
        overflow-y: auto;
    }

    .custom-legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .legend-indicator.square {
        border-radius: 2px;
    }

    .legend-name {
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .legend-stats {
        margin-left: auto;
        color: #0f172a;
        font-weight: 600;
        white-space: nowrap;
    }

    .doc-badge {
        color: #2563eb;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .doc-badge:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .doc-badge.zero {
        color: #ef4444;
    }

    .detail-info-row {
        display: grid;
        grid-template-columns: 110px 10px 1fr;
        gap: 6px;
        margin-bottom: 8px;
        font-size: 12px;
    }

    .detail-info-label {
        color: #64748b;
        font-weight: 500;
    }

    .detail-info-sep {
        color: #94a3b8;
    }

    .detail-info-val {
        color: #0f172a;
        font-weight: 600;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .gallery-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .gallery-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .gallery-thumb-img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        display: block;
    }

    .gallery-label {
        padding: 4px;
        font-size: 10px;
        color: #475569;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gallery-upload-card {
        border: 2px dashed #cbd5e1;
        border-radius: 6px;
        height: 106px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: #f8fafc;
        color: #64748b;
        transition: all 0.2s;
    }

    .gallery-upload-card:hover {
        border-color: var(--green-primary);
        color: var(--green-primary);
        background: #ecfdf5;
    }

    .nav-tabs-detail {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 12px;
    }

    .nav-tabs-detail .nav-link {
        border: none;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }

    .nav-tabs-detail .nav-link.active {
        color: #8b5cf6;
        border-bottom-color: #8b5cf6;
        background: transparent;
    }

    .upload-drag-area {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-drag-area:hover {
        border-color: var(--green-primary);
        background: #ecfdf5;
    }

    .charts-row-events {
        display: grid;
        grid-template-columns: 1.28fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .filter-grid-events {
        grid-template-columns: 195px 140px 140px 145px 140px 1fr auto;
    }

    @media (max-width: 1200px) {
        .charts-row-events { grid-template-columns: 1fr; }
        .filter-grid-events { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box purple">
            <i class="bi bi-calendar-event"></i>
        </div>
        <div>
            <h1 class="page-heading">Events</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Events</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalTambahEvent">
                <i class="bi bi-plus-lg"></i> Tambah Event
            </button>
        @endadminManagement
        @canExportPdf
        <a href="{{ route('events.export', request()->query()) }}" class="btn-action-outline" id="btnExportPdf">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </a>
        @endcanExportPdf
    </div>
</div>

<!-- FILTER BAR -->
<div class="filter-card">
    <form id="filterForm" method="GET" action="{{ route('events.index') }}">
        <div class="filter-grid filter-grid-events">
            <!-- Date Range -->
            <div class="filter-input-wrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="text"
                       name="date_range"
                       id="dateRangePicker"
                       class="form-control form-control-filter"
                       placeholder="Pilih Rentang Tanggal"
                       value="{{ request('date_range', '') }}">
            </div>

            <!-- Dropdown Status -->
            <div>
                <select name="status" id="filterStatus" class="form-select form-select-filter">
                    <option value="Semua" {{ request('status', 'Semua') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai (Terlaksana)</option>
                    <option value="Berlangsung" {{ request('status') == 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="Direncanakan" {{ request('status') == 'Direncanakan' ? 'selected' : '' }}>Direncanakan</option>
                    <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Dropdown Kategori -->
            <div>
                <select name="category" id="filterCategory" class="form-select form-select-filter">
                    <option value="Semua" {{ request('category') == 'Semua' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($allCategories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Unit/Komponen -->
            <div>
                <select name="unit" id="filterUnit" class="form-select form-select-filter">
                    <option value="Semua" {{ request('unit') == 'Semua' ? 'selected' : '' }}>Semua Unit</option>
                    @foreach($allUnits as $u)
                        <option value="{{ $u }}" {{ request('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Lokasi -->
            <div>
                <select name="location" id="filterLocation" class="form-select form-select-filter">
                    <option value="Semua" {{ request('location') == 'Semua' ? 'selected' : '' }}>Semua Lokasi</option>
                    @foreach($allLocations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Box -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="text"
                       name="search"
                       id="filterSearch"
                       class="form-control form-control-filter"
                       placeholder="Cari judul kegiatan, lokasi, atau deskripsi..."
                       value="{{ request('search') }}">
            </div>

            <!-- Hidden per_page -->
            <input type="hidden" name="per_page" id="filterPerPage" value="{{ $perPage }}">

            <!-- Submit / Reset -->
            <div>
                <button type="submit" class="btn btn-sm btn-primary px-3" style="background:#059669; border-color:#059669;">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- KARTU RINGKASAN (SUMMARY CARDS - DARI DATA DATABASE SEBENARNYA) -->
@php
    $pctSelesai = $totalKegiatan > 0 ? round(($selesai / $totalKegiatan) * 100) : 0;
@endphp
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <!-- Total Kegiatan -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-calendar-event"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Kegiatan</div>
            <div class="stat-number" id="cardTotalKegiatan">{{ $totalKegiatan }}</div>
            <div class="stat-trend">
                <span class="stat-trend-neutral">Diurutkan terbaru ke terlama</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Selesai -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Selesai (Terlaksana)</div>
            <div class="stat-number text-success" id="cardSelesai">{{ $selesai }}</div>
            <div class="stat-trend">
                <span class="stat-trend-up">{{ $pctSelesai }}% terlaksana</span>
                <span class="stat-trend-caption">• {{ number_format($selesaiParticipants ?? 0, 0, ',', '.') }} peserta</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Berlangsung -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Berlangsung / Direncanakan</div>
            <div class="stat-number text-warning" id="cardBerlangsung">{{ $berlangsung }}</div>
            <div class="stat-trend">
                <span class="stat-trend-caption">Direncanakan: {{ $direncanakan ?? 0 }} kegiatan</span>
            </div>
        </div>
    </div>

    <!-- Kegiatan Dibatalkan -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-cancelled rounded-circle">
            <i class="bi bi-x-lg"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Dibatalkan</div>
            <div class="stat-number text-danger" id="cardDibatalkan">{{ $dibatalkan }}</div>
            <div class="stat-trend">
                <span class="stat-trend-caption">Tidak dihitung sebagai selesai</span>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK (3 CHARTS IN A ROW) -->
<div class="charts-row-events">
    <!-- Chart 1: Jumlah Kegiatan per Bulan -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Jumlah Kegiatan per Bulan</h2>
        </div>
        <div class="monthly-chart-layout">
            <div class="chart-canvas-container">
                <canvas id="monthlyEventsChart"></canvas>
            </div>
            <div class="custom-legend-list" id="monthlyLegendList"></div>
        </div>
    </div>

    <!-- Chart 2: Kegiatan per Kategori (Donut) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Kegiatan per Kategori</h2>
        </div>
        <div class="donut-chart-layout">
            <div class="donut-relative">
                <canvas id="categoryDonutChart"></canvas>
                <div class="donut-center-text">
                    <div class="donut-center-number" id="donutCenterCount">{{ $totalKegiatan }}</div>
                    <div class="donut-center-label">Total</div>
                </div>
            </div>
            <div class="custom-legend-list" id="categoryLegendList">
                @foreach($categoryChartData as $c)
                    <div class="custom-legend-item">
                        <span class="legend-indicator" style="background: {{ $c['color'] }}"></span>
                        <span class="legend-name" title="{{ $c['name'] }}">{{ $c['name'] }}</span>
                        <span class="legend-stats">{{ $c['count'] }} ({{ $c['percentage'] }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Chart 3: Kegiatan per Unit/Komponen (Horizontal Bar) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Kegiatan per Unit/Komponen</h2>
        </div>
        <div class="chart-canvas-container">
            <canvas id="unitBarChart"></canvas>
        </div>
    </div>
</div>

<!-- TABEL KEGIATAN -->
<div class="table-card">
    <div class="table-topbar">
        <h2 class="table-section-title">Daftar Kegiatan</h2>

        <div class="table-controls">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:11.5px">Tampilkan</span>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px; font-size:11.5px">
                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-muted" style="font-size:11.5px">data per halaman</span>
            </div>

            <div class="table-search-wrap ms-auto">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="text"
                       id="inlineTableSearch"
                       class="form-control table-search-input"
                       placeholder="Cari kegiatan..."
                       value="{{ request('search') }}">
            </div>

            <button class="table-filter-btn" type="button" onclick="document.getElementById('dateRangePicker').focus()">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 95px;">Tanggal</th>
                    <th>Judul Kegiatan</th>
                    <th style="width: 120px;">Kategori</th>
                    <th style="width: 100px;">Unit/Komponen</th>
                    <th style="width: 120px;">Lokasi</th>
                    <th style="width: 100px; text-align: center;">Jumlah Peserta</th>
                    <th style="width: 105px; text-align: center;">Status</th>
                    <th style="width: 110px; text-align: center;">Dokumentasi</th>
                    <th style="width: 95px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="eventsTableBody">
                @forelse($events as $index => $event)
                    <tr>
                        <td style="text-align: center;">{{ $events->firstItem() + $index }}</td>
                        <td>{{ $event->event_date ? $event->event_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $event->title }}</div>
                        </td>
                        <td>
                            <span class="badge-cat" style="background: {{ $event->category_color }}; color: {{ $event->category_text_color }};">
                                {{ $event->category }}
                            </span>
                        </td>
                        <td>{{ $event->unit }}</td>
                        <td>{{ $event->location }}</td>
                        <td style="text-align: center;">{{ $event->participants ?: '-' }}</td>
                        <td style="text-align: center;">
                            @php
                                $statusClass = match($event->status) {
                                    'Selesai' => 'status-selesai',
                                    'Berlangsung' => 'status-berlangsung',
                                    'Dibatalkan' => 'status-dibatalkan',
                                    default => 'status-direncanakan'
                                };
                            @endphp
                            <span class="badge-status {{ $statusClass }}">
                                {{ $event->status }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($event->documents_count > 0)
                                <a href="javascript:void(0)" class="doc-badge" onclick="openDetailModal({{ $event->id }}, 'docs')">
                                    <i class="bi bi-file-earmark-text"></i> ({{ $event->documents_count }})
                                </a>
                            @else
                                <span class="doc-badge zero">
                                    <i class="bi bi-file-earmark-text"></i> (0)
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <button class="btn-action-icon btn-action-view" title="Detail Kegiatan" onclick="openDetailModal({{ $event->id }})">
                                <i class="bi bi-eye"></i>
                            </button>
                            @adminManagement
                                <button class="btn-action-icon btn-action-edit" title="Edit Kegiatan" onclick="openEditModal({{ $event->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action-icon btn-action-delete" title="Hapus Kegiatan" onclick="confirmDeleteEvent({{ $event->id }}, '{{ addslashes($event->title) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endadminManagement
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                            Tidak ada data kegiatan yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer & Pagination -->
    <div class="table-bottom-bar">
        <div>
            Menampilkan {{ $events->firstItem() ?? 0 }} - {{ $events->lastItem() ?? 0 }} dari {{ $events->total() }} data
        </div>
        <div>
            {{ $events->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- MODALS -->
<!-- MODAL TAMBAH EVENT -->
<div class="modal fade" id="modalTambahEvent" tabindex="-1" aria-labelledby="modalTambahEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalTambahEventLabel">
                    <i class="bi bi-calendar-plus me-1"></i> Tambah Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahEvent" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Masukkan judul kegiatan" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select form-select-sm" required>
                                <option value="">Pilih kategori</option>
                                <option value="Pelatihan">Pelatihan</option>
                                <option value="Safety Talk">Safety Talk</option>
                                <option value="Inspeksi">Inspeksi</option>
                                <option value="Kampanye">Kampanye</option>
                                <option value="Simulasi/Drill">Simulasi/Drill</option>
                                <option value="Sosialisasi">Sosialisasi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:12px">Komponen/Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select form-select-sm" required>
                                <option value="">Pilih Unit</option>
                                <option value="PMU">PMU</option>
                                <option value="Utility">Utility</option>
                                <option value="JOTRE">JOTRE</option>
                                <option value="BISMO">BISMO</option>
                                <option value="Kontraktor">Kontraktor</option>
                                <option value="Operasi">Operasi</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control form-control-sm" placeholder="Masukkan lokasi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:12px">Jumlah Peserta</label>
                            <input type="number" name="participants" class="form-control form-control-sm" placeholder="Masukkan jumlah" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="Direncanakan">Direncanakan</option>
                                <option value="Berlangsung">Berlangsung</option>
                                <option value="Selesai" selected>Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Penanggung Jawab (PIC)</label>
                            <input type="text" name="pic" class="form-control form-control-sm" placeholder="Nama PIC" value="{{ Auth::user()->name ?? 'Budi Santoso' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Waktu (Mulai - Selesai)</label>
                            <div class="input-group input-group-sm">
                                <input type="time" name="start_time" class="form-control" value="08:00">
                                <span class="input-group-text">-</span>
                                <input type="time" name="end_time" class="form-control" value="11:00">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Deskripsi Kegiatan</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Deskripsi kegiatan..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Dokumentasi</label>
                            <div class="upload-drag-area" onclick="document.getElementById('inputDocsTambah').click()">
                                <i class="bi bi-cloud-arrow-up fs-3 text-secondary d-block"></i>
                                <span class="text-muted" style="font-size:11.5px">Klik untuk upload foto/laporan (JPG, PNG, PDF)</span>
                                <input type="file" name="documentation_files[]" id="inputDocsTambah" class="d-none" multiple accept="image/*,.pdf,.doc,.docx" onchange="previewFilesTambah(this)">
                            </div>
                            <div id="previewFilesTambahContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
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

<!-- MODAL EDIT EVENT -->
<div class="modal fade" id="modalEditEvent" tabindex="-1" aria-labelledby="modalEditEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalEditEventLabel">
                    <i class="bi bi-pencil-square me-1"></i> Edit Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditEvent" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editTitle" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Kategori <span class="text-danger">*</span></label>
                            <select name="category" id="editCategory" class="form-select form-select-sm" required>
                                <option value="Pelatihan">Pelatihan</option>
                                <option value="Safety Talk">Safety Talk</option>
                                <option value="Inspeksi">Inspeksi</option>
                                <option value="Kampanye">Kampanye</option>
                                <option value="Simulasi/Drill">Simulasi/Drill</option>
                                <option value="Sosialisasi">Sosialisasi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:12px">Komponen/Unit <span class="text-danger">*</span></label>
                            <select name="unit" id="editUnit" class="form-select form-select-sm" required>
                                <option value="PMU">PMU</option>
                                <option value="Utility">Utility</option>
                                <option value="JOTRE">JOTRE</option>
                                <option value="BISMO">BISMO</option>
                                <option value="Kontraktor">Kontraktor</option>
                                <option value="Operasi">Operasi</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="editLocation" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" id="editEventDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:12px">Jumlah Peserta</label>
                            <input type="number" name="participants" id="editParticipants" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select form-select-sm" required>
                                <option value="Direncanakan">Direncanakan</option>
                                <option value="Berlangsung">Berlangsung</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Penanggung Jawab (PIC)</label>
                            <input type="text" name="pic" id="editPic" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px">Waktu</label>
                            <div class="input-group input-group-sm">
                                <input type="time" name="start_time" id="editStartTime" class="form-control">
                                <span class="input-group-text">-</span>
                                <input type="time" name="end_time" id="editEndTime" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Deskripsi Kegiatan</label>
                            <textarea name="description" id="editDescription" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Tambah Dokumentasi Baru</label>
                            <div class="upload-drag-area" onclick="document.getElementById('inputDocsEdit').click()">
                                <i class="bi bi-cloud-arrow-up fs-3 text-secondary d-block"></i>
                                <span class="text-muted" style="font-size:11.5px">Klik untuk upload foto/laporan tambahan</span>
                                <input type="file" name="documentation_files[]" id="inputDocsEdit" class="d-none" multiple accept="image/*,.pdf,.doc,.docx" onchange="previewFilesEdit(this)">
                            </div>
                            <div id="previewFilesEditContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
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

<!-- MODAL DETAIL EVENT -->
<div class="modal fade" id="modalDetailEvent" tabindex="-1" aria-labelledby="modalDetailEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalDetailEventLabel">
                    <i class="bi bi-info-circle me-1"></i> Detail Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-3 text-dark" style="font-size:13px">Informasi Kegiatan</h6>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Judul</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailJudul">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Kategori</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailKategori">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Unit/Komponen</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailUnit">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Tanggal</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailTanggal">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Lokasi</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailLokasi">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Jumlah Peserta</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailPeserta">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Status</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val">
                                <span id="detailStatusBadge" class="badge-status status-selesai">Selesai</span>
                            </div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Deskripsi</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val fw-normal text-muted" id="detailDeskripsi">-</div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="detail-info-row">
                                <div class="detail-info-label">Dibuat oleh</div>
                                <div class="detail-info-sep">:</div>
                                <div class="detail-info-val fw-normal text-muted" id="detailDibuat">-</div>
                            </div>
                            <div class="detail-info-row">
                                <div class="detail-info-label">Diubah oleh</div>
                                <div class="detail-info-sep">:</div>
                                <div class="detail-info-val fw-normal text-muted" id="detailDiubah">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <ul class="nav nav-tabs nav-tabs-detail" id="detailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tabDocsBtn" data-bs-toggle="tab" data-bs-target="#tabDocs" type="button" role="tab">
                                    Dokumentasi (<span id="detailDocsCount">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tabLogsBtn" data-bs-toggle="tab" data-bs-target="#tabLogs" type="button" role="tab">
                                    Riwayat Kegiatan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="detailTabContent">
                            <div class="tab-pane fade show active" id="tabDocs" role="tabpanel">
                                <div class="gallery-grid" id="detailGalleryGrid"></div>
                                <input type="file" id="directDocUploadInput" class="d-none" multiple accept="image/*,.pdf,.doc,.docx" onchange="uploadDirectDoc(this)">
                            </div>

                            <div class="tab-pane fade" id="tabLogs" role="tabpanel">
                                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                    <table class="table table-sm table-hover" style="font-size: 11px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Pengguna</th>
                                                <th>Aksi</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailLogsTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW GAMBAR BESAR -->
<div class="modal fade" id="modalImagePreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-2 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="modalPreviewImage" src="" alt="Preview Foto" class="img-fluid rounded shadow" style="max-height: 80vh;">
                <div id="modalPreviewTitle" class="text-white mt-2 fw-semibold" style="font-size: 13px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Form for Delete -->
<form id="deleteEventForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentDetailEventId = null;

    const monthlyLabels = {!! json_encode($monthsLabels) !!};
    const monthlyDatasetsRaw = {!! json_encode($monthlyDatasets) !!};
    const categoryChartData = {!! json_encode($categoryChartData) !!};
    const unitChartData = {!! json_encode($unitChartData) !!};

    const categoryColors = {
        'Pelatihan': '#3b82f6',
        'Safety Talk': '#06b6d4',
        'Inspeksi': '#f97316',
        'Kampanye': '#f43f5e',
        'Simulasi/Drill': '#a855f7',
        'Sosialisasi': '#10b981',
        'Lainnya': '#ec4899'
    };

    flatpickr("#dateRangePicker", {
        mode: "range",
        dateFormat: "d/m/Y",
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                document.getElementById('filterForm').submit();
            }
        }
    });

    ['filterStatus', 'filterCategory', 'filterUnit', 'filterLocation'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => document.getElementById('filterForm').submit());
        }
    });

    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterPerPage').value = this.value;
            document.getElementById('filterForm').submit();
        });
    }

    const inlineTableSearch = document.getElementById('inlineTableSearch');
    if (inlineTableSearch) {
        inlineTableSearch.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('filterSearch').value = this.value;
                document.getElementById('filterForm').submit();
            }
        });
    }

    // Chart 1: Jumlah Kegiatan per Bulan
    const ctxMonthly = document.getElementById('monthlyEventsChart').getContext('2d');
    const monthlyDatasetsFormatted = Object.keys(monthlyDatasetsRaw).map(catName => ({
        label: catName,
        data: monthlyDatasetsRaw[catName],
        backgroundColor: categoryColors[catName] || '#94a3b8',
        borderRadius: 3,
        borderSkipped: false
    }));

    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: monthlyDatasetsFormatted
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#64748b' }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 2, font: { size: 10 }, color: '#64748b' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });

    const monthlyLegendContainer = document.getElementById('monthlyLegendList');
    if (monthlyLegendContainer) {
        monthlyLegendContainer.innerHTML = Object.keys(monthlyDatasetsRaw).map(catName => `
            <div class="custom-legend-item">
                <span class="legend-indicator square" style="background: ${categoryColors[catName] || '#94a3b8'}"></span>
                <span class="legend-name">${catName}</span>
            </div>
        `).join('');
    }

    // Chart 2: Kegiatan per Kategori (Donut)
    const ctxCategory = document.getElementById('categoryDonutChart').getContext('2d');
    new Chart(ctxCategory, {
        type: 'doughnut',
        data: {
            labels: categoryChartData.map(c => c.name),
            datasets: [{
                data: categoryChartData.map(c => c.count),
                backgroundColor: categoryChartData.map(c => c.color),
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const val = context.raw || 0;
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${context.label}: ${val} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // Chart 3: Kegiatan per Unit/Komponen (Horizontal Bar)
    const ctxUnit = document.getElementById('unitBarChart').getContext('2d');
    new Chart(ctxUnit, {
        type: 'bar',
        data: {
            labels: unitChartData.map(u => u.unit),
            datasets: [{
                data: unitChartData.map(u => u.count),
                backgroundColor: unitChartData.map(u => u.color),
                borderRadius: 4,
                barThickness: 14
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.raw} kegiatan` } }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 5, font: { size: 10 }, color: '#64748b' },
                    grid: { color: '#f1f5f9' }
                },
                y: {
                    ticks: { font: { size: 11, weight: '600' }, color: '#334155' },
                    grid: { display: false }
                }
            }
        }
    });

    async function openDetailModal(id, activeTab = 'docs') {
        currentDetailEventId = id;
        try {
            const response = await fetch(`/events/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await response.json();

            if (res.success) {
                const e = res.event;
                document.getElementById('detailJudul').textContent = e.title;
                document.getElementById('detailKategori').textContent = e.category;
                document.getElementById('detailUnit').textContent = e.unit;

                const dateFormatted = e.event_date ? new Date(e.event_date).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'long', year: 'numeric'
                }) : '-';
                document.getElementById('detailTanggal').textContent = dateFormatted;
                document.getElementById('detailLokasi').textContent = e.location;
                document.getElementById('detailPeserta').textContent = (e.participants || 0) + ' orang';

                const badge = document.getElementById('detailStatusBadge');
                badge.textContent = e.status;
                badge.className = 'badge-status ' + (
                    e.status === 'Selesai' ? 'status-selesai' :
                    e.status === 'Berlangsung' ? 'status-berlangsung' :
                    e.status === 'Dibatalkan' ? 'status-dibatalkan' : 'status-direncanakan'
                );

                document.getElementById('detailDeskripsi').textContent = e.description || '-';
                document.getElementById('detailDibuat').textContent = (e.created_by || 'Budi Santoso') +
                    (e.created_at ? ' (' + new Date(e.created_at).toLocaleDateString('id-ID') + ')' : '');
                document.getElementById('detailDiubah').textContent = (e.updated_by || 'Rina Dewi') +
                    (e.updated_at ? ' (' + new Date(e.updated_at).toLocaleDateString('id-ID') + ')' : '');

                document.getElementById('detailDocsCount').textContent = res.documents.length;
                const gallery = document.getElementById('detailGalleryGrid');
                let galleryHtml = '';

                res.documents.forEach((doc, idx) => {
                    const isImg = doc.mime_type && doc.mime_type.startsWith('image/');
                    const thumbSrc = isImg ? doc.file_url : '/images/backgrounds/login-dieng.webp';

                    galleryHtml += `
                        <div class="gallery-item-card" onclick="previewImage('${doc.file_url}', '${doc.title}')">
                            <img src="${thumbSrc}" alt="${doc.title}" class="gallery-thumb-img" onerror="this.src='/images/backgrounds/login-dieng.webp'">
                            <div class="gallery-label">${doc.title || 'Foto Kegiatan ' + (idx + 1)}</div>
                        </div>
                    `;
                });

                galleryHtml += `
                    <div class="gallery-upload-card" onclick="document.getElementById('directDocUploadInput').click()">
                        <i class="bi bi-plus-lg fs-4"></i>
                        <span style="font-size:10px; font-weight:600">Upload Foto</span>
                    </div>
                `;
                gallery.innerHTML = galleryHtml;

                const logTable = document.getElementById('detailLogsTableBody');
                if (res.logs && res.logs.length > 0) {
                    logTable.innerHTML = res.logs.map(log => `
                        <tr>
                            <td>${log.created_at || '-'}</td>
                            <td class="fw-semibold">${log.user_name || 'Admin'}</td>
                            <td><span class="badge bg-secondary">${log.action}</span></td>
                            <td>${log.description}</td>
                        </tr>
                    `).join('');
                } else {
                    logTable.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada riwayat aktivitas.</td></tr>`;
                }

                new bootstrap.Tab(document.getElementById('tabDocsBtn')).show();
                new bootstrap.Modal(document.getElementById('modalDetailEvent')).show();
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Gagal memuat detail kegiatan', 'error');
        }
    }

    function previewImage(url, title) {
        document.getElementById('modalPreviewImage').src = url;
        document.getElementById('modalPreviewTitle').textContent = title || 'Dokumentasi Kegiatan';
        new bootstrap.Modal(document.getElementById('modalImagePreview')).show();
    }

    async function uploadDirectDoc(input) {
        if (!input.files || input.files.length === 0 || !currentDetailEventId) return;

        const formData = new FormData();
        for (let i = 0; i < input.files.length; i++) {
            formData.append('files[]', input.files[i]);
        }
        formData.append('_token', csrfToken);

        try {
            Swal.fire({
                title: 'Mengunggah...',
                text: 'Sedang menyimpan file dokumentasi',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch(`/events/${currentDetailEventId}/upload`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();
            if (data.success) {
                Swal.fire('Berhasil', data.message, 'success');
                openDetailModal(currentDetailEventId, 'docs');
            } else {
                Swal.fire('Gagal', data.message || 'Gagal mengunggah foto', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat upload', 'error');
        }
    }

    async function openEditModal(id) {
        try {
            const response = await fetch(`/events/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await response.json();

            if (res.success) {
                const e = res.event;
                const form = document.getElementById('formEditEvent');
                form.action = `/events/${id}`;

                document.getElementById('editTitle').value = e.title;
                document.getElementById('editCategory').value = e.category;
                document.getElementById('editUnit').value = e.unit;
                document.getElementById('editLocation').value = e.location;
                document.getElementById('editEventDate').value = e.event_date ? e.event_date.split('T')[0] : '';
                document.getElementById('editParticipants').value = e.participants || 0;
                document.getElementById('editStatus').value = e.status;
                document.getElementById('editPic').value = e.pic || '';
                document.getElementById('editStartTime').value = e.start_time || '';
                document.getElementById('editEndTime').value = e.end_time || '';
                document.getElementById('editDescription').value = e.description || '';

                new bootstrap.Modal(document.getElementById('modalEditEvent')).show();
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal memuat data kegiatan untuk diedit', 'error');
        }
    }

    function confirmDeleteEvent(id, title) {
        Swal.fire({
            title: 'Hapus Kegiatan?',
            html: `Apakah Anda yakin ingin menghapus kegiatan <b>"${title}"</b>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan dan seluruh dokumentasi terkait akan terhapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteEventForm');
                form.action = `/events/${id}`;
                form.submit();
            }
        });
    }

    function previewFilesTambah(input) {
        const container = document.getElementById('previewFilesTambahContainer');
        container.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-dark border p-2';
                badge.innerHTML = `<i class="bi bi-file-earmark me-1 text-primary"></i> ${file.name} (${Math.round(file.size / 1024)} KB)`;
                container.appendChild(badge);
            });
        }
    }

    function previewFilesEdit(input) {
        const container = document.getElementById('previewFilesEditContainer');
        container.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-dark border p-2';
                badge.innerHTML = `<i class="bi bi-file-earmark me-1 text-primary"></i> ${file.name} (${Math.round(file.size / 1024)} KB)`;
                container.appendChild(badge);
            });
        }
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
        });
    @endif
</script>
@endpush

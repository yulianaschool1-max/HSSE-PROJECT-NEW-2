@extends('layouts.app', [
    'title' => 'Incident',
    'activeModule' => 'incident'
])

@push('styles')
<style>
    .header-icon-box.red {
        background: #dc2626;
        color: #ffffff;
    }
    :root {
            --navy: #102b3f;
            --navy-dark: #071e2e;
            --green: #07845c;
            --red-incident: #dc2626;
            --red-soft: #fef2f2;
            --page-bg: #f8fafc;
            --border: #e2e8f0;
            --muted: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--page-bg);
            color: #1e293b;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 245px;
            min-height: 100vh;
        }

        .topbar {
            height: 54px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .content {
            padding: 24px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .page-heading h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: bold;
        }

        .breadcrumb-text {
            color: var(--muted);
            font-size: 11px;
        }

        /* STAT CARDS INCIDENT */
        .incident-stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 18px;
            height: 100%;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: transform .15s, box-shadow .15s;
        }

        .incident-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 43, 63, 0.08);
        }

        .incident-stat-card .stat-card-value {
            font-size: 26px;
            line-height: 1.2;
            margin: 6px 0 4px;
        }

        /* 3 DONUT CHARTS PANEL - KORPORAT & ELEGAN */
        .incident-charts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .incident-charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .incident-chart-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .incident-chart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(16, 43, 63, 0.08);
            border-color: #e2e8f0;
        }

        .incident-chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .incident-chart-header h6 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.2px;
        }

        .incident-chart-box {
            position: relative;
            height: 205px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px;
        }

        /* BADGES */
        .badge-ebtke { background: #e0e7ff; color: #3730a3; font-weight: 600; border-radius: 4px; padding: 3px 6px; }
        .badge-umum { background: #f1f5f9; color: #475569; font-weight: 600; border-radius: 4px; padding: 3px 6px; }
        .badge-panas-bumi { background: #ffedd5; color: #c2410c; font-weight: 600; border-radius: 4px; padding: 3px 6px; }
        .badge-non-panas { background: #f1f5f9; color: #334155; font-weight: 600; border-radius: 4px; padding: 3px 6px; }
        .modal-badge-counter {
            background: #e2e8f0;
            color: #0f172a;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: 0.2px;
            border: 1px solid #cbd5e1;
        }

        /* Tag Styling Spesifik */
        .tag-pill-sm {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            margin: 1px;
        }
        .tag-pill-umum { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .tag-pill-ebtke { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .tag-pill-fatality { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: bold; }

        /* Filter Row Grid */
        .filter-grid-row {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.3fr 1.3fr 2.3fr 1.2fr;
            gap: 10px;
            align-items: flex-end;
        }

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f6;
            background: #f8fafc;
        }
        .modal-body.scrollable-modal-body {
            max-height: calc(85vh - 130px);
            overflow-y: auto;
            padding: 22px;
        }
        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #eef2f6;
            background: #f8fafc;
        }

        /* ==================== UI/UX EXPERT STYLES ==================== */
        /* 1. Toggle Biner Kanan-Kiri */
        .binary-toggle-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .binary-toggle-card {
            border: 2px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all .2s ease-in-out;
            display: flex;
            align-items: center;
            gap: 10px;
            user-select: none;
            position: relative;
        }

        .binary-toggle-card:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .binary-toggle-card.active-panas {
            border-color: #ea580c;
            background: #fff7ed;
            color: #9a3412;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.12);
        }

        .binary-toggle-card.active-non-panas {
            border-color: #0284c7;
            background: #f0f9ff;
            color: #0369a1;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12);
        }

        .binary-radio-indicator {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .2s;
        }

        .binary-toggle-card.active-panas .binary-radio-indicator {
            border-color: #ea580c;
            background: #ea580c;
        }
        .binary-toggle-card.active-non-panas .binary-radio-indicator {
            border-color: #0284c7;
            background: #0284c7;
        }
        .binary-radio-indicator::after {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: white;
            display: block;
        }

        /* 2. Tagging System Ala "Add Skill" LinkedIn */
        .linkedin-tag-container {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            background: #ffffff;
            min-height: 44px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            transition: border-color .15s, box-shadow .15s;
        }

        .linkedin-tag-container:focus-within {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }

        .linkedin-selected-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0a66c2; /* Khas LinkedIn */
            color: #ffffff;
            border-radius: 16px;
            padding: 4px 10px 4px 12px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.2px;
            box-shadow: 0 1px 3px rgba(10, 102, 194, 0.25);
            animation: badgePop .18s ease-out;
        }

        .linkedin-selected-badge.badge-ebtke-style {
            background: #0f766e; /* EBTKE Teal Dark */
            box-shadow: 0 1px 3px rgba(15, 118, 110, 0.25);
        }

        @keyframes badgePop {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .linkedin-remove-btn {
            background: transparent;
            border: 0;
            color: rgba(255, 255, 255, 0.85);
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            font-size: 13px;
            line-height: 1;
            transition: color .15s;
        }
        .linkedin-remove-btn:hover {
            color: #ffffff;
        }

        /* Suggested Badges Pills (Klik untuk Add) */
        .tag-suggestions-box {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .tag-suggestion-pill {
            background: #f1f5f9;
            color: #334155;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .tag-suggestion-pill:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }

        .tag-suggestion-pill.selected {
            opacity: 0.45;
            pointer-events: none;
            text-decoration: line-through;
            border-style: solid;
        }

        @media (max-width: 1200px) {
            .filter-grid-row { grid-template-columns: repeat(3, 1fr); }
            .incident-charts-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .filter-grid-row { grid-template-columns: 1fr; }
            .incident-charts-grid { grid-template-columns: 1fr; }
            .sidebar { position: static; width: 100%; }
            .main { margin-left: 0; }
            .binary-toggle-container { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
<!-- HEADER & ACTIONS GLOBAL INCIDENT (TEMA MERAH) -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box red">
            <i class="bi bi-exclamation-octagon"></i>
        </div>
        <div>
            <h1 class="page-heading">Incident</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Incident</span>
                <span class="mx-1 text-muted">·</span>
                <span class="text-muted">Pencatatan Insiden & Kecelakaan Kerja Lapangan</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <!-- Tombol Export PDF -->
        @canExportPdf
        <a href="{{ route('incident.export', request()->query()) }}" class="btn-action-outline text-decoration-none">
            <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Export PDF
        </a>
        @endcanExportPdf
        <!-- Tombol Lapor Insiden Baru -->
        @adminManagement
            <button class="btn-action-primary bg-danger border-danger" data-bs-toggle="modal" data-bs-target="#modalTambahIncident">
                <i class="bi bi-plus-lg me-1"></i> + Lapor Insiden Baru
            </button>
        @endadminManagement
    </div>
</div>

            <!-- Flash Alert -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- ==================== 3 GRAFIK LINGKARAN (DONUT CHARTS) BERDAMPINGAN ==================== -->
            <div class="incident-charts-grid">
                <!-- Grafik 1: Kategori Insiden (Area Kejadian) -->
                <div class="incident-chart-card bg-white rounded-xl shadow-sm border border-slate-100">
                    <div class="incident-chart-header">
                        <h6>
                            <i class="bi bi-pie-chart-fill text-danger"></i> 1. Kategori Insiden
                        </h6>
                        <span class="badge bg-light text-secondary font-monospace border" style="font-size: 11px;">
                            Total: {{ $chartArea['total'] }} Kasus
                        </span>
                    </div>
                    <div class="incident-chart-box">
                        <canvas id="chartAreaCanvas"></canvas>
                    </div>
                    <div class="d-flex justify-content-around mt-2 pt-2 border-top text-center" style="font-size: 11px;">
                        <div style="cursor: pointer;" onclick="openChartDetailModal('area', 'Kecelakaan Panas Bumi')" title="Klik untuk melihat insiden Panas Bumi">
                            <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #ea580c;"></span>
                            <span class="text-muted">Panas Bumi:</span> 
                            <strong class="text-dark">{{ $chartArea['data'][0] ?? 0 }}</strong>
                        </div>
                        <div style="cursor: pointer;" onclick="openChartDetailModal('area', 'Kecelakaan Non-Panas Bumi')" title="Klik untuk melihat insiden Non-Panas Bumi">
                            <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #0284c7;"></span>
                            <span class="text-muted">Non-Panas:</span> 
                            <strong class="text-dark">{{ $chartArea['data'][1] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Grafik 2: Kategori Umum (Multiple Tags Sebaran) -->
                <div class="incident-chart-card bg-white rounded-xl shadow-sm border border-slate-100">
                    <div class="incident-chart-header">
                        <h6>
                            <i class="bi bi-tags-fill text-danger"></i> 2. Kategori Umum (Tags)
                        </h6>
                        <span class="badge bg-light text-secondary font-monospace border" style="font-size: 11px;">
                            Total Tag: {{ $chartUmum['total'] }}
                        </span>
                    </div>
                    <div class="incident-chart-box">
                        <canvas id="chartUmumCanvas"></canvas>
                    </div>
                    <div class="mt-2 pt-2 border-top text-center" style="font-size: 11px;">
                        <span class="text-muted">Akumulasi sebaran tag: </span>
                        <strong class="text-secondary">{{ count(array_filter($chartUmum['data'])) }} Jenis Tag Terisi</strong>
                    </div>
                </div>

                <!-- Grafik 3: Kategori EBTKE (Standar ESDM) -->
                <div class="incident-chart-card bg-white rounded-xl shadow-sm border border-slate-100">
                    <div class="incident-chart-header">
                        <h6>
                            <i class="bi bi-award-fill text-danger"></i> 3. Kategori EBTKE (Tags)
                        </h6>
                        <span class="badge bg-light text-secondary font-monospace border" style="font-size: 11px;">
                            Total Tag: {{ $chartEbtke['total'] }}
                        </span>
                    </div>
                    <div class="incident-chart-box">
                        <canvas id="chartEbtkeCanvas"></canvas>
                    </div>
                    <div class="mt-2 pt-2 border-top text-center" style="font-size: 11px;">
                        <span class="text-muted">Akumulasi sebaran tag: </span>
                        <strong class="text-secondary">{{ count(array_filter($chartEbtke['data'])) }} Jenis Tag Terisi</strong>
                    </div>
                </div>
            </div>

            <!-- FILTER KOMPREHENSIF INSIDEN -->
            <div class="panel mb-4">
                <form method="GET" action="{{ route('incident.index') }}" class="filter-grid-row">
                    <!-- 1. Kata Kunci -->
                    <div>
                        <label class="form-label small fw-bold mb-1 text-secondary">Cari Insiden</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="No. Insiden, Lokasi, PIC, Tag...">
                    </div>

                    <!-- 2. Klasifikasi Area -->
                    <div>
                        <label class="form-label small fw-bold mb-1 text-secondary">Klasifikasi Area</label>
                        <select name="klasifikasi_area" class="form-select form-select-sm">
                            <option value="">Semua Area</option>
                            @foreach($areaList as $area)
                                <option value="{{ $area }}" {{ request('klasifikasi_area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Kategori Umum -->
                    <div>
                        <label class="form-label small fw-bold mb-1 text-secondary">Kategori Umum</label>
                        <select name="kategori_umum" class="form-select form-select-sm">
                            <option value="">Semua Tag Umum</option>
                            @foreach($kategoriUmumList as $ku)
                                <option value="{{ $ku }}" {{ request('kategori_umum') == $ku ? 'selected' : '' }}>{{ $ku }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Kategori EBTKE -->
                    <div>
                        <label class="form-label small fw-bold mb-1 text-secondary">Kategori EBTKE</label>
                        <select name="kategori_ebtke" class="form-select form-select-sm">
                            <option value="">Semua Tag EBTKE</option>
                            @foreach($kategoriEbtkeList as $ke)
                                <option value="{{ $ke }}" {{ request('kategori_ebtke') == $ke ? 'selected' : '' }}>{{ $ke }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Rentang Tanggal -->
                    <div>
                        <label class="form-label small fw-bold mb-1 text-secondary">Rentang Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control" title="Mulai">
                            <span class="input-group-text bg-light text-muted px-2">s/d</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control" title="Sampai">
                        </div>
                    </div>

                    <!-- 6. Tombol Aksi -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <a href="{{ route('incident.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR INSIDEN -->
            <div class="panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Insiden</th>
                                <th>Tanggal & Waktu</th>
                                <th>Klasifikasi Area</th>
                                <th>Kategori Umum (Tags)</th>
                                <th>Kategori EBTKE (Tags)</th>
                                <th>Lokasi & Komponen</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-3" style="width: 130px; min-width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidents as $inc)
                                <tr>
                                    <td class="ps-3">
                                        <span class="badge bg-danger-subtle text-danger font-monospace border border-danger-subtle">{{ $inc->nomor_insiden }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        <i class="bi bi-calendar-event text-muted me-1"></i>
                                        {{ $inc->tanggal_waktu->format('d/m/Y') }}
                                        <div class="text-muted" style="font-size: 10px;">{{ $inc->tanggal_waktu->format('H:i') }} WIB</div>
                                    </td>
                                    <td>
                                        @if($inc->klasifikasi_area === 'Kecelakaan Panas Bumi' || $inc->klasifikasi_area === 'Panas Bumi')
                                            <span class="badge-panas-bumi">
                                                <i class="bi bi-fire me-1"></i>Kec. Panas Bumi
                                            </span>
                                        @else
                                            <span class="badge-non-panas">
                                                <i class="bi bi-building me-1"></i>Kec. Non-Panas Bumi
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                            @php
                                                $uTags = is_array($inc->kategori_umum) ? $inc->kategori_umum : (json_decode($inc->kategori_umum, true) ?: []);
                                            @endphp
                                            @forelse($uTags as $tag)
                                                <span class="tag-pill-sm {{ $tag === 'Fatality' ? 'tag-pill-fatality' : 'tag-pill-umum' }}">
                                                    {{ $tag }}
                                                </span>
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                            @php
                                                $eTags = is_array($inc->kategori_ebtke) ? $inc->kategori_ebtke : (json_decode($inc->kategori_ebtke, true) ?: []);
                                            @endphp
                                            @forelse($eTags as $tag)
                                                <span class="tag-pill-sm {{ $tag === 'Kecelakaan Mati' ? 'tag-pill-fatality' : 'tag-pill-ebtke' }}">
                                                    {{ $tag }}
                                                </span>
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><i class="bi bi-geo-alt text-danger me-1"></i>{{ $inc->lokasi_kejadian }}</div>
                                        <small class="text-muted">Unit: {{ $inc->komponen_terkait }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($inc->status_laporan === 'Final')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Final</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Preliminary</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3 text-nowrap" style="white-space: nowrap;">
                                        <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                            <!-- Tombol Detail / Investigasi -->
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="openDetailModal({{ json_encode($inc) }})" 
                                                    title="Lihat Detail Investigasi">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            @adminManagement
                                                <!-- Tombol Edit -->
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="openEditModal({{ json_encode($inc) }})" 
                                                        title="Edit Laporan Insiden">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Tombol Hapus Pop-up -->
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="openDeleteConfirmation({{ $inc->id }}, '{{ addslashes($inc->nomor_insiden) }}', '{{ addslashes($inc->lokasi_kejadian) }}')" 
                                                        title="Hapus Laporan">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endadminManagement
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                                        Tidak ada catatan insiden kecelakaan kerja yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $incidents->links('pagination::bootstrap-5') }}
                </div>
            </div>

    <!-- ==================== MODAL TAMBAH INSIDEN (ALPINE.JS INTERACTIVE) ==================== -->
    <div class="modal fade" id="modalTambahIncident" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('incident.store') }}" method="POST" enctype="multipart/form-data" 
                  class="modal-content"
                  x-data="incidentFormHandler({
                      area: 'Kecelakaan Panas Bumi',
                      umum: [],
                      ebtke: []
                  })">
                @csrf
                <div class="modal-header bg-light">
                    <div>
                        <h6 class="modal-title fw-bold text-danger mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Lapor Insiden Kecelakaan Kerja Baru
                        </h6>
                        <small class="text-muted">Pencatatan klasifikasi area biner & multiple tags kategori</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <div class="row g-3">
                        <!-- Baris 1: No. Insiden & Tanggal -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nomor Insiden (Auto-Generated)</label>
                            <input type="text" name="nomor_insiden" class="form-control form-control-sm font-monospace bg-light" value="{{ $nextIncidentNumber }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal & Waktu Kejadian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_waktu" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                        </div>

                        <!-- 1. KLASIFIKASI AREA: PILIHAN BINER KANAN-KIRI -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block mb-1">
                                Klasifikasi Area Kejadian <span class="text-danger">*</span>
                                <small class="text-muted fw-normal">(Pilih salah satu)</small>
                            </label>
                            
                            <!-- Hidden input untuk submit form -->
                            <input type="hidden" name="klasifikasi_area" :value="area">

                            <div class="binary-toggle-container">
                                <!-- Opsi Kiri: Kecelakaan Panas Bumi -->
                                <div class="binary-toggle-card" 
                                     :class="{ 'active-panas': area === 'Kecelakaan Panas Bumi' }"
                                     @click="area = 'Kecelakaan Panas Bumi'">
                                    <div class="binary-radio-indicator"></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">
                                            <i class="bi bi-fire text-danger me-1"></i> Kecelakaan Panas Bumi
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Area operasi, wellpad, power plant, atau fasilitas geotermal</small>
                                    </div>
                                </div>

                                <!-- Opsi Kanan: Kecelakaan Non-Panas Bumi -->
                                <div class="binary-toggle-card" 
                                     :class="{ 'active-non-panas': area === 'Kecelakaan Non-Panas Bumi' }"
                                     @click="area = 'Kecelakaan Non-Panas Bumi'">
                                    <div class="binary-radio-indicator"></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">
                                            <i class="bi bi-building text-primary me-1"></i> Kecelakaan Non-Panas Bumi
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Kantor administrasi, mess, jalan raya luar, atau fasilitas umum</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. KATEGORI UMUM (SISTEM "ADD SKILL") -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="bi bi-tags text-primary me-1"></i> Kategori Umum
                                </span>
                                <span class="badge bg-light text-primary border" x-text="selectedUmum.length + ' tag terpilih'"></span>
                            </label>

                            <!-- Container Tags Terpilih Ala LinkedIn -->
                            <div class="linkedin-tag-container">
                                <template x-for="(tag, idx) in selectedUmum" :key="tag">
                                    <div class="linkedin-selected-badge">
                                        <span x-text="tag"></span>
                                        <button type="button" class="linkedin-remove-btn" @click="removeUmum(tag)" title="Hapus tag">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                        <!-- Hidden input array per tag yang terpilih -->
                                        <input type="hidden" name="kategori_umum[]" :value="tag">
                                    </div>
                                </template>
                                <span x-show="selectedUmum.length === 0" class="text-muted small ps-1" style="font-size: 11px;">
                                    Belum ada tag dipilih. Klik tombol pilihan di bawah untuk menambahkan:
                                </span>
                            </div>

                            <!-- Opsi Tag Tersedia (LinkedIn Style Suggestion Pills) -->
                            <div class="tag-suggestions-box">
                                @foreach($kategoriUmumList as $itemUmum)
                                    <span class="tag-suggestion-pill"
                                          :class="{ 'selected': selectedUmum.includes('{{ $itemUmum }}') }"
                                          @click="addUmum('{{ $itemUmum }}')">
                                        <i class="bi bi-plus text-muted" x-show="!selectedUmum.includes('{{ $itemUmum }}')"></i>
                                        <i class="bi bi-check text-success" x-show="selectedUmum.includes('{{ $itemUmum }}')"></i>
                                        {{ $itemUmum }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. KATEGORI EBTKE (SISTEM "ADD SKILL") -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span>
                                    <i class="bi bi-award text-success me-1"></i> Kategori EBTKE
                                </span>
                                <span class="badge bg-light text-success border" x-text="selectedEbtke.length + ' tag terpilih'"></span>
                            </label>

                            <!-- Container Tags Terpilih EBTKE Ala LinkedIn -->
                            <div class="linkedin-tag-container" style="background: #fbfdfc;">
                                <template x-for="(tag, idx) in selectedEbtke" :key="tag">
                                    <div class="linkedin-selected-badge badge-ebtke-style">
                                        <span x-text="tag"></span>
                                        <button type="button" class="linkedin-remove-btn" @click="removeEbtke(tag)" title="Hapus tag">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                        <!-- Hidden input array per tag yang terpilih -->
                                        <input type="hidden" name="kategori_ebtke[]" :value="tag">
                                    </div>
                                </template>
                                <span x-show="selectedEbtke.length === 0" class="text-muted small ps-1" style="font-size: 11px;">
                                    Opsional: Klik tag kategori EBTKE di bawah untuk laporan ke instansi ESDM/EBTKE:
                                </span>
                            </div>

                            <!-- Opsi Tag Tersedia EBTKE -->
                            <div class="tag-suggestions-box">
                                @foreach($kategoriEbtkeList as $itemEbtke)
                                    <span class="tag-suggestion-pill"
                                          :class="{ 'selected': selectedEbtke.includes('{{ $itemEbtke }}') }"
                                          @click="addEbtke('{{ $itemEbtke }}')">
                                        <i class="bi bi-plus text-muted" x-show="!selectedEbtke.includes('{{ $itemEbtke }}')"></i>
                                        <i class="bi bi-check text-success" x-show="selectedEbtke.includes('{{ $itemEbtke }}')"></i>
                                        {{ $itemEbtke }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Baris: Lokasi & Komponen Terkait -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lokasi Kejadian <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi_kejadian" class="form-control form-control-sm" placeholder="Contoh: Wellpad 28 / Power Plant Unit 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Komponen / Unit Terkait <span class="text-danger">*</span></label>
                            <input type="text" name="komponen_terkait" class="form-control form-control-sm" placeholder="Contoh: GDE, Utility, Kontraktor PT X" required>
                        </div>

                        <!-- Baris: Deskripsi Kejadian -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Deskripsi Kejadian (Kronologi Singkat) <span class="text-danger">*</span></label>
                            <textarea name="deskripsi_kejadian" class="form-control form-control-sm" rows="3" placeholder="Jelaskan apa yang terjadi, siapa yang terlibat, dan kondisi korban/peralatan..." required></textarea>
                        </div>

                        <!-- Baris: Tindakan Perbaikan & Penanggung Jawab -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Tindakan Perbaikan / Korektif</label>
                            <textarea name="tindakan_perbaikan" class="form-control form-control-sm" rows="2" placeholder="Tindakan segera yang telah diambil untuk mitigasi..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Penanggung Jawab (PIC)</label>
                            <input type="text" name="penanggung_jawab" class="form-control form-control-sm" placeholder="Nama PIC Penanganan">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Target Penyelesaian</label>
                            <input type="date" name="target_penyelesaian" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Status Laporan <span class="text-danger">*</span></label>
                            <select name="status_laporan" class="form-select form-select-sm" required>
                                <option value="Preliminary">Preliminary (Awal)</option>
                                <option value="Final">Final (Selesai)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Jumlah Hari Hilang</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="jumlah_hari_hilang" class="form-control form-control-sm" min="0" value="0" placeholder="0">
                                <span class="input-group-text bg-light text-muted" style="font-size: 11px;">Hari</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 10px;">Indikator LTI-SR (Severity Rate)</div>
                        </div>

                        <!-- Baris: Upload Dokumen Investigasi / Foto -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Upload Dokumen Investigasi / Foto Bukti</label>
                            <input type="file" name="dokumen_investigasi" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Format: PDF, JPG, PNG, DOCX (Maksimal 10MB)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan Laporan Insiden
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT INSIDEN (ALPINE.JS INTERACTIVE) ==================== -->
    <div class="modal fade" id="modalEditIncident" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formEditIncident" method="POST" enctype="multipart/form-data" 
                  class="modal-content"
                  x-data="incidentEditHandler()">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light">
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Insiden
                        </h6>
                        <small class="text-muted">Perbarui klasifikasi, multiple tags kategori, dan dokumen investigasi</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nomor Insiden</label>
                            <input type="text" name="nomor_insiden" id="editNomorInsiden" class="form-control form-control-sm font-monospace bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal & Waktu Kejadian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_waktu" id="editTanggalWaktu" class="form-control form-control-sm" required>
                        </div>

                        <!-- 1. EDIT KLASIFIKASI AREA (PILIHAN BINER KANAN-KIRI) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block mb-1">Klasifikasi Area Kejadian <span class="text-danger">*</span></label>
                            <input type="hidden" name="klasifikasi_area" :value="area">

                            <div class="binary-toggle-container">
                                <div class="binary-toggle-card" 
                                     :class="{ 'active-panas': area === 'Kecelakaan Panas Bumi' }"
                                     @click="area = 'Kecelakaan Panas Bumi'">
                                    <div class="binary-radio-indicator"></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">
                                            <i class="bi bi-fire text-danger me-1"></i> Kecelakaan Panas Bumi
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Fasilitas operasional & pembangkit geotermal</small>
                                    </div>
                                </div>

                                <div class="binary-toggle-card" 
                                     :class="{ 'active-non-panas': area === 'Kecelakaan Non-Panas Bumi' }"
                                     @click="area = 'Kecelakaan Non-Panas Bumi'">
                                    <div class="binary-radio-indicator"></div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">
                                            <i class="bi bi-building text-primary me-1"></i> Kecelakaan Non-Panas Bumi
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Fasilitas umum, kantor, jalan akses non-geotermal</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. EDIT KATEGORI UMUM -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span><i class="bi bi-tags text-primary me-1"></i> Kategori Umum</span>
                                <span class="badge bg-light text-primary border" x-text="selectedUmum.length + ' tag terpilih'"></span>
                            </label>

                            <div class="linkedin-tag-container">
                                <template x-for="(tag, idx) in selectedUmum" :key="tag">
                                    <div class="linkedin-selected-badge">
                                        <span x-text="tag"></span>
                                        <button type="button" class="linkedin-remove-btn" @click="removeUmum(tag)">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                        <input type="hidden" name="kategori_umum[]" :value="tag">
                                    </div>
                                </template>
                                <span x-show="selectedUmum.length === 0" class="text-muted small ps-1" style="font-size: 11px;">
                                    Belum ada tag dipilih. Klik tombol opsi di bawah untuk menambahkan:
                                </span>
                            </div>

                            <div class="tag-suggestions-box">
                                @foreach($kategoriUmumList as $itemUmum)
                                    <span class="tag-suggestion-pill"
                                          :class="{ 'selected': selectedUmum.includes('{{ $itemUmum }}') }"
                                          @click="addUmum('{{ $itemUmum }}')">
                                        <i class="bi bi-plus text-muted" x-show="!selectedUmum.includes('{{ $itemUmum }}')"></i>
                                        <i class="bi bi-check text-success" x-show="selectedUmum.includes('{{ $itemUmum }}')"></i>
                                        {{ $itemUmum }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. EDIT KATEGORI EBTKE -->
                        <div class="col-12">
                            <label class="form-label small fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span><i class="bi bi-award text-success me-1"></i> Kategori EBTKE</span>
                                <span class="badge bg-light text-success border" x-text="selectedEbtke.length + ' tag terpilih'"></span>
                            </label>

                            <div class="linkedin-tag-container" style="background: #fbfdfc;">
                                <template x-for="(tag, idx) in selectedEbtke" :key="tag">
                                    <div class="linkedin-selected-badge badge-ebtke-style">
                                        <span x-text="tag"></span>
                                        <button type="button" class="linkedin-remove-btn" @click="removeEbtke(tag)">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                        <input type="hidden" name="kategori_ebtke[]" :value="tag">
                                    </div>
                                </template>
                                <span x-show="selectedEbtke.length === 0" class="text-muted small ps-1" style="font-size: 11px;">
                                    Opsional: Klik tag kategori EBTKE di bawah:
                                </span>
                            </div>

                            <div class="tag-suggestions-box">
                                @foreach($kategoriEbtkeList as $itemEbtke)
                                    <span class="tag-suggestion-pill"
                                          :class="{ 'selected': selectedEbtke.includes('{{ $itemEbtke }}') }"
                                          @click="addEbtke('{{ $itemEbtke }}')">
                                        <i class="bi bi-plus text-muted" x-show="!selectedEbtke.includes('{{ $itemEbtke }}')"></i>
                                        <i class="bi bi-check text-success" x-show="selectedEbtke.includes('{{ $itemEbtke }}')"></i>
                                        {{ $itemEbtke }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lokasi Kejadian <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi_kejadian" id="editLokasiKejadian" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Komponen / Unit Terkait <span class="text-danger">*</span></label>
                            <input type="text" name="komponen_terkait" id="editKomponenTerkait" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Deskripsi Kejadian <span class="text-danger">*</span></label>
                            <textarea name="deskripsi_kejadian" id="editDeskripsiKejadian" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Tindakan Perbaikan / Korektif</label>
                            <textarea name="tindakan_perbaikan" id="editTindakanPerbaikan" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Penanggung Jawab (PIC)</label>
                            <input type="text" name="penanggung_jawab" id="editPenanggungJawab" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Target Penyelesaian</label>
                            <input type="date" name="target_penyelesaian" id="editTargetPenyelesaian" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Status Laporan <span class="text-danger">*</span></label>
                            <select name="status_laporan" id="editStatusLaporan" class="form-select form-select-sm" required>
                                <option value="Preliminary">Preliminary</option>
                                <option value="Final">Final</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Jumlah Hari Hilang</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="jumlah_hari_hilang" id="editJumlahHariHilang" class="form-control form-control-sm" min="0" placeholder="0">
                                <span class="input-group-text bg-light text-muted" style="font-size: 11px;">Hari</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 10px;">Indikator LTI-SR (Severity Rate)</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Ganti Dokumen Investigasi / Foto (Opsional)</label>
                            <input type="file" name="dokumen_investigasi" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div id="currentDocumentInfo" class="mt-1 small text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL DETAIL INVESTIGASI ==================== -->
    <div class="modal fade" id="modalDetailIncident" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-info-circle-fill text-info me-2"></i>Detail Investigasi Insiden
                        </h6>
                        <span id="detailNomor" class="badge bg-danger-subtle text-danger font-monospace"></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tanggal & Waktu:</small>
                            <strong id="detailTanggal"></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Lokasi & Komponen:</small>
                            <strong id="detailLokasi"></strong> (<span id="detailKomponen"></span>)
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Klasifikasi Area:</small>
                            <span id="detailArea" class="badge"></span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Status Laporan:</small>
                            <span id="detailStatus" class="badge"></span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Penanggung Jawab (PIC):</small>
                            <strong id="detailPIC"></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Jumlah Hari Hilang (SR):</small>
                            <strong id="detailHariHilang" class="text-danger">0 Hari Kerja</strong>
                        </div>

                        <!-- Tagging Display in Detail Modal -->
                        <div class="col-md-6 border-top pt-2">
                            <small class="text-muted d-block fw-bold mb-1">Kategori Umum (Tags):</small>
                            <div id="detailTagsUmum" class="d-flex flex-wrap gap-1"></div>
                        </div>
                        <div class="col-md-6 border-top pt-2">
                            <small class="text-muted d-block fw-bold mb-1">Kategori EBTKE (Tags):</small>
                            <div id="detailTagsEbtke" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        <div class="col-12 border-top pt-3">
                            <small class="text-muted d-block fw-bold mb-1">Deskripsi & Kronologi Kejadian:</small>
                            <div id="detailDeskripsi" class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap;"></div>
                        </div>

                        <div class="col-12">
                            <small class="text-muted d-block fw-bold mb-1">Tindakan Perbaikan / Mitigasi:</small>
                            <div id="detailTindakan" class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap;"></div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Target Penyelesaian:</small>
                            <strong id="detailTarget"></strong>
                        </div>

                        <div class="col-12 border-top pt-3" id="detailDokumenContainer">
                            <small class="text-muted d-block fw-bold mb-1">Dokumen Lampiran / Foto Bukti:</small>
                            <div id="detailDokumenLink"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL POP-UP RINCIAN INSIDEN DARI GRAFIK ==================== -->
    <div class="modal fade" id="modalChartDetailIncidents" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <!-- SISI KIRI: Icon & Judul & Badge Kategori -->
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                        <div id="chartModalIconBox" class="brand-mark text-white flex-shrink-0" style="width: 36px; height: 36px; font-size: 16px; border-radius: 8px;">
                            <i id="chartModalIcon" class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="fw-bold text-dark fs-6 mb-0">Rincian Insiden:</span>
                                <span id="chartModalCategoryTitle" class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-6"></span>
                            </div>
                            <small id="chartModalCategorySubtitle" class="text-muted" style="font-size: 11px; margin-top: 1px;"></small>
                        </div>
                    </div>
                    <!-- SISI KANAN: Counter Badge & Tombol Close (X) -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span id="chartModalTotalBadge" class="modal-badge-counter">0 Insiden</span>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- SEARCH / FILTER DALAM MODAL -->
                <div class="px-3 pt-3 pb-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="chartModalSearchInput" class="form-control border-start-0" placeholder="Filter di dalam rincian..." oninput="filterChartModalTable()">
                    </div>
                    <small class="text-muted" style="font-size: 11px;">
                        <i class="bi bi-info-circle me-1"></i>Menampilkan seluruh insiden yang terasosiasi dengan kategori ini
                    </small>
                </div>

                <div class="modal-body scrollable-modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>No. Insiden</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Klasifikasi Area</th>
                                    <th>Kategori Terkait</th>
                                    <th>Lokasi & Unit</th>
                                    <th class="text-center">Hari Hilang</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width: 90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="chartModalTableBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <span id="chartModalShowingText" class="text-muted small">Menampilkan 0 insiden</span>
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL KONFIRMASI HAPUS ==================== -->
    <div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3">
                <div class="modal-body pb-2">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Hapus Data Insiden?</h6>
                    <p class="text-muted small mb-2">
                        Laporan insiden dengan nomor berikut akan dihapus permanen:
                    </p>
                    <div class="p-2 bg-light rounded border mb-2">
                        <div id="deleteIncidentNumber" class="fw-bold text-danger font-monospace"></div>
                        <div id="deleteIncidentLocation" class="text-muted small"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-2">
                    <form id="formHapusIncident" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-light btn-sm px-3 me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            <i class="bi bi-trash me-1"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
        // Alpine Handler Form Tambah Insiden
        function incidentFormHandler(initial = {}) {
            return {
                area: initial.area || 'Kecelakaan Panas Bumi',
                selectedUmum: initial.umum || [],
                selectedEbtke: initial.ebtke || [],

                addUmum(tag) {
                    if (!this.selectedUmum.includes(tag)) {
                        this.selectedUmum.push(tag);
                    }
                },
                removeUmum(tag) {
                    this.selectedUmum = this.selectedUmum.filter(t => t !== tag);
                },
                addEbtke(tag) {
                    if (!this.selectedEbtke.includes(tag)) {
                        this.selectedEbtke.push(tag);
                    }
                },
                removeEbtke(tag) {
                    this.selectedEbtke = this.selectedEbtke.filter(t => t !== tag);
                }
            };
        }

        // Global reference untuk sinkronisasi Alpine edit form
        let activeEditAlpine = null;

        function incidentEditHandler() {
            return {
                area: 'Kecelakaan Panas Bumi',
                selectedUmum: [],
                selectedEbtke: [],

                init() {
                    activeEditAlpine = this;
                },
                addUmum(tag) {
                    if (!this.selectedUmum.includes(tag)) {
                        this.selectedUmum.push(tag);
                    }
                },
                removeUmum(tag) {
                    this.selectedUmum = this.selectedUmum.filter(t => t !== tag);
                },
                addEbtke(tag) {
                    if (!this.selectedEbtke.includes(tag)) {
                        this.selectedEbtke.push(tag);
                    }
                },
                removeEbtke(tag) {
                    this.selectedEbtke = this.selectedEbtke.filter(t => t !== tag);
                }
            };
        }

        // Modal Edit Insiden
        function openEditModal(inc) {
            const form = document.getElementById('formEditIncident');
            form.action = `/incident/${inc.id}`;

            document.getElementById('editNomorInsiden').value = inc.nomor_insiden;
            document.getElementById('editTanggalWaktu').value = inc.tanggal_waktu ? inc.tanggal_waktu.substring(0, 16) : '';
            document.getElementById('editLokasiKejadian').value = inc.lokasi_kejadian;
            document.getElementById('editKomponenTerkait').value = inc.komponen_terkait;
            document.getElementById('editDeskripsiKejadian').value = inc.deskripsi_kejadian;
            document.getElementById('editTindakanPerbaikan').value = inc.tindakan_perbaikan || '';
            document.getElementById('editPenanggungJawab').value = inc.penanggung_jawab || '';
            document.getElementById('editTargetPenyelesaian').value = inc.target_penyelesaian ? inc.target_penyelesaian.substring(0, 10) : '';
            document.getElementById('editStatusLaporan').value = inc.status_laporan;
            document.getElementById('editJumlahHariHilang').value = inc.jumlah_hari_hilang ?? 0;

            // Parsing tags umum & EBTKE
            let rawUmum = inc.kategori_umum;
            if (typeof rawUmum === 'string') {
                try { rawUmum = JSON.parse(rawUmum); } catch(e) { rawUmum = [rawUmum]; }
            }
            if (!Array.isArray(rawUmum)) rawUmum = [];

            let rawEbtke = inc.kategori_ebtke;
            if (typeof rawEbtke === 'string') {
                try { rawEbtke = JSON.parse(rawEbtke); } catch(e) { rawEbtke = [rawEbtke]; }
            }
            if (!Array.isArray(rawEbtke)) rawEbtke = [];

            // Sinkronkan ke Alpine component
            if (activeEditAlpine) {
                activeEditAlpine.area = inc.klasifikasi_area || 'Kecelakaan Panas Bumi';
                activeEditAlpine.selectedUmum = [...rawUmum];
                activeEditAlpine.selectedEbtke = [...rawEbtke];
            }

            const docInfo = document.getElementById('currentDocumentInfo');
            if (inc.dokumen_investigasi) {
                docInfo.innerHTML = `File saat ini: <a href="/storage/${inc.dokumen_investigasi}" target="_blank" class="text-primary text-decoration-none"><i class="bi bi-file-earmark-check me-1"></i>Lihat Dokumen</a>`;
            } else {
                docInfo.innerHTML = 'Belum ada dokumen yang diunggah.';
            }

            const modal = new bootstrap.Modal(document.getElementById('modalEditIncident'));
            modal.show();
        }

        // Modal Detail Investigasi
        function openDetailModal(inc) {
            document.getElementById('detailNomor').textContent = inc.nomor_insiden;
            let tgl = inc.tanggal_waktu || '-';
            if (tgl.includes('T')) {
                tgl = tgl.replace('T', ' ').substring(0, 16) + ' WIB';
            }
            document.getElementById('detailTanggal').textContent = tgl;
            document.getElementById('detailLokasi').textContent = inc.lokasi_kejadian;
            document.getElementById('detailKomponen').textContent = inc.komponen_terkait;
            
            const areaSpan = document.getElementById('detailArea');
            areaSpan.textContent = inc.klasifikasi_area;
            areaSpan.className = inc.klasifikasi_area === 'Kecelakaan Panas Bumi' ? 'badge badge-panas-bumi' : 'badge badge-non-panas';

            document.getElementById('detailDeskripsi').textContent = inc.deskripsi_kejadian;
            document.getElementById('detailTindakan').textContent = inc.tindakan_perbaikan || 'Belum ada catatan tindakan perbaikan.';
            document.getElementById('detailPIC').textContent = inc.penanggung_jawab || '-';
            document.getElementById('detailTarget').textContent = inc.target_penyelesaian ? (inc.target_penyelesaian.substring(0, 10)) : '-';
            document.getElementById('detailHariHilang').textContent = (inc.jumlah_hari_hilang ?? 0) + ' Hari Kerja';

            // Render Tags di Detail Modal
            const tagsUmumBox = document.getElementById('detailTagsUmum');
            tagsUmumBox.innerHTML = '';
            let rawUmum = inc.kategori_umum;
            if (typeof rawUmum === 'string') {
                try { rawUmum = JSON.parse(rawUmum); } catch(e) { rawUmum = [rawUmum]; }
            }
            if (Array.isArray(rawUmum) && rawUmum.length > 0) {
                rawUmum.forEach(t => {
                    tagsUmumBox.innerHTML += `<span class="tag-pill-sm tag-pill-umum">${t}</span>`;
                });
            } else {
                tagsUmumBox.innerHTML = '<span class="text-muted small">-</span>';
            }

            const tagsEbtkeBox = document.getElementById('detailTagsEbtke');
            tagsEbtkeBox.innerHTML = '';
            let rawEbtke = inc.kategori_ebtke;
            if (typeof rawEbtke === 'string') {
                try { rawEbtke = JSON.parse(rawEbtke); } catch(e) { rawEbtke = [rawEbtke]; }
            }
            if (Array.isArray(rawEbtke) && rawEbtke.length > 0) {
                rawEbtke.forEach(t => {
                    tagsEbtkeBox.innerHTML += `<span class="tag-pill-sm tag-pill-ebtke">${t}</span>`;
                });
            } else {
                tagsEbtkeBox.innerHTML = '<span class="text-muted small">-</span>';
            }

            const statusBadge = document.getElementById('detailStatus');
            statusBadge.textContent = inc.status_laporan;
            if (inc.status_laporan === 'Final') {
                statusBadge.className = 'badge bg-success';
            } else {
                statusBadge.className = 'badge bg-warning text-dark';
            }

            const docContainer = document.getElementById('detailDokumenContainer');
            const docLink = document.getElementById('detailDokumenLink');
            if (inc.dokumen_investigasi) {
                docContainer.style.display = 'block';
                docLink.innerHTML = `<a href="/storage/${inc.dokumen_investigasi}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i>Unduh / Buka Dokumen Investigasi</a>`;
            } else {
                docContainer.style.display = 'block';
                docLink.innerHTML = '<span class="text-muted small">Tidak ada dokumen lampiran.</span>';
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDetailIncident'));
            modal.show();
        }

        // Modal Konfirmasi Hapus
        function openDeleteConfirmation(id, nomor, lokasi) {
            const form = document.getElementById('formHapusIncident');
            form.action = `/incident/${id}`;

            document.getElementById('deleteIncidentNumber').textContent = nomor;
            document.getElementById('deleteIncidentLocation').textContent = lokasi;

            const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
            modal.show();
        }

        // Data Lengkap Insiden untuk Interaktivitas Modal Grafik
        const allIncidentsData = @json($chartDetailIncidents);
        let currentFilteredModalData = [];

        // Fungsi Buka Modal Detail dari Klik Grafik
        function openChartDetailModal(chartType, selectedLabel) {
            if (!selectedLabel || selectedLabel === 'Belum ada data' || selectedLabel === 'Belum ada tag') return;

            let iconClass = 'bi-pie-chart-fill';
            let iconBg = 'background: #ea580c;';
            let subtitle = '';

            if (chartType === 'area') {
                iconClass = 'bi-fire';
                const isPanas = selectedLabel.includes('Panas') && !selectedLabel.includes('Non');
                iconBg = isPanas ? 'background: #ea580c;' : 'background: #0284c7;';
                subtitle = 'Insiden berdasarkan Klasifikasi Area Kejadian';
                currentFilteredModalData = allIncidentsData.filter(inc => {
                    const area = (inc.klasifikasi_area || '');
                    return isPanas ? (!area.includes('Non') && area.includes('Panas')) : area.includes('Non');
                });
            } else if (chartType === 'umum') {
                iconClass = 'bi-tags-fill';
                iconBg = 'background: #0d6efd;';
                subtitle = 'Insiden yang terasosiasi dengan Tag Kategori Umum terpilih';
                currentFilteredModalData = allIncidentsData.filter(inc => inc.kategori_umum && inc.kategori_umum.includes(selectedLabel));
            } else if (chartType === 'ebtke') {
                iconClass = 'bi-award-fill';
                iconBg = 'background: #198754;';
                subtitle = 'Insiden yang terasosiasi dengan Tag Kategori EBTKE (ESDM) terpilih';
                currentFilteredModalData = allIncidentsData.filter(inc => inc.kategori_ebtke && inc.kategori_ebtke.includes(selectedLabel));
            }

            document.getElementById('chartModalIcon').className = 'bi ' + iconClass;
            document.getElementById('chartModalIconBox').style = iconBg;
            document.getElementById('chartModalCategoryTitle').textContent = selectedLabel;
            document.getElementById('chartModalCategorySubtitle').textContent = subtitle;
            document.getElementById('chartModalTotalBadge').textContent = currentFilteredModalData.length + ' Insiden';
            document.getElementById('chartModalSearchInput').value = '';

            renderChartModalTable(currentFilteredModalData);

            const modal = new bootstrap.Modal(document.getElementById('modalChartDetailIncidents'));
            modal.show();
        }

        function renderChartModalTable(data) {
            const tbody = document.getElementById('chartModalTableBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                            Tidak ada insiden yang terasosiasi dengan kategori ini.
                        </td>
                    </tr>
                `;
                document.getElementById('chartModalShowingText').textContent = 'Menampilkan 0 insiden';
                return;
            }

            data.forEach((item, index) => {
                const statusBadge = item.status_laporan === 'Final'
                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Final</span>'
                    : '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Preliminary</span>';

                const areaBadge = item.klasifikasi_area.includes('Non')
                    ? '<span class="badge-non-panas"><i class="bi bi-building me-1"></i>Non-Panas</span>'
                    : '<span class="badge-panas-bumi"><i class="bi bi-fire me-1"></i>Panas Bumi</span>';

                let tagsHtml = '';
                if (item.kategori_umum && item.kategori_umum.length > 0) {
                    item.kategori_umum.forEach(t => {
                        tagsHtml += `<span class="tag-pill-sm tag-pill-umum">${t}</span>`;
                    });
                }
                if (item.kategori_ebtke && item.kategori_ebtke.length > 0) {
                    item.kategori_ebtke.forEach(t => {
                        tagsHtml += `<span class="tag-pill-sm tag-pill-ebtke">${t}</span>`;
                    });
                }
                if (!tagsHtml) tagsHtml = '<span class="text-muted small">-</span>';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center text-muted fw-semibold">${index + 1}</td>
                    <td><span class="badge bg-danger-subtle text-danger font-monospace border border-danger-subtle">${item.nomor_insiden}</span></td>
                    <td class="text-nowrap"><i class="bi bi-calendar-event text-muted me-1"></i>${item.tanggal_waktu}</td>
                    <td>${areaBadge}</td>
                    <td style="max-width: 200px;"><div class="d-flex flex-wrap gap-1">${tagsHtml}</div></td>
                    <td>
                        <div class="fw-bold text-dark"><i class="bi bi-geo-alt text-danger me-1"></i>${item.lokasi_kejadian}</div>
                        <small class="text-muted">Unit: ${item.komponen_terkait}</small>
                    </td>
                    <td class="text-center">
                        <span class="badge ${item.jumlah_hari_hilang > 0 ? 'bg-danger text-white' : 'bg-light text-muted border'}">
                            ${item.jumlah_hari_hilang} Hari
                        </span>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" onclick="openDetailModalFromChart(${item.id})" title="Buka Detail Investigasi">
                            <i class="bi bi-eye"></i> Detail
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            document.getElementById('chartModalShowingText').textContent = `Menampilkan ${data.length} insiden`;
        }

        function filterChartModalTable() {
            const q = document.getElementById('chartModalSearchInput').value.toLowerCase().trim();
            if (!q) {
                renderChartModalTable(currentFilteredModalData);
                return;
            }
            const filtered = currentFilteredModalData.filter(item => {
                const no = (item.nomor_insiden || '').toLowerCase();
                const loc = (item.lokasi_kejadian || '').toLowerCase();
                const pic = (item.penanggung_jawab || '').toLowerCase();
                const desc = (item.deskripsi_kejadian || '').toLowerCase();
                const umum = (item.kategori_umum || []).join(' ').toLowerCase();
                const ebtke = (item.kategori_ebtke || []).join(' ').toLowerCase();
                return no.includes(q) || loc.includes(q) || pic.includes(q) || desc.includes(q) || umum.includes(q) || ebtke.includes(q);
            });
            renderChartModalTable(filtered);
        }

        function openDetailModalFromChart(id) {
            const item = allIncidentsData.find(x => x.id === id);
            if (item) {
                // Sembunyikan modal chart terlebih dahulu agar tidak bertumpuk
                const chartModalEl = document.getElementById('modalChartDetailIncidents');
                const chartModalInstance = bootstrap.Modal.getInstance(chartModalEl);
                if (chartModalInstance) {
                    chartModalInstance.hide();
                }
                setTimeout(() => {
                    openDetailModal(item);
                }, 300);
            }
        }

        // Inisialisasi 3 Grafik Lingkaran Interaktif (Chart.js Donut Charts Clickable)
        document.addEventListener('DOMContentLoaded', function() {
            const chartAreaConfig = @json($chartArea);
            const chartUmumConfig = @json($chartUmum);
            const chartEbtkeConfig = @json($chartEbtke);

            // Plugin Center Text untuk Donut Chart
            const donutCenterTextPlugin = {
                id: 'donutCenterText',
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    const total = chart.config.options.plugins.centerTotal ?? 0;
                    const centerLabel = chart.config.options.plugins.centerLabel ?? 'Total';
                    const x = (chartArea.left + chartArea.right) / 2;
                    const y = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    // Angka Total
                    ctx.font = 'bold 18px Arial, sans-serif';
                    ctx.fillStyle = '#0f172a';
                    ctx.fillText(total, x, y - 6);

                    // Label Teks
                    ctx.font = '9px Arial, sans-serif';
                    ctx.fillStyle = '#64748b';
                    ctx.fillText(centerLabel, x, y + 12);

                    ctx.restore();
                }
            };

            // 1. Chart Kategori Insiden (Area) Clickable
            const ctxArea = document.getElementById('chartAreaCanvas');
            if (ctxArea) {
                new Chart(ctxArea, {
                    type: 'doughnut',
                    data: {
                        labels: chartAreaConfig.labels,
                        datasets: [{
                            data: chartAreaConfig.total > 0 ? chartAreaConfig.data : [1, 1],
                            backgroundColor: chartAreaConfig.total > 0 ? chartAreaConfig.colors : ['#e2e8f0', '#cbd5e1'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        onClick: (event, elements, chart) => {
                            if (!elements || elements.length === 0) return;
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openChartDetailModal('area', label);
                        },
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (chartAreaConfig.total === 0) return 'Belum ada data';
                                        const val = context.raw || 0;
                                        const pct = ((val / chartAreaConfig.total) * 100).toFixed(1);
                                        return ` ${context.label}: ${val} (${pct}%) - Klik untuk detail`;
                                    }
                                }
                            },
                            centerTotal: chartAreaConfig.total,
                            centerLabel: 'Insiden'
                        }
                    },
                    plugins: [donutCenterTextPlugin]
                });
            }

            // 2. Chart Kategori Umum Clickable
            const ctxUmum = document.getElementById('chartUmumCanvas');
            if (ctxUmum) {
                let filteredUmumLabels = [];
                let filteredUmumData = [];
                let filteredUmumColors = [];

                if (chartUmumConfig.total > 0) {
                    chartUmumConfig.labels.forEach((lbl, idx) => {
                        if (chartUmumConfig.data[idx] > 0) {
                            filteredUmumLabels.push(lbl);
                            filteredUmumData.push(chartUmumConfig.data[idx]);
                            const color = (chartUmumConfig.color_map && chartUmumConfig.color_map[lbl]) 
                                || chartUmumConfig.colors[idx] 
                                || '#2563eb';
                            filteredUmumColors.push(color);
                        }
                    });
                } else {
                    filteredUmumLabels = ['Belum ada tag'];
                    filteredUmumData = [1];
                    filteredUmumColors = ['#e2e8f0'];
                }

                new Chart(ctxUmum, {
                    type: 'doughnut',
                    data: {
                        labels: filteredUmumLabels,
                        datasets: [{
                            data: filteredUmumData,
                            backgroundColor: filteredUmumColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        onClick: (event, elements, chart) => {
                            if (!elements || elements.length === 0) return;
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openChartDetailModal('umum', label);
                        },
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 9,
                                    font: { size: 10 },
                                    padding: 6
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (chartUmumConfig.total === 0) return 'Belum ada tag terpilih';
                                        const val = context.raw || 0;
                                        const pct = ((val / chartUmumConfig.total) * 100).toFixed(1);
                                        return ` ${context.label}: ${val} (${pct}%) - Klik untuk detail`;
                                    }
                                }
                            },
                            centerTotal: chartUmumConfig.total,
                            centerLabel: 'Tag'
                        }
                    },
                    plugins: [donutCenterTextPlugin]
                });
            }

            // 3. Chart Kategori EBTKE Clickable
            const ctxEbtke = document.getElementById('chartEbtkeCanvas');
            if (ctxEbtke) {
                let filteredEbtkeLabels = [];
                let filteredEbtkeData = [];
                let filteredEbtkeColors = [];

                if (chartEbtkeConfig.total > 0) {
                    chartEbtkeConfig.labels.forEach((lbl, idx) => {
                        if (chartEbtkeConfig.data[idx] > 0) {
                            filteredEbtkeLabels.push(lbl);
                            filteredEbtkeData.push(chartEbtkeConfig.data[idx]);
                            const color = (chartEbtkeConfig.color_map && chartEbtkeConfig.color_map[lbl]) 
                                || chartEbtkeConfig.colors[idx] 
                                || '#dc2626';
                            filteredEbtkeColors.push(color);
                        }
                    });
                } else {
                    filteredEbtkeLabels = ['Belum ada tag'];
                    filteredEbtkeData = [1];
                    filteredEbtkeColors = ['#e2e8f0'];
                }

                new Chart(ctxEbtke, {
                    type: 'doughnut',
                    data: {
                        labels: filteredEbtkeLabels,
                        datasets: [{
                            data: filteredEbtkeData,
                            backgroundColor: filteredEbtkeColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        onClick: (event, elements, chart) => {
                            if (!elements || elements.length === 0) return;
                            const index = elements[0].index;
                            const label = chart.data.labels[index];
                            openChartDetailModal('ebtke', label);
                        },
                        onHover: (event, chartElement) => {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        },
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 9,
                                    font: { size: 10 },
                                    padding: 6
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (chartEbtkeConfig.total === 0) return 'Belum ada tag EBTKE terpilih';
                                        const val = context.raw || 0;
                                        const pct = ((val / chartEbtkeConfig.total) * 100).toFixed(1);
                                        return ` ${context.label}: ${val} (${pct}%) - Klik untuk detail`;
                                    }
                                }
                            },
                            centerTotal: chartEbtkeConfig.total,
                            centerLabel: 'Tag'
                        }
                    },
                    plugins: [donutCenterTextPlugin]
                });
            }
        });
    </script>
@endpush

@extends('layouts.app', [
    'title' => 'HSSE Plan - Riwayat Tercapai',
    'activeModule' => 'hsse-plan'
])

@push('styles')
<style>
    .filter-grid-achieved {
        grid-template-columns: 160px 160px 160px 140px 1fr auto;
    }

    .stats-grid-achieved {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
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
        background: #10b981;
    }

    .progress-label { min-width: 28px; font-size: 10px; text-align: right; font-weight: 600; }

    .kpi-row-selected {
        background: #ecfdf5 !important;
    }

    @media (max-width: 992px) {
        .stats-grid-achieved { grid-template-columns: 1fr 1fr; }
        .filter-grid-achieved { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
            <h1 class="page-heading">Riwayat KPI Tercapai <span class="ms-2 fs-6 fw-semibold text-success"><i class="bi bi-shield-fill-check me-1"></i>100% Selesai</span></h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <a href="{{ route('hsse-plan.index') }}">HSSE Plan</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Riwayat Tercapai</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('hsse-plan.index') }}" class="btn btn-action-outline">
            <i class="bi bi-arrow-left me-1"></i> Semua KPI HSSE
        </a>
        <button class="btn btn-action-outline" onclick="downloadPDF()">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </button>
    </div>
</div>

<!-- KARTU RINGKASAN DATA TERCAPAI -->
<div class="stats-grid-achieved">
    <!-- Total KPI Tercapai -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total KPI Tercapai</div>
            <div class="stat-number text-success" id="statTotal">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Target kinerja terpenuhi</span></div>
        </div>
    </div>

    <!-- Leading Indicator Tercapai -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Leading Indicator</div>
            <div class="stat-number text-primary" id="statLeading">{{ $stats['leading'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Indikator proaktif terpenuhi</span></div>
        </div>
    </div>

    <!-- Lagging Indicator Tercapai -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Lagging Indicator</div>
            <div class="stat-number" id="statLagging" style="color: #7c3aed">{{ $stats['lagging'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Indikator hasil akhir terpenuhi</span></div>
        </div>
    </div>

    <!-- Tingkat Kepatuhan -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-award-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Tingkat Capaian</div>
            <div class="stat-number text-success" id="statPct">100%</div>
            <div class="stat-trend"><span class="stat-trend-up">Semua KPI di halaman ini berstatus Tercapai</span></div>
        </div>
    </div>
</div>

<!-- FILTER CARD -->
<div class="filter-card">
    <div class="filter-grid filter-grid-achieved">
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

        <!-- Jenis KPI -->
        <div>
            <select id="filterType" class="form-select form-select-filter">
                <option value="">Semua Jenis (Leading & Lagging)</option>
                <option value="Leading">Leading</option>
                <option value="Lagging">Lagging</option>
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
            <input type="search" id="globalSearch" class="form-control form-control-filter" placeholder="Cari nama KPI atau deskripsi...">
        </div>

        <!-- Reset -->
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilters()">
                Reset
            </button>
        </div>
    </div>
</div>

<!-- TABEL DAFTAR KPI TERCAPAI -->
<div class="table-card">
    <div class="table-topbar">
        <div class="d-flex align-items-center gap-2">
            <h2 class="table-section-title mb-0">Daftar HSSE Plan yang Sudah Tercapai</h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:11px">
                <i class="bi bi-check-all me-1"></i>Hanya Status Tercapai
            </span>
        </div>

        <div class="table-controls">
            <div class="table-search-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" class="form-control table-search-input" id="tableSearch" placeholder="Cari data...">
            </div>

            <select class="form-select form-select-sm" id="sortBy" style="width:145px; font-size:11.5px">
                <option value="name">Urut Nama</option>
                <option value="progress-desc">Progress Tertinggi</option>
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
                    <th style="width: 100px;">Jenis</th>
                    <th style="width: 110px;">Unit</th>
                    <th style="width: 100px; text-align: center;">Target</th>
                    <th style="width: 100px; text-align: center;">Realisasi</th>
                    <th style="width: 130px;">Persentase</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 70px; text-align: center;">Periode</th>
                    <th>Keterangan</th>
                    <th style="width: 90px; text-align: center;">Catatan</th>
                    <th style="width: 70px; text-align: center;">Aksi</th>
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

<!-- MODAL DETAIL KPI -->
<div class="modal fade" id="kpiDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed green">
                <h5 class="modal-title" id="kpiDetailModalTitle">
                    <i class="bi bi-bullseye me-1"></i> Detail KPI Tercapai
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="kpiDetailModalBody">
                <div class="text-center py-4 text-muted">Memuat data detail KPI...</div>
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
@endsection

@push('scripts')
<script>
    // Data KPI Tercapai dari Database MySQL (status === 'Tercapai')
    let kpiData = @json($kpis ?? []);

    // Filter strict di sisi client juga untuk memastikan integritas
    kpiData = kpiData.filter(item => item.status === 'Tercapai');

    let currentPage = 1;
    const pageSize = 10;
    const $ = id => document.getElementById(id);
    const kpiDetailModal = new bootstrap.Modal($('kpiDetailModal'));

    function escapeHTML(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
        })[ch]);
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

    function percentage(item) {
        if (typeof item.percentage !== 'undefined') return Number(item.percentage);
        const target = Number(item.target ?? 0);
        const actual = Number(item.realization ?? 0);
        if (target <= 0) return actual <= 0 ? 100 : 0;
        return Math.max(0, Math.min(100, Math.round(actual / target * 100)));
    }

    function getFilteredData() {
        const start = $('filterStart').value;
        const end = $('filterEnd').value;
        const type = $('filterType').value;
        const period = $('filterPeriod').value;
        const search = ($('globalSearch').value + ' ' + $('tableSearch').value).trim().toLowerCase();

        return kpiData.filter(item => {
            // PASTIKAN: status harus 'Tercapai'
            if (item.status !== 'Tercapai') return false;

            const title = (item.title || item.name || '').toLowerCase();
            const desc = (item.description || '').toLowerCase();
            const unit = (item.unit || '').toLowerCase();
            const kpiType = item.category || item.type || '';
            const searchable = title + ' ' + desc + ' ' + unit + ' ' + kpiType.toLowerCase();
            const searchMatch = !search || searchable.includes(search);

            const typeMatch = !type || kpiType === type;

            const kpiPeriod = String(item.year || item.period || '');
            const periodMatch = !period || kpiPeriod === period;

            const kpiStart = item.start_date || item.start || '';
            const kpiEnd = item.end_date || item.end || '';
            const dateMatch = (!start || (kpiEnd && kpiEnd >= start)) && (!end || (kpiStart && kpiStart <= end));

            return searchMatch && typeMatch && periodMatch && dateMatch;
        });
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
            const targetLabel = item.target_label || (item.target ? `${item.target} ${item.unit_measure || ''}` : '-');
            const actualVal = item.realization ?? item.actual ?? 0;
            const notes = item.notes || [];

            return `
                <tr>
                    <td class="text-center">${startIndex + index + 1}</td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHTML(item.title || item.name)}</div>
                    </td>
                    <td><span class="${typeClass}">${escapeHTML(kpiType)}</span></td>
                    <td><span class="badge bg-light text-dark border">${escapeHTML(item.unit || 'Dieng Unit 1')}</span></td>
                    <td class="text-center">${escapeHTML(targetLabel)}</td>
                    <td class="text-center fw-semibold text-success">${escapeHTML(formatNumber(actualVal))}</td>
                    <td>
                        <div class="progress-wrap">
                            <div class="progress-custom"><div class="progress-bar-custom" style="width:${pct}%"></div></div>
                            <span class="progress-label text-success">${pct}%</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge-status status-achieved">
                            <i class="bi bi-check-circle me-1"></i>Tercapai
                        </span>
                    </td>
                    <td class="text-center">${item.year || item.period || '-'}</td>
                    <td>${escapeHTML(item.description || '-')}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-chat-left-text me-1"></i>${notes.length}
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn-action-icon btn-action-view" title="Detail KPI" onclick="viewKPIDetail(${item.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (!visible.length) {
            $('kpiTableBody').innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted">Tidak ada data KPI Tercapai yang sesuai filter.</td></tr>';
        }

        $('tableInfo').textContent = rows.length
            ? `Menampilkan ${startIndex + 1} - ${Math.min(startIndex + pageSize, rows.length)} dari ${rows.length} KPI tercapai`
            : 'Tidak ada data';

        let pages = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><button class="page-link" onclick="goPage(${currentPage-1})">‹</button></li>`;
        for (let p = 1; p <= totalPages; p++) {
            pages += `<li class="page-item ${p === currentPage ? 'active' : ''}"><button class="page-link" onclick="goPage(${p})">${p}</button></li>`;
        }
        pages += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><button class="page-link" onclick="goPage(${currentPage+1})">›</button></li>`;
        $('pagination').innerHTML = pages;

        updateStats(rows);
    }

    function goPage(page) {
        const maxPage = Math.max(1, Math.ceil(getFilteredData().length / pageSize));
        if (page < 1 || page > maxPage) return;
        currentPage = page;
        renderTable();
    }

    function updateStats(rows) {
        const total = rows.length;
        const leading = rows.filter(x => (x.category || x.type) === 'Leading').length;
        const lagging = rows.filter(x => (x.category || x.type) === 'Lagging').length;

        $('statTotal').textContent = total;
        $('statLeading').textContent = leading;
        $('statLagging').textContent = lagging;
    }

    function viewKPIDetail(id) {
        $('kpiDetailModalTitle').innerHTML = '<i class="bi bi-bullseye me-1"></i> Memuat Detail KPI...';
        $('kpiDetailModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <div class="mt-2 text-muted fw-semibold" style="font-size:12px">Mengambil data dari database...</div>
            </div>
        `;
        kpiDetailModal.show();

        fetch(`/hsse-plan/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Data KPI tidak ditemukan.');
            return res.json();
        })
        .then(data => {
            if (!data.success || !data.kpi) throw new Error('Data KPI tidak ditemukan.');
            const item = data.kpi;
            const pct = percentage(item);
            const notes = item.notes || [];
            const kpiType = item.category || item.type || 'Leading';
            const targetLabel = item.target_label || (item.target ? `${item.target} ${item.unit_measure || ''}` : '-');
            const actualVal = item.realization ?? item.actual ?? 0;

            $('kpiDetailPermalink').href = `/hsse-plan/${item.id}`;
            $('kpiDetailModalTitle').innerHTML = `<i class="bi bi-bullseye me-1"></i> Detail: ${escapeHTML(item.title || item.name)}`;

            $('kpiDetailModalBody').innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge-status status-achieved px-3 py-1 fs-6">
                            <i class="bi bi-check-circle me-1"></i>Tercapai
                        </span>
                        <span class="badge ms-2 ${kpiType === 'Leading' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-purple-subtle text-purple border'}" style="font-size:12px">
                            ${escapeHTML(kpiType)} Indicator
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
                            <div class="fw-bold text-success mt-1" style="font-size:13px">${escapeHTML(formatNumber(actualVal))} ${escapeHTML(item.unit_measure || '')}</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 border rounded mb-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold text-dark" style="font-size:11.5px">Persentase Pemenuhan Target</span>
                        <span class="fw-bold text-success" style="font-size:13px">${pct}%</span>
                    </div>
                    <div class="progress-custom" style="height:10px">
                        <div class="progress-bar-custom" style="width:${pct}%"></div>
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

                <div class="border-top pt-3">
                    <h6 class="fw-bold text-dark mb-2" style="font-size:12px">
                        <i class="bi bi-chat-left-text me-1 text-success"></i> Catatan & Riwayat Tindak Lanjut (${notes.length})
                    </h6>
                    <div style="max-height:160px; overflow-y:auto;">
                        ${notes.length ? notes.map(n => `
                            <div class="p-2 mb-2 bg-light border-start border-3 border-success rounded">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark" style="font-size:11px"><i class="bi bi-person-fill text-muted me-1"></i>${escapeHTML(n.author || 'Admin')}</span>
                                    <span class="text-muted" style="font-size:10px">${formatDateTime(n.created_at)}</span>
                                </div>
                                <div class="text-secondary" style="font-size:11.5px; line-height:1.35;">${escapeHTML(n.note)}</div>
                            </div>
                        `).join('') : '<div class="text-muted text-center py-2" style="font-size:11px">Belum ada catatan untuk KPI ini.</div>'}
                    </div>
                </div>
            `;
        })
        .catch(err => {
            kpiDetailModal.hide();
            Swal.fire('Error', err.message || 'Gagal memuat detail KPI.', 'error');
        });
    }

    function resetFilters() {
        $('filterStart').value = '';
        $('filterEnd').value = '';
        $('filterType').value = '';
        $('filterPeriod').value = '2025';
        $('globalSearch').value = '';
        $('tableSearch').value = '';
        $('sortBy').value = 'name';
        renderTable();
    }

    function downloadPDF() {
        window.location.href = '{{ route('hsse-plan.export') }}';
    }

    ['filterStart','filterEnd','filterType','filterPeriod','sortBy'].forEach(id => {
        $(id).addEventListener('change', () => { currentPage = 1; renderTable(); });
    });

    ['globalSearch','tableSearch'].forEach(id => {
        $(id).addEventListener('input', () => { currentPage = 1; renderTable(); });
    });

    // Inisialisasi awal
    renderTable();
</script>
@endpush


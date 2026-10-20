@extends('layouts.app', [
    'title' => 'Inventory',
    'activeModule' => 'inventory'
])

@push('styles')
<style>
    :root {
        --navy: #102b3f;
        --navy-dark: #071e2e;
        --green: #07845c;
        --page-bg: #f8fafc;
        --border: #dce7f2;
        --muted: #70849b;
    }

    /* INVENTORY SUMMARY STAT CARDS (CLEAN VERTICAL FLOW - NO HORIZONTAL SQUEEZING) */
    .inventory-stat-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 16px 18px;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        align-items: stretch !important;
        min-height: 114px;
        height: 100%;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border-width: 1.5px;
        border-style: solid;
        user-select: none;
    }

    .inventory-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -5px rgba(16, 43, 63, 0.12);
    }

    .inventory-stat-card:active {
        transform: translateY(-1px);
    }

    .inventory-stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .inventory-stat-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inventory-stat-badge {
        font-size: 10.5px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .inventory-stat-value {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
        margin: 4px 0 2px;
    }

    .inventory-stat-desc {
        font-size: 11px;
        line-height: 1.35;
        color: #64748b;
        margin-top: 2px;
        word-break: break-word;
    }

    /* CUSTOM MODAL OVERLAY (ALPINE & HSSE THEME) */
    [x-cloak] { display: none !important; }

    .modal-overlay-custom {
        position: fixed;
        inset: 0;
        z-index: 1060;
        background-color: rgba(10, 24, 37, 0.65);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .modal-box-custom {
        background: #ffffff;
        width: 100%;
        max-width: 920px;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        display: flex;
        flex-direction: column;
        max-height: 88vh;
        overflow: hidden;
        animation: modalSlideIn 0.22s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(12px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header-hsse {
        padding: 14px 20px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body-scrollable {
        padding: 16px 20px;
        overflow-y: auto;
        flex: 1 1 auto;
    }

    .modal-footer-hsse {
        padding: 12px 20px;
        background: #f8fafc;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-badge-counter {
        background: #e2e8f0;
        color: var(--navy);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .stat-label {
        font-size: 11px;
        color: var(--muted);
        font-weight: bold;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        margin: 6px 0 2px;
    }

    .panel {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 18px;
    }

    .badge-kategori {
        font-size: 10px;
        background: #e2e8f0;
        color: #334155;
        font-weight: 600;
        border-radius: 4px;
        padding: 4px 7px;
    }

    /* MODERN TAB NAVIGATION */
    .inventory-nav-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid var(--border);
        margin-bottom: 20px;
        padding-bottom: 0;
    }

    .inventory-nav-tabs .tab-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        text-decoration: none;
        color: var(--muted);
        font-weight: 600;
        font-size: 13px;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all .2s;
        background: transparent;
    }

    .inventory-nav-tabs .tab-link:hover {
        color: var(--navy);
        background: rgba(16, 43, 63, 0.03);
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
    }

    .inventory-nav-tabs .tab-link.active {
        color: var(--navy);
        border-bottom-color: var(--green);
        background: white;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        border-top: 1px solid var(--border);
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
    }

    .tab-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        background: #e2e8f0;
        color: #475569;
    }

    .tab-link.active .tab-badge {
        background: #e6f4ea;
        color: var(--green);
    }

    /* Modal Transaksi styling */
    .trx-type-btn {
        cursor: pointer;
        border: 2px solid #cbd5e1;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        transition: all .2s;
        background: #f8fafc;
    }
    .trx-type-btn:hover {
        border-color: #94a3b8;
    }
    .trx-radio:checked + .trx-type-masuk {
        border-color: #198754;
        background: #e8f5e9;
        color: #198754;
        font-weight: bold;
    }
    .trx-radio:checked + .trx-type-keluar {
        border-color: #dc3545;
        background: #ffebee;
        color: #dc3545;
        font-weight: bold;
    }

    /* Modal Layout Polish & Scrollable Container */
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
    .modal-body {
        padding: 22px;
    }
    .modal-body.scrollable-modal-body {
        max-height: calc(85vh - 130px);
        overflow-y: auto;
    }
    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #eef2f6;
        background: #f8fafc;
    }

    /* Filter Row Grid Polish */
    .filter-grid-row {
        display: grid;
        grid-template-columns: 2.2fr 1.6fr 1.8fr 2.6fr 1.2fr;
        gap: 12px;
        align-items: flex-end;
    }

    @media (max-width: 1200px) {
        .filter-grid-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .filter-grid-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS GLOBAL -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box teal">
            <i class="bi bi-boxes"></i>
        </div>
        <div>
            <h1 class="page-heading">Inventory</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Inventory</span>
                <span class="mx-1 text-muted">·</span>
                <span class="text-muted">Manajemen Logistik & Inventaris HSSE</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <!-- Tombol Export PDF (Khusus Riwayat Transaksi History) -->
        @canExportPdf
        <a href="{{ route('inventory.export', array_merge(request()->query(), ['tab' => 'history'])) }}" 
           class="btn-action-outline text-decoration-none" 
           title="Export Riwayat Transaksi Barang ke PDF">
            <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Export PDF (History)
        </a>
        @endcanExportPdf
        @canManageInventory
            <!-- Tombol Form Peminjaman Barang -->
            <button type="button" class="btn-action-outline border-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalPinjamBarang" onclick="openBorrowModal()">
                <i class="bi bi-box-arrow-right text-warning me-1"></i> + Pinjam Barang
            </button>
            <!-- Tombol Form Transaksi Terpadu -->
            <button class="btn-action-outline" data-bs-toggle="modal" data-bs-target="#modalTransaksiTerpadu">
                <i class="bi bi-arrow-left-right me-1"></i> Catat Transaksi (In/Out)
            </button>
            <!-- Tombol Tambah Master Barang -->
            <button class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                <i class="bi bi-plus-lg me-1"></i> + Tambah Master
            </button>
        @endcanManageInventory
    </div>
</div>

            <!-- Pesan Notifikasi Flash -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- CONTAINER ALPINE DATA UNTUK 4 KARTU & MODAL -->
            <div x-data="inventoryModalHandler()" class="w-100">
                <!-- 4 KARTU RINGKASAN STOK INTERAKTIF (RAPAT, PROPORSIONAL & VERTIKAL TERATUR) -->
                <div class="row g-3 mb-4">
                    <!-- Card 1: Total Barang Masuk -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="inventory-stat-card border-primary"
                             @click="openModal('masuk')"
                             title="Klik untuk melihat rincian barang masuk">
                            <div class="inventory-stat-header">
                                <span class="inventory-stat-label text-primary">Total Barang Masuk</span>
                                <span class="inventory-stat-badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Masuk
                                </span>
                            </div>
                            <div>
                                <div class="inventory-stat-value text-primary">{{ number_format($totalMasuk) }}</div>
                                <div class="inventory-stat-desc">Total kuantitas unit fisik masuk</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Total Barang Keluar -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="inventory-stat-card border-danger"
                             @click="openModal('keluar')"
                             title="Klik untuk melihat rincian barang keluar">
                            <div class="inventory-stat-header">
                                <span class="inventory-stat-label text-danger">Total Barang Keluar</span>
                                <span class="inventory-stat-badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Keluar
                                </span>
                            </div>
                            <div>
                                <div class="inventory-stat-value text-danger">{{ number_format($totalKeluar) }}</div>
                                <div class="inventory-stat-desc">Total kuantitas unit fisik keluar</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Stok Fisik Tersedia & Aman -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="inventory-stat-card border-success"
                             @click="openModal('aman')"
                             title="Klik untuk melihat rincian barang kondisi stok aman">
                            <div class="inventory-stat-header">
                                <span class="inventory-stat-label text-success">Stok Fisik Tersedia</span>
                                <span class="inventory-stat-badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-shield-check me-1"></i>{{ $stokAmanCount }} Item Aman
                                </span>
                            </div>
                            <div>
                                <div class="inventory-stat-value text-success">{{ number_format($totalStokTersedia) }}</div>
                                <div class="inventory-stat-desc">
                                    Tersedia: <strong>{{ number_format($totalStokBersihTersedia ?? $totalStokTersedia) }}</strong> unit
                                    @if(($totalUnitTerpinjam ?? 0) > 0)
                                        <span class="text-warning fw-semibold">· Dipinjam: {{ number_format($totalUnitTerpinjam) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Perlu Pengadaan -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="inventory-stat-card border-warning"
                             @click="openModal('pengadaan')"
                             title="Klik untuk melihat barang yang perlu segera pengadaan/reorder">
                            <div class="inventory-stat-header">
                                <span class="inventory-stat-label text-danger">Perlu Pengadaan</span>
                                <span class="inventory-stat-badge bg-danger text-white">Reorder Alert</span>
                            </div>
                            <div>
                                <div class="inventory-stat-value text-danger">{{ number_format($perluPengadaanCount) }}</div>
                                <div class="inventory-stat-desc text-danger fw-semibold">
                                    ⚠️ Stok ≤ Batas Minimum ({{ $totalItemJenis }} Jenis)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POP-UP MODAL TERPADU 4 KATEGORI (BERSIH, BACKDROP BLUR, PAGINATION 10 BARIS) -->
                <div x-show="isOpen" 
                     x-cloak
                     @keydown.escape.window="closeModal()"
                     class="modal-overlay-custom">
                    
                    <div class="modal-box-custom" @click.outside="closeModal()">
                        <!-- HEADER MODAL -->
                        <div class="modal-header-hsse">
                            <div class="d-flex align-items-center gap-2">
                                <div class="brand-mark text-white" :style="headerStyle.markBg">
                                    <i :class="headerStyle.icon" style="font-size: 16px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark" x-text="headerStyle.title"></h6>
                                    <small class="text-muted" x-text="headerStyle.subtitle"></small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="modal-badge-counter" x-text="'Total: ' + currentList.length + ' Data'"></span>
                                <button type="button" @click="closeModal()" class="btn-close" aria-label="Close"></button>
                            </div>
                        </div>

                        <!-- SEARCH BAR RINGKAS DALAM MODAL -->
                        <div class="px-3 pt-3 pb-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="max-width: 320px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" x-model="searchQuery" @input="currentPage = 1" class="form-control border-start-0" placeholder="Filter di dalam rincian...">
                            </div>
                            <span class="badge bg-white text-muted border font-monospace" style="font-size: 11px;">Maks. 10 baris / halaman</span>
                        </div>

                        <!-- BODY TABEL RINCIAN -->
                        <div class="modal-body-scrollable p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                                    <thead class="table-light sticky-top" style="z-index: 2;">
                                        <template x-if="activeType === 'masuk'">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Nama Barang</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Jumlah Masuk</th>
                                                <th>Tanggal</th>
                                                <th>Petugas Penginput</th>
                                            </tr>
                                        </template>

                                        <template x-if="activeType === 'keluar'">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Nama Barang</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Jumlah Keluar</th>
                                                <th>Tanggal</th>
                                                <th>Petugas / Pengambil</th>
                                            </tr>
                                        </template>

                                        <template x-if="activeType === 'aman'">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Kode & Nama Barang</th>
                                                <th>Kategori</th>
                                                <th class="text-center">Stok Saat Ini</th>
                                                <th class="text-center">Batas Minimum</th>
                                                <th>Lokasi Default</th>
                                            </tr>
                                        </template>

                                        <template x-if="activeType === 'pengadaan'">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Kode & Nama Barang</th>
                                                <th>Kategori</th>
                                                <th class="text-center">Stok Saat Ini</th>
                                                <th class="text-center">Batas Minimum</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </template>
                                    </thead>
                                    <tbody>
                                        <!-- KONDISI JIKA DATA KOSONG -->
                                        <template x-if="paginatedData.length === 0">
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                    Tidak ada data yang ditemukan.
                                                </td>
                                            </tr>
                                        </template>

                                        <!-- LOOP DATA SESUAI PAGINASI -->
                                        <template x-for="(item, index) in paginatedData" :key="item.id || index">
                                            <tr>
                                                <td class="text-center text-muted fw-semibold" x-text="(currentPage - 1) * pageSize + index + 1"></td>

                                                <!-- KOLOM 1 & 2: TRANSAKSI MASUK & KELUAR -->
                                                <template x-if="activeType === 'masuk' || activeType === 'keluar'">
                                                    <td class="fw-semibold text-dark">
                                                        <div x-text="item.nama_barang"></div>
                                                        <small class="text-muted font-monospace" style="font-size: 10px;" x-text="item.barang_id"></small>
                                                    </td>
                                                </template>
                                                <template x-if="activeType === 'masuk' || activeType === 'keluar'">
                                                    <td><span class="badge-kategori" x-text="item.kategori"></span></td>
                                                </template>
                                                <template x-if="activeType === 'masuk'">
                                                    <td class="text-end fw-bold text-primary" x-text="'+ ' + Number(item.jumlah).toLocaleString() + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'keluar'">
                                                    <td class="text-end fw-bold text-danger" x-text="'- ' + Number(item.jumlah).toLocaleString() + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'masuk' || activeType === 'keluar'">
                                                    <td class="text-muted" x-text="item.tanggal"></td>
                                                </template>
                                                <template x-if="activeType === 'masuk' || activeType === 'keluar'">
                                                    <td><i class="bi bi-person me-1 text-muted"></i><span x-text="item.petugas"></span></td>
                                                </template>

                                                <!-- KOLOM 3: STOK AMAN -->
                                                <template x-if="activeType === 'aman'">
                                                    <td class="fw-semibold text-dark">
                                                        <div x-text="item.nama_barang"></div>
                                                        <small class="text-muted font-monospace" style="font-size: 10px;" x-text="item.barang_id"></small>
                                                    </td>
                                                </template>
                                                <template x-if="activeType === 'aman'">
                                                    <td><span class="badge-kategori" x-text="item.kategori"></span></td>
                                                </template>
                                                <template x-if="activeType === 'aman'">
                                                    <td class="text-center fw-bold text-success" x-text="item.stok_sekarang + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'aman'">
                                                    <td class="text-center text-muted" x-text="item.stok_minimum + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'aman'">
                                                    <td class="text-muted" x-text="item.lokasi"></td>
                                                </template>

                                                <!-- KOLOM 4: PERLU PENGADAAN -->
                                                <template x-if="activeType === 'pengadaan'">
                                                    <td class="fw-semibold text-dark">
                                                        <div x-text="item.nama_barang"></div>
                                                        <small class="text-muted font-monospace" style="font-size: 10px;" x-text="item.barang_id"></small>
                                                    </td>
                                                </template>
                                                <template x-if="activeType === 'pengadaan'">
                                                    <td><span class="badge-kategori" x-text="item.kategori"></span></td>
                                                </template>
                                                <template x-if="activeType === 'pengadaan'">
                                                    <td class="text-center fw-bold text-danger" x-text="item.stok_sekarang + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'pengadaan'">
                                                    <td class="text-center text-muted" x-text="item.stok_minimum + ' ' + item.satuan"></td>
                                                </template>
                                                <template x-if="activeType === 'pengadaan'">
                                                    <td class="text-center">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Reorder
                                                        </span>
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- FOOTER DENGAN PAGINASI RAFTING MAKSIMAL 10 ITEM -->
                        <div class="modal-footer-hsse">
                            <div class="text-muted small" style="font-size: 11px;">
                                Menampilkan 
                                <span class="fw-bold" x-text="filteredData.length === 0 ? 0 : (currentPage - 1) * pageSize + 1"></span> - 
                                <span class="fw-bold" x-text="Math.min(currentPage * pageSize, filteredData.length)"></span> 
                                dari <span class="fw-bold" x-text="filteredData.length"></span> baris
                            </div>

                            <div class="d-flex align-items-center gap-1" x-show="totalPages > 1">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" 
                                        :disabled="currentPage === 1" 
                                        @click="currentPage--">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                
                                <span class="px-2 text-muted small" x-text="'Halaman ' + currentPage + ' / ' + totalPages"></span>

                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" 
                                        :disabled="currentPage >= totalPages" 
                                        @click="currentPage++">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>

                            <button type="button" @click="closeModal()" class="btn btn-secondary btn-sm px-3">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3 TAB NAVIGASI UTAMA -->
            <div class="inventory-nav-tabs">
                <a href="{{ route('inventory.index', ['tab' => 'master']) }}" 
                   class="tab-link {{ $activeTab === 'master' ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Master Barang & Stok Real-Time
                    <span class="tab-badge">{{ $items->total() }}</span>
                </a>
                <a href="{{ route('inventory.index', ['tab' => 'history']) }}" 
                   class="tab-link {{ $activeTab === 'history' ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Riwayat Transaksi (History)
                    <span class="tab-badge">{{ $transactions->total() }}</span>
                </a>
                <a href="{{ route('inventory.index', ['tab' => 'borrowed']) }}" 
                   class="tab-link {{ $activeTab === 'borrowed' ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right text-warning"></i> Barang Terpinjam
                    <span class="tab-badge {{ ($totalPeminjamanOverdue ?? 0) > 0 ? 'bg-danger text-white' : '' }}">
                        {{ $totalPeminjamanAktif ?? 0 }}
                    </span>
                </a>
            </div>

            @if($activeTab === 'master')
                <!-- ==================== TAB 1: MASTER BARANG & STOK REAL-TIME ==================== -->
                <!-- FILTER & PENCARIAN MASTER -->
                <div class="panel mb-4">
                    <form method="GET" action="{{ route('inventory.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="master">
                        <div class="col-md-5">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari Kode Barang, Nama, atau Lokasi...">
                        </div>
                        <div class="col-md-5">
                            <select name="kategori" class="form-select form-select-sm">
                                <option value="">-- Semua Kategori Master (7 Kategori) --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="{{ route('inventory.index', ['tab' => 'master']) }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- TABEL MASTER BARANG -->
                <div class="panel p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kode Barang</th>
                                    <th>Nama Barang & Spesifikasi</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Stok Real-Time</th>
                                    <th class="text-center">Batas Minimum</th>
                                    <th>Lokasi Default</th>
                                    <th class="text-center pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-secondary font-monospace">{{ $item->barang_id }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->nama_barang }}</div>
                                            <small class="text-muted">{{ $item->keterangan ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge-kategori">{{ $item->kategori }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->is_low_stock)
                                                <span class="badge bg-danger text-white py-1 px-2" title="Segera lakukan pengadaan!">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $item->current_stock }} {{ $item->satuan }}
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success py-1 px-2 fw-bold">
                                                    {{ $item->current_stock }} {{ $item->satuan }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $item->stok_minimum }} {{ $item->satuan }}
                                        </td>
                                        <td>
                                            <i class="bi bi-geo-alt text-muted me-1"></i>{{ $item->lokasi_default ?? 'Gudang HSSE' }}
                                        </td>
                                        <td class="text-center pe-3">
                                            @canManageInventory
                                                <!-- Tombol Cepat Catat Transaksi Khusus Barang Ini -->
                                                <button class="btn btn-sm btn-outline-success me-1"
                                                        onclick="openQuickTransaction({{ $item->id }}, '{{ addslashes($item->nama_barang) }}', {{ $item->current_stock }}, '{{ $item->satuan }}', '{{ addslashes($item->lokasi_default ?? 'Gudang HSSE') }}')"
                                                        title="Catat Mutasi Barang Ini">
                                                    <i class="bi bi-arrow-down-up"></i>
                                                </button>

                                                <!-- Tombol Edit Terintegrasi Bersih (Tanpa Form Rusak di Dalam Tabel) -->
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->barang_id) }}', '{{ addslashes($item->nama_barang) }}', '{{ addslashes($item->kategori) }}', '{{ addslashes($item->satuan) }}', {{ $item->stok_minimum }}, '{{ addslashes($item->lokasi_default ?? '') }}', '{{ addslashes($item->keterangan ?? '') }}')"
                                                        title="Edit Master Barang">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Tombol Membuka Modal Konfirmasi Hapus Elegan -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="openDeleteConfirmation({{ $item->id }}, '{{ addslashes($item->nama_barang) }}', '{{ $item->barang_id }}')"
                                                        title="Hapus Master Barang">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small"><i class="bi bi-eye"></i> Read-only</span>
                                            @endcanManageInventory
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                                            Belum ada barang di inventaris. Silakan klik tombol <strong>+ Tambah Master Barang</strong> di atas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            @elseif($activeTab === 'history')
                <!-- ==================== TAB 2: RIWAYAT TRANSAKSI (HISTORY) ==================== -->
                <!-- FILTER & PENCARIAN RIWAYAT DENGAN GRID PROPOSIONAL & SEJAJAR RAPI -->
                <div class="panel mb-4">
                    <form method="GET" action="{{ route('inventory.index') }}" class="filter-grid-row">
                        <input type="hidden" name="tab" value="history">
                        
                        <!-- 1. Kata Kunci -->
                        <div>
                            <label class="form-label small fw-bold mb-1 text-secondary">Cari Kata Kunci</label>
                            <input type="text" name="trx_search" value="{{ request('trx_search') }}" class="form-control form-control-sm" placeholder="Barang, PIC, Lokasi...">
                        </div>

                        <!-- 2. Jenis Transaksi -->
                        <div>
                            <label class="form-label small fw-bold mb-1 text-secondary">Jenis Transaksi</label>
                            <select name="trx_jenis" class="form-select form-select-sm">
                                <option value="">Semua Jenis</option>
                                <option value="masuk" {{ request('trx_jenis') == 'masuk' ? 'selected' : '' }}>Barang Masuk (In)</option>
                                <option value="keluar" {{ request('trx_jenis') == 'keluar' ? 'selected' : '' }}>Barang Keluar (Out)</option>
                                <option value="penyesuaian" {{ request('trx_jenis') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Audit)</option>
                            </select>
                        </div>

                        <!-- 3. Kategori Master -->
                        <div>
                            <label class="form-label small fw-bold mb-1 text-secondary">Kategori</label>
                            <select name="trx_kategori" class="form-select form-select-sm">
                                <option value="">Semua Kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}" {{ request('trx_kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Rentang Tanggal (Dari & Sampai yang Luas dan Proporsional) -->
                        <div>
                            <label class="form-label small fw-bold mb-1 text-secondary">Rentang Tanggal</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="trx_start" value="{{ request('trx_start') }}" class="form-control" title="Tanggal Mulai">
                                <span class="input-group-text bg-light text-muted px-2">s/d</span>
                                <input type="date" name="trx_end" value="{{ request('trx_end') }}" class="form-control" title="Tanggal Akhir">
                            </div>
                        </div>

                        <!-- 5. Tombol Filter, Export PDF & Reset -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1" title="Terapkan Filter">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            @canExportPdf
                            <a href="{{ route('inventory.export', request()->query()) }}" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-1" title="Export PDF Riwayat">
                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                            </a>
                            @endcanExportPdf
                            <a href="{{ route('inventory.index', ['tab' => 'history']) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- TABEL RIWAYAT TRANSAKSI (TANPA NOMOR DOKUMEN & PIC OTOMATIS) -->
                <div class="panel p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tanggal & Waktu</th>
                                    <th>Petugas (PIC)</th>
                                    <th class="text-center">Jenis Mutasi</th>
                                    <th>Kategori</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th>Lokasi Penempatan</th>
                                    <th class="pe-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trx)
                                    @php
                                        $invItem = $trx->inventory ?? $trx->item;
                                        $jenisLower = strtolower(trim((string)($trx->jenis_transaksi ?? $trx->transaction_type ?? '')));
                                        $isMasuk = $jenisLower === 'masuk';
                                        $isKeluar = $jenisLower === 'keluar';
                                        $tglTrx = $trx->transaction_date ?? $trx->tanggal ?? $trx->created_at;
                                        $picName = $trx->petugas ?? $trx->person_in_charge ?? $trx->nama_pengisi ?? 'Petugas HSSE';
                                        $namaBarang = $invItem?->nama_barang ?? $invItem?->item_name;
                                        $kodeBarang = $invItem?->kode_barang ?? $invItem?->item_code ?? '-';
                                        $kategoriBarang = $invItem?->kategori ?? $invItem?->category ?? '-';
                                        $satuanBarang = $invItem?->satuan ?? $invItem?->unit_measure ?? 'Unit';
                                        $lokasiBarang = $invItem?->lokasi_default ?? $invItem?->location ?? 'Gudang HSSE';
                                        $qtyVal = (int) ($trx->jumlah ?? $trx->quantity ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-nowrap">
                                            <i class="bi bi-calendar3 text-muted me-1"></i>
                                            {{ $tglTrx ? \Carbon\Carbon::parse($tglTrx)->format('d/m/Y') : '-' }}
                                            <div class="text-muted" style="font-size: 10px;">
                                                <i class="bi bi-clock me-1"></i>{{ $trx->created_at ? $trx->created_at->format('H:i') : ($tglTrx ? \Carbon\Carbon::parse($tglTrx)->format('H:i') : '-') }} WIB
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <i class="bi bi-person-circle me-1 text-primary"></i>{{ $picName }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($isMasuk)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-box-arrow-in-down me-1"></i> Masuk (In)
                                                </span>
                                            @elseif($isKeluar)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="bi bi-box-arrow-up me-1"></i> Keluar (Out)
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                    <i class="bi bi-sliders me-1"></i> Penyesuaian
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-kategori">{{ $kategoriBarang }}</span>
                                        </td>
                                        <td>
                                            @if($namaBarang)
                                                <div class="fw-bold text-dark">{{ $namaBarang }}</div>
                                                <small class="badge bg-secondary font-monospace" style="font-size: 9px;">{{ $kodeBarang }}</small>
                                            @else
                                                <div class="fw-bold text-secondary fst-italic">
                                                    <i class="bi bi-exclamation-circle me-1 text-muted"></i>Master Barang Dihapus
                                                </div>
                                                <small class="badge bg-light text-muted font-monospace" style="font-size: 9px;">ID: #{{ $trx->inventory_item_id }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">
                                            @if($isMasuk)
                                                <span class="text-success">+{{ number_format($qtyVal) }}</span>
                                            @elseif($isKeluar)
                                                <span class="text-danger">-{{ number_format($qtyVal) }}</span>
                                            @else
                                                <span class="text-warning-emphasis">{{ $qtyVal >= 0 ? '+'.$qtyVal : $qtyVal }}</span>
                                            @endif
                                            <small class="text-muted fw-normal">{{ $satuanBarang }}</small>
                                        </td>
                                        <td>
                                            <small><i class="bi bi-geo-alt text-muted me-1"></i>{{ $lokasiBarang }}</small>
                                        </td>
                                        <td class="pe-3">
                                            <small class="text-muted">{{ $trx->keterangan ?? $trx->notes ?? '-' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                                            Belum ada log riwayat transaksi barang. Silakan klik tombol <strong>Catat Transaksi</strong> di atas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            @elseif($activeTab === 'borrowed')
                <!-- ==================== TAB 3: BARANG TERPINJAM (ACTIVE & HISTORY) ==================== -->
                <!-- 1. KARTU RINGKASAN STATUS PEMINJAMAN -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Peminjaman Aktif</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><i class="bi bi-clock"></i> Aktif</span>
                            </div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($totalPeminjamanAktif ?? 0) }}</div>
                            <small class="text-muted" style="font-size: 11px;">Transaksi belum selesai</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Unit Fisik Terpinjam</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-box"></i> Unit</span>
                            </div>
                            <div class="fs-4 fw-bold text-primary">{{ number_format($totalUnitTerpinjam ?? 0) }}</div>
                            <small class="text-muted" style="font-size: 11px;">Barang beredar di lapangan</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Jenis Alat/Barang</span>
                                <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-tools"></i> Aset</span>
                            </div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($totalJenisBarangTerpinjam ?? 0) }}</div>
                            <small class="text-muted" style="font-size: 11px;">Varian barang terpinjam</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white border rounded-3 shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Melewati Tenggat</span>
                                <span class="badge bg-danger-subtle text-danger rounded-pill"><i class="bi bi-exclamation-circle"></i> Overdue</span>
                            </div>
                            <div class="fs-4 fw-bold text-danger">{{ number_format($totalPeminjamanOverdue ?? 0) }}</div>
                            <small class="text-danger fw-semibold" style="font-size: 11px;">Perlu segera ditagih</small>
                        </div>
                    </div>
                </div>

                <!-- 2. FILTER & PENCARIAN BARANG TERPINJAM -->
                <div class="panel mb-4">
                    <form method="GET" action="{{ route('inventory.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="borrowed">

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1 text-secondary">Cari Peminjaman</label>
                            <input type="text" name="borrow_search" value="{{ request('borrow_search') }}" 
                                   class="form-control form-control-sm" placeholder="No. Pinjam, Peminjam, Unit, Barang...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1 text-secondary">Status Peminjaman</label>
                            <select name="borrow_status" class="form-select form-select-sm">
                                <option value="active" {{ request('borrow_status', 'active') === 'active' ? 'selected' : '' }}>Dipinjam Aktif (Belum Selesai)</option>
                                <option value="overdue" {{ request('borrow_status') === 'overdue' ? 'selected' : '' }}>Lewat Tenggat (Overdue)</option>
                                <option value="returned" {{ request('borrow_status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan (Lunas)</option>
                                <option value="all" {{ request('borrow_status') === 'all' ? 'selected' : '' }}>Semua Riwayat Peminjaman</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1 text-secondary">Rentang Tanggal</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="borrow_start" value="{{ request('borrow_start') }}" class="form-control" title="Tanggal Mulai">
                                <span class="input-group-text bg-light text-muted px-1">s/d</span>
                                <input type="date" name="borrow_end" value="{{ request('borrow_end') }}" class="form-control" title="Tanggal Akhir">
                            </div>
                        </div>

                        <div class="col-md-2 d-flex gap-2 align-items-end pt-3">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('inventory.index', ['tab' => 'borrowed']) }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- 3. TABEL DAFTAR BARANG TERPINJAM -->
                <div class="panel p-0 overflow-hidden">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark"><i class="bi bi-arrow-left-right me-1 text-warning"></i> Daftar Barang Terpinjam</span>
                            <span class="badge bg-secondary-subtle text-secondary border font-monospace">{{ $borrowings->total() }} Data</span>
                        </div>
                        @canManageInventory
                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPinjamBarang" onclick="openBorrowModal()">
                                <i class="bi bi-plus-circle"></i> + Pinjam Barang Baru
                            </button>
                        @endcanManageInventory
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Kode Pinjam</th>
                                    <th>Barang & Kategori</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th>Peminjam & Unit</th>
                                    <th>Tgl Pinjam / Kembali</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($borrowings as $index => $b)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $borrowings->firstItem() + $index }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                                                {{ $b->borrow_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $b->item->item_name ?? $b->item->nama_barang ?? 'Barang Dihapus' }}</div>
                                            <small class="text-muted font-monospace">{{ $b->item->item_code ?? $b->item->kode_barang ?? '-' }} · {{ $b->item->kategori ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold text-dark">
                                                Dipinjam: {{ $b->quantity_borrowed }} {{ $b->item->satuan ?? 'pcs' }}
                                            </div>
                                            <div class="small">
                                                @if($b->quantity_returned > 0)
                                                    <span class="text-success">Kembali: {{ $b->quantity_returned }}</span> ·
                                                @endif
                                                <span class="{{ $b->remaining_quantity > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                    Sisa: {{ $b->remaining_quantity }} {{ $b->item->satuan ?? 'pcs' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $b->borrower_name }}</div>
                                            <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $b->department ?: 'Operasional' }}</small>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-calendar-event me-1 text-muted"></i>{{ $b->borrow_date ? $b->borrow_date->format('d/m/Y') : '-' }}</div>
                                            @if($b->expected_return_date)
                                                <div class="small {{ $b->is_overdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                                    Rencana: {{ $b->expected_return_date->format('d/m/Y') }}
                                                    @if($b->is_overdue)
                                                        <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">Terlambat</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($b->actual_return_date)
                                                <div class="small text-success">
                                                    Aktual: {{ $b->actual_return_date->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($b->status === \App\Models\InventoryBorrowing::STATUS_RETURNED || $b->remaining_quantity <= 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                    <i class="bi bi-check-circle me-1"></i>Dikembalikan
                                                </span>
                                            @elseif($b->status === \App\Models\InventoryBorrowing::STATUS_PARTIAL)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">
                                                    <i class="bi bi-pie-chart me-1"></i>Sebagian Kembali
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                                    <i class="bi bi-clock me-1"></i>Dipinjam
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @canManageInventory
                                                    @if($b->remaining_quantity > 0)
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm py-1 px-2"
                                                                onclick="openReturnModal({{ json_encode([
                                                                    'id' => $b->id,
                                                                    'code' => $b->borrow_code,
                                                                    'name' => $b->item->item_name ?? '',
                                                                    'borrower' => $b->borrower_name,
                                                                    'satuan' => $b->item->satuan ?? 'pcs',
                                                                    'borrowed' => $b->quantity_borrowed,
                                                                    'returned' => $b->quantity_returned,
                                                                    'remaining' => $b->remaining_quantity,
                                                                ]) }})"
                                                                title="Kembalikan Barang">
                                                            <i class="bi bi-box-arrow-in-left me-1"></i> Kembalikan
                                                        </button>
                                                    @endif
                                                @endcanManageInventory

                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm py-1 px-2"
                                                        onclick="openDetailBorrowingModal({{ json_encode([
                                                            'code' => $b->borrow_code,
                                                            'name' => $b->item->item_name ?? '',
                                                            'item_code' => $b->item->item_code ?? '',
                                                            'borrower' => $b->borrower_name,
                                                            'department' => $b->department,
                                                            'satuan' => $b->item->satuan ?? 'pcs',
                                                            'borrowed' => $b->quantity_borrowed,
                                                            'returned' => $b->quantity_returned,
                                                            'remaining' => $b->remaining_quantity,
                                                            'borrow_date' => $b->borrow_date ? $b->borrow_date->format('d/m/Y H:i') : '-',
                                                            'expected_return_date' => $b->expected_return_date ? $b->expected_return_date->format('d/m/Y') : '-',
                                                            'actual_return_date' => $b->actual_return_date ? $b->actual_return_date->format('d/m/Y H:i') : '-',
                                                            'status' => $b->status,
                                                            'purpose' => $b->purpose ?? '-',
                                                            'notes' => $b->notes ?? '-',
                                                            'recorded_by' => $b->recorded_by ?? '-',
                                                        ]) }})"
                                                        title="Detail Riwayat Peminjaman">
                                                    <i class="bi bi-eye"></i> Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-arrow-left-right fs-2 d-block mb-2 text-warning"></i>
                                            @if(request('borrow_status') === 'returned')
                                                Tidak ada riwayat pengembalian barang yang cocok dengan filter.
                                            @else
                                                Tidak ada barang yang sedang dipinjam saat ini. Semua alat aman tersimpan di gudang!
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $borrowings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

    <!-- ==================== MODAL GLOBAL BERSIH DI LUAR TABEL ==================== -->

    <!-- 1. MODAL EDIT MASTER BARANG TUNGGAL (RAPI, TIDAK BERTUMPUK, SCROLLABLE VERTIKAL) -->
    <div class="modal fade" id="modalEditMasterGlobal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formEditMaster" method="POST" action="" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Master Barang
                        </h6>
                        <small class="text-muted">Perbarui data spesifikasi dan parameter stok inventaris</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <div class="row g-3">
                        <!-- Baris 1: Kode Barang & Kategori -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kode Barang (Barcode / ID) <span class="text-danger">*</span></label>
                            <input type="text" name="barang_id" id="editBarangId" class="form-control form-control-sm font-monospace" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kategori Master (7 Kategori) <span class="text-danger">*</span></label>
                            <select name="kategori" id="editKategori" class="form-select form-select-sm" required>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Baris 2: Nama Barang (Full Width) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="editNamaBarang" class="form-control form-control-sm" required>
                        </div>

                        <!-- Baris 3: Satuan & Batas Minimum Stok -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Satuan Kuantitas <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="editSatuan" class="form-control form-control-sm" placeholder="Pcs, Box, Set, Unit" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-danger">Batas Minimum Stok (Alert) <span class="text-danger">*</span></label>
                            <input type="number" name="stok_minimum" id="editStokMinimum" class="form-control form-control-sm border-danger" min="0" required>
                            <div class="form-text text-muted" style="font-size: 11px;">Peringatan merah akan muncul jika stok &le; angka ini.</div>
                        </div>

                        <!-- Baris 4: Lokasi Default -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Lokasi Default Penyimpanan</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="lokasi_default" id="editLokasiDefault" class="form-control" placeholder="Contoh: Rak APD - Gudang HSSE Lt. 1">
                            </div>
                        </div>

                        <!-- Baris 5: Keterangan Spesifikasi -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Keterangan / Spesifikasi Teknis</label>
                            <textarea name="keterangan" id="editKeterangan" class="form-control form-control-sm" rows="3" placeholder="Tambahkan catatan standar sertifikasi, serial number, atau detail lainnya..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. MODAL TRANSAKSI TERPADU (LOKASI READ-ONLY, PIC OTOMATIS, TANPA NOMOR DOKUMEN) -->
    <div class="modal fade" id="modalTransaksiTerpadu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('inventory.transactions.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="bi bi-arrow-left-right text-primary me-2"></i>Catat Transaksi Mutasi Barang
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <!-- PILIHAN JENIS TRANSAKSI INTERAKTIF -->
                    <label class="form-label small fw-bold mb-2">Jenis Transaksi :</label>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="radio" name="jenis_transaksi" id="trxMasuk" value="masuk" class="d-none trx-radio" checked onchange="handleTypeChange('masuk')">
                            <label for="trxMasuk" class="trx-type-btn trx-type-masuk w-100 d-block">
                                <i class="bi bi-box-arrow-in-down fs-5 d-block mb-1 text-success"></i>
                                <span>Barang Masuk (In)</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" name="jenis_transaksi" id="trxKeluar" value="keluar" class="d-none trx-radio" onchange="handleTypeChange('keluar')">
                            <label for="trxKeluar" class="trx-type-btn trx-type-keluar w-100 d-block">
                                <i class="bi bi-box-arrow-up fs-5 d-block mb-1 text-danger"></i>
                                <span>Barang Keluar (Out)</span>
                            </label>
                        </div>
                    </div>

                    <!-- PILIH BARANG DARI DAFTAR MASTER -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Barang dari Master <span class="text-danger">*</span></label>
                        <select name="inventory_id" id="modalInventorySelect" class="form-select form-select-sm" required onchange="handleItemSelect()">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($inventories as $inv)
                                <option value="{{ $inv->id }}" 
                                        data-stock="{{ $inv->current_stock }}" 
                                        data-unit="{{ $inv->satuan }}" 
                                        data-location="{{ $inv->lokasi_default ?? 'Gudang HSSE' }}">
                                    [{{ $inv->barang_id }}] {{ $inv->nama_barang }} (Sisa: {{ $inv->current_stock }} {{ $inv->satuan }})
                                </option>
                            @endforeach
                        </select>
                        <div id="stockInfoAlert" class="form-text mt-1 text-primary fw-bold" style="display:none;"></div>
                    </div>

                    <!-- JUMLAH & SATUAN -->
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-bold">Kuantitas / Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="modalJumlahInput" class="form-control form-control-sm" min="1" placeholder="Masukkan jumlah" required oninput="validateOutgoingStock()">
                            <div id="stockWarningText" class="text-danger small mt-1" style="display:none;">
                                <i class="bi bi-exclamation-circle-fill"></i> Jumlah melebihi stok yang tersedia!
                            </div>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold">Satuan</label>
                            <input type="text" id="modalSatuanPreview" class="form-control form-control-sm bg-light" placeholder="-" readonly>
                        </div>
                    </div>

                    <!-- LOKASI PENEMPATAN (READ-ONLY / TERKUNCI OTOMATIS) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lokasi Penempatan <span class="text-muted fw-normal">(Terkunci otomatis mengikuti master)</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input type="text" id="modalLokasiInput" class="form-control bg-light text-secondary" placeholder="Otomatis terisi..." readonly>
                        </div>
                    </div>

                    <!-- PETUGAS & TANGGAL -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Petugas Pencatat (PIC)</label>
                            <input type="text" class="form-control form-control-sm bg-light text-muted" value="{{ Auth::user()->name ?? 'Administrator HSSE' }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <!-- KETERANGAN -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Keterangan / Keperluan</label>
                        <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Cth: Distribusi APD untuk kontraktor maintenance"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitTrx" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. MODAL TAMBAH MASTER BARANG (DENGAN STOK AWAL & BATAS MINIMUM) -->
    <div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('inventory.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">
                            <i class="bi bi-box-seam-fill text-success me-2"></i>Tambah Master Barang Baru
                        </h6>
                        <small class="text-muted">Daftarkan item barang baru ke dalam katalog inventaris</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body scrollable-modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kode Barang (Barcode / ID) <span class="text-danger">*</span></label>
                            <input type="text" name="barang_id" class="form-control form-control-sm font-monospace" placeholder="Contoh: APD-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kategori Master (7 Kategori) <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select form-select-sm" required>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control form-control-sm" placeholder="Contoh: Safety Helmet MSA Putih" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Satuan Kuantitas <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control form-control-sm" placeholder="Pcs, Box, Set" value="Pcs" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-success">Stok Awal Saat Ini</label>
                            <input type="number" name="stok_awal" class="form-control form-control-sm border-success" value="0" min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-danger">Batas Minimum Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok_minimum" class="form-control form-control-sm border-danger" value="5" min="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Lokasi Default Penyimpanan</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="lokasi_default" class="form-control" placeholder="Contoh: Rak APD - Gudang HSSE Lt. 1">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Keterangan / Spesifikasi</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="3" placeholder="Keterangan spesifikasi atau standar barang..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Master Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. POP-UP MODAL KONFIRMASI HAPUS ELEGAN -->
    <div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3">
                <div class="modal-body pb-2">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Konfirmasi Penghapusan</h6>
                    <p class="text-muted small mb-2">
                        Apakah Anda yakin ingin menghapus master barang:
                    </p>
                    <div class="p-2 bg-light rounded border mb-2">
                        <div id="deleteItemName" class="fw-bold text-dark small"></div>
                        <div id="deleteItemCode" class="font-monospace text-muted" style="font-size: 11px;"></div>
                    </div>
                    <p class="text-danger small mb-0" style="font-size: 10px;">
                        ⚠️ Seluruh riwayat mutasi transaksi terkait barang ini juga akan ikut terhapus secara permanen.
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-2">
                    <form id="formHapusItem" method="POST" action="">
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

    <!-- 4. MODAL PINJAM BARANG BARU -->
    <div class="modal fade" id="modalPinjamBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formPinjamBarang" method="POST" action="{{ route('inventory.borrowings.store') }}" class="modal-content border-0 shadow" onsubmit="handleBorrowSubmit(event)">
                @csrf
                <div class="modal-header bg-light">
                    <div>
                        <h6 class="modal-title fw-bold text-dark"><i class="bi bi-box-arrow-right text-warning me-2"></i>Catat Peminjaman Barang / Alat HSSE</h6>
                        <small class="text-muted">Formulir sirkulasi peminjaman peralatan kerja lapangan</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Pilih Barang yang Dipinjam <span class="text-danger">*</span></label>
                            <select name="inventory_item_id" id="borrowItemSelect" class="form-select form-select-sm" required onchange="handleBorrowItemSelect()">
                                <option value="">-- Pilih Barang dari Master Stok --</option>
                                @foreach($allItemsForSelect as $it)
                                    @php
                                        $avail = $it->available_stock;
                                        $isConsumable = str_contains(strtolower($it->kategori ?? ''), 'habis pakai') || str_contains(strtolower($it->kategori ?? ''), 'p3k');
                                    @endphp
                                    <option value="{{ $it->id }}" 
                                            data-available="{{ $avail }}" 
                                            data-satuan="{{ $it->satuan ?? 'pcs' }}"
                                            data-consumable="{{ $isConsumable ? '1' : '0' }}"
                                            {{ $isConsumable ? 'disabled' : '' }}>
                                        [{{ $it->item_code ?? $it->kode_barang }}] {{ $it->item_name ?? $it->nama_barang }} (Tersedia: {{ $avail }} {{ $it->satuan }}) {{ $isConsumable ? '- [Habis Pakai: Tidak Dapat Dipinjam]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="borrowStockAlert" class="small mt-1 text-muted"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Jumlah / Kuantitas Dipinjam <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="quantity_borrowed" id="borrowQtyInput" class="form-control" min="1" required placeholder="Contoh: 1" oninput="validateBorrowQty()">
                                <span class="input-group-text bg-light text-muted" id="borrowSatuanLabel">Unit</span>
                            </div>
                            <small id="borrowQtyWarning" class="text-danger fw-semibold d-none"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Peminjam / Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_name" class="form-control form-control-sm" required placeholder="Contoh: Budi Santoso (Kontraktor Utility)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Unit / Departemen / Divisi</label>
                            <input type="text" name="department" class="form-control form-control-sm" placeholder="Contoh: Maintenance / Power Plant Dieng">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal & Waktu Peminjaman <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="borrow_date" class="form-control form-control-sm" required value="{{ date('Y-m-d\TH:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Rencana Tanggal Pengembalian</label>
                            <input type="date" name="expected_return_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                            <small class="text-muted" style="font-size: 11px;">Perkiraan tanggal barang dikembalikan ke gudang</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Keperluan Pemakaian / Lokasi Pekerjaan</label>
                            <textarea name="purpose" rows="2" class="form-control form-control-sm" placeholder="Contoh: Pekerjaan penggantian packing turbin Unit 1..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Catatan Tambahan (Kondisi Awal / Kelengkapan)</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Contoh: Lengkap dengan box dan sertifikat kalibrasi..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitBorrow" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. MODAL FORM PENGEMBALIAN BARANG -->
    <div class="modal fade" id="modalKembalikanBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formReturnBorrowing" method="POST" action="" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-success text-white">
                    <div>
                        <h6 class="modal-title fw-bold text-white"><i class="bi bi-box-arrow-in-left me-2"></i>Pengembalian Barang / Peralatan</h6>
                        <small class="text-white-50">Catat penerimaan fisik barang kembali ke gudang</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">No. Peminjaman:</span>
                            <strong class="font-monospace text-dark" id="returnModalCode">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Nama Barang:</span>
                            <strong class="text-dark" id="returnModalName">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Peminjam:</span>
                            <strong class="text-dark" id="returnModalBorrower">-</strong>
                        </div>
                        <div class="d-flex justify-content-between text-primary">
                            <span class="small">Sisa Belum Kembali:</span>
                            <strong class="fs-6 text-danger" id="returnModalRemaining">-</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jumlah yang Dikembalikan Sekarang <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="quantity_returned" id="returnQtyInput" class="form-control" min="1" required placeholder="Jumlah unit kembali">
                            <span class="input-group-text bg-light text-muted" id="returnSatuanLabel">Unit</span>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Mendukung pengembalian penuh maupun bertahap (sebagian).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Pengembalian Aktual <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="actual_return_date" class="form-control form-control-sm" required value="{{ date('Y-m-d\TH:i') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kondisi Fisik Barang Saat Diterima</label>
                        <select name="return_condition" class="form-select form-select-sm">
                            <option value="Baik (Lengkap)">Baik & Lengkap</option>
                            <option value="Baik (Kotor / Butuh Pembersihan)">Baik (Kotor / Butuh Pembersihan)</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat / Butuh Servis">Rusak Berat / Butuh Servis</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Catatan Penerimaan Pengembalian</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Catatan kelengkapan, kondisi fisik aksesoris..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Konfirmasi Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 6. MODAL DETAIL CATATAN PEMINJAMAN & RIWAYAT -->
    <div class="modal fade" id="modalDetailBorrowing" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Detail Riwayat Peminjaman</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="detailBorrowingContent">
                    <!-- Populated dynamically via JS -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
        function handleTypeChange(type) {
            validateOutgoingStock();
        }

        function handleItemSelect() {
            const select = document.getElementById('modalInventorySelect');
            const selectedOption = select.options[select.selectedIndex];
            const stockInfo = document.getElementById('stockInfoAlert');
            const satuanPreview = document.getElementById('modalSatuanPreview');
            const lokasiInput = document.getElementById('modalLokasiInput');

            if (selectedOption && selectedOption.value) {
                const stock = selectedOption.getAttribute('data-stock');
                const unit = selectedOption.getAttribute('data-unit');
                const location = selectedOption.getAttribute('data-location');

                satuanPreview.value = unit || '-';
                lokasiInput.value = location || 'Gudang HSSE';

                stockInfo.innerHTML = `Stok Saat Ini: <strong>${stock} ${unit}</strong>`;
                stockInfo.style.display = 'block';
            } else {
                satuanPreview.value = '-';
                lokasiInput.value = '';
                stockInfo.style.display = 'none';
            }
            validateOutgoingStock();
        }

        function validateOutgoingStock() {
            const isKeluar = document.getElementById('trxKeluar').checked;
            const select = document.getElementById('modalInventorySelect');
            const selectedOption = select.options[select.selectedIndex];
            const jumlahInput = document.getElementById('modalJumlahInput');
            const warningText = document.getElementById('stockWarningText');
            const submitBtn = document.getElementById('btnSubmitTrx');

            if (isKeluar && selectedOption && selectedOption.value) {
                const availableStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
                const requestedQty = parseInt(jumlahInput.value) || 0;

                if (requestedQty > availableStock) {
                    warningText.style.display = 'block';
                    warningText.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> Jumlah keluar (${requestedQty}) melebihi stok yang tersedia (${availableStock})!`;
                    submitBtn.disabled = true;
                    return;
                }
            }

            warningText.style.display = 'none';
            submitBtn.disabled = false;
        }

        function openQuickTransaction(id, name, stock, unit, location) {
            const select = document.getElementById('modalInventorySelect');
            select.value = id;
            handleItemSelect();

            const modal = new bootstrap.Modal(document.getElementById('modalTransaksiTerpadu'));
            modal.show();
        }

        // Modal Edit Master Global Bersih
        function openEditModal(id, barangId, nama, kategori, satuan, stokMin, lokasi, keterangan) {
            const form = document.getElementById('formEditMaster');
            form.action = `/inventory/${id}`;

            document.getElementById('editBarangId').value = barangId;
            document.getElementById('editNamaBarang').value = nama;
            document.getElementById('editKategori').value = kategori;
            document.getElementById('editSatuan').value = satuan;
            document.getElementById('editStokMinimum').value = stokMin;
            document.getElementById('editLokasiDefault').value = lokasi;
            document.getElementById('editKeterangan').value = keterangan;

            const modal = new bootstrap.Modal(document.getElementById('modalEditMasterGlobal'));
            modal.show();
        }

        // Modal Konfirmasi Hapus Elegan
        function openDeleteConfirmation(itemId, itemName, itemCode) {
            const form = document.getElementById('formHapusItem');
            form.action = `/inventory/${itemId}`;

            document.getElementById('deleteItemName').textContent = itemName;
            document.getElementById('deleteItemCode').textContent = itemCode;

            const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
            modal.show();
        }
        // JS Peminjaman & Pengembalian Barang
        function openBorrowModal() {
            const modalEl = document.getElementById('modalPinjamBarang');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                const btn = document.getElementById('btnSubmitBorrow');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Peminjaman';
                }
                modal.show();
            }
        }

        function handleBorrowSubmit(e) {
            const submitBtn = document.getElementById('btnSubmitBorrow');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
            }
            return true;
        }

        function handleBorrowItemSelect() {
            const select = document.getElementById('borrowItemSelect');
            const selected = select.options[select.selectedIndex];
            const alertBox = document.getElementById('borrowStockAlert');
            const satuanLabel = document.getElementById('borrowSatuanLabel');
            const qtyInput = document.getElementById('borrowQtyInput');

            if (selected && selected.value) {
                const avail = parseInt(selected.getAttribute('data-available')) || 0;
                const unit = selected.getAttribute('data-satuan') || 'Unit';
                satuanLabel.textContent = unit;
                alertBox.innerHTML = `Stok Tersedia: <strong class="text-success">${avail} ${unit}</strong>`;
                qtyInput.max = avail;
            } else {
                satuanLabel.textContent = 'Unit';
                alertBox.textContent = '';
                qtyInput.removeAttribute('max');
            }
            validateBorrowQty();
        }

        function validateBorrowQty() {
            const select = document.getElementById('borrowItemSelect');
            const selected = select.options[select.selectedIndex];
            const qtyInput = document.getElementById('borrowQtyInput');
            const warning = document.getElementById('borrowQtyWarning');
            const submitBtn = document.getElementById('btnSubmitBorrow');

            if (selected && selected.value) {
                const avail = parseInt(selected.getAttribute('data-available')) || 0;
                const requested = parseInt(qtyInput.value) || 0;

                if (requested > avail) {
                    warning.textContent = `Jumlah peminjaman (${requested}) melebihi stok yang tersedia (${avail})!`;
                    warning.classList.remove('d-none');
                    submitBtn.disabled = true;
                    return;
                }
            }
            warning.classList.add('d-none');
            submitBtn.disabled = false;
        }

        function openReturnModal(data) {
            const form = document.getElementById('formReturnBorrowing');
            form.action = `/inventory/borrowings/${data.id}/return`;

            document.getElementById('returnModalCode').textContent = data.code;
            document.getElementById('returnModalName').textContent = data.name;
            document.getElementById('returnModalBorrower').textContent = data.borrower;
            document.getElementById('returnModalRemaining').textContent = `${data.remaining} ${data.satuan}`;
            document.getElementById('returnSatuanLabel').textContent = data.satuan;

            const qtyInput = document.getElementById('returnQtyInput');
            qtyInput.max = data.remaining;
            qtyInput.value = data.remaining;

            const modal = new bootstrap.Modal(document.getElementById('modalKembalikanBarang'));
            modal.show();
        }

        function openDetailBorrowingModal(data) {
            const container = document.getElementById('detailBorrowingContent');
            container.innerHTML = `
                <div class="list-group list-group-flush small">
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Kode Peminjaman:</span>
                        <strong class="font-monospace text-dark">${data.code}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Nama Barang:</span>
                        <strong class="text-dark">${data.name} [${data.item_code}]</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Peminjam:</span>
                        <strong class="text-dark">${data.borrower} (${data.department || '-'})</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Kuantitas:</span>
                        <span>Dipinjam: <strong>${data.borrowed}</strong> · Kembali: <strong class="text-success">${data.returned}</strong> · Sisa: <strong class="text-danger">${data.remaining} ${data.satuan}</strong></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tgl Pinjam:</span>
                        <span>${data.borrow_date}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Rencana Kembali:</span>
                        <span>${data.expected_return_date}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Tgl Kembali Aktual:</span>
                        <span class="text-success">${data.actual_return_date}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-secondary">${data.status}</span>
                    </div>
                    <div class="list-group-item px-0">
                        <div class="text-muted mb-1">Keperluan:</div>
                        <div class="p-2 bg-light rounded text-dark">${data.purpose}</div>
                    </div>
                    <div class="list-group-item px-0">
                        <div class="text-muted mb-1">Catatan & Histori Pengembalian:</div>
                        <div class="p-2 bg-light rounded text-dark font-monospace" style="white-space: pre-line; font-size: 11px;">${data.notes}</div>
                    </div>
                </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('modalDetailBorrowing'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const borrowModalEl = document.getElementById('modalPinjamBarang');
            if (borrowModalEl) {
                borrowModalEl.addEventListener('hidden.bs.modal', function () {
                    const btn = document.getElementById('btnSubmitBorrow');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Peminjaman';
                    }
                });
                borrowModalEl.addEventListener('show.bs.modal', function () {
                    const btn = document.getElementById('btnSubmitBorrow');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Peminjaman';
                    }
                });
            }
        });

        // Alpine JS Data Handler untuk Modal 4 Kartu Ringkasan
        document.addEventListener('alpine:init', () => {
            Alpine.data('inventoryModalHandler', () => ({
                isOpen: false,
                activeType: 'masuk',
                currentPage: 1,
                pageSize: 10,
                searchQuery: '',

                dataSources: {
                    masuk: @json($modalMasukList),
                    keluar: @json($modalKeluarList),
                    aman: @json($modalStokAmanList),
                    pengadaan: @json($modalPerluPengadaanList)
                },

                openModal(type) {
                    this.activeType = type;
                    this.currentPage = 1;
                    this.searchQuery = '';
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },

                get currentList() {
                    return this.dataSources[this.activeType] || [];
                },

                get filteredData() {
                    if (!this.searchQuery.trim()) {
                        return this.currentList;
                    }
                    const query = this.searchQuery.toLowerCase();
                    return this.currentList.filter(item => {
                        const name = (item.nama_barang || '').toLowerCase();
                        const code = (item.barang_id || '').toLowerCase();
                        const kat = (item.kategori || '').toLowerCase();
                        const petugas = (item.petugas || '').toLowerCase();
                        return name.includes(query) || code.includes(query) || kat.includes(query) || petugas.includes(query);
                    });
                },

                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.pageSize) || 1;
                },

                get paginatedData() {
                    const start = (this.currentPage - 1) * this.pageSize;
                    return this.filteredData.slice(start, start + this.pageSize);
                },

                get headerStyle() {
                    const config = {
                        masuk: {
                            title: 'Daftar Rincian Transaksi: Barang Masuk',
                            subtitle: 'Histori penerimaan dan pengadaan barang HSSE',
                            icon: 'bi-box-arrow-in-down',
                            markBg: 'background: #0d6efd;'
                        },
                        keluar: {
                            title: 'Daftar Rincian Transaksi: Barang Keluar',
                            subtitle: 'Histori pengeluaran dan pemakaian barang HSSE di lapangan',
                            icon: 'bi-box-arrow-up-right',
                            markBg: 'background: #dc3545;'
                        },
                        aman: {
                            title: 'Daftar Master Barang: Kondisi Stok Aman',
                            subtitle: 'Barang dengan tingkat ketersediaan di atas batas minimum',
                            icon: 'bi-shield-check',
                            markBg: 'background: #198754;'
                        },
                        pengadaan: {
                            title: 'Daftar Perlu Pengadaan Segera (Reorder Alert)',
                            subtitle: 'Barang dengan stok telah mencapai atau berada di bawah batas minimum',
                            icon: 'bi-exclamation-triangle-fill',
                            markBg: 'background: #dc3545;'
                        }
                    };
                    return config[this.activeType] || config.masuk;
                }
            }));
        });
    </script>
@endpush

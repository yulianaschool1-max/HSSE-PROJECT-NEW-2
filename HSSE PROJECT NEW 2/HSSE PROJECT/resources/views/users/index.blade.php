@extends('layouts.app', [
    'title' => 'Manajemen Pengguna',
    'activeModule' => 'users'
])

@push('styles')
<style>
    .filter-grid-users {
        display: grid;
        grid-template-columns: 160px 160px 160px 1fr auto;
        gap: 12px;
        align-items: center;
    }

    .badge-role-admin {
        background: #ede9fe;
        color: #7c3aed;
        border: 1px solid #ddd6fe;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-role-inventory {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-role-user {
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-aktif {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-nonaktif {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Badge Status Aktivitas Online / Offline */
    .badge-online {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-offline {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        font-weight: 500;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-dot-online {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        display: inline-block;
    }

    .status-dot-offline {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #94a3b8;
        display: inline-block;
    }

    .user-avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        position: relative;
    }

    .user-avatar-circle.admin {
        background: #ede9fe;
        color: #7c3aed;
    }

    .user-avatar-circle.inventory {
        background: #fef3c7;
        color: #d97706;
    }

    .detail-card-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 10px;
    }

    .detail-card-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-card-value {
        font-size: 13px;
        color: #0f172a;
        font-weight: 600;
    }

    .btn-action-history {
        color: #d97706;
        border-color: #fde68a;
        background: #fffbeb;
    }

    .btn-action-history:hover {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }

    .activity-badge-action {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
        display: inline-block;
    }

    .act-tambah { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .act-ubah { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .act-hapus { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .act-catatan { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

    @media (max-width: 992px) {
        .filter-grid-users {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- NOTIFIKASI SUKSES / ERROR -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm border-0" role="alert" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #059669 !important;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm border-0" role="alert" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm border-0" role="alert" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terdapat kesalahan input:</div>
        <ul class="mb-0 ps-3" style="font-size: 12.5px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box blue">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <h1 class="page-heading">Manajemen Pengguna</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Manajemen Pengguna</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('users.activities.all') }}" class="btn btn-action-outline">
            <i class="bi bi-clock-history text-primary"></i> Riwayat Seluruh Aktivitas
        </a>
        @if(Auth::user()?->isAdminManagement())
            <button type="button" class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
            </button>
        @endif
    </div>
</div>

<!-- KARTU RINGKASAN JUMLAH PENGGUNA -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <!-- Total Pengguna -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Pengguna</div>
            <div class="stat-number">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">{{ $stats['active'] ?? 0 }} akun aktif</span></div>
        </div>
    </div>

    <!-- Admin Management -->
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #ede9fe; color: #7c3aed;">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Admin Management</div>
            <div class="stat-number" style="color: #7c3aed;">{{ $stats['admin_management'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Hak akses penuh</span></div>
        </div>
    </div>

    <!-- Admin Inventory -->
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #fef3c7; color: #d97706;">
            <i class="bi bi-boxes"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Admin Inventory</div>
            <div class="stat-number" style="color: #d97706;">{{ $stats['admin_inventory'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Kelola Inventory</span></div>
        </div>
    </div>

    <!-- Regular User / Staff -->
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #e0f2fe; color: #0284c7;">
            <i class="bi bi-person-badge-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">User</div>
            <div class="stat-number text-info">{{ $stats['user'] ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Read-only operasional</span></div>
        </div>
    </div>

    <!-- Pengguna Online -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed">
            <i class="bi bi-broadcast"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Sedang Online</div>
            <div class="stat-number text-success">{{ $stats['online'] ?? 0 }}</div>
            <div class="stat-trend">
                <span class="stat-trend-up">
                    <span class="status-dot-online me-1"></span>Aktif saat ini
                </span>
            </div>
        </div>
    </div>
</div>

<!-- FILTER CARD -->
<div class="filter-card">
    <form id="filterForm" method="GET" action="{{ route('users.index') }}">
        <div class="filter-grid-users">
            <!-- Filter Role -->
            <div>
                <select name="role" id="filterRole" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua" {{ request('role') == 'Semua' || !request('role') ? 'selected' : '' }}>Semua Role</option>
                    @foreach($allRoles as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" {{ request('role') == $roleKey ? 'selected' : '' }}>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Akun -->
            <div>
                <select name="status" id="filterStatus" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua" {{ request('status') == 'Semua' || !request('status') ? 'selected' : '' }}>Semua Status Akun</option>
                    @foreach($allStatuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>Status: {{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Aktivitas Online / Offline -->
            <div>
                <select name="activity" id="filterActivity" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua" {{ request('activity') == 'Semua' || !request('activity') ? 'selected' : '' }}>Semua Aktivitas</option>
                    @foreach($allActivities as $act)
                        <option value="{{ $act }}" {{ request('activity') == $act ? 'selected' : '' }}>{{ $act }}</option>
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
                       placeholder="Cari berdasarkan nama atau email..."
                       value="{{ request('search') }}">
            </div>

            <!-- Reset Filter Button -->
            <div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="height: 38px; font-size: 12px;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- TABEL PENGGUNA -->
<div class="table-card">
    <div class="table-topbar">
        <h2 class="table-section-title">Daftar Akun Pengguna</h2>

        <div class="table-controls">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:11.5px">Tampilkan</span>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px; font-size:11.5px" onchange="changePerPage(this.value)">
                    <option value="5" {{ request('per_page') == '5' ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page') == '10' || !request('per_page') ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-muted" style="font-size:11.5px">data per halaman</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No.</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th style="width: 110px; text-align: center;">Role</th>
                    <th style="width: 110px; text-align: center;">Status Akun</th>
                    <th style="width: 140px; text-align: center;">Status Aktivitas</th>
                    <th style="width: 120px;">Tgl Terdaftar</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                    <tr>
                        <td style="text-align: center;">{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-circle {{ $u->isAdminManagement() ? 'admin' : ($u->isAdminInventory() ? 'inventory' : '') }}">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                    @if(Auth::id() === $u->id)
                                        <span class="badge bg-light text-muted border" style="font-size: 10px;">(Anda)</span>
                                    @endif
                                    @if($u->isPrimaryAdmin())
                                        <span class="badge bg-purple text-white ms-1" style="font-size: 9px; background: #7c3aed;">Utama</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-secondary">{{ $u->email }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($u->isAdminManagement())
                                <span class="badge-role-admin">
                                    <i class="bi bi-shield-check"></i> Admin Management
                                </span>
                            @elseif($u->isAdminInventory())
                                <span class="badge-role-inventory">
                                    <i class="bi bi-boxes"></i> Admin Inventory
                                </span>
                            @else
                                <span class="badge-role-user">
                                    <i class="bi bi-person"></i> User
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($u->isActive())
                                <span class="badge-status-aktif">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge-status-nonaktif">
                                    <i class="bi bi-dash-circle"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($u->is_online)
                                <span class="badge-online" title="Aktif dalam 5 menit terakhir">
                                    <span class="status-dot-online"></span> Online
                                </span>
                            @else
                                <span class="badge-offline" title="{{ $u->last_seen_date }}">
                                    <span class="status-dot-offline"></span> {{ $u->last_seen }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #475569;">
                                {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex gap-1">
                                <!-- Tombol View -->
                                <button type="button"
                                        class="btn-action-icon btn-action-view"
                                        title="Lihat Detail Pengguna"
                                        onclick="openDetailModal({{ $u->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Tombol Riwayat Aktivitas -->
                                <button type="button"
                                        class="btn-action-icon btn-action-history"
                                        title="Riwayat Aktivitas Pengguna"
                                        onclick="openActivityModal({{ $u->id }})">
                                    <i class="bi bi-clock-history"></i>
                                </button>

                                {{-- Tombol Edit dan Hapus HANYA tersedia untuk Admin Management --}}
                                @if(Auth::user()?->isAdminManagement())
                                    <!-- Tombol Edit -->
                                    <button type="button"
                                            class="btn-action-icon btn-action-edit"
                                            title="Edit Pengguna"
                                            onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $u->role }}', '{{ $u->status }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    @if(Auth::id() === $u->id || $u->isPrimaryAdmin())
                                        <button type="button"
                                                class="btn-action-icon text-muted"
                                                style="opacity: 0.35; cursor: not-allowed;"
                                                title="{{ Auth::id() === $u->id ? 'Anda tidak dapat menghapus akun Anda sendiri' : 'Akun Admin Management utama dilindungi dan tidak dapat dihapus' }}"
                                                disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn-action-icon btn-action-delete"
                                                title="Hapus Pengguna"
                                                onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->role_label }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-search me-1"></i> Tidak ditemukan pengguna yang cocok dengan filter atau kata kunci.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- BOTTOM BAR: PAGINATION -->
    <div class="table-bottom-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div style="font-size: 12px; color: #64748b;">
            @if($users->total() > 0)
                Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari total {{ $users->total() }} akun
            @else
                Tidak ada data pengguna
            @endif
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL DETAIL PENGGUNA (Bisa dibuka Admin dan User) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalDetailPengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed blue">
                <h5 class="modal-title" id="detailModalTitle">
                    <i class="bi bi-person-lines-fill me-1"></i> Detail Informasi Pengguna
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailModalBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Memuat data pengguna...</div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL RIWAYAT AKTIVITAS PENGGUNA (Bisa dibuka Admin dan User) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalRiwayatAktivitas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed blue">
                <div class="d-flex align-items-center gap-2">
                    <div class="user-avatar-circle" id="activityAvatar" style="width:36px; height:36px; font-size:15px; background:white; color:#2563eb;">
                        U
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="activityModalTitle">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Aktivitas Pengguna
                        </h5>
                        <div class="text-white-50" style="font-size: 11.5px;" id="activityModalSubtitle">
                            Memuat data aktivitas...
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Filter Khusus Riwayat Aktivitas -->
            <div class="p-3 bg-light border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3" id="activityUserFilterCol">
                        <select id="activityUserFilter" class="form-select form-select-sm" onchange="onActivityUserChange()">
                            <option value="Semua">Semua Pengguna</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="activityModuleFilter" class="form-select form-select-sm" onchange="filterActivityLogs()">
                            <option value="Semua">Semua Modul</option>
                            @if(!empty($dropdownModules))
                                @foreach($dropdownModules as $dMod)
                                    <option value="{{ $dMod['key'] }}">{{ $dMod['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="activityActionFilter" class="form-select form-select-sm" onchange="filterActivityLogs()">
                            <option value="Semua">Semua Jenis Aksi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="activityDateFilter" class="form-control form-control-sm" onchange="filterActivityLogs()" title="Filter Tanggal Aktivitas">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <input type="text" id="activitySearchFilter" class="form-control form-control-sm" placeholder="Cari aktivitas..." onkeyup="filterActivityLogsDebounced()">
                            <button class="btn btn-outline-secondary" type="button" onclick="resetActivityFilters()" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body p-0" id="activityModalBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-teal mb-2" role="status"></div>
                    <div>Memuat log aktivitas riil dari database...</div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between align-items-center">
                <div id="activityPaginationInfo" style="font-size: 12px; color: #64748b;">
                    Memuat...
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('users.activities.all') }}" class="btn btn-sm btn-outline-primary px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Penuh
                    </a>
                    <div id="activityPaginationButtons" class="d-flex gap-1"></div>
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CRUD KHUSUS ADMINISTRATOR MANAGEMENT --}}
@if(Auth::user()?->isAdminManagement())
    <!-- ============================================================== -->
    <!-- MODAL TAMBAH PENGGUNA -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-custom-content">
                <div class="modal-header modal-header-themed blue">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-semibold" style="font-size: 12px;">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" required placeholder="Contoh: Budi Santoso" value="{{ old('name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="create_email" class="form-label fw-semibold" style="font-size: 12px;">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email" required placeholder="nama@geodipa.co.id" value="{{ old('email') }}">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="create_role" class="form-label fw-semibold" style="font-size: 12px;">Role Pengguna <span class="text-danger">*</span></label>
                                <select class="form-select" id="create_role" name="role" required>
                                    <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>User (Read-Only Operasional)</option>
                                    <option value="admin_inventory" {{ old('role') == 'admin_inventory' ? 'selected' : '' }}>Admin Inventory (Kelola Inventory)</option>
                                    <option value="admin_management" {{ old('role') == 'admin_management' ? 'selected' : '' }}>Admin Management (Hak Akses Penuh)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_status" class="form-label fw-semibold" style="font-size: 12px;">Status Akun <span class="text-danger">*</span></label>
                                <select class="form-select" id="create_status" name="status" required>
                                    <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="create_password" class="form-label fw-semibold" style="font-size: 12px;">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password" name="password" required placeholder="Minimal 8 karakter">
                        </div>

                        <div class="mb-2">
                            <label for="create_password_confirmation" class="form-label fw-semibold" style="font-size: 12px;">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation" required placeholder="Ulangi password di atas">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-2 border-top">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-action-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL EDIT PENGGUNA -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-custom-content">
                <div class="modal-header modal-header-themed blue">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-1"></i> Edit Data Pengguna
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-semibold" style="font-size: 12px;">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_email" class="form-label fw-semibold" style="font-size: 12px;">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="edit_role" class="form-label fw-semibold" style="font-size: 12px;">Role Pengguna <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="user">User (Read-Only Operasional)</option>
                                    <option value="admin_inventory">Admin Inventory (Kelola Inventory)</option>
                                    <option value="admin_management">Admin Management (Hak Akses Penuh)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label fw-semibold" style="font-size: 12px;">Status Akun <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-light border py-2 px-3 mb-3 text-muted" style="font-size: 11.5px;">
                            <i class="bi bi-info-circle me-1 text-primary"></i> Kosongkan password jika tidak ingin mengubah password lama.
                        </div>

                        <div class="mb-3">
                            <label for="edit_password" class="form-label fw-semibold" style="font-size: 12px;">Password Baru (Opsional)</label>
                            <input type="password" class="form-control" id="edit_password" name="password" placeholder="Minimal 8 karakter jika diisi">
                        </div>

                        <div class="mb-2">
                            <label for="edit_password_confirmation" class="form-label fw-semibold" style="font-size: 12px;">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-2 border-top">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-action-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Perbarui Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FORM TERSEMBUNYI UNTUK DELETE -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection

@push('scripts')
<script>
    const detailModal = new bootstrap.Modal(document.getElementById('modalDetailPengguna'));
    @if(Auth::user()?->isAdminManagement())
        const editModal = new bootstrap.Modal(document.getElementById('modalEditPengguna'));
    @endif

    function changePerPage(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function openDetailModal(userId) {
        document.getElementById('detailModalBody').innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-teal mb-2" role="status"></div>
                <div>Mengambil data pengguna...</div>
            </div>
        `;
        detailModal.show();

        fetch(`/users/${userId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Data pengguna tidak ditemukan.');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.user) {
                throw new Error(data.message || 'Gagal memuat data pengguna.');
            }
            const u = data.user;
            let roleBadge = '<span class="badge-role-user"><i class="bi bi-person"></i> User</span>';
            let avatarClass = '';
            if (u.role === 'admin_management' || u.role === 'Admin') {
                roleBadge = '<span class="badge-role-admin"><i class="bi bi-shield-check"></i> Admin Management</span>';
                avatarClass = 'admin';
            } else if (u.role === 'admin_inventory') {
                roleBadge = '<span class="badge-role-inventory"><i class="bi bi-boxes"></i> Admin Inventory</span>';
                avatarClass = 'inventory';
            }
            const statusBadge = (u.status === 'Aktif' || !u.status)
                ? '<span class="badge-status-aktif"><i class="bi bi-check-circle"></i> Akun Aktif</span>'
                : '<span class="badge-status-nonaktif"><i class="bi bi-dash-circle"></i> Akun Nonaktif</span>';

            const activityBadge = u.is_online
                ? '<span class="badge-online"><span class="status-dot-online"></span> Sedang Online</span>'
                : `<span class="badge-offline"><span class="status-dot-offline"></span> Offline (${escapeHtml(u.last_seen || '-')})</span>`;

            const createdAt = u.created_at ? new Date(u.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
            const lastSeenDate = u.last_seen_date || '-';

            document.getElementById('detailModalBody').innerHTML = `
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="user-avatar-circle ${avatarClass} mx-auto mb-2" style="width: 54px; height: 54px; font-size: 22px;">
                        ${(u.name || 'U').charAt(0).toUpperCase()}
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">${escapeHtml(u.name)}</h5>
                    <div class="text-muted" style="font-size: 13px;">${escapeHtml(u.email)}</div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                        ${roleBadge}
                        ${statusBadge}
                        ${activityBadge}
                    </div>
                </div>

                <div class="detail-card-item">
                    <div class="detail-card-label">Identitas Pengguna</div>
                    <div class="detail-card-value">${escapeHtml(u.name)} (ID: #${u.id})</div>
                </div>

                <div class="detail-card-item">
                    <div class="detail-card-label">Alamat Email Resmi</div>
                    <div class="detail-card-value">${escapeHtml(u.email)}</div>
                </div>

                <div class="detail-card-item">
                    <div class="detail-card-label">Status Aktivitas Sesi</div>
                    <div class="detail-card-value">${escapeHtml(u.is_online ? 'Sedang aktif dalam sesi saat ini' : ('Terakhir aktif: ' + lastSeenDate))}</div>
                </div>

                <div class="detail-card-item mb-0">
                    <div class="detail-card-label">Tanggal Pendaftaran Akun</div>
                    <div class="detail-card-value" style="font-size: 12.5px;">${createdAt} WIB</div>
                </div>
            `;
        })
        .catch(error => {
            detailModal.hide();
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Ditemukan',
                text: error.message || 'Gagal memuat rincian pengguna.'
            });
        });
    }

    @if(Auth::user()?->isAdminManagement())
        function openEditModal(id, name, email, role, status) {
            const form = document.getElementById('editUserForm');
            form.action = `/users/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;

            let normalizedRole = role;
            if (role === 'Admin') normalizedRole = 'admin_management';
            if (role === 'User') normalizedRole = 'user';
            document.getElementById('edit_role').value = normalizedRole;

            document.getElementById('edit_status').value = status || 'Aktif';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';

            editModal.show();
        }

        function confirmDelete(id, name, role) {
            Swal.fire({
                title: 'Hapus Pengguna Ini?',
                html: `Apakah Anda yakin ingin menghapus akun <b>${escapeHtml(name)}</b>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteUserForm');
                    form.action = `/users/${id}`;
                    form.submit();
                }
            });
        }
    @endif

    const activityModal = new bootstrap.Modal(document.getElementById('modalRiwayatAktivitas'));
    let currentActivityUserId = null;
    let currentActivityPage = 1;
    let activityDebounceTimer = null;

    function openActivityModal(userId) {
        currentActivityUserId = userId;
        currentActivityPage = 1;

        // Reset filter inputs
        if (document.getElementById('activityUserFilter')) {
            document.getElementById('activityUserFilter').value = userId ? String(userId) : 'Semua';
        }
        document.getElementById('activityModuleFilter').value = 'Semua';
        document.getElementById('activityActionFilter').value = 'Semua';
        document.getElementById('activityDateFilter').value = '';
        document.getElementById('activitySearchFilter').value = '';

        document.getElementById('activityAvatar').textContent = '...';
        document.getElementById('activityModalTitle').innerHTML = '<i class="bi bi-clock-history me-1"></i> Riwayat Aktivitas';
        document.getElementById('activityModalSubtitle').textContent = 'Mengambil data dari server...';
        document.getElementById('activityModalBody').innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-teal mb-2" role="status"></div>
                <div>Mengambil riwayat audit aktivitas sistem...</div>
            </div>
        `;
        document.getElementById('activityPaginationInfo').textContent = '';
        document.getElementById('activityPaginationButtons').innerHTML = '';

        activityModal.show();
        loadUserActivities();
    }

    function openGlobalActivityModal() {
        openActivityModal(null);
    }

    function onActivityUserChange() {
        const selectedVal = document.getElementById('activityUserFilter').value;
        currentActivityUserId = (selectedVal && selectedVal !== 'Semua') ? selectedVal : null;
        currentActivityPage = 1;
        loadUserActivities();
    }

    function filterActivityLogs() {
        currentActivityPage = 1;
        loadUserActivities();
    }

    function filterActivityLogsDebounced() {
        clearTimeout(activityDebounceTimer);
        activityDebounceTimer = setTimeout(() => {
            currentActivityPage = 1;
            loadUserActivities();
        }, 350);
    }

    function resetActivityFilters() {
        if (document.getElementById('activityUserFilter')) {
            document.getElementById('activityUserFilter').value = currentActivityUserId ? String(currentActivityUserId) : 'Semua';
        }
        document.getElementById('activityModuleFilter').value = 'Semua';
        document.getElementById('activityActionFilter').value = 'Semua';
        document.getElementById('activityDateFilter').value = '';
        document.getElementById('activitySearchFilter').value = '';
        currentActivityPage = 1;
        loadUserActivities();
    }

    function loadUserActivities(page = null) {
        if (page) currentActivityPage = page;

        const userVal = document.getElementById('activityUserFilter') ? document.getElementById('activityUserFilter').value : '';
        const moduleVal = document.getElementById('activityModuleFilter').value;
        const actionVal = document.getElementById('activityActionFilter').value;
        const dateVal = document.getElementById('activityDateFilter').value;
        const searchVal = document.getElementById('activitySearchFilter').value;

        // Determine URL: if specific user is targeted, query user activities; else query all activities
        const targetUserId = (userVal && userVal !== 'Semua') ? userVal : currentActivityUserId;
        const url = (targetUserId && targetUserId !== 'Semua')
            ? new URL(`/users/${targetUserId}/activities`, window.location.origin)
            : new URL(`/users/activities/all`, window.location.origin);

        url.searchParams.set('page', currentActivityPage);
        url.searchParams.set('per_page', 10);
        if (moduleVal && moduleVal !== 'Semua') url.searchParams.set('module', moduleVal);
        if (actionVal && actionVal !== 'Semua') url.searchParams.set('action', actionVal);
        if (dateVal) url.searchParams.set('date', dateVal);
        if (searchVal) url.searchParams.set('search', searchVal);

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat log aktivitas.');
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Gagal memuat log aktivitas.');

            // Update user filter options if available
            const userSelect = document.getElementById('activityUserFilter');
            if (userSelect && userSelect.options.length <= 1 && data.available_users) {
                data.available_users.forEach(usr => {
                    const opt = document.createElement('option');
                    opt.value = usr.id;
                    opt.textContent = `${usr.name} (${usr.role})`;
                    userSelect.appendChild(opt);
                });
                if (targetUserId) {
                    userSelect.value = String(targetUserId);
                }
            }

            const u = data.user;
            if (u && targetUserId) {
                document.getElementById('activityAvatar').textContent = (u.name || 'U').charAt(0).toUpperCase();
                document.getElementById('activityModalTitle').innerHTML = `<i class="bi bi-clock-history me-1"></i> Riwayat Aktivitas: ${escapeHtml(u.name)}`;
                document.getElementById('activityModalSubtitle').textContent = `${escapeHtml(u.email)} &bull; ${u.role} &bull; Total ${data.stats.total} Aktivitas Tercatat`;
            } else {
                document.getElementById('activityAvatar').textContent = 'ALL';
                document.getElementById('activityModalTitle').innerHTML = `<i class="bi bi-clock-history me-1"></i> Riwayat Seluruh Aktivitas Sistem`;
                document.getElementById('activityModalSubtitle').textContent = `Seluruh Pengguna &bull; Seluruh Modul &bull; Total ${data.stats.total} Aktivitas Tercatat`;
            }

            // Populate Module Filter Options dynamically if not yet populated
            const modSelect = document.getElementById('activityModuleFilter');
            if (modSelect && modSelect.options.length <= 1) {
                const modulesList = data.dropdown_modules || data.available_modules || [];
                modulesList.forEach(mod => {
                    const opt = document.createElement('option');
                    if (typeof mod === 'object' && mod !== null) {
                        opt.value = mod.key || mod.log_name;
                        opt.textContent = mod.label || mod.log_name;
                    } else {
                        opt.value = mod;
                        opt.textContent = mod;
                    }
                    modSelect.appendChild(opt);
                });
            }

            // Populate Action Filter Options dynamically if not yet populated
            const actSelect = document.getElementById('activityActionFilter');
            if (actSelect.options.length <= 1 && data.available_actions) {
                data.available_actions.forEach(act => {
                    const opt = document.createElement('option');
                    opt.value = act;
                    opt.textContent = act;
                    actSelect.appendChild(opt);
                });
            }

            const logs = data.logs.data || [];
            if (!logs.length) {
                document.getElementById('activityModalBody').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 38px;"></i>
                        <div class="mt-2 fw-semibold">Belum Ada Riwayat Aktivitas</div>
                        <div style="font-size: 12px;">Tidak ada data log aktivitas yang cocok dengan filter.</div>
                    </div>
                `;
                document.getElementById('activityPaginationInfo').textContent = 'Tidak ada aktivitas';
                document.getElementById('activityPaginationButtons').innerHTML = '';
                return;
            }

            // Render table of activity logs
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 12px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th style="width: 45px;" class="text-center">No.</th>
                                <th style="width: 140px;">Waktu</th>
                                <th style="width: 150px;">Pengguna</th>
                                <th style="width: 110px;">Modul</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                                <th>Deskripsi Perubahan</th>
                                <th style="width: 90px;" class="text-center">ID Data</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const fromIndex = (data.logs.current_page - 1) * data.logs.per_page;
            logs.forEach((log, idx) => {
                let badgeClass = 'act-ubah';
                const actUpper = (log.action || '').toUpperCase();
                if (actUpper.includes('TAMBAH') || actUpper.includes('CREATE')) {
                    badgeClass = 'act-tambah';
                } else if (actUpper.includes('HAPUS') || actUpper.includes('DELETE')) {
                    badgeClass = 'act-hapus';
                } else if (actUpper.includes('CATATAN') || actUpper.includes('NOTE')) {
                    badgeClass = 'act-catatan';
                }

                const createdAt = log.created_at ? new Date(log.created_at).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                }) : '-';

                // Check diff changes
                let changesHtml = '';
                if (log.changes && typeof log.changes === 'object') {
                    if (log.changes.before && log.changes.after) {
                        const beforeKeys = Object.keys(log.changes.before);
                        const diffBadges = [];
                        beforeKeys.forEach(k => {
                            const bVal = log.changes.before[k];
                            const aVal = log.changes.after[k];
                            if (String(bVal) !== String(aVal)) {
                                diffBadges.push(`<span class="badge bg-light text-secondary border me-1 mb-1" style="font-size:10px;">${escapeHtml(k)}: <span class="text-danger text-decoration-line-through">${escapeHtml(String(bVal ?? 'kosong'))}</span> &rarr; <span class="text-success">${escapeHtml(String(aVal ?? 'kosong'))}</span></span>`);
                            }
                        });
                        if (diffBadges.length > 0) {
                            changesHtml = `<div class="mt-1 d-flex flex-wrap">${diffBadges.join('')}</div>`;
                        }
                    } else if (log.changes.before && !log.changes.after) {
                        const keys = Object.keys(log.changes.before);
                        const badges = keys.slice(0, 3).map(k => `<span class="badge bg-light text-danger border me-1 mb-1" style="font-size:10px;">${escapeHtml(k)}: ${escapeHtml(String(log.changes.before[k] ?? ''))}</span>`).join('');
                        changesHtml = `<div class="mt-1 d-flex flex-wrap">${badges}</div>`;
                    } else if (!log.changes.before && log.changes.after) {
                        const keys = Object.keys(log.changes.after);
                        const badges = keys.slice(0, 3).map(k => `<span class="badge bg-light text-success border me-1 mb-1" style="font-size:10px;">${escapeHtml(k)}: ${escapeHtml(String(log.changes.after[k] ?? ''))}</span>`).join('');
                        changesHtml = `<div class="mt-1 d-flex flex-wrap">${badges}</div>`;
                    }
                }

                tableHtml += `
                    <tr>
                        <td class="text-center text-muted">${fromIndex + idx + 1}</td>
                        <td class="text-nowrap"><i class="bi bi-calendar3 me-1 text-muted"></i>${createdAt} WIB</td>
                        <td>
                            <div class="fw-semibold text-dark">${escapeHtml(log.user_name || log.user?.name || 'Sistem')}</div>
                            <small class="text-muted" style="font-size:10.5px">${escapeHtml(log.user?.email || '')}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(log.module)}</span></td>
                        <td class="text-center"><span class="activity-badge-action ${badgeClass}">${escapeHtml(log.action)}</span></td>
                        <td>
                            <div class="fw-semibold text-dark">${escapeHtml(log.description || '-')}</div>
                            ${changesHtml}
                            ${log.ip_address ? `<small class="text-muted" style="font-size:10px">IP: ${escapeHtml(log.ip_address)}</small>` : ''}
                        </td>
                        <td class="text-center text-muted">#${log.record_id || '-'}</td>
                    </tr>
                `;
            });

            tableHtml += `</tbody></table></div>`;
            document.getElementById('activityModalBody').innerHTML = tableHtml;

            // Pagination setup
            const total = data.logs.total;
            const from = (data.logs.current_page - 1) * data.logs.per_page + 1;
            const to = Math.min(data.logs.current_page * data.logs.per_page, total);
            document.getElementById('activityPaginationInfo').textContent = `Menampilkan ${from} - ${to} dari ${total} aktivitas`;

            let paginationBtns = '';
            const lastPage = data.logs.last_page;
            for (let p = 1; p <= lastPage; p++) {
                if (p === 1 || p === lastPage || Math.abs(p - data.logs.current_page) <= 2) {
                    paginationBtns += `
                        <button type="button" class="btn btn-sm ${p === data.logs.current_page ? 'btn-teal' : 'btn-outline-secondary'}" style="${p === data.logs.current_page ? 'background:#0d9488; color:white; border-color:#0d9488;' : ''}" onclick="loadUserActivities(${p})">
                            ${p}
                        </button>
                    `;
                }
            }
            document.getElementById('activityPaginationButtons').innerHTML = paginationBtns;
        })
        .catch(err => {
            document.getElementById('activityModalBody').innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle" style="font-size: 32px;"></i>
                    <div class="mt-2 fw-semibold">${escapeHtml(err.message || 'Gagal memuat data aktivitas.')}</div>
                </div>
            `;
        });
    }


    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush

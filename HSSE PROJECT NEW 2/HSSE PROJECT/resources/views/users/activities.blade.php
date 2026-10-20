@extends('layouts.app', [
    'title' => 'Riwayat Aktivitas',
    'activeModule' => 'users'
])

@push('styles')
<style>
    .filter-grid-activities {
        display: grid;
        grid-template-columns: 180px 160px 150px 145px 145px 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .activity-badge-module {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid;
    }

    .mod-daily-work { background: #f0fdfa; color: #0d9488; border-color: #99f6e4; }
    .mod-hsse-plan { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
    .mod-events { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
    .mod-gas { background: #fff7ed; color: #ea580c; border-color: #ffedd5; }
    .mod-inventory { background: #fefce8; color: #ca8a04; border-color: #fef08a; }
    .mod-incident { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .mod-users { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .mod-auth { background: #f8fafc; color: #475569; border-color: #cbd5e1; }

    .activity-badge-act {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
        display: inline-block;
        border: 1px solid;
    }

    .act-tambah { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .act-ubah { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
    .act-hapus { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    .act-catatan { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .act-upload { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .act-login { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .act-logout { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    .diff-badge-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 5px;
        font-size: 11px;
    }

    .diff-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 2px 7px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 1200px) {
        .filter-grid-activities {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .filter-grid-activities {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- PAGE HEADER -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box blue">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <h1 class="page-heading">
                @if(isset($user))
                    Riwayat Aktivitas: <span class="text-primary">{{ $user->name }}</span>
                @else
                    Riwayat Aktivitas Seluruh Modul
                @endif
            </h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <a href="{{ route('users.index') }}">Manajemen Pengguna</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Riwayat Aktivitas</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('users.index') }}" class="btn btn-action-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pengguna
        </a>
        @if(isset($user))
            <a href="{{ route('users.activities.all') }}" class="btn btn-action-primary">
                <i class="bi bi-globe me-1"></i> Semua Riwayat Modul
            </a>
        @endif
    </div>
</div>

<!-- KARTU STATISTIK RINGKAS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Log Aktivitas</div>
            <div class="stat-number">{{ $totalLogs ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Tercatat di sistem</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-calendar2-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Aktivitas Hari Ini</div>
            <div class="stat-number text-success">{{ $todayLogs ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Aktivitas 24 jam</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-grid-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Modul Terintegrasi</div>
            <div class="stat-number">{{ $modulesCount ?? 0 }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Daily Work, Plan, Events, dsb</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Pelaku Aktivitas</div>
            <div class="stat-number text-warning">{{ $usersCount ?? (isset($user) ? 1 : ($availableUsers->count() ?? 0)) }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Akun yang bertransaksi</span></div>
        </div>
    </div>
</div>

<!-- FILTER CARD -->
<div class="filter-card">
    <form method="GET" action="{{ isset($user) ? route('users.activities', $user->id) : route('users.activities.all') }}">
        <div class="filter-grid filter-grid-activities">
            <!-- Filter Pengguna (Hanya jika tampilan keseluruhan) -->
            <div>
                <select name="user_id" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua">Semua Pengguna</option>
                    @foreach($availableUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id || (isset($user) && $user->id == $u->id) ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Modul -->
            <div>
                <select name="module" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua">Semua Modul</option>
                    @if(!empty($dropdownModules))
                        @foreach($dropdownModules as $dMod)
                            <option value="{{ $dMod['key'] }}" {{ (request('module') == $dMod['key'] || request('module') == $dMod['label'] || request('module') == $dMod['log_name']) ? 'selected' : '' }}>
                                {{ $dMod['label'] }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Filter Jenis Aksi -->
            <div>
                <select name="action" class="form-select form-select-filter" onchange="this.form.submit()">
                    <option value="Semua">Semua Aksi</option>
                    <option value="TAMBAH" {{ request('action') == 'TAMBAH' ? 'selected' : '' }}>Tambah (Create)</option>
                    <option value="UBAH" {{ request('action') == 'UBAH' ? 'selected' : '' }}>Ubah (Update)</option>
                    <option value="HAPUS" {{ request('action') == 'HAPUS' ? 'selected' : '' }}>Hapus (Delete)</option>
                    <option value="UPLOAD" {{ request('action') == 'UPLOAD' ? 'selected' : '' }}>Upload Berkas</option>
                    <option value="LOGIN" {{ request('action') == 'LOGIN' ? 'selected' : '' }}>Login</option>
                    <option value="LOGOUT" {{ request('action') == 'LOGOUT' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div class="filter-input-wrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="date" name="start_date" class="form-control form-control-filter" value="{{ request('start_date') }}" title="Mulai Tanggal" onchange="this.form.submit()">
            </div>

            <!-- Tanggal Selesai -->
            <div class="filter-input-wrap">
                <i class="bi bi-calendar3 filter-icon-addon"></i>
                <input type="date" name="end_date" class="form-control form-control-filter" value="{{ request('end_date') }}" title="Sampai Tanggal" onchange="this.form.submit()">
            </div>

            <!-- Pencarian Teks -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" name="search" class="form-control form-control-filter" placeholder="Cari nama, deskripsi, atau modul..." value="{{ request('search') }}">
            </div>

            <!-- Reset Button -->
            <div>
                <a href="{{ isset($user) ? route('users.activities', $user->id) : route('users.activities.all') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="height:36px; font-size:12px;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- TABEL RIWAYAT AKTIVITAS -->
<div class="table-card">
    <div class="table-topbar">
        <h2 class="table-section-title">Log Aktivitas Terbaru (Terurut Waktu)</h2>

        <div class="table-controls">
            <span class="text-muted" style="font-size:11.5px">
                Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log aktivitas
            </span>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No.</th>
                    <th style="width: 140px;">Waktu Kejadian</th>
                    <th style="width: 160px;">Pengguna</th>
                    <th style="width: 130px;">Modul</th>
                    <th style="width: 110px; text-align: center;">Jenis Tindakan</th>
                    <th>Deskripsi &amp; Detail Perubahan</th>
                    <th style="width: 130px; text-align: right;">IP / Info</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                    @php
                        $mod = strtolower($log->module);
                        $modClass = 'mod-auth';
                        $modIcon = 'bi-folder';
                        if (str_contains($mod, 'daily')) { $modClass = 'mod-daily-work'; $modIcon = 'bi-calendar2-week'; }
                        elseif (str_contains($mod, 'plan')) { $modClass = 'mod-hsse-plan'; $modIcon = 'bi-bullseye'; }
                        elseif (str_contains($mod, 'event')) { $modClass = 'mod-events'; $modIcon = 'bi-calendar-event'; }
                        elseif (str_contains($mod, 'gas')) { $modClass = 'mod-gas'; $modIcon = 'bi-broadcast-pin'; }
                        elseif (str_contains($mod, 'inventory')) { $modClass = 'mod-inventory'; $modIcon = 'bi-box-seam'; }
                        elseif (str_contains($mod, 'incident')) { $modClass = 'mod-incident'; $modIcon = 'bi-exclamation-triangle'; }
                        elseif (str_contains($mod, 'user')) { $modClass = 'mod-users'; $modIcon = 'bi-people'; }
                        elseif (str_contains($mod, 'auth')) { $modClass = 'mod-auth'; $modIcon = 'bi-shield-lock'; }

                        $act = strtoupper($log->action);
                        $actClass = 'act-catatan';
                        if (str_contains($act, 'TAMBAH') || str_contains($act, 'CREATE')) $actClass = 'act-tambah';
                        elseif (str_contains($act, 'UBAH') || str_contains($act, 'UPDATE')) $actClass = 'act-ubah';
                        elseif (str_contains($act, 'HAPUS') || str_contains($act, 'DELETE')) $actClass = 'act-hapus';
                        elseif (str_contains($act, 'UPLOAD')) $actClass = 'act-upload';
                        elseif (str_contains($act, 'LOGIN')) $actClass = 'act-login';
                        elseif (str_contains($act, 'LOGOUT')) $actClass = 'act-logout';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $logs->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark" style="font-size: 11.5px;">
                                {{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }}
                            </div>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-circle" style="width:26px; height:26px; font-size:11px;">
                                    {{ strtoupper(substr($log->user_name ?? ($log->user?->name ?? 'S'), 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size:12px;">{{ $log->user_name ?? ($log->user?->name ?? 'System') }}</div>
                                    <small class="text-muted" style="font-size:10px;">{{ $log->user?->role ?? ($log->user_id ? 'User' : 'System') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="activity-badge-module {{ $modClass }}">
                                <i class="bi {{ $modIcon }}"></i> {{ $log->module }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="activity-badge-act {{ $actClass }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <div class="text-dark fw-medium" style="font-size: 12px; line-height: 1.4;">
                                {{ $log->description }}
                            </div>
                            @if(!empty($log->changes) && is_array($log->changes))
                                <div class="diff-badge-wrap">
                                    @if(isset($log->changes['before']) && is_array($log->changes['before']))
                                        <div class="diff-pill text-danger">
                                            <i class="bi bi-dash-circle"></i>
                                            Sebelum:
                                            @foreach($log->changes['before'] as $k => $v)
                                                <span>{{ $k }}: <strong>{{ is_scalar($v) ? $v : json_encode($v) }}</strong></span>{{ !$loop->last ? ',' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(isset($log->changes['after']) && is_array($log->changes['after']))
                                        <div class="diff-pill text-success">
                                            <i class="bi bi-plus-circle"></i>
                                            Sesudah:
                                            @foreach($log->changes['after'] as $k => $v)
                                                <span>{{ $k }}: <strong>{{ is_scalar($v) ? $v : json_encode($v) }}</strong></span>{{ !$loop->last ? ',' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <span class="badge bg-light text-muted border font-monospace" style="font-size:10.5px;">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history d-block mb-2" style="font-size: 32px;"></i>
                            <div class="fw-semibold">Belum ada riwayat aktivitas yang sesuai dengan filter.</div>
                            <div class="small">Ubah filter atau lakukan pencarian lain untuk melihat log aktivitas.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- TABLE BOTTOM BAR -->
    <div class="table-bottom-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div style="font-size: 12px; color: #64748b;">
            @if($logs->total() > 0)
                Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari total {{ $logs->total() }} log
            @else
                Tidak ada data
            @endif
        </div>
        <div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection


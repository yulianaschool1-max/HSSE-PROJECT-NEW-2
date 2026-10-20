@extends('layouts.app', [
    'title' => 'Detail Pengguna',
    'activeModule' => 'users'
])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-heading">Detail Informasi Pengguna</h1>
        <div class="breadcrumb-nav">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="mx-1 text-muted">›</span>
            <a href="{{ route('users.index') }}">Manajemen Pengguna</a>
            <span class="mx-1 text-muted">›</span>
            <span class="text-secondary fw-semibold">{{ $user->name }}</span>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('users.edit', $user->id) }}" class="btn-action-primary">
            <i class="bi bi-pencil me-1"></i> Edit Pengguna
        </a>
        <a href="{{ route('users.index') }}" class="btn-action-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold text-dark" style="font-size: 15px;">
                    <i class="bi bi-person-lines-fill text-teal me-2"></i>Rincian Akun Pengguna
                </h5>
                <div>
                    @if($user->isAdminManagement())
                        <span class="badge bg-purple-subtle text-purple border" style="background: #ede9fe; color: #7c3aed; border-color: #ddd6fe !important;">
                            <i class="bi bi-shield-check"></i> Admin Management
                        </span>
                    @elseif($user->isAdminInventory())
                        <span class="badge bg-warning-subtle text-warning border" style="background: #fef3c7; color: #d97706; border-color: #fde68a !important;">
                            <i class="bi bi-boxes"></i> Admin Inventory
                        </span>
                    @else
                        <span class="badge bg-info-subtle text-info border" style="background: #e0f2fe; color: #0284c7; border-color: #bae6fd !important;">
                            <i class="bi bi-person"></i> User
                        </span>
                    @endif

                    @if($user->isActive())
                        <span class="badge bg-success-subtle text-success border ms-1" style="background: #dcfce7; color: #15803d; border-color: #bbf7d0 !important;">
                            <i class="bi bi-check-circle"></i> Aktif
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border ms-1" style="background: #fee2e2; color: #b91c1c; border-color: #fecaca !important;">
                            <i class="bi bi-dash-circle"></i> Nonaktif
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 pb-4 mb-4 border-bottom">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: {{ $user->isAdminManagement() ? '#ede9fe' : ($user->isAdminInventory() ? '#fef3c7' : '#e2e8f0') }}; color: {{ $user->isAdminManagement() ? '#7c3aed' : ($user->isAdminInventory() ? '#d97706' : '#475569') }}; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
                        <div class="text-secondary">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">ID Pengguna</div>
                            <div class="fw-bold text-dark">#{{ $user->id }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">Alamat Email</div>
                            <div class="fw-bold text-dark">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">Hak Akses (Role)</div>
                            <div class="fw-bold text-dark">{{ $user->role_label }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">Status Akun</div>
                            <div class="fw-bold text-dark">{{ $user->status }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">Tanggal Pendaftaran</div>
                            <div class="fw-bold text-dark">{{ $user->created_at ? $user->created_at->format('d F Y, H:i') : '-' }} WIB</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">Terakhir Diperbarui</div>
                            <div class="fw-bold text-dark">{{ $user->updated_at ? $user->updated_at->format('d F Y, H:i') : '-' }} WIB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


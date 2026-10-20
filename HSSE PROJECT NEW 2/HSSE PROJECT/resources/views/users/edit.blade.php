@extends('layouts.app', [
    'title' => 'Edit Pengguna',
    'activeModule' => 'users'
])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-heading">Edit Data Pengguna</h1>
        <div class="breadcrumb-nav">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="mx-1 text-muted">›</span>
            <a href="{{ route('users.index') }}">Manajemen Pengguna</a>
            <span class="mx-1 text-muted">›</span>
            <span class="text-secondary fw-semibold">Edit ({{ $user->name }})</span>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('users.index') }}" class="btn-action-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-dark" style="font-size: 15px;">
                    <i class="bi bi-pencil-square text-teal me-2"></i>Perbarui Data Pengguna
                </h5>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-semibold">Role Pengguna <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="user" {{ old('role', $user->role) == 'user' || old('role', $user->role) == 'User' ? 'selected' : '' }}>User (Read-Only Operasional)</option>
                                <option value="admin_inventory" {{ old('role', $user->role) == 'admin_inventory' ? 'selected' : '' }}>Admin Inventory (Kelola Inventory)</option>
                                <option value="admin_management" {{ old('role', $user->role) == 'admin_management' || old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin Management (Hak Akses Penuh)</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Akun <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="Aktif" {{ old('status', $user->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Nonaktif" {{ old('status', $user->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-light border py-2 px-3 mb-3 text-muted" style="font-size: 12px;">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Kosongkan password di bawah ini jika tidak ingin mengubah password akun.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Password Baru (Opsional)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter jika diisi">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">Batal</a>
                        <button type="submit" class="btn-action-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Perbarui Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


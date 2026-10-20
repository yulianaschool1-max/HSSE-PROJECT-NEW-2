@extends('layouts.app', [
    'title' => 'Tambah Temuan HSSE - Issue & Follow Up',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .ifu-form-page {
        color: #1e293b;
        padding-bottom: 36px;
    }

    .ifu-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .ifu-title-group {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ifu-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 6px 16px rgba(4, 120, 87, 0.22);
    }

    .ifu-page-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 2px 0;
    }

    .ifu-breadcrumb {
        font-size: 12.5px;
        color: #64748b;
        margin: 0;
    }

    .ifu-breadcrumb a {
        color: #047857;
        text-decoration: none;
        font-weight: 600;
    }

    .ifu-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }

    .ifu-form-section-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        padding-bottom: 10px;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ifu-form-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .ifu-control {
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 9px 12px;
        font-size: 13px;
        color: #1e293b;
    }

    .ifu-control:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
    }

    .btn-ifu-primary {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-ifu-primary:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    }

    .btn-ifu-secondary {
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-ifu-secondary:hover {
        background: #f8fafc;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="ifu-form-page">
    <div class="ifu-header">
        <div class="ifu-title-group">
            <div class="ifu-icon-box">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div>
                <h1 class="ifu-page-title">Tambah Temuan HSSE Baru</h1>
                <p class="ifu-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="mx-1">&rsaquo;</span>
                    <span>Data</span>
                    <span class="mx-1">&rsaquo;</span>
                    <a href="{{ route('issue-follow-up.index') }}">Issue &amp; Follow Up</a>
                    <span class="mx-1">&rsaquo;</span>
                    <span class="fw-semibold text-dark">Tambah Temuan</span>
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('issue-follow-up.index') }}" class="btn-ifu-secondary">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Periksa kembali isian formulir:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ifu-form-card">
        <form action="{{ route('issue-follow-up.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="ifu-form-section-title">
                <i class="fa-solid fa-clipboard-list text-success"></i> Informasi Utama Temuan
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Tanggal Temuan <span class="text-danger">*</span></label>
                    <input type="date" name="reported_date" class="form-control ifu-control @error('reported_date') is-invalid @enderror" value="{{ old('reported_date', date('Y-m-d')) }}" required>
                    @error('reported_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Divisi HSSE (H / S / E) <span class="text-danger">*</span></label>
                    <select name="hsse_division" class="form-select ifu-control @error('hsse_division') is-invalid @enderror" required>
                        <option value="">-- Pilih Divisi HSSE --</option>
                        @foreach(\App\Models\IssueFollowUp::HSSE_DIVISIONS as $divCode => $divLabel)
                            <option value="{{ $divCode }}" @selected(old('hsse_division') === $divCode)>{{ $divLabel }}</option>
                        @endforeach
                    </select>
                    @error('hsse_division')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Unit / Area <span class="text-danger">*</span></label>
                    <select name="unit" class="form-select ifu-control @error('unit') is-invalid @enderror" required>
                        @foreach($units as $u)
                            <option value="{{ $u }}" @selected(old('unit', 'PMU') === $u)>{{ $u }}</option>
                        @endforeach
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Kategori Temuan <span class="text-danger">*</span></label>
                    <select name="category" class="form-select ifu-control @error('category') is-invalid @enderror" required>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" @selected(old('category') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label ifu-form-label">Judul / Nama Temuan <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control ifu-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Kebocoran oli ringan pada pompa utilitas area Wellpad" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Lokasi Spesifik</label>
                    <input type="text" name="location" class="form-control ifu-control @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="Contoh: Area Cooling Tower Unit 1">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label ifu-form-label">Deskripsi Temuan <span class="text-danger">*</span></label>
                    <textarea name="description" rows="3" class="form-control ifu-control @error('description') is-invalid @enderror" placeholder="Uraikan kondisi tidak aman, tindakan tidak aman, atau temuan inspeksi secara jelas..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="ifu-form-section-title">
                <i class="fa-solid fa-user-check text-primary"></i> Penanggung Jawab &amp; Rencana Tindak Lanjut
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label ifu-form-label">Rencana Tindak Lanjut (Action Plan)</label>
                    <textarea name="action_plan" rows="2" class="form-control ifu-control @error('action_plan') is-invalid @enderror" placeholder="Langkah perbaikan / mitigasi yang harus dilaksanakan...">{{ old('action_plan') }}</textarea>
                    @error('action_plan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Penanggung Jawab / PIC <span class="text-danger">*</span></label>
                    <input type="text" name="pic" class="form-control ifu-control @error('pic') is-invalid @enderror" value="{{ old('pic') }}" placeholder="Nama PIC / Tim Penanggung Jawab" required>
                    @error('pic')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Tenggat Follow Up (Due Date) <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control ifu-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label ifu-form-label">Prioritas</label>
                    <select name="priority" class="form-select ifu-control @error('priority') is-invalid @enderror">
                        @foreach($priorities as $prio)
                            <option value="{{ $prio }}" @selected(old('priority', 'Sedang') === $prio)>{{ $prio }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label ifu-form-label">Status Awal <span class="text-danger">*</span></label>
                    <select name="status" class="form-select ifu-control @error('status') is-invalid @enderror" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" @selected(old('status', 'Open') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label ifu-form-label">Catatan Tindak Lanjut Awal (Opsional)</label>
                    <input type="text" name="notes" class="form-control ifu-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}" placeholder="Catatan progres awal bila sudah mulai ditindaklanjuti">
                </div>

                <div class="col-md-6">
                    <label class="form-label ifu-form-label">Bukti / Foto Temuan (Opsional)</label>
                    <input type="file" name="attachment" class="form-control ifu-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                    @error('attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('issue-follow-up.index') }}" class="btn-ifu-secondary">Batal</a>
                <button type="submit" class="btn-ifu-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Temuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


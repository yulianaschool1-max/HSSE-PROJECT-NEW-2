@extends('layouts.app', [
    'title' => 'Detail Temuan ' . $issue->issue_code . ' - Issue & Follow Up',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .ifu-detail-page {
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
        font-size: 21px;
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

    .ifu-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }

    .ifu-card-title {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 16px 0;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .ifu-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .ifu-badge.status-open {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .ifu-badge.status-in-progress {
        background: #dbeafe;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .ifu-badge.status-closed {
        background: #d1fae5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .ifu-badge.status-overdue {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .ifu-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    @media (max-width: 768px) {
        .ifu-meta-grid {
            grid-template-columns: 1fr;
        }
    }

    .ifu-meta-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
    }

    .ifu-meta-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .ifu-meta-val {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .ifu-timeline {
        position: relative;
        padding-left: 24px;
    }

    .ifu-timeline::before {
        content: "";
        position: absolute;
        left: 7px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #cbd5e1;
    }

    .ifu-timeline-item {
        position: relative;
        margin-bottom: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 13px 16px;
    }

    .ifu-timeline-item::before {
        content: "";
        position: absolute;
        left: -21px;
        top: 15px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #059669;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #059669;
    }

    .btn-ifu {
        border-radius: 10px;
        padding: 8px 15px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .btn-ifu-primary {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
    }

    .btn-ifu-primary:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    }

    .btn-ifu-outline {
        background: #ffffff;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-ifu-outline:hover {
        background: #f8fafc;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="ifu-detail-page">
    <div class="ifu-header">
        <div class="ifu-title-group">
            <div class="ifu-icon-box">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
            <div>
                <h1 class="ifu-page-title">Detail Temuan: {{ $issue->issue_code }}</h1>
                <p class="ifu-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span class="mx-1">&rsaquo;</span>
                    <span>Data</span>
                    <span class="mx-1">&rsaquo;</span>
                    <a href="{{ route('issue-follow-up.index') }}">Issue &amp; Follow Up</a>
                    <span class="mx-1">&rsaquo;</span>
                    <span class="fw-semibold text-dark">{{ $issue->issue_code }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('issue-follow-up.index') }}" class="btn-ifu btn-ifu-outline">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('issue-follow-up.edit', $issue->id) }}" class="btn-ifu btn-ifu-outline">
                <i class="fa-solid fa-pen"></i> Edit Temuan
            </a>
            <form action="{{ route('issue-follow-up.destroy', $issue->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus temuan {{ $issue->issue_code }} beserta seluruh riwayat tindak lanjutnya?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ifu btn-danger text-white">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- KOLOM KIRI: INFORMASI LENGKAP & RIWAYAT TINDAK LANJUT --}}
        <div class="col-12 col-xl-8">
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <div>
                        <span class="text-muted small d-block fw-normal">Judul Temuan</span>
                        <span class="fs-5">{{ $issue->title }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ifu-badge {{ $issue->status_badge_class }}">
                            {{ $issue->status }}
                        </span>
                        @if($issue->isOverdue())
                            <span class="ifu-badge status-overdue">
                                <i class="fa-solid fa-fire-flame-curved"></i> Overdue
                            </span>
                        @endif
                    </div>
                </div>

                <div class="ifu-meta-grid">
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Kode &amp; Divisi HSSE</div>
                        <div class="ifu-meta-val d-flex align-items-center gap-2">
                            <span>{{ $issue->issue_code }}</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $issue->division_label }}</span>
                        </div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Tanggal Temuan</div>
                        <div class="ifu-meta-val">{{ $issue->reported_date ? $issue->reported_date->format('d F Y') : '-' }}</div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Tenggat Follow Up (Due Date)</div>
                        <div class="ifu-meta-val {{ $issue->isOverdue() ? 'text-danger' : '' }}">
                            {{ $issue->due_date ? $issue->due_date->format('d F Y') : '-' }}
                            @if($issue->isOverdue())
                                <span class="badge bg-danger ms-1">Terlambat</span>
                            @endif
                        </div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Unit / Area &amp; Lokasi</div>
                        <div class="ifu-meta-val">{{ $issue->unit }} {{ $issue->location ? '— ' . $issue->location : '' }}</div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Kategori Temuan</div>
                        <div class="ifu-meta-val">{{ $issue->category }}</div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Penanggung Jawab (PIC) &amp; Prioritas</div>
                        <div class="ifu-meta-val">{{ $issue->pic }} ({{ $issue->priority ?: 'Sedang' }})</div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="ifu-meta-label mb-1">Deskripsi Temuan</div>
                    <div class="p-3 rounded-3 bg-light border text-dark" style="font-size: 13.5px; line-height: 1.6;">
                        {{ $issue->description }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="ifu-meta-label mb-1">Rencana Tindak Lanjut (Action Plan)</div>
                    <div class="p-3 rounded-3 bg-light border text-dark" style="font-size: 13.5px; line-height: 1.6;">
                        {{ $issue->action_plan ?: 'Belum ada rincian rencana tindak lanjut.' }}
                    </div>
                </div>

                @if($issue->attachment_path)
                    <div class="mb-3">
                        <div class="ifu-meta-label mb-1">Bukti / Dokumentasi Temuan</div>
                        <a href="{{ asset('storage/' . $issue->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-paperclip me-1"></i> Lihat Lampiran Dokumentasi
                        </a>
                    </div>
                @endif

                <div class="d-flex flex-wrap justify-content-between text-muted small pt-3 border-top">
                    <span>Dibuat pada: <strong>{{ $issue->created_at ? $issue->created_at->format('d/m/Y H:i') : '-' }}</strong></span>
                    <span>Diperbarui terakhir: <strong>{{ $issue->updated_at ? $issue->updated_at->format('d/m/Y H:i') : '-' }}</strong></span>
                    @if($issue->status === 'Closed' && $issue->closed_date)
                        <span class="text-success fw-semibold">
                            Ditutup pada: {{ $issue->closed_date->format('d/m/Y') }}
                            {{ $issue->closed_by ? 'oleh ' . $issue->closed_by : '' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- RIWAYAT TINDAK LANJUT SECARA KRONOLOGIS --}}
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <span><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Riwayat Tindak Lanjut &amp; Perubahan Status (Kronologis)</span>
                    <span class="badge bg-secondary rounded-pill">{{ $issue->chronologicalLogs->count() }} Catatan</span>
                </div>

                @if($issue->chronologicalLogs->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        Belum ada catatan tindak lanjut untuk temuan ini.
                    </div>
                @else
                    <div class="ifu-timeline">
                        @foreach($issue->chronologicalLogs as $log)
                            <div class="ifu-timeline-item">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">
                                        <i class="fa-solid fa-user-shield text-success me-1"></i>
                                        {{ $log->followed_up_by ?: 'Petugas HSSE' }}
                                    </span>
                                    <span class="text-muted" style="font-size: 11.5px;">
                                        {{ $log->followed_up_at ? $log->followed_up_at->format('d/m/Y H:i') : ($log->created_at ? $log->created_at->format('d/m/Y H:i') : '-') }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    @if($log->previous_status && $log->previous_status !== $log->new_status)
                                        <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                            Status: {{ $log->previous_status }} &rarr; <strong>{{ $log->new_status }}</strong>
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                            Status: <strong>{{ $log->new_status ?: $issue->status }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-dark" style="font-size: 13px; line-height: 1.5;">
                                    {{ $log->note }}
                                </div>
                                @if($log->attachment_path)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/' . $log->attachment_path) }}" target="_blank" class="small text-success fw-semibold text-decoration-none">
                                            <i class="fa-solid fa-paperclip me-1"></i>Lihat Bukti Tindak Lanjut
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: FORM TAMBAH TINDAK LANJUT & UBAH STATUS --}}
        <div class="col-12 col-xl-4">
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <span><i class="fa-solid fa-list-check text-primary me-2"></i>Tambah Tindak Lanjut</span>
                </div>
                <form action="{{ route('issue-follow-up.follow-up', $issue->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status Tindak Lanjut</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="In Progress" @selected($issue->status !== 'Closed')>In Progress (Sedang Ditindaklanjuti)</option>
                            <option value="Closed" @selected($issue->status === 'Closed')>Closed (Selesai &amp; Ditutup)</option>
                            <option value="Open">Open</option>
                        </select>
                        <div class="form-text small">Jika temuan berstatus Open dan mulai ditindaklanjuti, status otomatis beralih ke In Progress.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Catatan Tindak Lanjut <span class="text-danger">*</span></label>
                        <textarea name="note" rows="4" class="form-control form-control-sm" placeholder="Tuliskan progres perbaikan atau tindakan penyelesaian yang telah dilakukan..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Bukti Penyelesaian (Opsional)</label>
                        <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                    </div>

                    <button type="submit" class="btn-ifu btn-ifu-primary w-100 justify-content-center">
                        <i class="fa-solid fa-paper-plane"></i> Simpan Tindak Lanjut
                    </button>
                </form>
            </div>

            @if($issue->status !== 'Closed')
                <div class="ifu-card border-success">
                    <div class="ifu-card-title text-success">
                        <span><i class="fa-solid fa-circle-check me-2"></i>Tutup Temuan (Mark as Closed)</span>
                    </div>
                    <form action="{{ route('issue-follow-up.close', $issue->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Penutupan</label>
                            <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Catatan verifikasi penutupan temuan...">Temuan telah diverifikasi dan diselesaikan (Closed).</textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="fa-solid fa-check-double me-1"></i> Tandai Selesai (Closed)
                        </button>
                    </form>
                </div>
            @else
                <div class="ifu-card border-warning">
                    <div class="ifu-card-title text-warning">
                        <span><i class="fa-solid fa-rotate-left me-2"></i>Buka Kembali Temuan (Reopen)</span>
                    </div>
                    <form action="{{ route('issue-follow-up.reopen', $issue->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Status Tujuan</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="In Progress">In Progress</option>
                                <option value="Open">Open</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Alasan Membuka Kembali <span class="text-danger">*</span></label>
                            <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Jelaskan alasan temuan dibuka kembali..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                            <i class="fa-solid fa-rotate-left me-1"></i> Buka Kembali Temuan
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


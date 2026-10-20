@extends('layouts.app', ['activeModule' => 'workforce', 'title' => 'Detail Manpower - ' . $workforce->employee_code])

@section('content')
<div class="hp-header-card">
    <div class="hp-header-left">
        <div class="hp-header-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="hp-header-text">
            <h1>Detail Manpower — {{ $workforce->employee_code }}</h1>
            <p class="hp-subtitle">{{ $workforce->category }} • {{ $workforce->unit }}</p>
            <div class="hp-breadcrumb">
                <a href="{{ route('dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
                <span class="sep">›</span>
                <span>Data</span>
                <span class="sep">›</span>
                <a href="{{ route('workforce.index') }}" style="color:inherit;text-decoration:none;">Manpower</a>
                <span class="sep">›</span>
                <span class="active">Detail</span>
            </div>
        </div>
    </div>
    <div class="hp-header-actions">
        <a href="{{ route('workforce.index') }}" class="hp-btn hp-btn-outline">
            <i class="fas fa-arrow-left"></i> Kembali ke Manpower
        </a>
        <a href="{{ route('man-hours.index', ['search' => $workforce->employee_code]) }}" class="hp-btn hp-btn-success">
            <i class="fas fa-business-time"></i> Lihat di Man Hours
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;margin-bottom:20px;">
    <div class="hp-table-card" style="padding:20px;">
        <h3 style="margin:0 0 14px 0;font-size:15px;font-weight:700;color:#0f172a;">
            <i class="fas fa-id-card" style="color:#059669;margin-right:6px;"></i> Informasi Data Manpower
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Kode Rekaman</div>
                <div style="font-weight:700;color:#0f172a;margin-top:2px;">{{ $workforce->employee_code }}</div>
            </div>
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Tanggal Pencatatan</div>
                <div style="font-weight:700;color:#0f172a;margin-top:2px;">{{ $workforce->record_date ? $workforce->record_date->format('d/m/Y') : '-' }}</div>
            </div>
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Kategori / Komponen</div>
                <div style="font-weight:700;color:#059669;margin-top:2px;">{{ $workforce->category }}</div>
            </div>
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Unit / Area</div>
                <div style="font-weight:700;color:#0f172a;margin-top:2px;">{{ $workforce->unit }}</div>
            </div>
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Sub-Unit / Perusahaan</div>
                <div style="font-weight:600;color:#334155;margin-top:2px;">{{ $workforce->department ?: ($workforce->name ?: '-') }}</div>
            </div>
            <div>
                <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Status</div>
                <div style="font-weight:700;color:#059669;margin-top:2px;">{{ $workforce->status }}</div>
            </div>
        </div>
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #e2e8f0;font-size:13px;">
            <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Catatan</div>
            <div style="color:#334155;margin-top:4px;">{{ $workforce->notes ?: 'Tidak ada catatan tambahan.' }}</div>
        </div>
    </div>

    <div class="hp-table-card" style="padding:20px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border:1px solid #a7f3d0;">
        <h3 style="margin:0 0 14px 0;font-size:15px;font-weight:700;color:#065f46;">
            <i class="fas fa-calculator" style="color:#059669;margin-right:6px;"></i> Rincian Perhitungan Man Hours Otomatis
        </h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px;">
            <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #d1fae5;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Jumlah Pekerja</div>
                <div style="font-size:20px;font-weight:800;color:#0f172a;margin-top:4px;">{{ number_format((int) $workforce->worker_count) }} <span style="font-size:12px;font-weight:600;color:#64748b;">Orang</span></div>
            </div>
            <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #d1fae5;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Jam Kerja / Orang</div>
                <div style="font-size:20px;font-weight:800;color:#2563eb;margin-top:4px;">{{ number_format((float) $workforce->hours_per_worker, 2) }} <span style="font-size:12px;font-weight:600;color:#64748b;">Jam</span></div>
            </div>
            <div style="background:#fff;padding:12px;border-radius:10px;border:1px solid #d1fae5;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Total Man Hours</div>
                <div style="font-size:20px;font-weight:800;color:#059669;margin-top:4px;">{{ number_format((float) $workforce->calculated_man_hours, 2) }} <span style="font-size:12px;font-weight:600;color:#64748b;">Jam</span></div>
            </div>
        </div>
        <div style="background:#fff;padding:12px 14px;border-radius:10px;border:1px dashed #34d399;font-size:13px;color:#065f46;">
            <strong>Rumus Perhitungan:</strong>
            {{ number_format((int) $workforce->worker_count) }} Pekerja × {{ number_format((float) $workforce->hours_per_worker, 2) }} Jam/Orang = <strong>{{ number_format((float) $workforce->calculated_man_hours, 2) }} Jam Man Hours</strong>
        </div>
    </div>
</div>
@endsection


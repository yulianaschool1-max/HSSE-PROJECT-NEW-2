@extends('layouts.app', [
    'title' => 'Detail Perangkat - ' . ($device->device_code ?? 'Gas Monitoring'),
    'activeModule' => 'gas-detector'
])

@section('content')
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box teal">
            <i class="bi bi-broadcast-pin"></i>
        </div>
        <div>
            <h1 class="page-heading">Detail Perangkat Gas Monitoring</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">Data</span>
                <span class="mx-1 text-muted">›</span>
                <a href="{{ route('gas-detector.index') }}">Gas Monitoring</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">{{ $device->device_code }}</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('gas-detector.index') }}" class="btn-action-outline">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Kode Alat</div>
                <div class="fw-bold">{{ $device->device_code }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Model / Merk</div>
                <div class="fw-semibold">{{ $device->model_name }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Nomor Seri</div>
                <div>{{ $device->serial_number ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Tipe Gas / Sensor</div>
                <div>{{ $device->gas_type ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Unit Kerja</div>
                <div class="fw-semibold">{{ $device->unit }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Lokasi Penyimpanan</div>
                <div>{{ $device->location ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <div><span class="badge-status {{ $device->status_badge_class }}">{{ $device->status }}</span></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Kalibrasi Terakhir</div>
                <div>{{ $device->last_calibration_date ? $device->last_calibration_date->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Kalibrasi Berikutnya</div>
                <div>{{ $device->next_calibration_date ? $device->next_calibration_date->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Penanggung Jawab</div>
                <div>{{ $device->user_in_charge ?: '-' }}</div>
            </div>
            <div class="col-md-8">
                <div class="text-muted small">Catatan</div>
                <div>{{ $device->notes ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app', [
    'title' => 'Detail KPI - ' . ($kpi->title ?? 'HSSE Plan'),
    'activeModule' => 'hsse-plan'
])

@section('content')
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-bullseye"></i>
        </div>
        <div>
            <h1 class="page-heading">Detail KPI Kontraktor</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <a href="{{ route('hsse-plan.index') }}">HSSE Plan</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">{{ $kpi->title }}</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('hsse-plan.index') }}" class="btn btn-action-outline btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke HSSE Plan
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Detail Card -->
    <div class="col-lg-7">
        <div class="chart-box-card h-100">
            <div class="chart-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="chart-title mb-1">{{ $kpi->title }}</h2>
                    <p class="chart-subtitle mb-0">Informasi lengkap indikator kinerja HSSE</p>
                </div>
                <div>
                    <span class="badge-status {{ $kpi->status === 'Tercapai' ? 'status-achieved' : ($kpi->status === 'Berlangsung' ? 'status-progress' : 'status-not') }}">
                        {{ $kpi->status }}
                    </span>
                    <span class="badge ms-1 {{ $kpi->category === 'Leading' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-purple-subtle text-purple border' }}" style="font-size:11px">
                        {{ $kpi->category }} Indicator
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" style="font-size: 12px;">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted" style="width: 35%;">Nama KPI</th>
                            <td class="fw-semibold text-dark">{{ $kpi->title }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Jenis Indikator</th>
                            <td>
                                <span class="fw-semibold {{ $kpi->category === 'Leading' ? 'text-primary' : 'text-purple' }}">
                                    {{ $kpi->category }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Unit / Lokasi</th>
                            <td>{{ $kpi->unit ?? 'Dieng Unit 1' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Target</th>
                            <td><strong class="text-primary">{{ $kpi->target_label }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Realisasi Aktual</th>
                            <td><strong class="text-success">{{ number_format($kpi->realization, 2, ',', '.') }} {{ $kpi->unit_measure }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Persentase Pemenuhan</th>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar {{ $kpi->percentage >= 100 ? 'bg-success' : ($kpi->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width: {{ $kpi->percentage }}%"></div>
                                    </div>
                                    <span class="fw-bold" style="min-width: 40px; text-align: right;">{{ $kpi->percentage }}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Periode</th>
                            <td>Tahun {{ $kpi->year }} ({{ $kpi->start_date ? $kpi->start_date->format('d M Y') : '-' }} s/d {{ $kpi->end_date ? $kpi->end_date->format('d M Y') : '-' }})</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted">Keterangan</th>
                            <td>{{ $kpi->description ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes Card -->
    <div class="col-lg-5">
        <div class="chart-box-card h-100">
            <div class="chart-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="chart-title mb-1">Catatan & Tindak Lanjut</h2>
                    <p class="chart-subtitle mb-0">Total {{ $kpi->notes->count() }} catatan evaluasi</p>
                </div>
            </div>

            <div class="notes-container" style="max-height: 380px; overflow-y: auto;">
                @forelse($kpi->notes as $note)
                    <div class="note-item-box p-3 mb-2 bg-light border-start border-4 border-success rounded">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-dark" style="font-size: 11.5px;">
                                <i class="bi bi-person-circle me-1 text-muted"></i>{{ $note->author ?? 'Admin HSSE' }}
                            </span>
                            <span class="text-muted" style="font-size: 10px;">
                                {{ $note->created_at ? $note->created_at->format('d M Y, H:i') : '-' }}
                            </span>
                        </div>
                        <p class="mb-0 text-secondary" style="font-size: 11.5px; line-height: 1.4;">
                            {{ $note->note }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-left-dots fs-3 d-block mb-2 text-secondary opacity-50"></i>
                        Belum ada catatan untuk KPI ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection


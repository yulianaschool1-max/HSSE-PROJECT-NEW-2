@extends('layouts.app', [
    'title' => 'Detail Event - ' . $event->title,
    'activeModule' => 'events'
])

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-info-circle text-primary me-2"></i>Detail Kegiatan</h4>
        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Events
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-1 text-muted">Judul Kegiatan</p>
                    <h5 class="fw-bold">{{ $event->title }}</h5>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted">Kategori</p>
                    <span class="badge bg-primary">{{ $event->category }}</span>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted">Status</p>
                    <span class="badge bg-success">{{ $event->status }}</span>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">Unit / Komponen</p>
                    <p class="fw-semibold">{{ $event->unit }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">Lokasi</p>
                    <p class="fw-semibold">{{ $event->location }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">Tanggal Kegiatan</p>
                    <p class="fw-semibold">{{ $event->event_date ? $event->event_date->format('d F Y') : '-' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">Jumlah Peserta</p>
                    <p class="fw-semibold">{{ $event->participants }} orang</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-muted">Penanggung Jawab (PIC)</p>
                    <p class="fw-semibold">{{ $event->pic ?: '-' }}</p>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-muted">Deskripsi</p>
                    <p>{{ $event->description ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


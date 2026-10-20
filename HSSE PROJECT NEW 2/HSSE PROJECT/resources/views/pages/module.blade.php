@extends('layouts.app', [
    'title' => $title ?? 'Modul HSSE',
    'activeModule' => $module ?? ''
])

@section('content')
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box blue">
            <i class="bi bi-folder2-open"></i>
        </div>
        <div>
            <h1 class="page-heading">{{ $title ?? 'Modul HSSE' }}</h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @if(in_array($module ?? '', ['man-hours', 'workforce', 'incident', 'issue-follow-up', 'gas-detector']))
                    <span class="mx-1 text-muted">›</span>
                    <span class="text-secondary">Data</span>
                @endif
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">{{ $title ?? 'Modul' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 text-center">
    <div class="py-5">
        <i class="bi bi-cone-striped text-warning" style="font-size: 48px;"></i>
        <h4 class="mt-3 fw-bold">{{ $title ?? 'Modul' }}</h4>
        <p class="text-muted">{{ $subtitle ?? 'Modul ini sedang dalam tahap pengembangan.' }}</p>
    </div>
</div>
@endsection


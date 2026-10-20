@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 22%;">Nama Kegiatan</th>
            <th style="width: 11%;">Kategori</th>
            <th style="width: 9%;">Unit</th>
            <th style="width: 9%;" class="text-center">Tanggal</th>
            <th style="width: 8%;" class="text-center">Waktu</th>
            <th style="width: 11%;">Lokasi</th>
            <th style="width: 10%;">Penyelenggara</th>
            <th style="width: 6%;" class="text-center">Peserta</th>
            <th style="width: 10%;" class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $badgeClass = match($item->status) {
                    'Selesai' => 'badge-success',
                    'Sedang Berlangsung' => 'badge-warning',
                    'Dibatalkan' => 'badge-danger',
                    default => 'badge-secondary',
                };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $item->title }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-center">{{ $item->event_date ? $item->event_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->start_time ? substr($item->start_time, 0, 5) : '-' }} - {{ $item->end_time ? substr($item->end_time, 0, 5) : '-' }}</td>
                <td>{{ $item->location ?? '-' }}</td>
                <td>{{ $item->organizer ?? '-' }}</td>
                <td class="text-center">{{ $item->participants ?? $item->participants_count ?? '-' }}</td>
                <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data kegiatan events yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

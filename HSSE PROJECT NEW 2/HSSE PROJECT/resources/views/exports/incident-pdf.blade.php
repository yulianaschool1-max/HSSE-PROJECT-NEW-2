@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 12%;">No. Laporan</th>
            <th style="width: 11%;" class="text-center">Tanggal/Waktu</th>
            <th style="width: 22%;">Judul Insiden</th>
            <th style="width: 14%;">Unit / Lokasi</th>
            <th style="width: 10%;" class="text-center">Keparahan</th>
            <th style="width: 13%;">Kategori</th>
            <th style="width: 6%;" class="text-center">LWD</th>
            <th style="width: 8%;" class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $sevClass = match($item->severity) {
                    'Fatality', 'Berat' => 'badge-danger',
                    'Sedang' => 'badge-warning',
                    'Ringan', 'First Aid' => 'badge-info',
                    default => 'badge-secondary',
                };
                $statClass = match($item->status_penanganan) {
                    'Selesai', 'Closed' => 'badge-success',
                    'Dalam Investigasi', 'Investigasi' => 'badge-warning',
                    default => 'badge-danger',
                };
                $kat = is_array($item->kategori_umum) ? implode(', ', $item->kategori_umum) : ($item->kategori_umum ?? '-');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $item->nomor_insiden ?? $item->incident_code ?? $item->nomor_laporan ?? '-' }}</td>
                <td class="text-center">{{ $item->tanggal_waktu ? $item->tanggal_waktu->format('d/m/Y H:i') : ($item->incident_date ? $item->incident_date->format('d/m/Y H:i') : '-') }}</td>
                <td class="text-bold">{{ $item->title ?? $item->judul_insiden ?? '-' }}</td>
                <td>{{ $item->unit ?? 'Unit Dieng' }} - {{ $item->lokasi_kejadian ?? $item->location ?? '-' }}</td>
                <td class="text-center"><span class="badge {{ $sevClass }}">{{ $item->severity ?? '-' }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($kat, 30) }}</td>
                <td class="text-center text-bold">{{ $item->jumlah_hari_hilang ?? 0 }}</td>
                <td class="text-center"><span class="badge {{ $statClass }}">{{ $item->status ?? $item->status_penanganan ?? 'Dilaporkan' }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data insiden yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 24%;">Nama Pekerjaan</th>
            <th style="width: 14%;">Perusahaan</th>
            <th style="width: 12%;">PTW Classification</th>
            <th style="width: 12%;">SIKKA</th>
            <th style="width: 9%;" class="text-center">Mulai</th>
            <th style="width: 9%;" class="text-center">Selesai</th>
            <th style="width: 16%;">Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->ptw_classification ?? '-' }}</td>
                <td>{{ $item->sikka ?? '-' }}</td>
                <td class="text-center">{{ $item->start_date ? $item->start_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->end_date ? $item->end_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->location ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data pekerjaan harian yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

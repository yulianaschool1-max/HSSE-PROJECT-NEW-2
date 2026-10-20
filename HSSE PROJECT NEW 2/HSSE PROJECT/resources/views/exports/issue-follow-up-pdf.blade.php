@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 10%;">Kode Isu</th>
            <th style="width: 9%;" class="text-center">Tgl Lapor</th>
            <th style="width: 12%;">Divisi HSSE</th>
            <th style="width: 22%;">Judul Isu & Tindak Lanjut</th>
            <th style="width: 11%;">Unit / Lokasi</th>
            <th style="width: 10%;">PIC</th>
            <th style="width: 7%;" class="text-center">Prioritas</th>
            <th style="width: 8%;" class="text-center">Tenggat</th>
            <th style="width: 7%;" class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $statClass = match(strtolower($item->status)) {
                    'closed', 'close' => 'badge-success',
                    'in progress' => 'badge-warning',
                    default => 'badge-danger',
                };
                $prioClass = match(strtolower($item->priority)) {
                    'kritis', 'tinggi' => 'badge-danger',
                    'sedang' => 'badge-warning',
                    default => 'badge-secondary',
                };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $item->issue_code ?? '-' }}</td>
                <td class="text-center">{{ $item->reported_date ? $item->reported_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->hsse_division_label ?? $item->hsse_division ?? '-' }}</td>
                <td class="text-bold">{{ $item->title }}</td>
                <td>{{ $item->unit }} - {{ $item->location ?? '-' }}</td>
                <td>{{ $item->pic ?? '-' }}</td>
                <td class="text-center"><span class="badge {{ $prioClass }}">{{ $item->priority }}</span></td>
                <td class="text-center">{{ $item->due_date ? $item->due_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center"><span class="badge {{ $statClass }}">{{ $item->status }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data Issue & Follow Up yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

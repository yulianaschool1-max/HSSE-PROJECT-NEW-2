@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 22%;">Program / KPI</th>
            <th style="width: 12%;">Kategori</th>
            <th style="width: 8%;">Unit</th>
            <th style="width: 14%;">Kontraktor</th>
            <th style="width: 6%;" class="text-center">Tahun</th>
            <th style="width: 7%;" class="text-right">Target</th>
            <th style="width: 7%;" class="text-right">Realisasi</th>
            <th style="width: 6%;" class="text-center">%</th>
            <th style="width: 7%;" class="text-center">Status</th>
            <th style="width: 7%;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $targetVal = (float) $item->target;
                $realVal = (float) $item->realization;
                $pct = $targetVal > 0 ? round(($realVal / $targetVal) * 100, 1) : 0;
                $badgeClass = match($item->status) {
                    'Tercapai' => 'badge-success',
                    'Berlangsung' => 'badge-warning',
                    'Tidak Tercapai' => 'badge-danger',
                    default => 'badge-secondary',
                };
                $contractorName = $item->contractor?->name ?? $item->contractor_name ?? 'Belum Ditentukan';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-bold">{{ $item->title }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $contractorName }}</td>
                <td class="text-center">{{ $item->year }}</td>
                <td class="text-right">{{ number_format($item->target) }} {{ $item->unit_measure }}</td>
                <td class="text-right">{{ number_format($item->realization) }} {{ $item->unit_measure }}</td>
                <td class="text-center text-bold">{{ $pct }}%</td>
                <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                <td>{{ $item->description ? \Illuminate\Support\Str::limit($item->description, 40) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data HSSE Plan yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

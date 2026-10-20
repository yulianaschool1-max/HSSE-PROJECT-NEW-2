@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 10%;" class="text-center">Tanggal</th>
            <th style="width: 18%;">Unit / Area Operasi</th>
            <th style="width: 16%;">Kategori</th>
            <th style="width: 12%;" class="text-right">Jumlah Pekerja</th>
            <th style="width: 12%;" class="text-right">Jam / Orang</th>
            <th style="width: 14%;" class="text-right">Total Jam Kerja</th>
            <th style="width: 14%;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sumWorkers = 0;
            $sumHours = 0;
        @endphp
        @forelse($records as $index => $row)
            @php
                $workers = (int) $row->workers_count;
                $hours = (float) $row->total_hours;
                $sumWorkers += $workers;
                $sumHours += $hours;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row->work_date ? $row->work_date->format('d/m/Y') : '-' }}</td>
                <td class="text-bold">{{ $row->unit }}</td>
                <td>{{ $row->category }}</td>
                <td class="text-right">{{ number_format($workers) }} Org</td>
                <td class="text-right">{{ number_format((float) $row->hours_per_worker, 1) }} Jam</td>
                <td class="text-right text-bold" style="color: #087c68;">{{ number_format($hours, 1) }} Jam</td>
                <td>{{ $row->notes ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data Man Hours yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right" style="padding: 6px;">TOTAL AKUMULATIF (FILTERED):</td>
                <td class="text-right" style="padding: 6px;">{{ number_format($sumWorkers) }} Org</td>
                <td class="text-right" style="padding: 6px;">-</td>
                <td class="text-right" style="padding: 6px; color: #087c68;">{{ number_format($sumHours, 1) }} Jam</td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>
@endsection

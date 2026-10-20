@extends('exports.layout')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 13%;" class="text-center">Tanggal Transaksi</th>
            <th style="width: 11%;">Kode Barang</th>
            <th style="width: 22%;">Nama Barang</th>
            <th style="width: 12%;">Kategori</th>
            <th style="width: 10%;" class="text-center">Jenis Transaksi</th>
            <th style="width: 6%;" class="text-right">Jumlah</th>
            <th style="width: 6%;">Satuan</th>
            <th style="width: 8%;">Petugas / PIC</th>
            <th style="width: 8%;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $t)
            @php
                $jenis = $t->transaction_type ?? $t->jenis_transaksi ?? '-';
                $badgeClass = match(strtolower($jenis)) {
                    'masuk' => 'badge-success',
                    'keluar' => 'badge-warning',
                    'pinjam', 'peminjaman' => 'badge-info',
                    'kembali', 'pengembalian' => 'badge-secondary',
                    default => 'badge-secondary',
                };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $t->transaction_date ? $t->transaction_date->format('d/m/Y H:i') : '-' }}</td>
                <td style="font-family: monospace;">{{ $t->item?->kode_barang ?? $t->item?->item_code ?? '-' }}</td>
                <td class="text-bold">{{ $t->item?->nama_barang ?? $t->item?->item_name ?? 'Barang Dihapus' }}</td>
                <td>{{ $t->item?->kategori ?? $t->item?->category ?? '-' }}</td>
                <td class="text-center"><span class="badge {{ $badgeClass }}">{{ ucfirst($jenis) }}</span></td>
                <td class="text-right text-bold">{{ number_format($t->quantity ?? $t->jumlah ?? 0) }}</td>
                <td>{{ $t->item?->satuan ?? $t->item?->unit_measure ?? 'pcs' }}</td>
                <td>{{ $t->person_in_charge ?? $t->petugas ?? '-' }}</td>
                <td>{{ $t->notes ?? $t->keterangan ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada riwayat transaksi barang yang sesuai dengan kriteria filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

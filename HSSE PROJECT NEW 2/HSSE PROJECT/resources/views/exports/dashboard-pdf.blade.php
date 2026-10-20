@extends('exports.layout')

@section('content')
<div style="margin-bottom: 12px;">
    <h3 style="font-size: 11pt; color: #087c68; margin-bottom: 4px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px;">
        1. INDIKATOR KINERJA HSSE (FAKTOR PENGALI 1.000.000)
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Indikator</th>
                <th style="width: 15%;" class="text-right">Nilai Indikator</th>
                <th style="width: 28%;">Formula Perhitungan</th>
                <th style="width: 16%;" class="text-right">Pembilang Aktual</th>
                <th style="width: 16%;" class="text-right">Total Man Hours</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-bold">TRIF (Total Recordable Incident Frequency)</td>
                <td class="text-right text-bold" style="color: #166534; font-size: 10pt;">{{ $hsseMetrics['trif_display'] }}</td>
                <td style="font-family: monospace; font-size: 7.5pt;">(Total Insiden / Total Man Hours) &times; 1.000.000</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_insiden']) }} Insiden</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_man_hours'], 1) }} Jam</td>
            </tr>
            <tr>
                <td class="text-bold">LTI-TF (Lost Time Injury Frequency Rate)</td>
                <td class="text-right text-bold" style="color: #92400e; font-size: 10pt;">{{ $hsseMetrics['lti_tf_display'] }}</td>
                <td style="font-family: monospace; font-size: 7.5pt;">(Total LTI / Total Man Hours) &times; 1.000.000</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_lti']) }} Kasus LTI</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_man_hours'], 1) }} Jam</td>
            </tr>
            <tr>
                <td class="text-bold">LTI-SF (Lost Time Injury Severity Rate)</td>
                <td class="text-right text-bold" style="color: #7c3aed; font-size: 10pt;">{{ $hsseMetrics['lti_sf_display'] }}</td>
                <td style="font-family: monospace; font-size: 7.5pt;">(Total Hari Hilang / Total Man Hours) &times; 1.000.000</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_hari_hilang']) }} Hari Kerja</td>
                <td class="text-right">{{ number_format($hsseMetrics['total_man_hours'], 1) }} Jam</td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-bottom: 12px;">
    <h3 style="font-size: 11pt; color: #087c68; margin-bottom: 4px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px;">
        2. RINGKASAN PROGRESS PROYEK & AKUMULATIF MAN HOURS
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Metrik Utama</th>
                <th style="width: 25%;" class="text-right">Nilai Tercatat</th>
                <th style="width: 45%;">Keterangan & Cakupan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-bold">Total Akumulatif Jam Kerja (Man Hours)</td>
                <td class="text-right text-bold" style="color: #087c68; font-size: 10pt;">{{ number_format($coreCards['man_hours']['total_hours'] ?? $coreCards['man_hours']['total'] ?? 0, 0, ',', '.') }} Jam</td>
                <td>Total akumulasi jam kerja aman seluruh pekerja dari modul Man Hours</td>
            </tr>
            <tr>
                <td class="text-bold">Total Manpower Aktif Terkini</td>
                <td class="text-right text-bold">{{ number_format($coreCards['workforce']['total_workers'] ?? 0, 0, ',', '.') }} Orang</td>
                <td>Pencatatan harian terbaru per {{ $coreCards['workforce']['latest_record_date_display'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-bold">Durasi Waktu Proyek Berjalan</td>
                <td class="text-right text-bold">{{ number_format($projectTimeline['elapsed_days'], 0, ',', '.') }} Hari ({{ $projectTimeline['percentage_display'] }})</td>
                <td>Periode {{ $projectTimeline['start_date_display'] }} s/d {{ $projectTimeline['end_date_display'] }}</td>
            </tr>
            <tr>
                <td class="text-bold">Total Insiden Tercatat</td>
                <td class="text-right text-bold">{{ number_format($hsseMetrics['total_insiden']) }} Kasus</td>
                <td>Insiden dalam periode laporan</td>
            </tr>
            <tr>
                <td class="text-bold">Hari Kerja Hilang (Lost Workdays)</td>
                <td class="text-right text-bold">{{ number_format($hsseMetrics['total_hari_hilang']) }} Hari</td>
                <td>Akumulasi hari kerja hilang akibat insiden</td>
            </tr>
        </tbody>
    </table>
</div>

<div>
    <h3 style="font-size: 11pt; color: #087c68; margin-bottom: 4px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px;">
        3. ISU TINDAK LANJUT TERBARU
    </h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 12%;" class="text-center">Tgl Lapor</th>
                <th style="width: 32%;">Judul Isu</th>
                <th style="width: 18%;">Divisi / Kategori</th>
                <th style="width: 14%;">Penanggung Jawab</th>
                <th style="width: 10%;" class="text-center">Tenggat</th>
                <th style="width: 10%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestIssues as $idx => $iss)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $iss->reported_date ? $iss->reported_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-bold">{{ $iss->title }}</td>
                    <td>{{ $iss->hsse_division_label ?? $iss->category ?? '-' }}</td>
                    <td>{{ $iss->pic ?? '-' }}</td>
                    <td class="text-center">{{ $iss->due_date ? $iss->due_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ strtolower($iss->status) === 'closed' ? 'badge-success' : 'badge-danger' }}">
                            {{ $iss->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #94a3b8;">Tidak ada data isu tindak lanjut terbaru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

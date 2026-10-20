@extends('exports.layout')

@section('content')
<style>
    .summary-box {
        margin-bottom: 12px;
        width: 100%;
        border-collapse: collapse;
    }
    .summary-box td {
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        font-size: 8pt;
    }
    .summary-title {
        background-color: #f1f5f9;
        font-weight: bold;
        color: #334155;
    }
    .val-cell {
        font-weight: bold;
        font-size: 9pt;
        text-align: center;
    }
    .chart-visual-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
        background: #ffffff;
    }
    .chart-visual-table th {
        background-color: #0f2d42;
        color: #ffffff;
        font-size: 7.5pt;
        padding: 5px;
        text-align: center;
    }
    .chart-visual-table td {
        border: 1px solid #cbd5e1;
        padding: 6px 5px;
        font-size: 8pt;
        text-align: center;
    }
    .bar-container {
        width: 100%;
        background-color: #f1f5f9;
        height: 12px;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 3px;
    }
    .bar-fill {
        height: 100%;
    }
</style>

<!-- Ringkasan Parameter & Ambang Batas (NAB) -->
<table class="summary-box">
    <tr class="summary-title">
        <td style="width: 20%;">Parameter</td>
        <td style="width: 20%;">Threshold / NAB</td>
        <td style="width: 20%;" class="text-center">Rata-rata Terukur</td>
        <td style="width: 20%;" class="text-center">Nilai Maksimum</td>
        <td style="width: 20%;" class="text-center">Status Pemantauan</td>
    </tr>
    <tr>
        <td><strong>Karbon Monoksida (CO)</strong></td>
        <td>&le; 25 ppm</td>
        <td class="val-cell">{{ $coSummary['avg'] !== null ? number_format($coSummary['avg'], 1) . ' ppm' : '-' }}</td>
        <td class="val-cell {{ $coSummary['max'] > 25 ? 'text-danger' : '' }}">{{ $coSummary['max'] !== null ? number_format($coSummary['max'], 1) . ' ppm' : '-' }}</td>
        <td class="text-center">
            <span class="badge {{ $coSummary['non_compliant'] == 0 && $coSummary['count'] > 0 ? 'badge-success' : ($coSummary['count'] > 0 ? 'badge-danger' : 'badge-secondary') }}">
                {{ $coSummary['non_compliant'] == 0 && $coSummary['count'] > 0 ? 'Sesuai' : ($coSummary['count'] > 0 ? 'Tidak Sesuai' : 'Belum Ada Data') }}
            </span>
        </td>
    </tr>
    <tr>
        <td><strong>Oksigen (O₂)</strong></td>
        <td>19.5% – 23.5%</td>
        <td class="val-cell">{{ $o2Summary['avg'] !== null ? number_format($o2Summary['avg'], 1) . ' %' : '-' }}</td>
        <td class="val-cell {{ ($o2Summary['min'] < 19.5 || $o2Summary['max'] > 23.5) && $o2Summary['count'] > 0 ? 'text-danger' : '' }}">
            {{ $o2Summary['count'] > 0 ? number_format($o2Summary['min'], 1) . ' - ' . number_format($o2Summary['max'], 1) . ' %' : '-' }}
        </td>
        <td class="text-center">
            <span class="badge {{ $o2Summary['non_compliant'] == 0 && $o2Summary['count'] > 0 ? 'badge-success' : ($o2Summary['count'] > 0 ? 'badge-danger' : 'badge-secondary') }}">
                {{ $o2Summary['non_compliant'] == 0 && $o2Summary['count'] > 0 ? 'Sesuai' : ($o2Summary['count'] > 0 ? 'Tidak Sesuai' : 'Belum Ada Data') }}
            </span>
        </td>
    </tr>
    <tr>
        <td><strong>Hidrogen Sulfida (H₂S)</strong></td>
        <td>&le; 10 ppm</td>
        <td class="val-cell">{{ $h2sSummary['avg'] !== null ? number_format($h2sSummary['avg'], 1) . ' ppm' : '-' }}</td>
        <td class="val-cell {{ $h2sSummary['max'] > 10 ? 'text-danger' : '' }}">{{ $h2sSummary['max'] !== null ? number_format($h2sSummary['max'], 1) . ' ppm' : '-' }}</td>
        <td class="text-center">
            <span class="badge {{ $h2sSummary['non_compliant'] == 0 && $h2sSummary['count'] > 0 ? 'badge-success' : ($h2sSummary['count'] > 0 ? 'badge-danger' : 'badge-secondary') }}">
                {{ $h2sSummary['non_compliant'] == 0 && $h2sSummary['count'] > 0 ? 'Sesuai' : ($h2sSummary['count'] > 0 ? 'Tidak Sesuai' : 'Belum Ada Data') }}
            </span>
        </td>
    </tr>
    <tr>
        <td><strong>Lower Explosive Limit (LEL)</strong></td>
        <td>&le; 10 %LEL</td>
        <td class="val-cell">{{ $lelSummary['avg'] !== null ? number_format($lelSummary['avg'], 1) . ' %' : '-' }}</td>
        <td class="val-cell {{ $lelSummary['max'] > 10 ? 'text-danger' : '' }}">{{ $lelSummary['max'] !== null ? number_format($lelSummary['max'], 1) . ' %' : '-' }}</td>
        <td class="text-center">
            <span class="badge {{ $lelSummary['non_compliant'] == 0 && $lelSummary['count'] > 0 ? 'badge-success' : ($lelSummary['count'] > 0 ? 'badge-danger' : 'badge-secondary') }}">
                {{ $lelSummary['non_compliant'] == 0 && $lelSummary['count'] > 0 ? 'Sesuai' : ($lelSummary['count'] > 0 ? 'Tidak Sesuai' : 'Belum Ada Data') }}
            </span>
        </td>
    </tr>
    <tr>
        <td><strong>Tekanan Operasi (Pressure)</strong></td>
        <td>&le; 20 barg</td>
        <td class="val-cell">{{ $pressSummary['avg'] !== null ? number_format($pressSummary['avg'], 1) . ' barg' : '-' }}</td>
        <td class="val-cell {{ $pressSummary['max'] > 20 ? 'text-danger' : '' }}">{{ $pressSummary['max'] !== null ? number_format($pressSummary['max'], 1) . ' barg' : '-' }}</td>
        <td class="text-center">
            <span class="badge {{ $pressSummary['non_compliant'] == 0 && $pressSummary['count'] > 0 ? 'badge-success' : ($pressSummary['count'] > 0 ? 'badge-danger' : 'badge-secondary') }}">
                {{ $pressSummary['non_compliant'] == 0 && $pressSummary['count'] > 0 ? 'Sesuai' : ($pressSummary['count'] > 0 ? 'Tidak Sesuai' : 'Belum Ada Data') }}
            </span>
        </td>
    </tr>
</table>

<!-- Tabel Visualisasi Grafik Batang per Well Pad -->
<div style="font-weight: bold; font-size: 9.5pt; color: #0f2d42; margin: 10px 0 4px 0; text-transform: uppercase;">
    Ringkasan Grafik Rata-Rata Pengukuran per Well Pad
</div>
<table class="chart-visual-table">
    <thead>
        <tr>
            <th style="width: 18%;">Well Pad</th>
            <th style="width: 16%;">CO (ppm)<br><span style="font-weight: normal; font-size: 6.5pt;">Threshold: &le; 25</span></th>
            <th style="width: 16%;">O₂ (%)<br><span style="font-weight: normal; font-size: 6.5pt;">Threshold: 19.5 - 23.5</span></th>
            <th style="width: 16%;">H₂S (ppm)<br><span style="font-weight: normal; font-size: 6.5pt;">Threshold: &le; 10</span></th>
            <th style="width: 16%;">LEL (%)<br><span style="font-weight: normal; font-size: 6.5pt;">Threshold: &le; 10</span></th>
            <th style="width: 18%;">Pressure (barg)<br><span style="font-weight: normal; font-size: 6.5pt;">Threshold: &le; 20</span></th>
        </tr>
    </thead>
    <tbody>
        @forelse($chartSummary as $cs)
            <tr>
                <td style="text-align: left; font-weight: bold;">{{ $cs['name'] }}</td>
                <!-- CO Bar -->
                <td>
                    <div style="font-weight: bold; {{ $cs['co'] > 25 ? 'color: #dc2626;' : '' }}">{{ $cs['co'] !== null ? number_format($cs['co'], 1) : '-' }}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ min(100, ($cs['co'] ?? 0) / 30 * 100) }}%; background-color: {{ ($cs['co'] ?? 0) > 25 ? '#ef4444' : (($cs['co'] ?? 0) >= 18 ? '#f59e0b' : '#10b981') }};"></div>
                    </div>
                </td>
                <!-- O2 Bar -->
                <td>
                    <div style="font-weight: bold; {{ ($cs['o2'] < 19.5 || $cs['o2'] > 23.5) && $cs['o2'] !== null ? 'color: #dc2626;' : '' }}">{{ $cs['o2'] !== null ? number_format($cs['o2'], 1) : '-' }}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ min(100, ($cs['o2'] ?? 0) / 25 * 100) }}%; background-color: {{ ($cs['o2'] ?? 0) < 19.5 || ($cs['o2'] ?? 0) > 23.5 ? '#ef4444' : '#10b981' }};"></div>
                    </div>
                </td>
                <!-- H2S Bar -->
                <td>
                    <div style="font-weight: bold; {{ $cs['h2s'] > 10 ? 'color: #dc2626;' : '' }}">{{ $cs['h2s'] !== null ? number_format($cs['h2s'], 1) : '-' }}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ min(100, ($cs['h2s'] ?? 0) / 15 * 100) }}%; background-color: {{ ($cs['h2s'] ?? 0) > 10 ? '#ef4444' : (($cs['h2s'] ?? 0) >= 7 ? '#f59e0b' : '#10b981') }};"></div>
                    </div>
                </td>
                <!-- LEL Bar -->
                <td>
                    <div style="font-weight: bold; {{ $cs['lel'] > 10 ? 'color: #dc2626;' : '' }}">{{ $cs['lel'] !== null ? number_format($cs['lel'], 1) : '-' }}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ min(100, ($cs['lel'] ?? 0) / 15 * 100) }}%; background-color: {{ ($cs['lel'] ?? 0) > 10 ? '#ef4444' : (($cs['lel'] ?? 0) >= 7 ? '#f59e0b' : '#10b981') }};"></div>
                    </div>
                </td>
                <!-- Pressure Bar -->
                <td>
                    <div style="font-weight: bold; {{ $cs['pressure'] > 20 ? 'color: #dc2626;' : '' }}">{{ $cs['pressure'] !== null ? number_format($cs['pressure'], 1) : '-' }}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: {{ min(100, ($cs['pressure'] ?? 0) / 25 * 100) }}%; background-color: {{ ($cs['pressure'] ?? 0) > 20 ? '#ef4444' : (($cs['pressure'] ?? 0) >= 18 ? '#f59e0b' : '#10b981') }};"></div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-2 text-muted">Belum ada data untuk grafik pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Tabel Rincian Data Pengukuran Hasil Filter -->
<div style="font-weight: bold; font-size: 9.5pt; color: #0f2d42; margin: 12px 0 4px 0; text-transform: uppercase;">
    Rincian Hasil Pengukuran Pemantauan Gas
</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;" class="text-center">No</th>
            <th style="width: 10%;" class="text-center">Tanggal</th>
            <th style="width: 13%;">Well Pad</th>
            <th style="width: 8%;" class="text-right">CO (ppm)</th>
            <th style="width: 8%;" class="text-right">O₂ (%)</th>
            <th style="width: 8%;" class="text-right">H₂S (ppm)</th>
            <th style="width: 8%;" class="text-right">LEL (%)</th>
            <th style="width: 9%;" class="text-right">Press (barg)</th>
            <th style="width: 10%;" class="text-center">Status</th>
            <th style="width: 14%;">Keterangan</th>
            <th style="width: 8%;">Petugas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $item)
            @php
                $coEx = $item->co_level !== null && $item->co_level > 25.0;
                $o2Ex = $item->o2_level !== null && ($item->o2_level < 19.5 || $item->o2_level > 23.5);
                $h2sEx = $item->h2s_level !== null && $item->h2s_level > 10.0;
                $lelEx = $item->lel_level !== null && $item->lel_level > 10.0;
                $pressEx = $item->pressure !== null && $item->pressure > 20.0;
                $isAllOk = !$coEx && !$o2Ex && !$h2sEx && !$lelEx && !$pressEx;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '-' }}</td>
                <td class="text-bold">{{ $item->well_pad }}</td>
                <td class="text-right {{ $coEx ? 'text-danger text-bold' : '' }}">
                    {{ $item->co_level !== null ? number_format($item->co_level, 1) : '-' }}
                </td>
                <td class="text-right {{ $o2Ex ? 'text-danger text-bold' : '' }}">
                    {{ $item->o2_level !== null ? number_format($item->o2_level, 1) : '-' }}
                </td>
                <td class="text-right {{ $h2sEx ? 'text-danger text-bold' : '' }}">
                    {{ $item->h2s_level !== null ? number_format($item->h2s_level, 1) : '-' }}
                </td>
                <td class="text-right {{ $lelEx ? 'text-danger text-bold' : '' }}">
                    {{ $item->lel_level !== null ? number_format($item->lel_level, 1) : '-' }}
                </td>
                <td class="text-right {{ $pressEx ? 'text-danger text-bold' : '' }}">
                    {{ $item->pressure !== null ? number_format($item->pressure, 1) : '-' }}
                </td>
                <td class="text-center">
                    <span class="badge {{ $isAllOk ? 'badge-success' : 'badge-danger' }}">
                        {{ $isAllOk ? 'Sesuai' : 'Tidak Sesuai' }}
                    </span>
                </td>
                <td>{{ $item->notes ?? '-' }}</td>
                <td>{{ $item->recorded_by ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Tidak ada data pengukuran pada periode yang dipilih.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection

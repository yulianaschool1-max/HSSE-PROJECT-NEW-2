<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan HSSE' }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #087c68;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .app-title {
            font-size: 15pt;
            font-weight: bold;
            color: #087c68;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .project-title {
            font-size: 9.5pt;
            font-weight: 600;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .meta-label {
            font-weight: bold;
            color: #64748b;
            width: 14%;
        }
        .meta-value {
            color: #0f172a;
            width: 36%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8pt;
        }
        .data-table th {
            background-color: #087c68;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 5px;
            border: 1px solid #066653;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; }
        .footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            display: flex;
            justify-content: space-between;
        }
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="app-title">HSSE MANAJEMEN</div>
                <div class="project-title">Safe Guard Monitoring System &bull; Project Manajemen Geodipa Unit Dieng 2</div>
                <div class="doc-title">{{ $reportTitle ?? 'LAPORAN' }}</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 8pt; color: #64748b;">Tanggal Cetak:</div>
                <div style="font-size: 9pt; font-weight: bold; color: #0f172a;">{{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
                <div style="font-size: 8pt; color: #64748b; margin-top: 2px;">Dicetak Oleh: <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong></div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Modul:</td>
            <td class="meta-value">{{ $moduleName ?? 'HSSE' }}</td>
            <td class="meta-label">Periode / Filter:</td>
            <td class="meta-value">{{ $filterPeriod ?? 'Seluruh Data' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Total Data:</td>
            <td class="meta-value"><strong>{{ number_format($totalRecords ?? count($records ?? [])) }}</strong> Catatan</td>
            <td class="meta-label">Unit / Kategori:</td>
            <td class="meta-value">{{ $filterScope ?? 'Semua Unit' }}</td>
        </tr>
    </table>

    @yield('content')

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT} | HSSE Manajemen - Geodipa Dieng 2";
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 8;
            $color = array(0.5, 0.5, 0.5);
            $y = $pdf->get_height() - 24;
            $x = $pdf->get_width() - 250;
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>

<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GasDetector;
use App\Models\GasMonitoring;
use App\Models\WellPad;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GasDetectorController extends Controller
{
    public const NAB_CONFIG = [
        'co' => [
            'key' => 'co',
            'name' => 'CO',
            'label' => 'CO (ppm)',
            'val' => 25.0,
            'unit' => 'ppm',
            'type' => 'max',
            'threshold_text' => 'Threshold: ≤ 25 ppm',
            'desc' => 'Batas maksimal paparan CO adalah 25 ppm',
        ],
        'o2' => [
            'key' => 'o2',
            'name' => 'O₂',
            'label' => 'O₂ (%)',
            'min' => 19.5,
            'max' => 23.5,
            'unit' => '%',
            'type' => 'range',
            'threshold_text' => 'Threshold: 19.5 – 23.5 %',
            'desc' => 'Rentang aman kadar oksigen O₂ adalah 19.5% hingga 23.5%',
        ],
        'h2s' => [
            'key' => 'h2s',
            'name' => 'H₂S',
            'label' => 'H₂S (ppm)',
            'val' => 10.0,
            'unit' => 'ppm',
            'type' => 'max',
            'threshold_text' => 'Threshold: ≤ 10 ppm',
            'desc' => 'Nilai Ambang Batas H₂S adalah 10 ppm',
        ],
        'lel' => [
            'key' => 'lel',
            'name' => 'LEL',
            'label' => 'LEL (%LEL)',
            'val' => 10.0,
            'unit' => '%LEL',
            'type' => 'max',
            'threshold_text' => 'Threshold: ≤ 10 %LEL',
            'desc' => 'Batas Lower Explosive Limit LEL adalah ≤ 10%',
        ],
        'pressure' => [
            'key' => 'pressure',
            'name' => 'Pressure',
            'label' => 'Pressure (barg)',
            'val' => 20.0,
            'unit' => 'barg',
            'type' => 'max',
            'threshold_text' => 'Threshold: ≤ 20 barg',
            'desc' => 'Batas tekanan operasi normal pressure adalah ≤ 20 barg',
        ],
    ];

    /**
     * Resolusi rentang tanggal, label periode, dan tipe periode untuk Gas Monitoring.
     * Mendukung: 'all', 'daily', 'weekly', 'monthly', 'yearly', 'custom'.
     */
    private function resolvePeriodFilter(Request $request, Carbon $latestRecordDate): array
    {
        $periodType = $request->input('period_type');
        $startDate = null;
        $endDate = null;
        $periodLabel = 'Semua Periode';

        // Jika tidak ada period_type eksplisit, periksa start_date & end_date lama
        if (!$request->filled('period_type')) {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $periodType = 'custom';
            } elseif ($request->filled('date')) {
                $periodType = 'daily';
            } elseif ($request->filled('period')) {
                $p = (string) $request->input('period');
                if (preg_match('/^\d{4}-\d{2}$/', $p)) {
                    $periodType = 'monthly';
                } elseif (preg_match('/^\d{4}$/', $p)) {
                    $periodType = 'yearly';
                } else {
                    $periodType = 'daily';
                }
            } else {
                // Default pertama kali membuka halaman Gas Monitoring: gunakan data pengukuran terakhir (Harian)
                $periodType = 'daily';
                $defaultDate = $latestRecordDate->format('Y-m-d');
                $startDate = $defaultDate;
                $endDate = $defaultDate;
                $periodLabel = $latestRecordDate->translatedFormat('d F Y');
                return [$startDate, $endDate, $periodLabel, $periodType];
            }
        }

        switch ($periodType) {
            case 'daily':
                $dateVal = $request->input('filter_date') ?: $request->input('date') ?: $request->input('start_date') ?: $latestRecordDate->format('Y-m-d');
                $cDate = Carbon::parse($dateVal);
                $startDate = $cDate->format('Y-m-d');
                $endDate = $cDate->format('Y-m-d');
                $periodLabel = $cDate->translatedFormat('d F Y');
                break;

            case 'weekly':
                $dateVal = $request->input('filter_week_date') ?: $request->input('date') ?: $request->input('start_date') ?: $latestRecordDate->format('Y-m-d');
                $cDate = Carbon::parse($dateVal);
                $startOfWeek = $cDate->copy()->startOfWeek(Carbon::MONDAY);
                $endOfWeek = $cDate->copy()->endOfWeek(Carbon::SUNDAY);
                $startDate = $startOfWeek->format('Y-m-d');
                $endDate = $endOfWeek->format('Y-m-d');
                if ($startOfWeek->isSameMonth($endOfWeek)) {
                    $periodLabel = $startOfWeek->translatedFormat('d') . ' – ' . $endOfWeek->translatedFormat('d F Y');
                } else {
                    $periodLabel = $startOfWeek->translatedFormat('d M') . ' – ' . $endOfWeek->translatedFormat('d M Y');
                }
                break;

            case 'monthly':
                $monthVal = $request->input('filter_month') ?: $request->input('period') ?: $latestRecordDate->format('Y-m');
                if (preg_match('/^\d{4}-\d{2}$/', $monthVal)) {
                    $cMonth = Carbon::createFromFormat('Y-m', $monthVal)->startOfMonth();
                } else {
                    $cMonth = Carbon::parse($monthVal)->startOfMonth();
                }
                $startDate = $cMonth->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $cMonth->copy()->endOfMonth()->format('Y-m-d');
                $periodLabel = $cMonth->translatedFormat('F Y');
                break;

            case 'yearly':
                $yearVal = $request->input('filter_year') ?: $request->input('year') ?: $request->input('period') ?: $latestRecordDate->format('Y');
                $yearInt = (int) $yearVal;
                if ($yearInt < 2000 || $yearInt > 2099) {
                    $yearInt = (int) $latestRecordDate->format('Y');
                }
                $cYear = Carbon::createFromDate($yearInt, 1, 1);
                $startDate = $cYear->copy()->startOfYear()->format('Y-m-d');
                $endDate = $cYear->copy()->endOfYear()->format('Y-m-d');
                $periodLabel = 'Tahun ' . $yearInt;
                break;

            case 'custom':
                $startVal = $request->input('start_date');
                $endVal = $request->input('end_date');
                if ($startVal && $endVal) {
                    $cStart = Carbon::parse($startVal);
                    $cEnd = Carbon::parse($endVal);
                    if ($cEnd < $cStart) {
                        $cEnd = $cStart->copy();
                    }
                    $startDate = $cStart->format('Y-m-d');
                    $endDate = $cEnd->format('Y-m-d');
                    if ($cStart->isSameDay($cEnd)) {
                        $periodLabel = $cStart->translatedFormat('d F Y');
                    } elseif ($cStart->format('F Y') === $cEnd->format('F Y')) {
                        $periodLabel = $cStart->format('d') . ' – ' . $cEnd->translatedFormat('d F Y');
                    } else {
                        $periodLabel = $cStart->translatedFormat('d F Y') . ' – ' . $cEnd->translatedFormat('d F Y');
                    }
                } elseif ($startVal) {
                    $cStart = Carbon::parse($startVal);
                    $startDate = $cStart->format('Y-m-d');
                    $endDate = $startDate;
                    $periodLabel = $cStart->translatedFormat('d F Y');
                } elseif ($endVal) {
                    $cEnd = Carbon::parse($endVal);
                    $startDate = $cEnd->format('Y-m-d');
                    $endDate = $startDate;
                    $periodLabel = $cEnd->translatedFormat('d F Y');
                } else {
                    $periodType = 'all';
                    $periodLabel = 'Semua Periode';
                    $startDate = null;
                    $endDate = null;
                }
                break;

            case 'all':
            default:
                $periodType = 'all';
                $periodLabel = 'Semua Periode';
                $startDate = null;
                $endDate = null;
                break;
        }

        return [$startDate, $endDate, $periodLabel, $periodType];
    }

    /**
     * Halaman Utama Gas Monitoring dengan Filter Periode Fleksibel (Semua, Harian, Mingguan, Bulanan, Tahunan, Rentang Tanggal)
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->seedInitialWellPadsIfEmpty();

        // 1. Ambil daftar Well Pad aktif dan semua Well Pad
        $allWellPads = WellPad::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        $activeWellPads = WellPad::active()->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        // 2. Tentukan Tanggal Pengukuran Terakhir yang Tersedia di Database
        $latestRecordDateString = GasMonitoring::max('measurement_date');
        $latestRecordDate = $latestRecordDateString ? Carbon::parse($latestRecordDateString) : Carbon::today('Asia/Jakarta');

        // Parameter Filter Periode
        [$startDate, $endDate, $formattedPeriodLabel, $periodType] = $this->resolvePeriodFilter($request, $latestRecordDate);

        $wellPadFilter = (string) $request->input('well_pad', 'all');
        $parameterFilter = (string) $request->input('indicator', 'all'); // co, o2, h2s, lel, pressure, all
        $searchQuery = trim((string) $request->input('search', ''));

        // Cek apakah data terakhir tersedia termasuk dalam filter
        $isLatestAvailable = ($periodType === 'all') || ($endDate && $endDate >= $latestRecordDate->format('Y-m-d'));

        // Cari waktu input terakhir (updated_at / created_at)
        $latestEntryQuery = GasMonitoring::query();
        if ($startDate && $endDate) {
            $latestEntryQuery->whereBetween('measurement_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $latestEntryQuery->whereDate('measurement_date', '>=', $startDate);
        } elseif ($endDate) {
            $latestEntryQuery->whereDate('measurement_date', '<=', $endDate);
        }
        $latestEntry = $latestEntryQuery->orderBy('updated_at', 'desc')->first();

        if (!$latestEntry) {
            $latestEntry = GasMonitoring::orderBy('updated_at', 'desc')->first();
        }

        $lastInputTimeFormatted = $latestEntry && $latestEntry->updated_at
            ? $latestEntry->updated_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i')
            : ($latestEntry && $latestEntry->created_at ? $latestEntry->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-');

        // 3. Query Pengukuran Gas sesuai Period Filter
        $baseQuery = GasMonitoring::query();
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('measurement_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $baseQuery->whereDate('measurement_date', '>=', $startDate);
        } elseif ($endDate) {
            $baseQuery->whereDate('measurement_date', '<=', $endDate);
        }

        if ($wellPadFilter !== 'all' && !empty($wellPadFilter)) {
            $baseQuery->where('well_pad', $wellPadFilter);
        }

        if (!empty($searchQuery)) {
            $baseQuery->where(function ($q) use ($searchQuery) {
                $q->where('well_pad', 'like', "%{$searchQuery}%")
                  ->orWhere('notes', 'like', "%{$searchQuery}%")
                  ->orWhere('recorded_by', 'like', "%{$searchQuery}%");
            });
        }

        // Ambil data untuk kartu ringkasan, grafik, dan statistik
        $allFilteredMeasurements = (clone $baseQuery)->orderBy('measurement_date', 'asc')->orderBy('id', 'asc')->get();

        // Data untuk tabel riwayat (dengan pagination)
        $tableRecords = (clone $baseQuery)
            ->orderBy('measurement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 4. Hitung Ringkasan 5 Kartu Parameter
        $coAvg = $allFilteredMeasurements->whereNotNull('co_level')->avg('co_level');
        $o2Avg = $allFilteredMeasurements->whereNotNull('o2_level')->avg('o2_level');
        $h2sAvg = $allFilteredMeasurements->whereNotNull('h2s_level')->avg('h2s_level');
        $lelAvg = $allFilteredMeasurements->whereNotNull('lel_level')->avg('lel_level');
        $pressureAvg = $allFilteredMeasurements->whereNotNull('pressure')->avg('pressure');

        // Cek status kepatuhan kartu parameter
        $coNonCompliant = $allFilteredMeasurements->where('co_status', 'Tidak Sesuai')->count();
        $o2NonCompliant = $allFilteredMeasurements->where('o2_status', 'Tidak Sesuai')->count();
        $h2sNonCompliant = $allFilteredMeasurements->where('h2s_status', 'Tidak Sesuai')->count();
        $lelNonCompliant = $allFilteredMeasurements->where('lel_status', 'Tidak Sesuai')->count();
        $pressureNonCompliant = $allFilteredMeasurements->where('pressure_status', 'Tidak Sesuai')->count();

        $summaryCards = [
            'co' => [
                'val' => $coAvg !== null ? round($coAvg, 1) : '-',
                'unit' => 'ppm',
                'status' => $coAvg !== null ? ($coNonCompliant > 0 ? 'Tidak Sesuai' : 'Sesuai') : 'Belum Ada Data',
                'threshold' => 'Threshold: ≤ 25 ppm',
            ],
            'o2' => [
                'val' => $o2Avg !== null ? round($o2Avg, 1) : '-',
                'unit' => '%',
                'status' => $o2Avg !== null ? ($o2NonCompliant > 0 ? 'Tidak Sesuai' : 'Sesuai') : 'Belum Ada Data',
                'threshold' => 'Threshold: 19.5 – 23.5 %',
            ],
            'h2s' => [
                'val' => $h2sAvg !== null ? round($h2sAvg, 1) : '-',
                'unit' => 'ppm',
                'status' => $h2sAvg !== null ? ($h2sNonCompliant > 0 ? 'Tidak Sesuai' : 'Sesuai') : 'Belum Ada Data',
                'threshold' => 'Threshold: ≤ 10 ppm',
            ],
            'lel' => [
                'val' => $lelAvg !== null ? round($lelAvg, 1) : '-',
                'unit' => '%LEL',
                'status' => $lelAvg !== null ? ($lelNonCompliant > 0 ? 'Tidak Sesuai' : 'Sesuai') : 'Belum Ada Data',
                'threshold' => 'Threshold: ≤ 10 %LEL',
            ],
            'pressure' => [
                'val' => $pressureAvg !== null ? round($pressureAvg, 1) : '-',
                'unit' => 'barg',
                'status' => $pressureAvg !== null ? ($pressureNonCompliant > 0 ? 'Tidak Sesuai' : 'Sesuai') : 'Belum Ada Data',
                'threshold' => 'Threshold: ≤ 20 barg',
            ],
        ];

        // 5. Bangun Data Grafik Batang per Well Pad (Rata-rata/Nilai Terkini dalam Rentang Waktu)
        $chartData = $this->buildBarChartData($allFilteredMeasurements, $activeWellPads);

        // 6. Hitung Statistik Detail untuk masing-masing parameter
        $statisticsData = $this->buildStatisticsData($allFilteredMeasurements);

        // 7. Hitung Rekap Master Well Pad untuk Modal Kelola Well Pad
        $wellPadsSummary = $allWellPads->map(function (WellPad $wp) {
            $count = GasMonitoring::where('well_pad', $wp->name)
                ->orWhere('well_pad_id', $wp->id)
                ->count();

            $lastDate = GasMonitoring::where('well_pad', $wp->name)
                ->orWhere('well_pad_id', $wp->id)
                ->max('measurement_date');

            return [
                'id' => $wp->id,
                'name' => $wp->name,
                'code' => $wp->code,
                'is_active' => (bool) $wp->is_active,
                'status_label' => $wp->is_active ? 'Aktif' : 'Nonaktif',
                'data_count' => $count,
                'last_input_date' => $lastDate ? Carbon::parse($lastDate)->format('d/m/Y') : '-',
            ];
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'periodType' => $periodType,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'formattedPeriod' => $formattedPeriodLabel,
                'isLatestAvailable' => $isLatestAvailable,
                'lastInputTimeFormatted' => $lastInputTimeFormatted,
                'summaryCards' => $summaryCards,
                'chartData' => $chartData,
                'statistics' => $statisticsData,
                'records' => $tableRecords,
                'wellPads' => $wellPadsSummary,
            ]);
        }

        return view('gas-detector.index', compact(
            'periodType',
            'startDate',
            'endDate',
            'formattedPeriodLabel',
            'latestRecordDate',
            'isLatestAvailable',
            'lastInputTimeFormatted',
            'wellPadFilter',
            'parameterFilter',
            'searchQuery',
            'allWellPads',
            'activeWellPads',
            'summaryCards',
            'chartData',
            'statisticsData',
            'tableRecords',
            'wellPadsSummary'
        ));
    }

    /**
     * Bangun data grafik batang per Well Pad
     * Jika dalam rentang ada beberapa hari, hitung rata-rata per Well Pad dalam rentang tersebut
     */
    protected function buildBarChartData($measurements, $activeWellPads): array
    {
        $grouped = $measurements->groupBy('well_pad');

        $labels = [];
        $coVals = [];
        $o2Vals = [];
        $h2sVals = [];
        $lelVals = [];
        $pressureVals = [];

        $coColors = [];
        $o2Colors = [];
        $h2sColors = [];
        $lelColors = [];
        $pressureColors = [];

        $green = '#10b981';
        $yellow = '#f59e0b';
        $red = '#ef4444';

        // Tampilkan hanya Well Pad yang sedang aktif digunakan saat ini (Well Pad 7, 9 C, 9 D, 29 C, 29 D)
        // Jika filter memilih 1 well pad tertentu, tampilkan well pad tersebut
        $activePadNames = $activeWellPads->pluck('name')->all();
        $targetPads = $activePadNames;

        // Jika data yang terfilter hanya memuat 1 well pad dan memang difilter khusus
        if ($grouped->count() === 1) {
            $filteredSingle = $grouped->keys()->first();
            if ($filteredSingle) {
                $targetPads = [$filteredSingle];
            }
        }

        foreach ($targetPads as $padName) {
            $items = $grouped->get($padName, collect());
            $labels[] = $padName;

            // CO
            $co = $items->whereNotNull('co_level')->avg('co_level');
            $co = $co !== null ? round($co, 1) : 0;
            $coVals[] = $co;
            if ($co > 25.0) {
                $coColors[] = $red;
            } elseif ($co >= 18.0) {
                $coColors[] = $yellow;
            } else {
                $coColors[] = $green;
            }

            // O2
            $o2 = $items->whereNotNull('o2_level')->avg('o2_level');
            $o2 = $o2 !== null ? round($o2, 1) : 0;
            $o2Vals[] = $o2;
            if ($o2 < 19.5 || $o2 > 23.5) {
                $o2Colors[] = $red;
            } elseif ($o2 <= 19.8 || $o2 >= 23.0) {
                $o2Colors[] = $yellow;
            } else {
                $o2Colors[] = $green;
            }

            // H2S
            $h2s = $items->whereNotNull('h2s_level')->avg('h2s_level');
            $h2s = $h2s !== null ? round($h2s, 1) : 0;
            $h2sVals[] = $h2s;
            if ($h2s > 10.0) {
                $h2sColors[] = $red;
            } elseif ($h2s >= 7.0) {
                $h2sColors[] = $yellow;
            } else {
                $h2sColors[] = $green;
            }

            // LEL
            $lel = $items->whereNotNull('lel_level')->avg('lel_level');
            $lel = $lel !== null ? round($lel, 1) : 0;
            $lelVals[] = $lel;
            if ($lel > 10.0) {
                $lelColors[] = $red;
            } elseif ($lel >= 7.0) {
                $lelColors[] = $yellow;
            } else {
                $lelColors[] = $green;
            }

            // Pressure
            $p = $items->whereNotNull('pressure')->avg('pressure');
            $p = $p !== null ? round($p, 1) : 0;
            $pressureVals[] = $p;
            if ($p > 20.0) {
                $pressureColors[] = $red;
            } elseif ($p >= 18.0) {
                $pressureColors[] = $yellow;
            } else {
                $pressureColors[] = $green;
            }
        }

        return [
            'labels' => $labels,
            'co' => ['data' => $coVals, 'colors' => $coColors, 'threshold' => 25.0, 'unit' => 'ppm'],
            'o2' => ['data' => $o2Vals, 'colors' => $o2Colors, 'thresholdMin' => 19.5, 'thresholdMax' => 23.5, 'unit' => '%'],
            'h2s' => ['data' => $h2sVals, 'colors' => $h2sColors, 'threshold' => 10.0, 'unit' => 'ppm'],
            'lel' => ['data' => $lelVals, 'colors' => $lelColors, 'threshold' => 10.0, 'unit' => '%LEL'],
            'pressure' => ['data' => $pressureVals, 'colors' => $pressureColors, 'threshold' => 20.0, 'unit' => 'barg'],
        ];
    }

    /**
     * Hitung statistik per parameter: rata-rata, max, min, jumlah well pad, status keseluruhan
     */
    protected function buildStatisticsData($measurements): array
    {
        $params = ['co', 'o2', 'h2s', 'lel', 'pressure'];
        $stats = [];

        foreach ($params as $param) {
            $field = $param === 'pressure' ? 'pressure' : $param . '_level';
            $filtered = $measurements->filter(fn($m) => $m->$field !== null);

            $count = $filtered->count();
            if ($count === 0) {
                $stats[$param] = [
                    'avg' => '-',
                    'max_val' => '-',
                    'max_pad' => '-',
                    'min_val' => '-',
                    'min_pad' => '-',
                    'count' => 0,
                    'overall_status' => 'Belum Ada Data',
                    'overall_status_class' => 'secondary',
                    'detail_note' => 'Tidak ada data pengukuran pada periode yang dipilih',
                ];
                continue;
            }

            $avg = round($filtered->avg($field), 1);
            $maxItem = $filtered->sortByDesc($field)->first();
            $minItem = $filtered->sortBy($field)->first();

            $hasViolation = false;
            if ($param === 'co') {
                $hasViolation = $filtered->contains(fn($m) => $m->$field > 25.0);
            } elseif ($param === 'o2') {
                $hasViolation = $filtered->contains(fn($m) => $m->$field < 19.5 || $m->$field > 23.5);
            } elseif ($param === 'h2s') {
                $hasViolation = $filtered->contains(fn($m) => $m->$field > 10.0);
            } elseif ($param === 'lel') {
                $hasViolation = $filtered->contains(fn($m) => $m->$field > 10.0);
            } elseif ($param === 'pressure') {
                $hasViolation = $filtered->contains(fn($m) => $m->$field > 20.0);
            }

            $overallStatus = $hasViolation ? 'Tidak Sesuai' : 'Sesuai';
            $overallClass = $hasViolation ? 'danger' : 'success';
            $detailNote = $hasViolation ? 'Terdapat parameter yang melebihi threshold' : 'Semua Well Pad berada dalam batas aman';

            $stats[$param] = [
                'avg' => $avg,
                'max_val' => $maxItem ? $maxItem->$field : '-',
                'max_pad' => $maxItem ? "({$maxItem->well_pad})" : '',
                'min_val' => $minItem ? $minItem->$field : '-',
                'min_pad' => $minItem ? "({$minItem->well_pad})" : '',
                'count' => $count,
                'overall_status' => $overallStatus,
                'overall_status_class' => $overallClass,
                'detail_note' => $detailNote,
            ];
        }

        return $stats;
    }

    /**
     * Batch Store: Menginput hasil pengukuran untuk seluruh Well Pad aktif dalam 1 form (Gambar 2)
     */
    public function storeBatchMeasurements(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'measurements' => ['required', 'array', 'min:1'],
            'measurements.*.well_pad_id' => ['nullable', 'integer'],
            'measurements.*.well_pad_name' => ['required', 'string', 'max:100'],
            'measurements.*.co_level' => ['nullable', 'numeric', 'min:0'],
            'measurements.*.o2_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'measurements.*.h2s_level' => ['nullable', 'numeric', 'min:0'],
            'measurements.*.lel_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'measurements.*.pressure' => ['nullable', 'numeric', 'min:0'],
            'measurements.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $measurementDate = Carbon::parse($validated['measurement_date'])->format('Y-m-d');
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $updatedCount = 0;

            foreach ($validated['measurements'] as $row) {
                $wpName = trim($row['well_pad_name']);
                $wpId = !empty($row['well_pad_id']) ? (int)$row['well_pad_id'] : null;

                if (!$wpId) {
                    $padObj = WellPad::where('name', $wpName)->first();
                    $wpId = $padObj?->id;
                }

                // Cek apakah ada minimal 1 nilai yang diisi
                $hasData = ($row['co_level'] !== null && $row['co_level'] !== '') ||
                           ($row['o2_level'] !== null && $row['o2_level'] !== '') ||
                           ($row['h2s_level'] !== null && $row['h2s_level'] !== '') ||
                           ($row['lel_level'] !== null && $row['lel_level'] !== '') ||
                           ($row['pressure'] !== null && $row['pressure'] !== '') ||
                           (!empty($row['notes']));

                if (!$hasData) {
                    continue;
                }

                // Cek duplikasi untuk Well Pad dan tanggal yang sama
                $existing = GasMonitoring::where('measurement_date', $measurementDate)
                    ->where(function ($q) use ($wpName, $wpId) {
                        $q->where('well_pad', $wpName);
                        if ($wpId) {
                            $q->orWhere('well_pad_id', $wpId);
                        }
                    })->first();

                $dataPayload = [
                    'measurement_date' => $measurementDate,
                    'well_pad_id' => $wpId,
                    'well_pad' => $wpName,
                    'co_level' => ($row['co_level'] !== null && $row['co_level'] !== '') ? (float)$row['co_level'] : null,
                    'o2_level' => ($row['o2_level'] !== null && $row['o2_level'] !== '') ? (float)$row['o2_level'] : null,
                    'h2s_level' => ($row['h2s_level'] !== null && $row['h2s_level'] !== '') ? (float)$row['h2s_level'] : null,
                    'lel_level' => ($row['lel_level'] !== null && $row['lel_level'] !== '') ? (float)$row['lel_level'] : null,
                    'pressure' => ($row['pressure'] !== null && $row['pressure'] !== '') ? (float)$row['pressure'] : null,
                    'notes' => $row['notes'] ?? '-',
                    'recorded_by' => $userName,
                ];

                if ($existing) {
                    $existing->update($dataPayload);
                    $updatedCount++;
                } else {
                    GasMonitoring::create($dataPayload);
                    $savedCount++;
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gas Monitoring',
                'action' => 'INPUT_MASSAL',
                'record_type' => GasMonitoring::class,
                'record_id' => null,
                'description' => "Input data gas monitoring massal tanggal {$measurementDate}: {$savedCount} data baru, {$updatedCount} data diperbarui",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            $msg = "Data pemantauan gas berhasil disimpan ({$savedCount} baru, {$updatedCount} diperbarui).";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'startDate' => $measurementDate,
                    'endDate' => $measurementDate,
                ]);
            }

            return redirect()->route('gas-detector.index', ['start_date' => $measurementDate, 'end_date' => $measurementDate])
                ->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Detail 1 Rekaman Pengukuran
     */
    public function showMeasurement(GasMonitoring $gasMonitoring): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $gasMonitoring,
        ]);
    }

    /**
     * Simpan data pengukuran tunggal
     */
    public function storeMeasurement(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'well_pad' => ['required', 'string', 'max:100'],
            'co_level' => ['nullable', 'numeric', 'min:0'],
            'o2_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'h2s_level' => ['nullable', 'numeric', 'min:0'],
            'lel_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pressure' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = Auth::user()?->name ?? 'Petugas HSSE Dieng';
        $pad = WellPad::where('name', $validated['well_pad'])->first();

        $monitoring = GasMonitoring::create([
            'measurement_date' => $validated['measurement_date'],
            'well_pad_id' => $pad?->id,
            'well_pad' => $validated['well_pad'],
            'co_level' => $validated['co_level'] ?? null,
            'o2_level' => $validated['o2_level'] ?? null,
            'h2s_level' => $validated['h2s_level'] ?? null,
            'lel_level' => $validated['lel_level'] ?? null,
            'pressure' => $validated['pressure'] ?? null,
            'notes' => $validated['notes'] ?? '-',
            'recorded_by' => $userName,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Gas Monitoring',
            'action' => 'TAMBAH',
            'record_type' => GasMonitoring::class,
            'record_id' => $monitoring->id,
            'description' => "Mencatat pengukuran gas di {$monitoring->well_pad}: CO {$monitoring->co_level} ppm, O2 {$monitoring->o2_level}%, H2S {$monitoring->h2s_level} ppm, LEL {$monitoring->lel_level}%, Pressure {$monitoring->pressure} barg",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil disimpan.',
                'data' => $monitoring,
            ]);
        }

        return redirect()->route('gas-detector.index', ['start_date' => $validated['measurement_date'], 'end_date' => $validated['measurement_date']])
            ->with('success', 'Data pengukuran gas berhasil disimpan.');
    }

    /**
     * Perbarui data pengukuran gas tunggal
     */
    public function updateMeasurement(Request $request, GasMonitoring $gasMonitoring): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'well_pad' => ['required', 'string', 'max:100'],
            'co_level' => ['nullable', 'numeric', 'min:0'],
            'o2_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'h2s_level' => ['nullable', 'numeric', 'min:0'],
            'lel_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pressure' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $pad = WellPad::where('name', $validated['well_pad'])->first();

        $gasMonitoring->update([
            'measurement_date' => $validated['measurement_date'],
            'well_pad_id' => $pad?->id ?? $gasMonitoring->well_pad_id,
            'well_pad' => $validated['well_pad'],
            'co_level' => $validated['co_level'] ?? null,
            'o2_level' => $validated['o2_level'] ?? null,
            'h2s_level' => $validated['h2s_level'] ?? null,
            'lel_level' => $validated['lel_level'] ?? null,
            'pressure' => $validated['pressure'] ?? null,
            'notes' => $validated['notes'] ?? '-',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
            'module' => 'Gas Monitoring',
            'action' => 'UBAH',
            'record_type' => GasMonitoring::class,
            'record_id' => $gasMonitoring->id,
            'description' => "Memperbarui pengukuran gas di {$gasMonitoring->well_pad} tanggal {$gasMonitoring->measurement_date?->format('d/m/Y')}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil diperbarui.',
                'data' => $gasMonitoring,
            ]);
        }

        return redirect()->route('gas-detector.index', ['start_date' => $validated['measurement_date'], 'end_date' => $validated['measurement_date']])
            ->with('success', 'Data pengukuran gas berhasil diperbarui.');
    }

    /**
     * Hapus data pengukuran gas di well pad
     */
    public function destroyMeasurement(GasMonitoring $gasMonitoring): RedirectResponse|JsonResponse
    {
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Gas Monitoring',
            'action' => 'HAPUS',
            'record_type' => GasMonitoring::class,
            'record_id' => $gasMonitoring->id,
            'description' => "Menghapus rekaman pengukuran gas di {$gasMonitoring->well_pad} tanggal {$gasMonitoring->measurement_date?->format('d/m/Y')}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $gasMonitoring->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil dihapus.',
            ]);
        }

        return redirect()->route('gas-detector.index')->with('success', 'Data pengukuran gas berhasil dihapus.');
    }

    // =========================================================================
    // KELOLA WELL PAD (Gambar 3)
    // =========================================================================

    /**
     * Ambil data Well Pad lengkap dengan statistik untuk Modal Kelola Well Pad
     */
    public function getWellPadsData(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $query = WellPad::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $wellPads = $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        $data = $wellPads->map(function (WellPad $wp, $index) {
            $count = GasMonitoring::where('well_pad', $wp->name)
                ->orWhere('well_pad_id', $wp->id)
                ->count();

            $lastDate = GasMonitoring::where('well_pad', $wp->name)
                ->orWhere('well_pad_id', $wp->id)
                ->max('measurement_date');

            return [
                'index' => $index + 1,
                'id' => $wp->id,
                'name' => $wp->name,
                'code' => $wp->code,
                'is_active' => (bool) $wp->is_active,
                'status_label' => $wp->is_active ? 'Aktif' : 'Nonaktif',
                'data_count' => $count,
                'last_input_date' => $lastDate ? Carbon::parse($lastDate)->format('d/m/Y') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Tambah Well Pad Baru
     */
    public function storeWellPad(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:well_pads,name'],
            'code' => ['required', 'string', 'max:50', 'unique:well_pads,code'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $maxOrder = WellPad::max('sort_order') ?? 0;

        $pad = WellPad::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'is_active' => true,
            'sort_order' => $maxOrder + 1,
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
            'module' => 'Well Pad',
            'action' => 'TAMBAH',
            'record_type' => WellPad::class,
            'record_id' => $pad->id,
            'description' => "Menambahkan Well Pad baru: {$pad->name} ({$pad->code})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Well Pad '{$pad->name}' berhasil ditambahkan.",
            'data' => $pad,
        ]);
    }

    /**
     * Update Well Pad (Nama, Kode, Status Aktif/Nonaktif)
     */
    public function updateWellPad(Request $request, WellPad $wellPad): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:well_pads,name,' . $wellPad->id],
            'code' => ['required', 'string', 'max:50', 'unique:well_pads,code,' . $wellPad->id],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $oldName = $wellPad->name;
        $newName = trim($validated['name']);

        DB::beginTransaction();
        try {
            $wellPad->update([
                'name' => $newName,
                'code' => strtoupper(trim($validated['code'])),
                'is_active' => (bool)$validated['is_active'],
                'description' => $validated['description'] ?? null,
            ]);

            if ($oldName !== $newName) {
                GasMonitoring::where('well_pad', $oldName)->update(['well_pad' => $newName]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
                'module' => 'Well Pad',
                'action' => 'UBAH',
                'record_type' => WellPad::class,
                'record_id' => $wellPad->id,
                'description' => "Memperbarui Well Pad: {$wellPad->name} ({$wellPad->code})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Data Well Pad '{$wellPad->name}' berhasil diperbarui.",
                'data' => $wellPad,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Well Pad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus / Nonaktifkan Well Pad
     */
    public function destroyWellPad(WellPad $wellPad): JsonResponse
    {
        $hasHistory = GasMonitoring::where('well_pad', $wellPad->name)
            ->orWhere('well_pad_id', $wellPad->id)
            ->exists();

        if ($hasHistory) {
            $wellPad->update(['is_active' => false]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
                'module' => 'Well Pad',
                'action' => 'NONAKTIFKAN',
                'record_type' => WellPad::class,
                'record_id' => $wellPad->id,
                'description' => "Menonaktifkan Well Pad: {$wellPad->name} karena memiliki riwayat pengukuran gas.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'action' => 'deactivated',
                'message' => "Well Pad '{$wellPad->name}' memiliki riwayat pengukuran gas, sehingga statusnya diubah menjadi Nonaktif agar data historis tetap terjaga.",
            ]);
        }

        $name = $wellPad->name;
        $wellPad->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
            'module' => 'Well Pad',
            'action' => 'HAPUS',
            'record_type' => WellPad::class,
            'record_id' => null,
            'description' => "Menghapus Well Pad: {$name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'action' => 'deleted',
            'message' => "Well Pad '{$name}' berhasil dihapus.",
        ]);
    }

    /**
     * Seed initial 5 valid well pads jika tabel kosong
     */
    protected function seedInitialWellPadsIfEmpty(): void
    {
        if (WellPad::count() > 0) {
            return;
        }

        $defaultPads = [
            ['name' => 'Well Pad 7', 'code' => 'WP-07', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Well Pad 9 C', 'code' => 'WP-09C', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Well Pad 9 D', 'code' => 'WP-09D', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Well Pad 29 C', 'code' => 'WP-29C', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Well Pad 29 D', 'code' => 'WP-29D', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($defaultPads as $pad) {
            WellPad::create($pad);
        }
    }

    // =========================================================================
    // INVENTARIS PERANGKAT GASDETEKTOR (Fungsi Asli Dipertahankan)
    // =========================================================================

    public function checkDuplicate(Request $request): JsonResponse
    {
        $deviceCode = trim((string) $request->input('device_code'));
        $ignoreId = $request->input('ignore_id');

        if (empty($deviceCode)) {
            return response()->json(['duplicate' => false]);
        }

        $query = GasDetector::where('device_code', $deviceCode);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->first();

        if ($existing) {
            return response()->json([
                'duplicate' => true,
                'message' => "Kode perangkat '{$existing->device_code}' sudah terdaftar untuk {$existing->model_name} ({$existing->unit}).",
                'data' => $existing,
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    public function show(Request $request, $id): JsonResponse|View
    {
        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $activityLogs = ActivityLog::where('module', 'Gasdetektor')
            ->where(function ($q) use ($device) {
                $q->where('record_id', $device->id)
                  ->orWhere('description', 'like', "%{$device->device_code}%");
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'device' => $device,
                'activityLogs' => $activityLogs,
            ]);
        }

        return view('gas-detector.show', compact('device', 'activityLogs'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:gas_detectors,device_code'],
            'model_name' => ['required', 'string', 'max:100'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:gas_detectors,serial_number'],
            'gas_type' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:Tersedia,Sedang Digunakan,Dalam Perawatan,Perlu Kalibrasi'],
            'last_calibration_date' => ['nullable', 'date'],
            'next_calibration_date' => ['nullable', 'date', 'after_or_equal:last_calibration_date'],
            'user_in_charge' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $device = GasDetector::create($validated);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'TAMBAH',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Menambahkan perangkat Gasdetektor baru: {$device->device_code} - {$device->model_name} ({$device->unit})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data perangkat Gasdetektor berhasil ditambahkan.',
                    'device' => $device,
                ]);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Data perangkat Gasdetektor berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id): RedirectResponse|JsonResponse
    {
        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:gas_detectors,device_code,' . $device->id],
            'model_name' => ['required', 'string', 'max:100'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:gas_detectors,serial_number,' . $device->id],
            'gas_type' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:Tersedia,Sedang Digunakan,Dalam Perawatan,Perlu Kalibrasi'],
            'last_calibration_date' => ['nullable', 'date'],
            'next_calibration_date' => ['nullable', 'date'],
            'user_in_charge' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $device->update($validated);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'UBAH',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Memperbarui perangkat Gasdetektor: {$device->device_code} - {$device->model_name} ({$device->unit})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data perangkat Gasdetektor berhasil diperbarui.',
                    'device' => $device,
                ]);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Data perangkat Gasdetektor berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id): RedirectResponse|JsonResponse
    {
        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'HAPUS',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Menghapus perangkat Gasdetektor: {$device->device_code} - {$device->model_name} ({$device->unit})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $device->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perangkat Gasdetektor berhasil dihapus.',
                ]);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Perangkat Gasdetektor berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    /**
     * Export Gas Monitoring measurements to PDF berdasarkan Date Range dan Filter Aktif
     */
    public function exportMeasurements(Request $request): Response
    {
        $wellPadFilter = (string) $request->input('well_pad', 'all');
        $indicatorFilter = (string) $request->input('indicator', 'all');

        $latestDateString = GasMonitoring::max('measurement_date');
        $latestRecordDate = $latestDateString ? Carbon::parse($latestDateString) : Carbon::today('Asia/Jakarta');

        [$startDate, $endDate, $periodLabel, $periodType] = $this->resolvePeriodFilter($request, $latestRecordDate);

        $query = GasMonitoring::query();
        if ($startDate && $endDate) {
            $query->whereBetween('measurement_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('measurement_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('measurement_date', '<=', $endDate);
        }

        if ($wellPadFilter !== 'all' && !empty($wellPadFilter)) {
            $query->where('well_pad', $wellPadFilter);
        }

        $records = $query->orderBy('measurement_date', 'asc')->orderBy('id', 'asc')->get();

        // Parameter Summaries
        $coSummary = [
            'count' => $records->whereNotNull('co_level')->count(),
            'avg' => $records->whereNotNull('co_level')->avg('co_level'),
            'max' => $records->whereNotNull('co_level')->max('co_level'),
            'non_compliant' => $records->where('co_status', 'Tidak Sesuai')->count(),
        ];
        $o2Summary = [
            'count' => $records->whereNotNull('o2_level')->count(),
            'avg' => $records->whereNotNull('o2_level')->avg('o2_level'),
            'min' => $records->whereNotNull('o2_level')->min('o2_level'),
            'max' => $records->whereNotNull('o2_level')->max('o2_level'),
            'non_compliant' => $records->where('o2_status', 'Tidak Sesuai')->count(),
        ];
        $h2sSummary = [
            'count' => $records->whereNotNull('h2s_level')->count(),
            'avg' => $records->whereNotNull('h2s_level')->avg('h2s_level'),
            'max' => $records->whereNotNull('h2s_level')->max('h2s_level'),
            'non_compliant' => $records->where('h2s_status', 'Tidak Sesuai')->count(),
        ];
        $lelSummary = [
            'count' => $records->whereNotNull('lel_level')->count(),
            'avg' => $records->whereNotNull('lel_level')->avg('lel_level'),
            'max' => $records->whereNotNull('lel_level')->max('lel_level'),
            'non_compliant' => $records->where('lel_status', 'Tidak Sesuai')->count(),
        ];
        $pressSummary = [
            'count' => $records->whereNotNull('pressure')->count(),
            'avg' => $records->whereNotNull('pressure')->avg('pressure'),
            'max' => $records->whereNotNull('pressure')->max('pressure'),
            'non_compliant' => $records->where('pressure_status', 'Tidak Sesuai')->count(),
        ];

        // Chart Summary per Well Pad
        $chartSummary = [];
        $groupedByPad = $records->groupBy('well_pad');
        foreach ($groupedByPad as $padName => $items) {
            $chartSummary[] = [
                'name' => $padName,
                'co' => $items->whereNotNull('co_level')->avg('co_level'),
                'o2' => $items->whereNotNull('o2_level')->avg('o2_level'),
                'h2s' => $items->whereNotNull('h2s_level')->avg('h2s_level'),
                'lel' => $items->whereNotNull('lel_level')->avg('lel_level'),
                'pressure' => $items->whereNotNull('pressure')->avg('pressure'),
            ];
        }

        $indicatorLabelMap = [
            'all' => 'Semua Parameter (CO, O₂, H₂S, LEL, Pressure)',
            'co' => 'CO (ppm)',
            'o2' => 'O₂ (%)',
            'h2s' => 'H₂S (ppm)',
            'lel' => 'LEL (%LEL)',
            'pressure' => 'Pressure (barg)',
        ];

        $pdf = Pdf::loadView('exports.gas-monitoring-pdf', [
            'reportTitle' => 'LAPORAN PEMANTAUAN GAS WELL PAD (GAS MONITORING)',
            'moduleName' => 'Gas Monitoring',
            'filterInfo' => [
                'Periode Pengukuran' => $periodLabel,
                'Well Pad' => $wellPadFilter === 'all' ? 'Semua Well Pad' : $wellPadFilter,
                'Indikator Parameter' => $indicatorLabelMap[$indicatorFilter] ?? 'Semua Parameter',
                'Total Data Terfilter' => $records->count() . ' Pengukuran',
            ],
            'records' => $records,
            'coSummary' => $coSummary,
            'o2Summary' => $o2Summary,
            'h2sSummary' => $h2sSummary,
            'lelSummary' => $lelSummary,
            'pressSummary' => $pressSummary,
            'chartSummary' => $chartSummary,
            'userName' => Auth::user()->name ?? 'Administrator Dieng',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s') . ' WIB',
        ])->setPaper('a4', 'landscape');

        $slugPeriod = preg_replace('/[^A-Za-z0-9_\-]/', '_', $periodLabel);
        $fileName = 'Laporan_Gas_Monitoring_' . $slugPeriod . '.pdf';
        return $pdf->download($fileName);
    }

    public function export(Request $request): Response
    {
        return $this->exportMeasurements($request);
    }
}

<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ManHour;
use App\Models\OperationalUnit;
use App\Models\UnitManHourAdjustment;
use App\Models\Workforce;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManHoursController extends Controller
{
    /**
     * Resolusi rentang tanggal, label periode, dan tipe periode berdasarkan request.
     * Mendukung: 'all', 'daily', 'weekly', 'monthly', 'yearly', 'custom'.
     */
    private function resolvePeriodFilter(Request $request): array
    {
        $periodType = $request->input('period_type', 'all');
        $startDate = null;
        $endDate = null;
        $periodLabel = 'Semua Periode';

        // Jika start_date dan end_date dikirim langsung tanpa period_type eksplisit
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
                }
            }
        }

        switch ($periodType) {
            case 'daily':
                $dateVal = $request->input('filter_date') ?: $request->input('date') ?: $request->input('start_date') ?: Carbon::today()->format('Y-m-d');
                $cDate = Carbon::parse($dateVal);
                $startDate = $cDate->format('Y-m-d');
                $endDate = $cDate->format('Y-m-d');
                $periodLabel = $cDate->translatedFormat('d F Y');
                break;

            case 'weekly':
                $dateVal = $request->input('filter_week_date') ?: $request->input('date') ?: $request->input('start_date') ?: Carbon::today()->format('Y-m-d');
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
                $monthVal = $request->input('filter_month') ?: $request->input('period') ?: Carbon::today()->format('Y-m');
                if (preg_match('/^\d{4}-\d{2}$/', $monthVal)) {
                    $cMonth = Carbon::createFromFormat('Y-m', $monthVal)->startOfMonth();
                } else {
                    $cMonth = Carbon::today()->startOfMonth();
                }
                $startDate = $cMonth->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $cMonth->copy()->endOfMonth()->format('Y-m-d');
                $periodLabel = $cMonth->translatedFormat('F Y');
                break;

            case 'yearly':
                $yearVal = $request->input('filter_year') ?: $request->input('year') ?: $request->input('period') ?: Carbon::today()->format('Y');
                $yearInt = (int) $yearVal;
                if ($yearInt < 2000 || $yearInt > 2099) {
                    $yearInt = (int) Carbon::today()->format('Y');
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
                    $startDate = $cStart->format('Y-m-d');
                    $endDate = $cEnd->format('Y-m-d');
                    if ($cStart->isSameDay($cEnd)) {
                        $periodLabel = $cStart->translatedFormat('d F Y');
                    } else {
                        $periodLabel = $cStart->translatedFormat('d F Y') . ' – ' . $cEnd->translatedFormat('d F Y');
                    }
                } elseif ($startVal) {
                    $cStart = Carbon::parse($startVal);
                    $startDate = $cStart->format('Y-m-d');
                    $periodLabel = 'Mulai ' . $cStart->translatedFormat('d F Y');
                } elseif ($endVal) {
                    $cEnd = Carbon::parse($endVal);
                    $endDate = $cEnd->format('Y-m-d');
                    $periodLabel = 'Sampai ' . $cEnd->translatedFormat('d F Y');
                } else {
                    $periodType = 'all';
                    $periodLabel = 'Semua Periode';
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
     * Tampilkan halaman Man Hours yang bersumber otomatis dari data Workforce.
     */
    public function index(Request $request): View|JsonResponse
    {
        OperationalUnit::ensureDefaultsSeeded();

        // Sinkronisasi idempoten: pastikan setiap Workforce memiliki 1 baris ManHour yang sesuai
        ManHour::syncAllFromWorkforce();

        [$filterStart, $filterEnd, $formattedPeriodLabel, $periodType] = $this->resolvePeriodFilter($request);

        // Hanya ambil ManHour yang terhubung dengan sumber Workforce valid (atau seluruh rekaman sah)
        $query = ManHour::with(['workforce', 'operationalUnit'])->whereHas('workforce');

        // Filter: Berdasarkan rentang periode yang terselesaikan
        if ($filterStart && $filterEnd) {
            $query->whereBetween('work_date', [$filterStart, $filterEnd]);
        } elseif ($filterStart) {
            $query->whereDate('work_date', '>=', $filterStart);
        } elseif ($filterEnd) {
            $query->whereDate('work_date', '<=', $filterEnd);
        }

        // Filter: Kategori / Komponen Pekerja
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Unit
        if ($request->filled('unit')) {
            $query->where('unit', $request->input('unit'));
        }

        // Filter: Periode (YYYY-MM atau YYYY)
        if ($request->filled('period')) {
            $period = (string) $request->input('period');
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                [$y, $m] = explode('-', $period);
                $query->whereYear('work_date', (int) $y)
                      ->whereMonth('work_date', (int) $m);
            } elseif (preg_match('/^\d{4}$/', $period)) {
                $query->whereYear('work_date', (int) $period);
            }
        } elseif ($request->filled('year')) {
            $query->whereYear('work_date', (int) $request->input('year'));
        }

        // Filter: Pencarian teks
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('category', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%")
                  ->orWhereHas('workforce', function ($wq) use ($term) {
                      $wq->where('employee_code', 'like', "%{$term}%")
                         ->orWhere('name', 'like', "%{$term}%")
                         ->orWhere('department', 'like', "%{$term}%");
                  });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'date-desc');
        if ($sort === 'date-asc') {
            $query->orderBy('work_date', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'hours-desc') {
            $query->orderBy('total_hours', 'desc')->orderBy('work_date', 'desc');
        } elseif ($sort === 'hours-asc') {
            $query->orderBy('total_hours', 'asc')->orderBy('work_date', 'desc');
        } elseif ($sort === 'workers-desc') {
            $query->orderBy('number_of_workers', 'desc')->orderBy('work_date', 'desc');
        } elseif ($sort === 'category') {
            $query->orderBy('category', 'asc')->orderBy('work_date', 'desc');
        } else {
            $query->orderBy('work_date', 'desc')->orderBy('id', 'desc');
        }

        // Koleksi penuh yang terfilter untuk perhitungan statistik & grafik
        $allFiltered = (clone $query)->get();

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 15;
        $manHours = $query->paginate($perPage)->withQueryString();

        // Seluruh data di DB untuk rekap kumulatif, bulanan, tahunan
        $allGlobal = ManHour::with(['workforce', 'operationalUnit'])->whereHas('workforce')->get();

        $totalManHours = round((float) $allFiltered->sum('total_hours'), 2);
        $totalWorkers = (int) $allFiltered->sum('number_of_workers');
        $cumulativeManHours = round((float) $allGlobal->sum('total_hours'), 2);

        // Tentukan tanggal acuan untuk rekap harian/bulanan/tahunan (dari data terbaru di filter atau global)
        $latestRecordDate = $allFiltered->max('work_date') ?? $allGlobal->max('work_date');
        $refDate = $latestRecordDate ? Carbon::parse($latestRecordDate) : Carbon::today();

        $latestDateRows = $latestRecordDate
            ? $allFiltered->filter(fn (ManHour $m) => $m->work_date && $m->work_date->isSameDay($refDate))
            : collect();

        $dailyManHours = round((float) $latestDateRows->sum('total_hours'), 2);
        $dailyWorkers = (int) $latestDateRows->sum('number_of_workers');

        $monthlyManHours = round((float) $allFiltered
            ->filter(fn (ManHour $m) => $m->work_date && $m->work_date->year === $refDate->year && $m->work_date->month === $refDate->month)
            ->sum('total_hours'), 2);

        $yearlyManHours = round((float) $allFiltered
            ->filter(fn (ManHour $m) => $m->work_date && $m->work_date->year === $refDate->year)
            ->sum('total_hours'), 2);

        // Master Operational Units & Deteksi Penambahan Unit Baru
        $operationalUnits = OperationalUnit::ordered()->get();
        $activeOperationalUnits = $operationalUnits->where('is_active', true)->values();
        $initialUnitsCount = $operationalUnits->where('is_initial', true)->count();
        $newUnitsAdded = $operationalUnits->where('is_initial', false)->where('is_active', true)->values();
        $newUnitsCount = $newUnitsAdded->count();
        $totalActiveUnitsCount = $activeOperationalUnits->count();

        // Rekap Perhitungan per Unit / Area Operasi (dihitung sebelum stats agar akumulasi koreksi manual tercakup)
        $unitRecap = $this->buildUnitRecap($operationalUnits, $allFiltered, $latestRecordDate ? Carbon::parse($latestRecordDate) : null);

        // Total akumulasi Man Hours dari seluruh unit (termasuk koreksi manual tersimpan)
        $totalManHours = (float) $unitRecap['totals']['period_man_hours'];
        $cumulativeManHours = max(0.0, round($cumulativeManHours + (float) $operationalUnits->sum('accumulated_hours_adjustment'), 2));

        $distinctDatesCount = $allFiltered
            ->map(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m-d') : null)
            ->filter()
            ->unique()
            ->count();

        $averageDailyManHours = $distinctDatesCount > 0
            ? round($totalManHours / $distinctDatesCount, 2)
            : 0.0;

        $unitExpansionInfo = [
            'has_new_units' => $newUnitsCount > 0,
            'initial_units_count' => $initialUnitsCount,
            'new_units_count' => $newUnitsCount,
            'new_active_count' => $newUnitsCount,
            'total_active_units_count' => $totalActiveUnitsCount,
            'new_units' => $newUnitsAdded->map(fn (OperationalUnit $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'created_at_formatted' => $u->created_at ? $u->created_at->format('d/m/Y') : '-',
                'description' => $u->description,
            ])->values()->all(),
            'summary_message' => $newUnitsCount > 0
                ? "Terdapat penambahan {$newUnitsCount} unit baru (" . $newUnitsAdded->pluck('name')->implode(', ') . ") dari sebelumnya {$initialUnitsCount} unit menjadi {$totalActiveUnitsCount} unit aktif."
                : "Seluruh {$totalActiveUnitsCount} unit/area operasi aktif disertakan dalam perhitungan otomatis Man Hours.",
        ];

        // Perhitungan Rata-rata Jam Kerja / Orang sesuai rumus: Total Manhour ÷ Total Pekerja
        $avgHoursPerWorker = $totalWorkers > 0 ? round($totalManHours / $totalWorkers, 1) : 0.0;

        $stats = [
            'total_man_hours' => $totalManHours,
            'total_workers' => $totalWorkers,
            'avg_hours_per_worker' => $avgHoursPerWorker,
            'daily_man_hours' => $dailyManHours,
            'daily_workers' => $dailyWorkers,
            'monthly_man_hours' => $monthlyManHours,
            'yearly_man_hours' => $yearlyManHours,
            'cumulative_man_hours' => $cumulativeManHours,
            'average_daily_man_hours' => $averageDailyManHours,
            'active_units_count' => $totalActiveUnitsCount,
            'new_units_count' => $newUnitsCount,
            'distinct_days_count' => $distinctDatesCount,
            'total_records' => $allFiltered->count(),
            'period_type' => $periodType,
            'reference_date' => $latestRecordDate ? Carbon::parse($latestRecordDate)->format('d/m/Y') : '-',
        ];

        // Daftar Kategori & Unit
        $dbCategories = Workforce::select('category')->distinct()->pluck('category')->filter()->values()->all();
        $categories = array_values(array_unique(array_merge(Workforce::CATEGORIES, $dbCategories)));

        $masterUnitNames = $operationalUnits->pluck('name')->all();
        $dbUnits = ManHour::select('unit')->distinct()->pluck('unit')->filter()->values()->all();
        $units = array_values(array_unique(array_merge($masterUnitNames, $dbUnits)));

        // Tabel Riwayat Harian Man Hours (Akumulasi Harian & Kumulatif Berjalan tanpa penggandaan)
        $dailyRecap = $this->buildDailyHistoryRecap($allFiltered, $operationalUnits);

        // Grafik 1: Tren Man Hours per Tanggal
        $groupedByDate = $allFiltered
            ->sortBy(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m-d') : '')
            ->groupBy(fn (ManHour $m) => $m->work_date ? $m->work_date->format('d/m/Y') : '-');

        $trendChart = [
            'labels' => $groupedByDate->keys()->values()->all(),
            'man_hours' => $groupedByDate->map(fn ($items) => round((float) $items->sum('total_hours'), 2))->values()->all(),
            'workers' => $groupedByDate->map(fn ($items) => (int) $items->sum('number_of_workers'))->values()->all(),
        ];

        // Grafik 2: Distribusi Man Hours per Kategori (Donut + Legend)
        $categoryColors = [
            '#059669', // GDE
            '#2563eb', // Kodipa
            '#0d9488', // Utility
            '#f59e0b', // Security
            '#7c3aed', // Contractor & Sub-Contractor
            '#ec4899', // Kerja Praktik
            '#64748b', // Lainnya
        ];
        $categoryLabels = [];
        $categoryHours = [];
        $categoryWorkers = [];
        foreach ($categories as $cat) {
            $categoryLabels[] = $cat;
            $categoryHours[] = round((float) $allFiltered->where('category', $cat)->sum('total_hours'), 2);
            $categoryWorkers[] = (int) $allFiltered->where('category', $cat)->sum('number_of_workers');
        }

        $categoryChart = [
            'labels' => $categoryLabels,
            'data' => $categoryHours,
            'workers' => $categoryWorkers,
            'colors' => array_slice($categoryColors, 0, count($categoryLabels)),
        ];

        // Grafik 3: Rekap Bulanan Man Hours & Perbandingan per Unit
        $groupedByMonth = $allFiltered
            ->sortBy(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m') : '')
            ->groupBy(fn (ManHour $m) => $m->work_date ? $m->work_date->translatedFormat('M Y') : '-');

        $monthlyChart = [
            'labels' => $groupedByMonth->keys()->values()->all(),
            'data' => $groupedByMonth->map(fn ($items) => round((float) $items->sum('total_hours'), 2))->values()->all(),
        ];

        $unitChart = [
            'labels' => collect($unitRecap['rows'])->pluck('unit_name')->values()->all(),
            'man_hours' => collect($unitRecap['rows'])->pluck('period_man_hours')->values()->all(),
            'workers' => collect($unitRecap['rows'])->pluck('period_workers')->values()->all(),
            'is_new' => collect($unitRecap['rows'])->pluck('is_new_unit')->values()->all(),
        ];

        // Format Period Label & Last Input Time for Header Cards
        $minDate = $allFiltered->min('work_date') ?? $allGlobal->min('work_date');
        $maxDate = $allFiltered->max('work_date') ?? $allGlobal->max('work_date');

        $lastUpdatedWorkforce = Workforce::latest('updated_at')->first();
        $lastInputTimeFormatted = $lastUpdatedWorkforce && $lastUpdatedWorkforce->updated_at
            ? $lastUpdatedWorkforce->updated_at->format('d M Y, H:i')
            : now()->format('d M Y, H:i');

        // Rekap Tenaga Kerja Hari Ini / Tanggal Acuan Terbaru (7 Perusahaan)
        $todayWorkforces = Workforce::whereDate('record_date', $refDate->format('Y-m-d'))->get();
        $todayTotalWorkers = (int) $todayWorkforces->sum('worker_count');
        $todayBreakdown = [];
        $targetCompanies = ['GDE', 'KODIPA', 'UTILITY', 'SECURITY', 'JOTRE', 'PMC', 'KP'];

        foreach ($targetCompanies as $cName) {
            $wMatch = $todayWorkforces->firstWhere('unit', $cName);
            $wCnt = $wMatch ? (int) $wMatch->worker_count : 0;
            $pct = $todayTotalWorkers > 0 ? round(($wCnt / $todayTotalWorkers) * 100, 1) : 0.0;
            $todayBreakdown[] = [
                'unit' => $cName,
                'count' => $wCnt,
                'percentage' => $pct,
                'hours_per_worker' => $wMatch ? (float) $wMatch->hours_per_worker : 8.0,
                'man_hours' => $wMatch ? (float) $wMatch->calculated_man_hours : 0.0,
            ];
        }

        // Riwayat Data Manhour Harian untuk Tabel (Grouped by Date)
        $groupedHistory = Workforce::orderBy('record_date', 'desc')->get();
        if ($filterStart && $filterEnd) {
            $groupedHistory = $groupedHistory->filter(fn ($w) => $w->record_date && $w->record_date->between($filterStart, $filterEnd));
        } elseif ($filterStart) {
            $groupedHistory = $groupedHistory->filter(fn ($w) => $w->record_date && $w->record_date->gte($filterStart));
        } elseif ($filterEnd) {
            $groupedHistory = $groupedHistory->filter(fn ($w) => $w->record_date && $w->record_date->lte($filterEnd));
        }
        if ($request->filled('unit') && $request->input('unit') !== 'Semua') {
            $uFilter = $request->input('unit');
            $groupedHistory = $groupedHistory->filter(fn ($w) => strcasecmp((string)$w->unit, $uFilter) === 0);
        }

        $groupedDates = $groupedHistory->groupBy(fn ($w) => $w->record_date ? $w->record_date->format('Y-m-d') : '');
        $historyTableRows = [];
        foreach ($groupedDates as $dStr => $wList) {
            if (!$dStr) continue;
            $cDate = Carbon::parse($dStr);
            $totW = (int) $wList->sum('worker_count');
            $totMh = (float) $wList->sum(fn ($w) => $w->calculated_man_hours);
            // Sesuai rumus revisi: Rata-rata Jam Kerja / Orang = Total Manhour ÷ Total Pekerja
            $avgH = $totW > 0 ? round($totMh / $totW, 1) : 0.0;

            $unitsData = [];
            foreach ($targetCompanies as $cName) {
                $unitsData[$cName] = $wList->firstWhere('unit', $cName)?->worker_count ?? 0;
            }

            $historyTableRows[] = [
                'date' => $dStr,
                'date_formatted' => $cDate->format('d/m/Y'),
                'date_long' => $cDate->translatedFormat('d F Y'),
                'units' => $unitsData,
                'total_workers' => $totW,
                'hours_per_worker' => $avgH,
                'total_man_hours' => $totMh,
                'status' => 'Sudah Diinput',
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'man_hours' => $allFiltered->values(),
                'pagination' => [
                    'current_page' => $manHours->currentPage(),
                    'last_page' => $manHours->lastPage(),
                    'per_page' => $manHours->perPage(),
                    'total' => $manHours->total(),
                ],
                'stats' => $stats,
                'formattedPeriodLabel' => $formattedPeriodLabel,
                'lastInputTimeFormatted' => $lastInputTimeFormatted,
                'todayBreakdown' => $todayBreakdown,
                'todayTotalWorkers' => $todayTotalWorkers,
                'historyTableRows' => $historyTableRows,
                'unitExpansionInfo' => $unitExpansionInfo,
                'unit_expansion' => $unitExpansionInfo,
                'unitRecap' => $unitRecap,
                'unit_recap' => $unitRecap,
                'dailyRecap' => $dailyRecap,
                'daily_recap' => $dailyRecap,
                'trendChart' => $trendChart,
                'categoryChart' => $categoryChart,
                'monthlyChart' => $monthlyChart,
                'unitChart' => $unitChart,
            ]);
        }

        return view('data.man-hours.index', compact(
            'manHours',
            'allFiltered',
            'stats',
            'categories',
            'units',
            'operationalUnits',
            'unitExpansionInfo',
            'unitRecap',
            'dailyRecap',
            'trendChart',
            'categoryChart',
            'monthlyChart',
            'unitChart',
            'formattedPeriodLabel',
            'lastInputTimeFormatted',
            'todayBreakdown',
            'todayTotalWorkers',
            'historyTableRows'
        ));
    }

    /**
     * Bangun rekap perhitungan per Unit / Area Operasi.
     */
    private function buildUnitRecap($operationalUnits, $allFiltered, ?Carbon $latestDate): array
    {
        $rows = [];
        $unitNamesMap = [];

        foreach ($operationalUnits as $opUnit) {
            $unitNamesMap[$opUnit->name] = $opUnit;
        }

        // Sertakan juga unit dari rekaman ManHour jika ada nama di luar master
        $extraUnits = $allFiltered->pluck('unit')->filter()->unique()->diff(array_keys($unitNamesMap));

        $allUnitList = collect($operationalUnits)->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'is_active' => (bool) $u->is_active,
            'is_new_unit' => !$u->is_initial,
            'created_at_formatted' => $u->created_at ? $u->created_at->format('d/m/Y') : null,
        ]);

        foreach ($extraUnits as $extraName) {
            $allUnitList->push([
                'id' => null,
                'name' => $extraName,
                'is_active' => true,
                'is_new_unit' => false,
                'created_at_formatted' => null,
            ]);
        }

        foreach ($allUnitList as $uMeta) {
            $uName = $uMeta['name'];
            $unitRows = $allFiltered->filter(fn (ManHour $m) => $m->unit === $uName);

            // Jika belum aktif dan tidak ada data pada periode ini, lewati agar tabel rapi
            if (!$uMeta['is_active'] && $unitRows->isEmpty()) {
                continue;
            }

            $latestUnitRows = $latestDate
                ? $unitRows->filter(fn (ManHour $m) => $m->work_date && $m->work_date->isSameDay($latestDate))
                : collect();

            $latestWorkers = (int) $latestUnitRows->sum('number_of_workers');
            $latestHoursPerWorker = $latestUnitRows->isNotEmpty()
                ? round((float) $latestUnitRows->avg('hours_per_worker'), 2)
                : ($unitRows->isNotEmpty() ? round((float) $unitRows->avg('hours_per_worker'), 2) : 8.0);
            $latestManHours = round((float) $latestUnitRows->sum('total_hours'), 2);

            $periodWorkers = (int) $unitRows->sum('number_of_workers');
            $rawPeriodManHours = round((float) $unitRows->sum('total_hours'), 2);
            $daysRecorded = $unitRows->map(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m-d') : null)->filter()->unique()->count();
            $firstRecordedDate = $unitRows->min('work_date');

            $opUnit = $unitNamesMap[$uName] ?? null;
            $adjustment = (float) ($opUnit ? $opUnit->accumulated_hours_adjustment : 0.0);
            $hasManualAdjustment = ($opUnit && (abs($adjustment) > 0.001 || $opUnit->manual_accumulated_hours !== null));
            $periodManHours = max(0.0, round($rawPeriodManHours + $adjustment, 2));

            $statusNotes = 'Aktif dalam perhitungan';
            if ($hasManualAdjustment) {
                $sign = $adjustment >= 0 ? '+' : '';
                $statusNotes = "Koreksi manual ({$sign}" . number_format($adjustment, 1, ',', '.') . " Jam) oleh " . ($opUnit->adjusted_by ?: 'User');
            } elseif ($uMeta['is_new_unit']) {
                $firstDateStr = $firstRecordedDate
                    ? Carbon::parse($firstRecordedDate)->format('d/m/Y')
                    : ($uMeta['created_at_formatted'] ?: '-');
                $statusNotes = $unitRows->isNotEmpty()
                    ? "Unit baru — disertakan mulai {$firstDateStr} ({$daysRecorded} hari tercatat)"
                    : "Unit baru ditambahkan ({$firstDateStr}) — belum ada catatan pekerja pada periode ini";
            } elseif (!$uMeta['is_active']) {
                $statusNotes = 'Unit Nonaktif (Data historis tetap tersimpan)';
            } elseif ($unitRows->isEmpty()) {
                $statusNotes = 'Belum tercatat pada periode ini (0 Jam)';
            }

            $rows[] = [
                'unit_id' => $uMeta['id'] ?? ($opUnit?->id),
                'unit_name' => $uName,
                'is_active' => $uMeta['is_active'],
                'is_new_unit' => $uMeta['is_new_unit'],
                'latest_workers' => $latestWorkers,
                'hours_per_worker' => $latestHoursPerWorker,
                'latest_man_hours' => $latestManHours,
                'period_workers' => $periodWorkers,
                'period_man_hours' => $periodManHours,
                'raw_man_hours' => $rawPeriodManHours,
                'has_manual_adjustment' => $hasManualAdjustment,
                'adjustment_delta' => $adjustment,
                'manual_accumulated_hours' => $opUnit?->manual_accumulated_hours !== null ? (float) $opUnit->manual_accumulated_hours : null,
                'adjustment_notes' => $opUnit?->adjustment_notes,
                'adjusted_by' => $opUnit?->adjusted_by,
                'last_adjusted_at' => $opUnit?->last_adjusted_at ? Carbon::parse($opUnit->last_adjusted_at)->format('d/m/Y H:i') : null,
                'days_recorded' => $daysRecorded,
                'first_recorded_date' => $firstRecordedDate ? Carbon::parse($firstRecordedDate)->format('d/m/Y') : '-',
                'status_notes' => $statusNotes,
            ];
        }

        return [
            'reference_date' => $latestDate ? $latestDate->format('d/m/Y') : '-',
            'rows' => $rows,
            'totals' => [
                'latest_workers' => collect($rows)->sum('latest_workers'),
                'latest_man_hours' => round((float) collect($rows)->sum('latest_man_hours'), 2),
                'period_workers' => collect($rows)->sum('period_workers'),
                'period_man_hours' => round((float) collect($rows)->sum('period_man_hours'), 2),
            ],
        ];
    }

    /**
     * Bangun Tabel Riwayat Harian Man Hours dengan perhitungan akumulasi harian & kumulatif berjalan.
     */
    private function buildDailyHistoryRecap($allFiltered, $operationalUnits): array
    {
        if ($allFiltered->isEmpty()) {
            return [];
        }

        $newUnitNames = $operationalUnits->where('is_initial', false)->pluck('name')->all();

        // Urutkan kronologis dari tanggal terlama ke terbaru terlebih dahulu untuk menghitung running cumulative dengan akurat
        $groupedChronological = $allFiltered
            ->sortBy(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m-d') : '')
            ->groupBy(fn (ManHour $m) => $m->work_date ? $m->work_date->format('Y-m-d') : '');

        $runningCumulativeHours = 0.0;
        $dailyList = [];

        foreach ($groupedChronological as $dateKey => $items) {
            if (!$dateKey) {
                continue;
            }

            $carbonDate = Carbon::parse($dateKey);
            $dailyWorkers = (int) $items->sum('number_of_workers');
            $dailyHours = round((float) $items->sum('total_hours'), 2);
            $runningCumulativeHours = round($runningCumulativeHours + $dailyHours, 2);

            $unitBreakdown = $items->groupBy('unit')->map(function ($uItems, $uName) use ($newUnitNames) {
                return [
                    'unit' => $uName ?: '-',
                    'workers' => (int) $uItems->sum('number_of_workers'),
                    'hours_per_worker' => round((float) $uItems->avg('hours_per_worker'), 2),
                    'man_hours' => round((float) $uItems->sum('total_hours'), 2),
                    'is_new_unit' => in_array($uName, $newUnitNames, true),
                ];
            })->values()->all();

            $activeUnitsOnDate = count($unitBreakdown);
            $newUnitsOnDate = collect($unitBreakdown)->where('is_new_unit', true)->pluck('unit')->all();

            $dailyList[] = [
                'work_date' => $dateKey,
                'work_date_formatted' => $carbonDate->format('d/m/Y'),
                'work_date_long' => $carbonDate->translatedFormat('d F Y'),
                'total_workers' => $dailyWorkers,
                'daily_man_hours' => $dailyHours,
                'running_cumulative_man_hours' => $runningCumulativeHours,
                'units_count' => $activeUnitsOnDate,
                'new_units_included' => $newUnitsOnDate,
                'units' => $unitBreakdown,
            ];
        }

        // Balik urutan agar tanggal terbaru tampil di paling atas (work_date DESC)
        return array_reverse($dailyList);
    }

    /**
     * Tampilkan detail perhitungan Man Hours beserta informasi sumber Workforce-nya.
     */
    public function show(Request $request, $id): JsonResponse|View|RedirectResponse
    {
        $manHour = ManHour::with('workforce')->find($id);

        if (!$manHour) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Man Hours tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('man-hours.index')->with('error', 'Data Man Hours tidak ditemukan.');
        }

        $activities = ActivityLog::where(function ($q) use ($manHour) {
            $q->where(function ($q1) use ($manHour) {
                $q1->where('module', 'Man Hours')->where('record_id', $manHour->id);
            });
            if ($manHour->workforce_id) {
                $q->orWhere(function ($q2) use ($manHour) {
                    $q2->where('module', 'Workforce')->where('record_id', $manHour->workforce_id);
                });
            }
        })->latest('id')->take(10)->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'man_hour' => $manHour,
                'workforce' => $manHour->workforce,
                'formula_text' => "{$manHour->number_of_workers} Pekerja × " . number_format((float) $manHour->hours_per_worker, 2) . " Jam/Orang = " . number_format((float) $manHour->total_hours, 2) . " Jam",
                'activities' => $activities,
            ]);
        }

        return view('data.man-hours.show', compact('manHour', 'activities'));
    }

    /**
     * Sesuaikan parameter jam kerja per orang (hours_per_worker) atau catatan pada Man Hours.
     * Jumlah pekerja tetap diambil dari sumber Workforce agar tidak terjadi ketidaksesuaian.
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $manHour = ManHour::with('workforce')->find($id);

        if (!$manHour) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Man Hours tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('man-hours.index')->with('error', 'Data Man Hours tidak ditemukan.');
        }

        $validated = $request->validate([
            'hours_per_worker' => 'required|numeric|min:0|max:24',
            'notes' => 'nullable|string',
        ], [
            'hours_per_worker.required' => 'Jam kerja per orang wajib diisi.',
            'hours_per_worker.numeric' => 'Jam kerja per orang harus berupa angka.',
            'hours_per_worker.min' => 'Jam kerja per orang tidak boleh bernilai negatif.',
            'hours_per_worker.max' => 'Jam kerja per orang maksimal 24 jam per hari.',
        ]);

        $oldHours = (float) $manHour->hours_per_worker;
        $oldTotal = (float) $manHour->total_hours;
        $newHours = (float) $validated['hours_per_worker'];

        // Jumlah pekerja diambil langsung dari Workforce sumber
        $workers = $manHour->workforce ? (int) $manHour->workforce->worker_count : (int) $manHour->number_of_workers;
        $newTotal = round($workers * $newHours, 2);

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            if ($manHour->workforce) {
                $manHour->workforce->update([
                    'hours_per_worker' => $newHours,
                    'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $manHour->workforce->notes,
                ]);
                $manHour->refresh();
            } else {
                $manHour->update([
                    'number_of_workers' => $workers,
                    'hours_per_worker' => $newHours,
                    'total_hours' => $newTotal,
                    'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $manHour->notes,
                ]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Man Hours',
                'action' => 'UBAH',
                'record_type' => ManHour::class,
                'record_id' => $manHour->id,
                'description' => "Menyesuaikan parameter jam kerja {$manHour->category} (" . ($manHour->work_date ? $manHour->work_date->format('d/m/Y') : '-') . "): {$workers} pekerja × {$newHours} jam = {$manHour->total_hours} Man Hours",
                'changes' => [
                    'before' => [
                        'hours_per_worker' => $oldHours,
                        'total_hours' => $oldTotal,
                    ],
                    'after' => [
                        'hours_per_worker' => (float) $manHour->hours_per_worker,
                        'total_hours' => (float) $manHour->total_hours,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parameter jam kerja berhasil disesuaikan dan Man Hours telah dihitung ulang.',
                    'man_hour' => $manHour->fresh('workforce'),
                ]);
            }

            return redirect()->route('man-hours.index')
                ->with('success', 'Parameter jam kerja berhasil disesuaikan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyesuaikan Man Hours: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menyesuaikan Man Hours: ' . $e->getMessage());
        }
    }


    /**
     * Koreksi manual Total Akumulasi Man Hours Unit oleh pengguna yang berwenang.
     */
    public function updateUnitAccumulatedHours(Request $request, string|int $unit): JsonResponse|RedirectResponse
    {
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk melakukan koreksi akumulasi.',
                ], 401);
            }
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'accumulated_hours' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:255'],
            'reset' => ['nullable', 'boolean'],
        ], [
            'accumulated_hours.required' => 'Nilai Total Akumulasi Man Hours Unit wajib diisi.',
            'accumulated_hours.numeric' => 'Nilai akumulasi harus berupa angka yang valid.',
            'accumulated_hours.min' => 'Nilai akumulasi tidak boleh bernilai negatif.',
            'accumulated_hours.max' => 'Nilai akumulasi melebihi batas wajar.',
        ]);

        // Cari Unit berdasarkan ID atau Nama
        $opUnit = is_numeric($unit) ? OperationalUnit::find((int) $unit) : null;
        if (!$opUnit) {
            $opUnit = OperationalUnit::where('name', (string) $unit)->first();
        }

        // Jika unit belum terdaftar di master unit, buatkan otomatis
        if (!$opUnit) {
            $maxSort = (int) OperationalUnit::max('sort_order') + 1;
            $opUnit = OperationalUnit::create([
                'name' => (string) $unit,
                'code' => 'UNIT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $unit), 0, 6)),
                'default_hours' => 8.0,
                'is_active' => true,
                'is_initial' => false,
                'sort_order' => $maxSort,
                'created_by' => Auth::user()?->name ?? 'Administrator Dieng',
            ]);
        }

        // Hitung total raw jam kerja di DB untuk unit ini
        $rawTotalHours = round((float) ManHour::where(function ($q) use ($opUnit) {
            $q->where('operational_unit_id', $opUnit->id)
              ->orWhere('unit', $opUnit->name);
        })->sum('total_hours'), 2);

        $isReset = $request->boolean('reset');
        $oldAccumulated = (float) ($opUnit->manual_accumulated_hours !== null
            ? $opUnit->manual_accumulated_hours
            : ($rawTotalHours + (float) $opUnit->accumulated_hours_adjustment));

        if ($isReset) {
            $newAccumulated = $rawTotalHours;
            $adjustmentDelta = 0.00;
            $notes = $validated['notes'] ?? 'Direset ke perhitungan otomatis sistem';
        } else {
            $newAccumulated = round((float) $validated['accumulated_hours'], 2);
            $adjustmentDelta = round($newAccumulated - $rawTotalHours, 2);
            $notes = $validated['notes'] ?? 'Koreksi manual Total Akumulasi Man Hours Unit';
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $opUnit->update([
                'manual_accumulated_hours' => $isReset ? null : $newAccumulated,
                'accumulated_hours_adjustment' => $adjustmentDelta,
                'accumulated_hours_baseline' => $rawTotalHours,
                'adjustment_notes' => $notes,
                'last_adjusted_at' => now(),
                'adjusted_by' => $userName,
            ]);

            UnitManHourAdjustment::create([
                'operational_unit_id' => $opUnit->id,
                'unit_name' => $opUnit->name,
                'previous_accumulated_hours' => $oldAccumulated,
                'new_accumulated_hours' => $newAccumulated,
                'adjustment_delta' => $adjustmentDelta,
                'notes' => $notes,
                'adjusted_by' => $userName,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Man Hours',
                'action' => 'UBAH',
                'record_type' => OperationalUnit::class,
                'record_id' => $opUnit->id,
                'description' => "Menyesuaikan Total Akumulasi Man Hours Unit {$opUnit->name} dari {$oldAccumulated} menjadi {$newAccumulated} Jam (koreksi manual: " . ($adjustmentDelta >= 0 ? '+' : '') . "{$adjustmentDelta} Jam)",
                'changes' => [
                    'before' => [
                        'accumulated_hours' => $oldAccumulated,
                    ],
                    'after' => [
                        'accumulated_hours' => $newAccumulated,
                        'manual_accumulated_hours' => $isReset ? null : $newAccumulated,
                        'accumulated_hours_adjustment' => $adjustmentDelta,
                        'raw_system_hours' => $rawTotalHours,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Total Akumulasi Man Hours Unit {$opUnit->name} berhasil diperbarui menjadi " . number_format($newAccumulated, 1, ',', '.') . " Jam.",
                    'unit' => [
                        'id' => $opUnit->id,
                        'name' => $opUnit->name,
                        'accumulated_hours' => $newAccumulated,
                        'raw_hours' => $rawTotalHours,
                        'adjustment_delta' => $adjustmentDelta,
                        'has_manual_adjustment' => abs($adjustmentDelta) > 0.001 || (!$isReset && $opUnit->manual_accumulated_hours !== null),
                        'adjusted_by' => $userName,
                        'last_adjusted_at' => now()->format('d/m/Y H:i'),
                        'notes' => $notes,
                    ],
                ]);
            }

            return redirect()->route('man-hours.index')
                ->with('success', "Total Akumulasi Man Hours Unit {$opUnit->name} berhasil disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan penyesuaian: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan penyesuaian: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor data Man Hours ke format PDF.
     */
    public function export(Request $request): Response
    {
        OperationalUnit::ensureDefaultsSeeded();
        ManHour::syncAllFromWorkforce();

        [$filterStart, $filterEnd, $formattedPeriodLabel, $periodType] = $this->resolvePeriodFilter($request);

        $query = ManHour::with(['workforce', 'operationalUnit'])->whereHas('workforce');

        if ($filterStart && $filterEnd) {
            $query->whereBetween('work_date', [$filterStart, $filterEnd]);
        } elseif ($filterStart) {
            $query->whereDate('work_date', '>=', $filterStart);
        } elseif ($filterEnd) {
            $query->whereDate('work_date', '<=', $filterEnd);
        }

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('unit') && $request->input('unit') !== 'Semua') {
            $query->where('unit', $request->input('unit'));
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('category', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $records = $query->orderBy('work_date', 'desc')->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('exports.man-hours-pdf', [
            'reportTitle' => 'LAPORAN REKAPITULASI JAM KERJA ORANG (MAN HOURS)',
            'moduleName' => 'Man Hours',
            'filterInfo' => [
                'Unit' => $request->input('unit', 'Semua Unit'),
                'Kategori' => $request->input('category', 'Semua Kategori'),
                'Periode' => $formattedPeriodLabel,
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Man-Hours-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Ekspor data Man Hours ke format Excel (CSV / Spreadsheet).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        OperationalUnit::ensureDefaultsSeeded();
        ManHour::syncAllFromWorkforce();

        [$filterStart, $filterEnd, $formattedPeriodLabel, $periodType] = $this->resolvePeriodFilter($request);

        $query = Workforce::orderBy('record_date', 'desc');

        if ($filterStart && $filterEnd) {
            $query->whereBetween('record_date', [$filterStart, $filterEnd]);
        } elseif ($filterStart) {
            $query->whereDate('record_date', '>=', $filterStart);
        } elseif ($filterEnd) {
            $query->whereDate('record_date', '<=', $filterEnd);
        }

        if ($request->filled('unit') && $request->input('unit') !== 'Semua') {
            $query->where('unit', $request->input('unit'));
        }

        $allWorkforces = $query->get();
        $grouped = $allWorkforces->groupBy(fn ($w) => $w->record_date ? $w->record_date->format('Y-m-d') : '');

        $fileName = 'Laporan-Manhour-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($grouped, $formattedPeriodLabel) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['LAPORAN DATA MANHOUR - PT GEO DIPA ENERGI (PERSERO) UNIT DIENG'], ';');
            fputcsv($handle, ['Periode Laporan:', $formattedPeriodLabel], ';');
            fputcsv($handle, ['Diekspor pada:', now()->translatedFormat('d F Y, H:i:s')], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, [
                'No', 'Tanggal', 'GDE', 'KODIPA', 'UTILITY', 'SECURITY', 'JOTRE', 'PMC', 'KP',
                'Total Pekerja', 'Jam Kerja / Orang', 'Total Manhour (Jam)', 'Status'
            ], ';');

            $no = 1;
            foreach ($grouped as $dateStr => $items) {
                if (!$dateStr) continue;
                $gde = $items->firstWhere('unit', 'GDE')?->worker_count ?? 0;
                $kodipa = $items->firstWhere('unit', 'KODIPA')?->worker_count ?? 0;
                $utility = $items->firstWhere('unit', 'UTILITY')?->worker_count ?? 0;
                $security = $items->firstWhere('unit', 'SECURITY')?->worker_count ?? 0;
                $jotre = $items->firstWhere('unit', 'JOTRE')?->worker_count ?? 0;
                $pmc = $items->firstWhere('unit', 'PMC')?->worker_count ?? 0;
                $kp = $items->firstWhere('unit', 'KP')?->worker_count ?? 0;

                $totalW = (int) $items->sum('worker_count');
                $totalMh = (float) $items->sum(fn ($i) => $i->calculated_man_hours);
                // Rumus revisi: Rata-rata Jam Kerja / Orang = Total Manhour ÷ Total Pekerja
                $hoursPerW = $totalW > 0 ? round($totalMh / $totalW, 1) : 0.0;

                fputcsv($handle, [
                    $no++,
                    Carbon::parse($dateStr)->format('d/m/Y'),
                    $gde,
                    $kodipa,
                    $utility,
                    $security,
                    $jotre,
                    $pmc,
                    $kp,
                    $totalW,
                    $hoursPerW,
                    $totalMh,
                    'Sudah Diinput'
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Hapus seluruh data Manhour / Workforce pada tanggal tertentu.
     */
    public function destroyDate(Request $request, string $date): JsonResponse|RedirectResponse
    {
        $parsedDate = Carbon::parse($date)->format('Y-m-d');
        $records = Workforce::whereDate('record_date', $parsedDate)->get();

        if ($records->isEmpty()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data pencatatan pada tanggal tersebut.',
                ], 404);
            }
            return back()->with('error', 'Tidak ada data pada tanggal tersebut.');
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';
        $count = $records->count();

        DB::beginTransaction();
        try {
            foreach ($records as $w) {
                ManHour::where('workforce_id', $w->id)->delete();
                $w->delete();
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Man Hours',
                'action' => 'HAPUS',
                'record_type' => Workforce::class,
                'record_id' => null,
                'description' => "Menghapus {$count} data Manhour pada tanggal " . Carbon::parse($parsedDate)->format('d/m/Y'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Manhour pada tanggal ' . Carbon::parse($parsedDate)->format('d/m/Y') . ' berhasil dihapus.',
                ]);
            }

            return redirect()->route('man-hours.index')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}


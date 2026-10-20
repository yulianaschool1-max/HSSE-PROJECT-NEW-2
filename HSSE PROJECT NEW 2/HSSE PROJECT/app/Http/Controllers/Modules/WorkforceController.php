<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ManHour;
use App\Models\OperationalUnit;
use App\Models\Workforce;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkforceController extends Controller
{
    /**
     * Tampilkan halaman Workforce beserta Highlight Tanggal Terbaru, Master Unit, kartu statistik, grafik, dan tabel riwayat.
     */
    public function index(Request $request): View|JsonResponse
    {
        OperationalUnit::ensureDefaultsSeeded();
        ManHour::syncAllFromWorkforce();

        $operationalUnits = OperationalUnit::ordered()->get();
        $activeOperationalUnits = $operationalUnits->where('is_active', true)->values();

        $query = Workforce::with(['manHour', 'operationalUnit']);

        // Filter: Rentang Tanggal atau Tanggal Tunggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('record_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('record_date', '>=', $request->input('start_date'));
        } elseif ($request->filled('end_date')) {
            $query->whereDate('record_date', '<=', $request->input('end_date'));
        } elseif ($request->filled('date')) {
            $query->whereDate('record_date', $request->input('date'));
        }

        // Filter: Kategori / Komponen Pekerja
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Unit / Area Operasi
        if ($request->filled('unit')) {
            $query->where('unit', $request->input('unit'));
        }

        // Filter: Periode Tahun / Bulan
        if ($request->filled('period')) {
            $period = (string) $request->input('period');
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                [$y, $m] = explode('-', $period);
                $query->whereYear('record_date', (int) $y)
                      ->whereMonth('record_date', (int) $m);
            } elseif (preg_match('/^\d{4}$/', $period)) {
                $query->whereYear('record_date', (int) $period);
            }
        } elseif ($request->filled('year')) {
            $query->whereYear('record_date', (int) $request->input('year'));
        }

        // Filter: Pencarian teks
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('category', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('department', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        // Sorting (Default: record_date DESC, id DESC)
        $sort = $request->input('sort', 'date-desc');
        if ($sort === 'date-asc') {
            $query->orderBy('record_date', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'workers-desc') {
            $query->orderBy('worker_count', 'desc')->orderBy('record_date', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'workers-asc') {
            $query->orderBy('worker_count', 'asc')->orderBy('record_date', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'category') {
            $query->orderBy('category', 'asc')->orderBy('record_date', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'unit') {
            $query->orderBy('unit', 'asc')->orderBy('record_date', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('record_date', 'desc')->orderBy('id', 'desc');
        }

        // Ambil seluruh koleksi terfilter untuk perhitungan kartu statistik & grafik akurat
        $allFiltered = (clone $query)->get();

        // Pagination untuk tabel riwayat
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 15;
        $workforces = $query->paginate($perPage)->withQueryString();

        // Highlight Workforce Berdasarkan Tanggal Terbaru yang Ada di Database
        $latestHighlight = $this->buildLatestDateHighlight($activeOperationalUnits);

        // Rekap Riwayat Harian (dikelompokkan per record_date DESC)
        $dailyHistory = $this->buildDailyHistoryRecap($allFiltered, $operationalUnits);

        // Kartu Statistik Ringkasan (Murni dari database, 0 jika kosong)
        $totalWorkers = (int) $allFiltered->sum('worker_count');
        $totalRecords = $allFiltered->count();
        $coreWorkers = (int) $allFiltered->whereIn('category', ['GDE', 'Kodipa', 'Utility'])->sum('worker_count');
        $partnerWorkers = (int) $allFiltered->whereIn('category', ['Security', 'Contractor & Sub-Contractor', 'Kerja Praktik'])->sum('worker_count');
        $totalManHoursSource = round((float) $allFiltered->sum(fn (Workforce $w) => $w->calculated_man_hours), 2);
        $activeCategoriesCount = $allFiltered->where('worker_count', '>', 0)->pluck('category')->unique()->count();
        $activeUnitsRecordedCount = $allFiltered->where('worker_count', '>', 0)->pluck('unit')->unique()->count();

        // Tanggal pencatatan terbaru pada data yang terfilter atau global
        $latestRecordDate = $allFiltered->max('record_date') ?? Workforce::max('record_date');
        $todayWorkersCount = $latestRecordDate 
            ? (int) $allFiltered->filter(fn (Workforce $w) => $w->record_date && $w->record_date->isSameDay(Carbon::parse($latestRecordDate)))->sum('worker_count')
            : 0;

        $stats = [
            'total_workers' => $todayWorkersCount,
            'today_workers' => $todayWorkersCount,
            'all_period_workers' => $totalWorkers,
            'total_records' => $totalRecords,
            'core_workers' => $coreWorkers,
            'partner_workers' => $partnerWorkers,
            'total_man_hours' => $totalManHoursSource,
            'active_categories' => $activeCategoriesCount,
            'active_units_recorded' => $activeUnitsRecordedCount,
            'master_active_units' => $activeOperationalUnits->count(),
            'latest_date' => $latestHighlight['formatted_date'],
            'latest_date_workers' => $latestHighlight['total_workers'],
            'latest_date_man_hours' => $latestHighlight['total_man_hours'],
        ];

        // Daftar Kategori Standar + Kategori yang ada di DB
        $dbCategories = Workforce::select('category')->distinct()->pluck('category')->filter()->values()->all();
        $categories = array_values(array_unique(array_merge(Workforce::CATEGORIES, $dbCategories)));

        // Daftar Unit Dinamis: Utamakan unit yang aktif memiliki data di filter, disusul master unit
        $recordedUnits = $allFiltered->pluck('unit')->filter()->unique()->values()->all();
        $masterUnitNames = $operationalUnits->pluck('name')->all();
        $units = array_values(array_unique(array_merge($recordedUnits, $masterUnitNames)));

        // Grafik 1: Tren Jumlah Pekerja berdasarkan Tanggal
        $groupedByDate = $allFiltered
            ->sortBy(fn (Workforce $w) => $w->record_date ? $w->record_date->format('Y-m-d') : '')
            ->groupBy(fn (Workforce $w) => $w->record_date ? $w->record_date->format('d/m/Y') : '-');

        $trendChart = [
            'labels' => $groupedByDate->keys()->values()->all(),
            'workers' => $groupedByDate->map(fn ($items) => (int) $items->sum('worker_count'))->values()->all(),
            'man_hours' => $groupedByDate->map(fn ($items) => round((float) $items->sum(fn ($i) => $i->calculated_man_hours), 2))->values()->all(),
        ];

        // Grafik 2: Distribusi Jumlah Pekerja per Unit / Area Operasi & Kategori
        $palette = [
            '#059669', '#2563eb', '#0d9488', '#f59e0b',
            '#7c3aed', '#ec4899', '#0284c7', '#dc2626',
            '#4f46e5', '#64748b',
        ];

        // Unit Chart: Distribusi Unit Pekerja (Diurutkan berdasarkan unit yang memiliki pekerja terbesar)
        $unitLabels = [];
        $unitWorkersData = [];
        $unitManHoursData = [];
        $unitColors = [];

        // Hitung dulu per unit
        $unitDataMap = [];
        foreach ($units as $uName) {
            $uSum = (int) $allFiltered->where('unit', $uName)->sum('worker_count');
            $uMh = round((float) $allFiltered->where('unit', $uName)->sum(fn ($i) => $i->calculated_man_hours), 2);
            $unitDataMap[$uName] = [
                'workers' => $uSum,
                'man_hours' => $uMh,
            ];
        }

        // Urutkan unit yang memiliki pekerja terlebih dahulu
        uksort($unitDataMap, function ($a, $b) use ($unitDataMap) {
            if ($unitDataMap[$a]['workers'] === $unitDataMap[$b]['workers']) {
                return 0;
            }
            return ($unitDataMap[$a]['workers'] > $unitDataMap[$b]['workers']) ? -1 : 1;
        });

        $uIdx = 0;
        foreach ($unitDataMap as $uName => $uVal) {
            $unitLabels[] = $uName;
            $unitWorkersData[] = $uVal['workers'];
            $unitManHoursData[] = $uVal['man_hours'];
            $unitColors[] = $palette[$uIdx % count($palette)];
            $uIdx++;
        }

        $unitChart = [
            'labels' => $unitLabels,
            'data' => $unitWorkersData,
            'workers' => $unitWorkersData,
            'man_hours' => $unitManHoursData,
            'colors' => $unitColors,
        ];

        $categoryLabels = [];
        $categoryData = [];
        $categoryManHours = [];
        $categoryColors = [];
        foreach ($categories as $idx => $cat) {
            $catSum = (int) $allFiltered->where('category', $cat)->sum('worker_count');
            $catMh = round((float) $allFiltered->where('category', $cat)->sum(fn ($i) => $i->calculated_man_hours), 2);
            $categoryLabels[] = $cat;
            $categoryData[] = $catSum;
            $categoryManHours[] = $catMh;
            $categoryColors[] = $palette[$idx % count($palette)];
        }

        $categoryChart = [
            'labels' => $categoryLabels,
            'data' => $categoryData,
            'workers' => $categoryData,
            'man_hours' => $categoryManHours,
            'colors' => $categoryColors,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'workforces' => $allFiltered->values(),
                'latest_highlight' => $latestHighlight,
                'daily_history' => $dailyHistory,
                'operational_units' => $operationalUnits,
                'active_operational_units' => $activeOperationalUnits,
                'pagination' => [
                    'current_page' => $workforces->currentPage(),
                    'last_page' => $workforces->lastPage(),
                    'per_page' => $workforces->perPage(),
                    'total' => $workforces->total(),
                ],
                'stats' => $stats,
                'trendChart' => $trendChart,
                'unitChart' => $unitChart,
                'categoryChart' => $categoryChart,
            ]);
        }

        return view('data.workforce.index', compact(
            'workforces',
            'allFiltered',
            'latestHighlight',
            'dailyHistory',
            'operationalUnits',
            'activeOperationalUnits',
            'stats',
            'categories',
            'units',
            'trendChart',
            'unitChart',
            'categoryChart'
        ));
    }

    /**
     * Bangun data Highlight Workforce berdasarkan tanggal terbaru (MAX(record_date)) yang benar-benar ada di database.
     */
    protected function buildLatestDateHighlight(Collection $activeOperationalUnits): array
    {
        $latestDateRaw = Workforce::max('record_date');

        if (!$latestDateRaw) {
            return [
                'has_data' => false,
                'record_date' => null,
                'formatted_date' => '-',
                'record_date_formatted' => '-',
                'translated_date' => 'Belum Ada Data',
                'record_date_long' => 'Belum Ada Data',
                'total_workers' => 0,
                'total_man_hours' => 0.0,
                'units_recorded_count' => 0,
                'unit_breakdown' => [],
                'units' => [],
                'records' => [],
            ];
        }

        $latestDate = Carbon::parse($latestDateRaw);
        $latestDateStr = $latestDate->format('Y-m-d');

        $records = Workforce::with('manHour')
            ->whereDate('record_date', $latestDateStr)
            ->orderBy('id', 'asc')
            ->get();

        // Gabungkan seluruh unit aktif dari master + unit historis yang tercatat pada tanggal tersebut
        $unitNamesMap = [];
        foreach ($activeOperationalUnits as $opUnit) {
            $unitNamesMap[$opUnit->name] = [
                'unit_id' => $opUnit->id,
                'unit' => $opUnit->name,
                'unit_name' => $opUnit->name,
                'is_active' => (bool) $opUnit->is_active,
                'is_new_unit' => !$opUnit->is_initial,
                'worker_count' => 0,
                'hours_per_worker' => (float) ($opUnit->default_hours ?: Workforce::DEFAULT_HOURS_PER_WORKER),
                'man_hours' => 0.0,
                'calculated_man_hours' => 0.0,
                'has_record' => false,
                'is_recorded' => false,
                'notes' => null,
            ];
        }

        foreach ($records as $rec) {
            $uName = $rec->unit ?: $rec->category;
            if (!isset($unitNamesMap[$uName])) {
                $unitNamesMap[$uName] = [
                    'unit_id' => $rec->operational_unit_id,
                    'unit' => $uName,
                    'unit_name' => $uName,
                    'is_active' => true,
                    'is_new_unit' => false,
                    'worker_count' => 0,
                    'hours_per_worker' => (float) ($rec->hours_per_worker ?? Workforce::DEFAULT_HOURS_PER_WORKER),
                    'man_hours' => 0.0,
                    'calculated_man_hours' => 0.0,
                    'has_record' => false,
                    'is_recorded' => false,
                    'notes' => null,
                ];
            }

            $unitNamesMap[$uName]['worker_count'] += (int) $rec->worker_count;
            $unitNamesMap[$uName]['hours_per_worker'] = (float) ($rec->hours_per_worker ?? Workforce::DEFAULT_HOURS_PER_WORKER);
            $newMh = round($unitNamesMap[$uName]['man_hours'] + (float) $rec->calculated_man_hours, 2);
            $unitNamesMap[$uName]['man_hours'] = $newMh;
            $unitNamesMap[$uName]['calculated_man_hours'] = $newMh;
            $unitNamesMap[$uName]['has_record'] = true;
            $unitNamesMap[$uName]['is_recorded'] = true;
            if (!empty($rec->notes)) {
                $unitNamesMap[$uName]['notes'] = $rec->notes;
            }
        }

        $unitList = array_values($unitNamesMap);
        $formattedDate = $latestDate->format('d/m/Y');
        $translatedDate = $latestDate->translatedFormat('d F Y');

        return [
            'has_data' => true,
            'record_date' => $latestDateStr,
            'formatted_date' => $formattedDate,
            'record_date_formatted' => $formattedDate,
            'translated_date' => $translatedDate,
            'record_date_long' => $translatedDate,
            'total_workers' => (int) $records->sum('worker_count'),
            'total_man_hours' => round((float) $records->sum(fn (Workforce $w) => $w->calculated_man_hours), 2),
            'units_recorded_count' => $records->pluck('unit')->filter()->unique()->count(),
            'unit_breakdown' => $unitList,
            'units' => $unitList,
            'records' => $records->values()->all(),
        ];
    }

    /**
     * Bangun rekap riwayat harian Workforce per tanggal (record_date DESC).
     */
    protected function buildDailyHistoryRecap(Collection $records, Collection $operationalUnits): array
    {
        if ($records->isEmpty()) {
            return [];
        }

        $grouped = $records
            ->sortByDesc(fn (Workforce $w) => ($w->record_date ? $w->record_date->format('Y-m-d') : '') . '-' . str_pad((string) $w->id, 10, '0', STR_PAD_LEFT))
            ->groupBy(fn (Workforce $w) => $w->record_date ? $w->record_date->format('Y-m-d') : '-');

        $dailyList = [];
        foreach ($grouped as $dateStr => $items) {
            $dateObj = $dateStr !== '-' ? Carbon::parse($dateStr) : null;

            $unitBreakdown = [];
            foreach ($items as $item) {
                $uName = $item->unit ?: $item->category;
                if (!isset($unitBreakdown[$uName])) {
                    $unitBreakdown[$uName] = [
                        'unit' => $uName,
                        'unit_name' => $uName,
                        'worker_count' => 0,
                        'man_hours' => 0.0,
                        'calculated_man_hours' => 0.0,
                    ];
                }
                $unitBreakdown[$uName]['worker_count'] += (int) $item->worker_count;
                $newMh = round($unitBreakdown[$uName]['man_hours'] + (float) $item->calculated_man_hours, 2);
                $unitBreakdown[$uName]['man_hours'] = $newMh;
                $unitBreakdown[$uName]['calculated_man_hours'] = $newMh;
            }

            $notes = $items->pluck('notes')->filter()->unique()->implode('; ');
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : '-';
            $translatedDate = $dateObj ? $dateObj->translatedFormat('d F Y') : '-';
            $unitValues = array_values($unitBreakdown);

            $dailyList[] = [
                'record_date' => $dateStr,
                'formatted_date' => $formattedDate,
                'record_date_formatted' => $formattedDate,
                'translated_date' => $translatedDate,
                'record_date_long' => $translatedDate,
                'total_workers' => (int) $items->sum('worker_count'),
                'total_man_hours' => round((float) $items->sum(fn (Workforce $w) => $w->calculated_man_hours), 2),
                'units_count' => count($unitBreakdown),
                'unit_breakdown' => $unitValues,
                'units' => $unitValues,
                'notes' => $notes ?: null,
            ];
        }

        return $dailyList;
    }

    /**
     * Ambil data Workforce seluruh unit aktif pada tanggal tertentu untuk form input dinamis.
     */
    public function getByDate(Request $request): JsonResponse
    {
        OperationalUnit::ensureDefaultsSeeded();

        $recordDate = $request->input('record_date', date('Y-m-d'));
        $activeUnits = OperationalUnit::active()->get();

        $existingRecords = Workforce::whereDate('record_date', $recordDate)
            ->orderBy('id', 'asc')
            ->get();

        $rows = [];
        foreach ($activeUnits as $unit) {
            $matched = $existingRecords->first(function (Workforce $w) use ($unit) {
                return (int) $w->operational_unit_id === (int) $unit->id
                    || strcasecmp((string) $w->unit, $unit->name) === 0;
            });

            $rows[] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'code' => $unit->code,
                'is_new_unit' => !$unit->is_initial,
                'workforce_id' => $matched?->id,
                'worker_count' => $matched ? (int) $matched->worker_count : 0,
                'hours_per_worker' => $matched ? (float) $matched->hours_per_worker : (float) ($unit->default_hours ?: Workforce::DEFAULT_HOURS_PER_WORKER),
                'notes' => $matched?->notes ?? '',
                'has_existing' => $matched !== null,
            ];
        }

        return response()->json([
            'success' => true,
            'record_date' => $recordDate,
            'formatted_date' => Carbon::parse($recordDate)->format('d/m/Y'),
            'has_existing_data' => $existingRecords->isNotEmpty(),
            'total_existing_workers' => (int) $existingRecords->sum('worker_count'),
            'units' => $rows,
        ]);
    }

    /**
     * Simpan input Workforce untuk seluruh Unit / Area Operasi aktif pada satu tanggal sekaligus (Batch Input).
     */
    public function storeBatch(Request $request): JsonResponse|RedirectResponse
    {
        OperationalUnit::ensureDefaultsSeeded();

        $rawUnits = $request->input('units') ?? $request->input('entries');
        if (!is_array($rawUnits) || empty($rawUnits)) {
            $msg = 'Data unit/area operasi wajib diisi.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['units' => [$msg]],
                ], 422);
            }
            return back()->withInput()->with('error', $msg);
        }

        // Normalisasi format array units agar mendukung indexed array maupun associative array keyed by unit_id / unit_name
        $normalizedUnits = [];
        foreach ($rawUnits as $key => $item) {
            if (is_array($item)) {
                $explicitUnitName = $item['unit'] ?? $item['unit_name'] ?? (!is_numeric($key) ? (string) $key : null);
                $normalizedUnits[] = [
                    'unit_id' => $item['unit_id'] ?? null,
                    'unit' => $explicitUnitName,
                    'lookup_key' => $item['unit_id'] ?? ($explicitUnitName === null ? $key : null),
                    'worker_count' => $item['worker_count'] ?? 0,
                    'hours_per_worker' => $item['hours_per_worker'] ?? null,
                    'category' => $item['category'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            } else {
                // Format sederhana: 'units' => ['PMU' => 25, 'Utility' => 15]
                $normalizedUnits[] = [
                    'unit_id' => is_numeric($key) ? (int) $key : null,
                    'unit' => !is_numeric($key) ? (string) $key : null,
                    'lookup_key' => $key,
                    'worker_count' => $item,
                    'hours_per_worker' => $request->input('hours_per_worker'),
                    'category' => null,
                    'notes' => $request->input('notes'),
                ];
            }
        }

        $validator = Validator::make(
            [
                'record_date' => $request->input('record_date'),
                'units' => $normalizedUnits,
            ],
            [
                'record_date' => ['required', 'date'],
                'units' => ['required', 'array', 'min:1'],
                'units.*.worker_count' => ['required', 'integer', 'min:0', 'max:100000'],
                'units.*.hours_per_worker' => ['nullable', 'numeric', 'min:0', 'max:24'],
                'units.*.notes' => ['nullable', 'string'],
            ],
            [
                'record_date.required' => 'Tanggal pencatatan wajib diisi.',
                'record_date.date' => 'Format tanggal pencatatan tidak valid.',
                'units.*.worker_count.required' => 'Jumlah pekerja pada setiap unit wajib diisi (minimal 0).',
                'units.*.worker_count.integer' => 'Jumlah pekerja harus berupa bilangan bulat.',
                'units.*.worker_count.min' => 'Jumlah pekerja tidak boleh bernilai negatif.',
                'units.*.hours_per_worker.numeric' => 'Jam kerja per orang harus berupa angka.',
                'units.*.hours_per_worker.min' => 'Jam kerja per orang tidak boleh negatif.',
                'units.*.hours_per_worker.max' => 'Jam kerja per orang maksimal 24 jam per hari.',
            ]
        );

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $recordDate = Carbon::parse($request->input('record_date'))->format('Y-m-d');
        $globalNotes = $request->input('global_notes');
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        $savedRecords = [];
        $totalWorkersDate = 0;
        $totalManHoursDate = 0.0;

        DB::beginTransaction();
        try {
            foreach ($normalizedUnits as $row) {
                $opUnit = null;
                if (!empty($row['unit_id'])) {
                    $opUnit = OperationalUnit::find($row['unit_id']);
                }
                if (!$opUnit && !empty($row['unit'])) {
                    $opUnit = OperationalUnit::where('name', trim((string) $row['unit']))->first();
                }
                if (!$opUnit && is_numeric($row['lookup_key'])) {
                    $opUnit = OperationalUnit::find((int) $row['lookup_key']);
                }

                $unitName = $opUnit ? $opUnit->name : trim((string) ($row['unit'] ?? 'PMU'));
                if ($unitName === '') {
                    continue;
                }

                // Jika unit belum ada di master unit, daftarkan secara otomatis agar master tetap sinkron
                if (!$opUnit) {
                    $maxSort = (int) OperationalUnit::max('sort_order') + 1;
                    $opUnit = OperationalUnit::create([
                        'name' => $unitName,
                        'code' => 'UNIT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $unitName), 0, 6)),
                        'default_hours' => Workforce::DEFAULT_HOURS_PER_WORKER,
                        'is_active' => true,
                        'is_initial' => false,
                        'sort_order' => $maxSort,
                        'created_by' => $userName,
                    ]);
                }

                $workerCount = max(0, (int) $row['worker_count']);
                $hoursPerWorker = ($row['hours_per_worker'] !== null && $row['hours_per_worker'] !== '')
                    ? max(0, (float) $row['hours_per_worker'])
                    : (float) ($opUnit->default_hours ?: Workforce::DEFAULT_HOURS_PER_WORKER);

                $noteVal = $row['notes'] !== null && $row['notes'] !== ''
                    ? $row['notes']
                    : $globalNotes;

                // Cari data Workforce yang sudah ada pada tanggal dan unit tersebut agar tidak terjadi duplikasi
                $existing = Workforce::whereDate('record_date', $recordDate)
                    ->where(function ($q) use ($opUnit, $unitName) {
                        $q->where('operational_unit_id', $opUnit->id)
                          ->orWhere('unit', $unitName);
                    })
                    ->first();

                $categoryVal = !empty($row['category'])
                    ? $row['category']
                    : ($existing?->category ?: (in_array($unitName, Workforce::CATEGORIES, true) ? $unitName : 'GDE'));

                if ($existing) {
                    $existing->update([
                        'operational_unit_id' => $opUnit->id,
                        'record_date' => $recordDate,
                        'unit' => $unitName,
                        'category' => $categoryVal,
                        'worker_count' => $workerCount,
                        'hours_per_worker' => $hoursPerWorker,
                        'name' => $unitName,
                        'notes' => $noteVal,
                        'status' => 'Aktif',
                    ]);
                    $wfRecord = $existing->fresh('manHour');
                } else {
                    $wfRecord = Workforce::create([
                        'operational_unit_id' => $opUnit->id,
                        'record_date' => $recordDate,
                        'unit' => $unitName,
                        'category' => $categoryVal,
                        'worker_count' => $workerCount,
                        'hours_per_worker' => $hoursPerWorker,
                        'name' => $unitName,
                        'notes' => $noteVal,
                        'status' => 'Aktif',
                    ])->load('manHour');
                }

                $savedRecords[] = $wfRecord;
                $totalWorkersDate += $workerCount;
                $totalManHoursDate += (float) $wfRecord->calculated_man_hours;
            }

            $dateFormatted = Carbon::parse($recordDate)->format('d/m/Y');
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'TAMBAH',
                'record_type' => Workforce::class,
                'record_id' => $savedRecords[0]->id ?? null,
                'description' => "Menyimpan rekap Workforce harian tanggal {$dateFormatted} untuk " . count($savedRecords) . " unit/area operasi (Total: {$totalWorkersDate} pekerja, " . round($totalManHoursDate, 2) . " Man Hours)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            $activeOperationalUnits = OperationalUnit::active()->get();
            $latestHighlight = $this->buildLatestDateHighlight($activeOperationalUnits);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Rekap Workforce tanggal {$dateFormatted} (" . count($savedRecords) . " unit, total {$totalWorkersDate} pekerja) berhasil disimpan dan disinkronkan ke Man Hours.",
                    'record_date' => $recordDate,
                    'total_workers' => $totalWorkersDate,
                    'total_man_hours' => round($totalManHoursDate, 2),
                    'records' => $savedRecords,
                    'latest_highlight' => $latestHighlight,
                ], 201);
            }

            return redirect()->route('workforce.index')
                ->with('success', "Rekap Workforce tanggal {$dateFormatted} (" . count($savedRecords) . " unit, total {$totalWorkersDate} pekerja) berhasil disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan rekap Workforce: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan rekap Workforce: ' . $e->getMessage());
        }
    }

    /**
     * Tambah Unit / Area Operasi baru ke tabel master operational_units.
     */
    public function storeUnit(Request $request): JsonResponse|RedirectResponse
    {
        OperationalUnit::ensureDefaultsSeeded();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:operational_units,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'default_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ], [
            'name.required' => 'Nama Unit / Area Operasi wajib diisi.',
            'name.unique' => 'Nama Unit / Area Operasi sudah terdaftar.',
            'name.max' => 'Nama Unit / Area Operasi maksimal 100 karakter.',
        ]);

        $unitName = trim($validated['name']);
        $userName = Auth::user()?->name ?? 'Administrator Dieng';
        $maxSort = (int) OperationalUnit::max('sort_order') + 1;

        DB::beginTransaction();
        try {
            $unit = OperationalUnit::create([
                'name' => $unitName,
                'code' => 'UNIT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $unitName), 0, 6)),
                'description' => $validated['description'] ?? "Unit Operasi {$unitName}",
                'default_hours' => isset($validated['default_hours']) && $validated['default_hours'] !== ''
                    ? (float) $validated['default_hours']
                    : Workforce::DEFAULT_HOURS_PER_WORKER,
                'is_active' => true,
                'is_initial' => false,
                'sort_order' => $maxSort,
                'created_by' => $userName,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'TAMBAH',
                'record_type' => OperationalUnit::class,
                'record_id' => $unit->id,
                'description' => "Menambahkan master Unit/Area Operasi baru: {$unit->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Unit / Area Operasi '{$unit->name}' berhasil ditambahkan.",
                    'unit' => $unit,
                    'operational_units' => OperationalUnit::ordered()->get(),
                    'active_operational_units' => OperationalUnit::active()->get(),
                ], 201);
            }

            return redirect()->route('workforce.index')
                ->with('success', "Unit / Area Operasi '{$unit->name}' berhasil ditambahkan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan unit: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menambahkan unit: ' . $e->getMessage());
        }
    }

    /**
     * Ubah nama atau parameter Unit / Area Operasi pada master operational_units.
     */
    public function updateUnit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $unit = OperationalUnit::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('operational_units', 'name')->ignore($unit->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'default_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ], [
            'name.required' => 'Nama Unit / Area Operasi wajib diisi.',
            'name.unique' => 'Nama Unit / Area Operasi sudah digunakan oleh unit lain.',
        ]);

        $oldName = $unit->name;
        $newName = trim($validated['name']);
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $unit->update([
                'name' => $newName,
                'description' => array_key_exists('description', $validated) ? $validated['description'] : $unit->description,
                'default_hours' => isset($validated['default_hours']) && $validated['default_hours'] !== ''
                    ? (float) $validated['default_hours']
                    : $unit->default_hours,
            ]);

            // Sinkronkan perubahan nama unit ke histori Workforce dan ManHour tanpa menghapus histori
            if ($oldName !== $newName) {
                Workforce::where('operational_unit_id', $unit->id)
                    ->orWhere('unit', $oldName)
                    ->update([
                        'operational_unit_id' => $unit->id,
                        'unit' => $newName,
                    ]);

                ManHour::where('operational_unit_id', $unit->id)
                    ->orWhere('unit', $oldName)
                    ->update([
                        'operational_unit_id' => $unit->id,
                        'unit' => $newName,
                    ]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'UBAH',
                'record_type' => OperationalUnit::class,
                'record_id' => $unit->id,
                'description' => "Mengubah nama master Unit/Area Operasi dari '{$oldName}' menjadi '{$newName}'",
                'changes' => [
                    'before' => ['name' => $oldName],
                    'after' => ['name' => $newName],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Unit / Area Operasi berhasil diperbarui menjadi '{$unit->name}'.",
                    'unit' => $unit->fresh(),
                    'operational_units' => OperationalUnit::ordered()->get(),
                    'active_operational_units' => OperationalUnit::active()->get(),
                ]);
            }

            return redirect()->route('workforce.index')
                ->with('success', "Unit / Area Operasi berhasil diperbarui menjadi '{$unit->name}'.");
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui unit: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal memperbarui unit: ' . $e->getMessage());
        }
    }

    /**
     * Aktifkan atau nonaktifkan Unit / Area Operasi tanpa menghapus data historisnya.
     */
    public function toggleUnit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $unit = OperationalUnit::findOrFail($id);
        $newStatus = $request->has('is_active')
            ? (bool) $request->boolean('is_active')
            : !$unit->is_active;

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        $unit->update(['is_active' => $newStatus]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Workforce',
            'action' => 'UBAH',
            'record_type' => OperationalUnit::class,
            'record_id' => $unit->id,
            'description' => ($newStatus ? 'Mengaktifkan' : 'Menonaktifkan') . " master Unit/Area Operasi: {$unit->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Unit '{$unit->name}' berhasil " . ($newStatus ? 'diaktifkan.' : 'dinonaktifkan (data historis tetap tersimpan).'),
                'unit' => $unit->fresh(),
                'operational_units' => OperationalUnit::ordered()->get(),
                'active_operational_units' => OperationalUnit::active()->get(),
            ]);
        }

        return redirect()->route('workforce.index')
            ->with('success', "Status unit '{$unit->name}' berhasil diperbarui.");
    }

    /**
     * Deteksi potensi input ganda berdasarkan kombinasi tanggal dan kategori/unit pekerja.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $recordDate = $request->input('record_date');
        $category = trim((string) $request->input('category'));
        $unit = trim((string) $request->input('unit'));
        $ignoreId = $request->input('ignore_id');

        if (empty($recordDate) || (empty($category) && empty($unit))) {
            return response()->json(['duplicate' => false, 'exists' => false]);
        }

        $query = Workforce::whereDate('record_date', $recordDate);
        if (!empty($category)) {
            $query->where('category', $category);
        }
        if (!empty($unit)) {
            $query->where('unit', $unit);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->latest('id')->first();

        if ($existing) {
            $dateFormatted = $existing->record_date ? $existing->record_date->format('d/m/Y') : $recordDate;
            return response()->json([
                'duplicate' => true,
                'exists' => true,
                'message' => "Data Workforce untuk kategori '{$existing->category}' (Unit: {$existing->unit}) pada tanggal {$dateFormatted} sudah terdaftar sebanyak {$existing->worker_count} pekerja ({$existing->calculated_man_hours} Man Hours).",
                'data' => $existing,
                'record' => $existing,
            ]);
        }

        return response()->json(['duplicate' => false, 'exists' => false]);
    }

    /**
     * Tampilkan detail data Workforce beserta hasil perhitungan Man Hours-nya.
     */
    public function show(Request $request, $id): JsonResponse|View|RedirectResponse
    {
        $workforce = Workforce::with(['manHour', 'operationalUnit'])->find($id);

        if (!$workforce) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Workforce tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('workforce.index')->with('error', 'Data Workforce tidak ditemukan.');
        }

        $activities = ActivityLog::where('module', 'Workforce')
            ->where('record_id', $workforce->id)
            ->latest('id')
            ->take(10)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'workforce' => $workforce,
                'man_hour' => $workforce->manHour,
                'activities' => $activities,
            ]);
        }

        return view('data.workforce.show', compact('workforce', 'activities'));
    }

    /**
     * Simpan data jumlah pekerja (Workforce) baru (mendukung Batch Seluruh Unit maupun Single Entry) dan sinkronkan otomatis ke Man Hours.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->has('units') && is_array($request->input('units'))) {
            return $this->storeBatch($request);
        }

        OperationalUnit::ensureDefaultsSeeded();
        $allowedCategories = array_values(array_unique(array_merge(
            Workforce::CATEGORIES,
            OperationalUnit::pluck('name')->all()
        )));

        $validated = $request->validate([
            'record_date' => 'required|date',
            'category' => ['required', 'string', 'max:100', Rule::in($allowedCategories)],
            'worker_count' => 'required|integer|min:0|max:100000',
            'hours_per_worker' => 'nullable|numeric|min:0|max:24',
            'unit' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'record_date.required' => 'Tanggal pencatatan wajib diisi.',
            'record_date.date' => 'Format tanggal pencatatan tidak valid.',
            'category.required' => 'Kategori pekerja wajib dipilih.',
            'category.in' => 'Kategori pekerja harus sesuai dengan kategori atau unit standar project.',
            'worker_count.required' => 'Jumlah pekerja wajib diisi.',
            'worker_count.integer' => 'Jumlah pekerja harus berupa bilangan bulat.',
            'worker_count.min' => 'Jumlah pekerja tidak boleh bernilai negatif.',
            'hours_per_worker.numeric' => 'Jam kerja per orang harus berupa angka.',
            'hours_per_worker.min' => 'Jam kerja per orang tidak boleh bernilai negatif.',
            'hours_per_worker.max' => 'Jam kerja per orang maksimal 24 jam per hari.',
        ]);

        // Cegah input ganda berdasarkan kombinasi tanggal dan kategori pekerja
        $existing = Workforce::whereDate('record_date', $validated['record_date'])
            ->where('category', $validated['category'])
            ->first();

        if ($existing) {
            $dateFormatted = Carbon::parse($validated['record_date'])->format('d/m/Y');
            $dupMessage = "Data Workforce untuk kategori '{$existing->category}' pada tanggal {$dateFormatted} sudah terdaftar ({$existing->worker_count} pekerja). Silakan edit data yang sudah ada.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => $dupMessage,
                    'existing' => $existing->load('manHour'),
                    'errors' => [
                        'category' => [$dupMessage],
                    ],
                ], 422);
            }

            return back()->withInput()->with('error', $dupMessage);
        }

        $hoursPerWorker = isset($validated['hours_per_worker']) && $validated['hours_per_worker'] !== ''
            ? (float) $validated['hours_per_worker']
            : Workforce::DEFAULT_HOURS_PER_WORKER;

        $unitName = !empty($validated['unit']) ? $validated['unit'] : 'PMU';
        $opUnit = OperationalUnit::where('name', $unitName)->first();
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $workforce = Workforce::create([
                'operational_unit_id' => $opUnit?->id,
                'record_date' => $validated['record_date'],
                'category' => $validated['category'],
                'worker_count' => (int) $validated['worker_count'],
                'hours_per_worker' => $hoursPerWorker,
                'unit' => $unitName,
                'department' => $validated['department'] ?? null,
                'name' => $validated['name'] ?? ($validated['department'] ?? $validated['category']),
                'notes' => $validated['notes'] ?? null,
                'status' => 'Aktif',
            ]);

            $manHour = $workforce->manHour()->first();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'TAMBAH',
                'record_type' => Workforce::class,
                'record_id' => $workforce->id,
                'description' => "Menambahkan data Workforce: {$workforce->category} - Unit {$workforce->unit} ({$workforce->worker_count} pekerja, {$workforce->calculated_man_hours} Man Hours) pada tanggal " . Carbon::parse($workforce->record_date)->format('d/m/Y'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Workforce berhasil ditambahkan dan otomatis dihitung ke Man Hours.',
                    'workforce' => $workforce->load('manHour'),
                    'man_hour' => $manHour,
                ], 201);
            }

            return redirect()->route('workforce.index')
                ->with('success', 'Data Workforce berhasil ditambahkan dan disinkronkan ke Man Hours.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data Workforce: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan data Workforce: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui data Workforce dan otomatis perbarui perhitungan Man Hours.
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $workforce = Workforce::find($id);

        if (!$workforce) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Workforce tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('workforce.index')->with('error', 'Data Workforce tidak ditemukan.');
        }

        OperationalUnit::ensureDefaultsSeeded();
        $allowedCategories = array_values(array_unique(array_merge(
            Workforce::CATEGORIES,
            OperationalUnit::pluck('name')->all(),
            [$workforce->category]
        )));

        $validated = $request->validate([
            'record_date' => 'required|date',
            'category' => ['required', 'string', 'max:100', Rule::in($allowedCategories)],
            'worker_count' => 'required|integer|min:0|max:100000',
            'hours_per_worker' => 'nullable|numeric|min:0|max:24',
            'unit' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'record_date.required' => 'Tanggal pencatatan wajib diisi.',
            'record_date.date' => 'Format tanggal pencatatan tidak valid.',
            'category.required' => 'Kategori pekerja wajib dipilih.',
            'category.in' => 'Kategori pekerja harus sesuai dengan kategori standar project.',
            'worker_count.required' => 'Jumlah pekerja wajib diisi.',
            'worker_count.integer' => 'Jumlah pekerja harus berupa bilangan bulat.',
            'worker_count.min' => 'Jumlah pekerja tidak boleh bernilai negatif.',
            'hours_per_worker.numeric' => 'Jam kerja per orang harus berupa angka.',
            'hours_per_worker.min' => 'Jam kerja per orang tidak boleh bernilai negatif.',
            'hours_per_worker.max' => 'Jam kerja per orang maksimal 24 jam per hari.',
        ]);

        // Cegah duplikasi kombinasi tanggal + kategori + unit dengan rekaman lain
        $targetUnit = !empty($validated['unit']) ? $validated['unit'] : $workforce->unit;
        $duplicateOther = Workforce::whereDate('record_date', $validated['record_date'])
            ->where('category', $validated['category'])
            ->where('unit', $targetUnit)
            ->where('id', '!=', $workforce->id)
            ->first();

        if ($duplicateOther) {
            $dateFormatted = Carbon::parse($validated['record_date'])->format('d/m/Y');
            $dupMessage = "Data Workforce untuk kategori '{$duplicateOther->category}' (Unit {$duplicateOther->unit}) pada tanggal {$dateFormatted} sudah terdaftar pada baris lain ({$duplicateOther->worker_count} pekerja).";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => $dupMessage,
                    'existing' => $duplicateOther->load('manHour'),
                    'errors' => [
                        'category' => [$dupMessage],
                    ],
                ], 422);
            }

            return back()->withInput()->with('error', $dupMessage);
        }

        $hoursPerWorker = isset($validated['hours_per_worker']) && $validated['hours_per_worker'] !== ''
            ? (float) $validated['hours_per_worker']
            : ($workforce->hours_per_worker ?? Workforce::DEFAULT_HOURS_PER_WORKER);

        $opUnit = OperationalUnit::where('name', $targetUnit)->first();

        $updatePayload = [
            'operational_unit_id' => $opUnit ? $opUnit->id : $workforce->operational_unit_id,
            'record_date' => $validated['record_date'],
            'category' => $validated['category'],
            'worker_count' => (int) $validated['worker_count'],
            'hours_per_worker' => $hoursPerWorker,
            'unit' => $targetUnit,
            'department' => array_key_exists('department', $validated) ? $validated['department'] : $workforce->department,
            'name' => $validated['name'] ?? ($validated['department'] ?? $validated['category']),
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $workforce->notes,
        ];

        $oldData = $workforce->only(array_keys($updatePayload));
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $workforce->update($updatePayload);
            $changedKeys = array_keys($workforce->getChanges());

            $changes = [
                'before' => array_intersect_key($oldData, array_flip($changedKeys)),
                'after' => $workforce->getChanges(),
            ];

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'UBAH',
                'record_type' => Workforce::class,
                'record_id' => $workforce->id,
                'description' => "Memperbarui data Workforce: {$workforce->unit} ({$workforce->category}) menjadi {$workforce->worker_count} pekerja ({$workforce->calculated_man_hours} Man Hours)",
                'changes' => $changes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Workforce berhasil diperbarui dan Man Hours telah disesuaikan.',
                    'workforce' => $workforce->fresh('manHour'),
                ]);
            }

            return redirect()->route('workforce.index')
                ->with('success', 'Data Workforce berhasil diperbarui dan Man Hours telah disesuaikan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data Workforce: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data Workforce: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data Workforce beserta rekap Man Hours terkait.
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $workforce = Workforce::find($id);

        if (!$workforce) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Workforce tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('workforce.index')->with('error', 'Data Workforce tidak ditemukan.');
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Workforce',
                'action' => 'HAPUS',
                'record_type' => Workforce::class,
                'record_id' => $workforce->id,
                'description' => "Menghapus data Workforce: {$workforce->unit} ({$workforce->category}, {$workforce->worker_count} pekerja) tanggal " . ($workforce->record_date ? $workforce->record_date->format('d/m/Y') : '-'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $workforce->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Workforce dan perhitungan Man Hours terkait berhasil dihapus.',
                ]);
            }

            return redirect()->route('workforce.index')
                ->with('success', 'Data Workforce berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data Workforce: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus data Workforce: ' . $e->getMessage());
        }
    }

    /**
     * Hapus massal (bulk delete) data Manpower terpilih.
     */
    public function destroyBulk(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:workforces,id',
        ], [
            'ids.required' => 'Pilih minimal satu data Manpower untuk dihapus.',
            'ids.min' => 'Pilih minimal satu data Manpower untuk dihapus.',
            'ids.*.exists' => 'Salah satu data yang dipilih tidak ditemukan dalam sistem.',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            return back()->with('error', $validator->errors()->first());
        }

        $ids = $request->input('ids');
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $workforces = Workforce::whereIn('id', $ids)->get();
            $count = $workforces->count();

            foreach ($workforces as $wf) {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'user_name' => $userName,
                    'module' => 'Manpower',
                    'action' => 'HAPUS_MASSAL',
                    'record_type' => Workforce::class,
                    'record_id' => $wf->id,
                    'description' => "Menghapus data Manpower: {$wf->unit} ({$wf->category}, {$wf->worker_count} pekerja) tanggal " . ($wf->record_date ? $wf->record_date->format('d/m/Y') : '-'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $wf->delete();
            }

            ManHour::syncAllFromWorkforce();

            DB::commit();

            $msg = "Berhasil menghapus {$count} data Manpower terpilih beserta sinkronisasi Man Hours terkait.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'deleted_count' => $count,
                ]);
            }

            return redirect()->route('workforce.index')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data massal: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus data massal: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor data Manpower ke PDF.
     */
    public function export(Request $request): Response
    {
        $query = Workforce::with('manHour');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('record_date', [$request->input('start_date'), $request->input('end_date')]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('record_date', '>=', $request->input('start_date'));
        } elseif ($request->filled('end_date')) {
            $query->whereDate('record_date', '<=', $request->input('end_date'));
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
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('department', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $records = $query->orderBy('record_date', 'desc')->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('exports.workforce-pdf', [
            'reportTitle' => 'LAPORAN DATA TENAGA KERJA (MANPOWER)',
            'moduleName' => 'Manpower',
            'filterInfo' => [
                'Unit' => $request->input('unit', 'Semua Unit'),
                'Kategori' => $request->input('category', 'Semua Kategori'),
                'Periode' => ($request->filled('start_date') ? $request->input('start_date') : 'Awal') . ' s/d ' . ($request->filled('end_date') ? $request->input('end_date') : 'Sekarang'),
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Manpower-' . now()->format('Ymd-His') . '.pdf');
    }
}

<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Contractor;
use App\Models\HssePlan;
use App\Models\HssePlanNote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class HSSEPlanController extends Controller
{
    /**
     * Display a listing of HSSE Plans / KPIs with stats and charts.
     */
    public function index(Request $request): View|JsonResponse
    {
        Contractor::ensureDefaultsSeeded();
        $contractors = Contractor::where('is_active', true)->orderBy('name')->get();

        $query = HssePlan::with(['notes', 'contractor']);

        // Filter: Contractor
        if ($request->input('contractor') === 'unassigned' || $request->input('contractor_id') === 'unassigned') {
            $query->whereNull('contractor_id');
        } elseif ($request->filled('contractor_id')) {
            $query->where('contractor_id', $request->input('contractor_id'));
        } elseif ($request->filled('contractor')) {
            $cVal = $request->input('contractor');
            $query->where(function ($q) use ($cVal) {
                $q->where('contractor_id', $cVal)
                  ->orWhere('contractor_name', $cVal)
                  ->orWhereHas('contractor', fn ($sub) => $sub->where('name', $cVal));
            });
        }

        // Filter: Category / Type (Leading vs Lagging)
        if ($request->filled('type')) {
            $query->where('category', $request->input('type'));
        } elseif ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $statusVal = $request->input('status');
            if ($statusVal === 'Dalam Progress') {
                $query->whereIn('status', ['Berlangsung', 'Dalam Progress']);
            } elseif ($statusVal === 'Belum Tercapai') {
                $query->whereIn('status', ['Tidak Tercapai', 'Belum Mulai', 'Belum Tercapai']);
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Filter: Year / Period
        if ($request->filled('period')) {
            $query->where('year', $request->input('period'));
        } elseif ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter: Date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->input('start_date'), $request->input('end_date')])
                  ->orWhereBetween('end_date', [$request->input('start_date'), $request->input('end_date')]);
            });
        }

        // Filter: Search
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'name');
        if ($sort === 'progress-desc') {
            $query->orderByRaw('(realization / CASE WHEN target <= 0 THEN 1 ELSE target END) DESC');
        } elseif ($sort === 'progress-asc') {
            $query->orderByRaw('(realization / CASE WHEN target <= 0 THEN 1 ELSE target END) ASC');
        } elseif ($sort === 'date') {
            $query->orderBy('start_date', 'asc');
        } else {
            $query->orderBy('title', 'asc');
        }

        $kpis = $query->get();

        // Calculate statistics
        $total = $kpis->count();
        $achieved = $kpis->where('status', 'Tercapai')->count();
        $progress = $kpis->whereIn('status', ['Berlangsung', 'Dalam Progress'])->count();
        $notAchieved = $kpis->whereIn('status', ['Tidak Tercapai', 'Belum Mulai', 'Belum Tercapai'])->count();
        $leading = $kpis->where('category', 'Leading')->count();
        $lagging = $kpis->where('category', 'Lagging')->count();

        $stats = [
            'total' => $total,
            'achieved' => $achieved,
            'progress' => $progress,
            'not_achieved' => $notAchieved,
            'leading' => $leading,
            'lagging' => $lagging,
        ];

        // Format chart data
        $statusChart = [
            'labels' => ['Tercapai', 'Dalam Progress', 'Belum Tercapai'],
            'data' => [$achieved, $progress, $notAchieved],
        ];

        $typeChart = [
            'labels' => ['Leading', 'Lagging'],
            'data' => [$leading, $lagging],
        ];

        // Monthly targets and actuals distribution (12 months)
        $monthlyTargets = array_fill(0, 12, 0);
        $monthlyActuals = array_fill(0, 12, 0);

        foreach ($kpis as $item) {
            $mStart = $item->start_date ? Carbon::parse($item->start_date)->month - 1 : 0;
            $mEnd = $item->end_date ? Carbon::parse($item->end_date)->month - 1 : 11;
            $span = max(1, $mEnd - $mStart + 1);

            $targetVal = (float) $item->target;
            $actualVal = (float) $item->realization;

            for ($m = $mStart; $m <= $mEnd; $m++) {
                $monthlyTargets[$m] += $targetVal / $span;
                $monthlyActuals[$m] += $actualVal / $span;
            }
        }

        $monthlyChart = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
            'targets' => array_map(fn ($v) => round($v, 1), $monthlyTargets),
            'actuals' => array_map(fn ($v) => round($v, 1), $monthlyActuals),
        ];

        // If AJAX / JSON request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kpis' => $kpis,
                'stats' => $stats,
                'statusChart' => $statusChart,
                'typeChart' => $typeChart,
                'monthlyChart' => $monthlyChart,
            ]);
        }

        return view('hsse-plan.index', compact(
            'kpis',
            'stats',
            'statusChart',
            'typeChart',
            'monthlyChart',
            'contractors'
        ));
    }

    /**
     * Display a listing of achieved HSSE Plans (Riwayat Tercapai).
     * Strictly filters data where status is 'Tercapai'.
     */
    public function achieved(Request $request): View|JsonResponse
    {
        Contractor::ensureDefaultsSeeded();
        $contractors = Contractor::where('is_active', true)->orderBy('name')->get();

        // STRICT filter: ONLY achieved status from database
        $query = HssePlan::with(['notes', 'contractor'])->where('status', 'Tercapai');

        // Filter: Contractor
        if ($request->input('contractor') === 'unassigned' || $request->input('contractor_id') === 'unassigned') {
            $query->whereNull('contractor_id');
        } elseif ($request->filled('contractor_id')) {
            $query->where('contractor_id', $request->input('contractor_id'));
        } elseif ($request->filled('contractor')) {
            $cVal = $request->input('contractor');
            $query->where(function ($q) use ($cVal) {
                $q->where('contractor_id', $cVal)
                  ->orWhere('contractor_name', $cVal)
                  ->orWhereHas('contractor', fn ($sub) => $sub->where('name', $cVal));
            });
        }

        // Filter: Category / Type (Leading vs Lagging)
        if ($request->filled('type')) {
            $query->where('category', $request->input('type'));
        } elseif ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Year / Period
        if ($request->filled('period')) {
            $query->where('year', $request->input('period'));
        } elseif ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter: Date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->input('start_date'), $request->input('end_date')])
                  ->orWhereBetween('end_date', [$request->input('start_date'), $request->input('end_date')]);
            });
        }

        // Filter: Search
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'name');
        if ($sort === 'progress-desc') {
            $query->orderByRaw('(realization / CASE WHEN target <= 0 THEN 1 ELSE target END) DESC');
        } elseif ($sort === 'progress-asc') {
            $query->orderByRaw('(realization / CASE WHEN target <= 0 THEN 1 ELSE target END) ASC');
        } elseif ($sort === 'date') {
            $query->orderBy('start_date', 'asc');
        } else {
            $query->orderBy('title', 'asc');
        }

        $kpis = $query->get();

        // Calculate statistics for achieved KPIs
        $totalAchieved = $kpis->count();
        $leadingAchieved = $kpis->where('category', 'Leading')->count();
        $laggingAchieved = $kpis->where('category', 'Lagging')->count();
        $averagePercentage = $totalAchieved > 0
            ? round($kpis->avg(fn ($k) => $k->percentage))
            : 0;

        $stats = [
            'total' => $totalAchieved,
            'achieved' => $totalAchieved,
            'leading' => $leadingAchieved,
            'lagging' => $laggingAchieved,
            'average_percentage' => $averagePercentage,
        ];

        // Format chart data for achieved KPIs
        $typeChart = [
            'labels' => ['Leading', 'Lagging'],
            'data' => [$leadingAchieved, $laggingAchieved],
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kpis' => $kpis,
                'stats' => $stats,
                'typeChart' => $typeChart,
            ]);
        }

        return view('hsse-plan.achieved', compact('kpis', 'stats', 'typeChart', 'contractors'));
    }

    /**
     * Display the specified KPI detail with notes.
     */
    public function show(Request $request, $id): JsonResponse|View
    {
        $kpi = HssePlan::with('notes')->find($id);

        if (!$kpi) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data KPI tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('hsse-plan.index')
                ->with('error', 'Data KPI tidak ditemukan.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kpi' => $kpi,
                'notes' => $kpi->notes,
            ]);
        }

        return view('hsse-plan.show', compact('kpi'));
    }

    /**
     * Store a newly created KPI in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2000|max:2100',
            'period' => 'nullable|integer|min:2000|max:2100',
            'target' => 'nullable|numeric|min:0',
            'targetValue' => 'nullable|numeric|min:0',
            'realization' => 'nullable|numeric|min:0',
            'actual' => 'nullable|numeric|min:0',
            'unit_measure' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'start' => 'nullable|date',
            'end_date' => 'nullable|date',
            'end' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $title = $validated['title'] ?? $validated['name'] ?? 'KPI Tanpa Nama';
        $category = $validated['category'] ?? $validated['type'] ?? 'Leading';
        $target = $validated['target'] ?? $validated['targetValue'] ?? 0;
        $realization = $validated['realization'] ?? $validated['actual'] ?? 0;
        $year = $validated['year'] ?? $validated['period'] ?? (int) date('Y');
        $start = $validated['start_date'] ?? $validated['start'] ?? date('Y-01-01');
        $end = $validated['end_date'] ?? $validated['end'] ?? date('Y-12-31');

        $contractorId = !empty($request->input('contractor_id')) && is_numeric($request->input('contractor_id')) ? (int) $request->input('contractor_id') : null;
        $contractorName = null;
        if ($contractorId) {
            $contractorName = Contractor::find($contractorId)?->name;
        } elseif ($request->filled('contractor_name')) {
            $contractorName = trim($request->input('contractor_name'));
        }

        $status = $validated['status'] ?? 'Berlangsung';
        if ($status === 'Dalam Progress') {
            $status = 'Berlangsung';
        } elseif ($status === 'Belum Tercapai') {
            $status = 'Tidak Tercapai';
        }
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $kpi = HssePlan::create([
                'title' => $title,
                'category' => $category,
                'unit' => $validated['unit'] ?? 'Dieng Unit 1',
                'contractor_id' => $contractorId,
                'contractor_name' => $contractorName,
                'year' => $year,
                'target' => $target,
                'realization' => $realization,
                'unit_measure' => $validated['unit_measure'] ?? '%',
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
                'description' => $validated['description'] ?? null,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'HSSE Plan',
                'action' => 'TAMBAH',
                'record_type' => HssePlan::class,
                'record_id' => $kpi->id,
                'description' => "Menambahkan KPI baru: {$kpi->title} ({$kpi->category} - Tahun {$kpi->year})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'KPI berhasil ditambahkan.',
                    'kpi' => $kpi->load('notes'),
                ], 201);
            }

            return redirect()->route('hsse-plan.index')
                ->with('success', 'KPI baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan KPI: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan KPI: ' . $e->getMessage());
        }
    }

    /**
     * Check for potential duplicate HSSE Plan KPI records.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $title = trim((string) ($request->input('title') ?? $request->input('name')));
        $year = (int) ($request->input('year') ?? $request->input('period') ?? date('Y'));
        $category = trim((string) ($request->input('category') ?? $request->input('type')));
        $ignoreId = $request->input('ignore_id');

        if (empty($title)) {
            return response()->json(['duplicate' => false]);
        }

        $query = HssePlan::where('year', $year)
            ->where(function ($q) use ($title) {
                $q->where('title', $title)
                  ->orWhere('title', 'like', "%{$title}%");
            });

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $duplicates = $query->latest('id')->take(3)->get();

        if ($duplicates->isNotEmpty()) {
            $first = $duplicates->first();
            $creatorLog = ActivityLog::where('module', 'HSSE Plan')
                ->where('action', 'TAMBAH')
                ->where('record_id', $first->id)
                ->latest('id')
                ->first();

            return response()->json([
                'duplicate' => true,
                'message' => "Ditemukan KPI serupa '{$first->title}' pada periode tahun {$year}.",
                'data' => $first,
                'created_by' => $creatorLog ? $creatorLog->user_name : 'Sistem HSSE',
                'created_at' => $first->created_at ? $first->created_at->format('d/m/Y H:i') : '-',
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Update the specified KPI in storage.
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $kpi = HssePlan::find($id);

        if (!$kpi) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
            }
            return redirect()->route('hsse-plan.index')->with('error', 'Data KPI tidak ditemukan.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'year' => 'nullable|integer',
            'period' => 'nullable|integer',
            'target' => 'nullable|numeric',
            'targetValue' => 'nullable|numeric',
            'realization' => 'nullable|numeric',
            'actual' => 'nullable|numeric',
            'unit_measure' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'start' => 'nullable|date',
            'end_date' => 'nullable|date',
            'end' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $updateData = [];
        if (isset($validated['title']) || isset($validated['name'])) {
            $updateData['title'] = $validated['title'] ?? $validated['name'];
        }
        if (isset($validated['category']) || isset($validated['type'])) {
            $updateData['category'] = $validated['category'] ?? $validated['type'];
        }
        if (isset($validated['unit'])) {
            $updateData['unit'] = $validated['unit'];
        }
        if (isset($validated['year']) || isset($validated['period'])) {
            $updateData['year'] = $validated['year'] ?? $validated['period'];
        }
        if (isset($validated['target']) || isset($validated['targetValue'])) {
            $updateData['target'] = $validated['target'] ?? $validated['targetValue'];
        }
        if (isset($validated['realization']) || isset($validated['actual'])) {
            $updateData['realization'] = $validated['realization'] ?? $validated['actual'];
        }
        if (isset($validated['unit_measure'])) {
            $updateData['unit_measure'] = $validated['unit_measure'];
        }
        if (isset($validated['start_date']) || isset($validated['start'])) {
            $updateData['start_date'] = $validated['start_date'] ?? $validated['start'];
        }
        if (isset($validated['end_date']) || isset($validated['end'])) {
            $updateData['end_date'] = $validated['end_date'] ?? $validated['end'];
        }
        if (isset($validated['status'])) {
            $st = $validated['status'];
            if ($st === 'Dalam Progress') $st = 'Berlangsung';
            if ($st === 'Belum Tercapai') $st = 'Tidak Tercapai';
            $updateData['status'] = $st;
        }
        if (array_key_exists('description', $validated)) {
            $updateData['description'] = $validated['description'];
        }

        if ($request->has('contractor_id')) {
            $cId = !empty($request->input('contractor_id')) && is_numeric($request->input('contractor_id')) ? (int) $request->input('contractor_id') : null;
            $updateData['contractor_id'] = $cId;
            $updateData['contractor_name'] = $cId ? Contractor::find($cId)?->name : null;
        } elseif ($request->has('contractor_name')) {
            $updateData['contractor_name'] = $request->input('contractor_name');
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        $oldData = $kpi->only(array_keys($updateData));
        DB::beginTransaction();
        try {
            $kpi->update($updateData);
            $changedKeys = array_keys($kpi->getChanges());
            $changes = [
                'before' => array_intersect_key($oldData, array_flip($changedKeys)),
                'after' => $kpi->getChanges(),
            ];

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'HSSE Plan',
                'action' => 'UBAH',
                'record_type' => HssePlan::class,
                'record_id' => $kpi->id,
                'description' => "Memperbarui KPI: {$kpi->title} (Realisasi: {$kpi->realization} / Target: {$kpi->target})",
                'changes' => $changes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data KPI berhasil diperbarui.',
                    'kpi' => $kpi->fresh('notes'),
                ]);
            }

            return redirect()->route('hsse-plan.index')->with('success', 'Data KPI berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui KPI: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui KPI: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified KPI from storage.
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $kpi = HssePlan::find($id);

        if (!$kpi) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
            }
            return redirect()->route('hsse-plan.index')->with('error', 'Data KPI tidak ditemukan.');
        }

        $kpiTitle = $kpi->title;
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $kpi->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'HSSE Plan',
                'action' => 'HAPUS',
                'record_type' => HssePlan::class,
                'record_id' => (int) $id,
                'description' => "Menghapus KPI: {$kpiTitle}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data KPI berhasil dihapus.',
                ]);
            }

            return redirect()->route('hsse-plan.index')->with('success', 'Data KPI berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus KPI: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('hsse-plan.index')->with('error', 'Gagal menghapus KPI: ' . $e->getMessage());
        }
    }



    /**
     * Get notes for a specific KPI.
     */
    public function getNotes($kpiId): JsonResponse
    {
        $kpi = HssePlan::find($kpiId);

        if (!$kpi) {
            return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'kpi_id' => $kpi->id,
            'notes' => $kpi->notes,
        ]);
    }

    /**
     * Store a new note for the specified KPI.
     */
    public function storeNote(Request $request, $kpiId): JsonResponse
    {
        $kpi = HssePlan::find($kpiId);

        if (!$kpi) {
            return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'note' => 'required|string|min:3|max:1000',
            'author' => 'nullable|string|max:100',
        ], [
            'note.required' => 'Catatan wajib diisi.',
            'note.min' => 'Catatan minimal terdiri dari 3 karakter.',
            'note.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $author = $validated['author'] ?? (Auth::user()?->name ?? 'Admin HSSE Dieng');

        $note = $kpi->notes()->create([
            'note' => $validated['note'],
            'author' => $author,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? $author,
            'module' => 'HSSE Plan',
            'action' => 'TAMBAH_CATATAN',
            'record_type' => HssePlanNote::class,
            'record_id' => $note->id,
            'description' => "Menambahkan catatan pada KPI '{$kpi->title}': " . \Illuminate\Support\Str::limit($note->note, 100),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil ditambahkan.',
            'note' => $note,
            'notes_count' => $kpi->notes()->count(),
        ], 201);
    }

    /**
     * Update an existing note for the specified KPI.
     */
    public function updateNote(Request $request, $kpiId, $noteId): JsonResponse
    {
        $kpi = HssePlan::find($kpiId);

        if (!$kpi) {
            return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
        }

        $note = $kpi->notes()->find($noteId);

        if (!$note) {
            return response()->json(['success' => false, 'message' => 'Catatan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'note' => 'required|string|min:3|max:1000',
            'author' => 'nullable|string|max:100',
        ], [
            'note.required' => 'Catatan wajib diisi.',
            'note.min' => 'Catatan minimal terdiri dari 3 karakter.',
        ]);

        $updateData = ['note' => $validated['note']];
        if (!empty($validated['author'])) {
            $updateData['author'] = $validated['author'];
        }

        $note->update($updateData);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? ($note->author ?? 'Administrator Dieng'),
            'module' => 'HSSE Plan',
            'action' => 'UBAH_CATATAN',
            'record_type' => HssePlanNote::class,
            'record_id' => $note->id,
            'description' => "Memperbarui catatan pada KPI '{$kpi->title}': " . \Illuminate\Support\Str::limit($note->note, 100),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil diperbarui.',
            'note' => $note->fresh(),
        ]);
    }

    /**
     * Delete an existing note for the specified KPI.
     */
    public function destroyNote($kpiId, $noteId): JsonResponse
    {
        $kpi = HssePlan::find($kpiId);

        if (!$kpi) {
            return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
        }

        $note = $kpi->notes()->find($noteId);

        if (!$note) {
            return response()->json(['success' => false, 'message' => 'Catatan tidak ditemukan.'], 404);
        }

        $notePreview = \Illuminate\Support\Str::limit($note->note, 100);
        $note->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Administrator Dieng',
            'module' => 'HSSE Plan',
            'action' => 'HAPUS_CATATAN',
            'record_type' => HssePlanNote::class,
            'record_id' => (int) $noteId,
            'description' => "Menghapus catatan pada KPI '{$kpi->title}': {$notePreview}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil dihapus.',
            'notes_count' => $kpi->notes()->count(),
        ]);
    }

    /**
     * Ekspor data HSSE Plan ke PDF dengan seluruh record sesuai filter aktif.
     */
    public function export(Request $request): Response
    {
        Contractor::ensureDefaultsSeeded();

        $query = HssePlan::with(['notes', 'contractor']);

        // Filter: Category / Type
        if ($request->filled('type')) {
            $query->where('category', $request->input('type'));
        } elseif ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $statusVal = $request->input('status');
            if ($statusVal === 'Dalam Progress') {
                $query->whereIn('status', ['Berlangsung', 'Dalam Progress']);
            } elseif ($statusVal === 'Belum Tercapai') {
                $query->whereIn('status', ['Tidak Tercapai', 'Belum Mulai', 'Belum Tercapai']);
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Filter: Year / Period
        if ($request->filled('period')) {
            $query->where('year', $request->input('period'));
        } elseif ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter: Contractor
        if ($request->input('contractor') === 'unassigned' || $request->input('contractor_id') === 'unassigned') {
            $query->whereNull('contractor_id');
        } elseif ($request->filled('contractor_id')) {
            $query->where('contractor_id', $request->input('contractor_id'));
        } elseif ($request->filled('contractor')) {
            $cVal = $request->input('contractor');
            $query->where(function ($q) use ($cVal) {
                $q->where('contractor_id', $cVal)
                  ->orWhere('contractor_name', $cVal)
                  ->orWhereHas('contractor', fn ($sub) => $sub->where('name', $cVal));
            });
        }

        // Filter: Search
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('contractor_name', 'like', "%{$term}%");
            });
        }

        $records = $query->orderBy('year', 'desc')->orderBy('title', 'asc')->get();
        $fileName = 'Laporan_HSSE_Plan_' . date('Ymd_His') . '.pdf';

        $contractorLabel = 'Semua Kontraktor';
        if ($request->input('contractor') === 'unassigned' || $request->input('contractor_id') === 'unassigned') {
            $contractorLabel = 'Belum Ditentukan';
        } elseif ($request->filled('contractor_id')) {
            $c = Contractor::find($request->input('contractor_id'));
            if ($c) $contractorLabel = $c->name;
        }

        $pdf = Pdf::loadView('exports.hsse-plan-pdf', [
            'title' => 'Laporan HSSE Plan - HSSE Manajemen',
            'reportTitle' => 'LAPORAN REKAPITULASI HSSE PLAN (KPI)',
            'moduleName' => 'HSSE Plan & KPI',
            'filterPeriod' => 'Tahun ' . ($request->input('year', $request->input('period', 'Semua'))),
            'filterScope' => $contractorLabel . ' / ' . ($request->input('category', 'Semua Kategori')),
            'totalRecords' => $records->count(),
            'records' => $records,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}


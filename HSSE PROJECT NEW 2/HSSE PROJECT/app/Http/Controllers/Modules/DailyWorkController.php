<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DailyWork;
use App\Models\DailyWorkCompany;
use App\Models\PtwClassification;
use App\Models\Sikka;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyWorkController extends Controller
{
    /**
     * Hitung statistik pekerjaan (zona waktu Asia/Jakarta).
     * Jika targetDate diberikan, menghitung pekerjaan yang tanggal mulainya sesuai targetDate.
     * Jika tidak, default hari ini.
     */
    protected function getStats(?string $targetDate = null): array
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $date = $targetDate ?: $today;

        // Pekerjaan yang tanggal mulainya tepat sama dengan tanggal acuan (DATE(start_date) = date)
        $dateWorks = DailyWork::whereDate('start_date', $date)->get();

        $companiesGrouped = $dateWorks->groupBy('unit')->map(function ($items, $unitName) {
            return [
                'name' => $unitName ?: 'Tanpa Perusahaan',
                'count' => $items->count(),
            ];
        })->values();

        $totalAll = DailyWork::count();

        return [
            'total_today' => $dateWorks->count(),
            'companies_today' => $companiesGrouped->count(),
            'companies_list_today' => $companiesGrouped->all(),
            'total_all' => $totalAll,
            'total_history' => $totalAll,
            'today_date' => $today,
            'target_date' => $date,
        ];
    }

    /**
     * Hitung distribusi pekerjaan per perusahaan untuk diagram lingkaran (pie chart).
     * Jika tidak diberikan koleksi, default menggunakan pekerjaan yang tanggal mulainya hari ini.
     */
    protected function getCompanyDistribution($works = null): array
    {
        if ($works === null) {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            $works = DailyWork::whereDate('start_date', $today)->get();
        }

        $distribution = [];
        $total = $works->count();

        if ($total > 0) {
            $grouped = $works->groupBy('unit');
            foreach ($grouped as $compName => $group) {
                $count = $group->count();
                $distribution[] = [
                    'name' => $compName ?: 'Lainnya',
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                ];
            }
            usort($distribution, fn($a, $b) => $b['count'] <=> $a['count']);
        }

        return $distribution;
    }

    /**
     * Tampilkan halaman utama Daily Work dengan tabel pekerjaan hari ini, pie chart perusahaan, dan statistik hari ini.
     */
    public function index(Request $request): View|JsonResponse
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Ambil data master untuk form dan filter
        $companies = DailyWorkCompany::orderBy('name')->get();
        $ptwClassifications = PtwClassification::orderBy('name')->get();
        $sikkas = Sikka::orderBy('name')->get();

        $query = DailyWork::query();

        // Filter: Perusahaan (unit)
        if ($request->filled('company') || $request->filled('unit')) {
            $comp = $request->input('company') ?? $request->input('unit');
            $query->where('unit', $comp);
        }

        // Filter: PTW Classification
        if ($request->filled('ptw_classification')) {
            $query->where('ptw_classification', $request->input('ptw_classification'));
        }

        // Filter: SIKKA
        if ($request->filled('sikka')) {
            $query->where('sikka', $request->input('sikka'));
        }

        // Mode Tanggal: 'today' (default) atau 'all' (semua riwayat)
        $dateMode = $request->input('date_mode', 'today');
        $selectedDate = $request->input('date');

        if ($dateMode === 'today') {
            // Pada mode Hari Ini: default adalah hari ini ($today).
            // Jika pengguna memilih tanggal lain di filter tanggal, gunakan tanggal tersebut.
            // Data yang tampil tetap mengikuti aturan tanggal mulai pekerjaan: start_date = targetDate.
            $targetDate = $selectedDate ?: $today;
            $query->whereDate('start_date', $targetDate);
        } else {
            // Mode Semua Riwayat: menampilkan seluruh data pekerjaan tanpa batasan hanya yang aktif
            // Jika ada filter tanggal spesifik di mode semua riwayat, cari pekerjaan yang aktif di tanggal tersebut
            if ($request->filled('date')) {
                $query->where(function ($q) use ($selectedDate) {
                    $q->whereDate('start_date', '<=', $selectedDate)
                      ->whereDate('end_date', '>=', $selectedDate);
                });
            } elseif ($request->filled('start_date') || $request->filled('end_date')) {
                if ($request->filled('start_date')) {
                    $query->whereDate('start_date', '>=', $request->input('start_date'));
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('end_date', '<=', $request->input('end_date'));
                }
            }
        }

        // Filter: Pencarian nama pekerjaan, lokasi, perusahaan, PTW, SIKKA
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('ptw_classification', 'like', "%{$term}%")
                  ->orWhere('sikka', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $works = $query->orderBy('start_date', 'desc')->orderBy('id', 'desc')->get();

        // Statistik mengikuti tanggal acuan terpilih
        $stats = $this->getStats($dateMode === 'today' ? ($selectedDate ?: $today) : null);

        // Grafik Pekerjaan per Perusahaan menggunakan kumpulan data yang sesuai dengan mode/tabel saat ini
        $companyDistribution = $this->getCompanyDistribution($works);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'works' => $works,
                'stats' => $stats,
                'company_distribution' => $companyDistribution,
                'today_date' => $today,
                'companies' => $companies,
                'ptw_classifications' => $ptwClassifications,
                'sikkas' => $sikkas,
            ]);
        }

        return view('daily-work.index', compact(
            'works',
            'stats',
            'companyDistribution',
            'companies',
            'ptwClassifications',
            'sikkas',
            'today'
        ));
    }

    /**
     * Cek potensi pekerjaan serupa / duplikat.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name'));
        $unit = trim((string) ($request->input('unit') ?? $request->input('company')));
        $startDate = $request->input('start_date');
        $ignoreId = $request->input('ignore_id');

        if (empty($name) || empty($unit)) {
            return response()->json(['duplicate' => false]);
        }

        $query = DailyWork::where('unit', $unit)
            ->where(function ($q) use ($name) {
                $q->where('name', $name)
                  ->orWhere('name', 'like', "%{$name}%");
            });

        if ($startDate) {
            $query->whereDate('start_date', $startDate);
        }

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $duplicates = $query->latest('id')->take(3)->get();

        if ($duplicates->isNotEmpty()) {
            $first = $duplicates->first();
            return response()->json([
                'duplicate' => true,
                'message' => "Ditemukan {$duplicates->count()} pekerjaan serupa di {$unit} pada tanggal yang sama.",
                'data' => $first,
                'created_at' => $first->created_at ? $first->created_at->format('d/m/Y H:i') : '-',
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Tampilkan detail pekerjaan harian.
     */
    public function show(Request $request, $id): View|JsonResponse|RedirectResponse
    {
        $work = DailyWork::find($id);

        if (!$work) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data pekerjaan tidak ditemukan.'], 404);
            }
            return redirect()->route('daily-work.index')->with('error', 'Data pekerjaan tidak ditemukan.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'work' => $work,
            ]);
        }

        return view('daily-work.show', compact('work'));
    }

    /**
     * Pastikan pengguna memiliki hak akses Admin Management.
     */
    protected function authorizeAdminManagement(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isActive() || !$user->isAdminManagement()) {
            if ($request->wantsJson() || $request->ajax()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh Admin Management.',
                ], 403));
            }
            abort(403, 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh Admin Management.');
        }
    }

    /**
     * Simpan data pekerjaan harian baru.
     * Tidak lagi menggunakan field kategori manual atau status manual.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        // Support field 'company' alias untuk 'unit'
        if (!$request->has('unit') && $request->has('company')) {
            $request->merge(['unit' => $request->input('company')]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:100', // Perusahaan
            'ptw_classification' => 'required|string|max:100',
            'sikka' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['category'] = 'Umum';
        $validated['executor'] = $validated['ptw_classification'];

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $validated['status'] = ($validated['end_date'] < $today) ? DailyWork::STATUS_COMPLETED : DailyWork::STATUS_ONGOING;

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $work = DailyWork::create($validated);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Daily Work',
                'action' => 'TAMBAH',
                'record_type' => DailyWork::class,
                'record_id' => $work->id,
                'description' => "Menambahkan pekerjaan: {$work->name} ({$work->unit} - {$work->ptw_classification})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pekerjaan baru berhasil ditambahkan.',
                    'work' => $work,
                    'stats' => $this->getStats(),
                    'company_distribution' => $this->getCompanyDistribution(),
                ], 201);
            }

            return redirect()->route('daily-work.index')->with('success', 'Pekerjaan baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Gagal menyimpan data pekerjaan: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui data pekerjaan harian.
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $work = DailyWork::find($id);

        if (!$work) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data pekerjaan tidak ditemukan.'], 404);
            }
            return redirect()->route('daily-work.index')->with('error', 'Data pekerjaan tidak ditemukan.');
        }

        if (!$request->has('unit') && $request->has('company')) {
            $request->merge(['unit' => $request->input('company')]);
        }

        $mergedInput = [
            'name' => $request->input('name', $work->name),
            'unit' => $request->input('unit', $work->unit),
            'ptw_classification' => $request->input('ptw_classification', $work->ptw_classification),
            'sikka' => $request->input('sikka', $work->sikka),
            'start_date' => $request->input('start_date', $work->start_date ? $work->start_date->format('Y-m-d') : null),
            'end_date' => $request->input('end_date', $work->end_date ? $work->end_date->format('Y-m-d') : null),
            'location' => $request->input('location', $work->location),
            'description' => $request->exists('description') ? $request->input('description') : $work->description,
        ];
        $request->merge($mergedInput);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'ptw_classification' => 'required|string|max:100',
            'sikka' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['executor'] = $validated['ptw_classification'];
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $validated['status'] = ($validated['end_date'] < $today) ? DailyWork::STATUS_COMPLETED : DailyWork::STATUS_ONGOING;

        $oldData = $work->only(array_keys($validated));
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $work->update($validated);
            $changedKeys = array_keys($work->getChanges());
            $changes = [
                'before' => array_intersect_key($oldData, array_flip($changedKeys)),
                'after' => $work->getChanges(),
            ];

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Daily Work',
                'action' => 'UBAH',
                'record_type' => DailyWork::class,
                'record_id' => $work->id,
                'description' => "Memperbarui pekerjaan: {$work->name}",
                'changes' => $changes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pekerjaan berhasil diperbarui.',
                    'work' => $work->fresh(),
                    'stats' => $this->getStats(),
                    'company_distribution' => $this->getCompanyDistribution(),
                ]);
            }

            return redirect()->route('daily-work.index')->with('success', 'Data pekerjaan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pekerjaan harian.
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $work = DailyWork::find($id);

        if (!$work) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data pekerjaan tidak ditemukan.'], 404);
            }
            return redirect()->route('daily-work.index')->with('error', 'Data pekerjaan tidak ditemukan.');
        }

        $workName = $work->name;
        $workUnit = $work->unit;
        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $work->delete();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Daily Work',
                'action' => 'HAPUS',
                'record_type' => DailyWork::class,
                'record_id' => (int) $id,
                'description' => "Menghapus pekerjaan: {$workName} ({$workUnit})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pekerjaan berhasil dihapus.',
                    'stats' => $this->getStats(),
                    'company_distribution' => $this->getCompanyDistribution(),
                ]);
            }

            return redirect()->route('daily-work.index')->with('success', 'Pekerjaan berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
            }
            return redirect()->route('daily-work.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Export data pekerjaan harian ke PDF.
     */
    public function export(Request $request): Response
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $query = DailyWork::query();

        if ($request->filled('company') || $request->filled('unit')) {
            $comp = $request->input('company') ?? $request->input('unit');
            $query->where('unit', $comp);
        }
        if ($request->filled('ptw_classification')) {
            $query->where('ptw_classification', $request->input('ptw_classification'));
        }
        if ($request->filled('sikka')) {
            $query->where('sikka', $request->input('sikka'));
        }

        $dateMode = $request->input('date_mode', 'today');
        $selectedDate = $request->input('date');

        if ($dateMode === 'today') {
            $targetDate = $selectedDate ?: $today;
            $query->whereDate('start_date', $targetDate);
        } elseif ($request->filled('date')) {
            $query->whereDate('start_date', '<=', $selectedDate)->whereDate('end_date', '>=', $selectedDate);
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $query->whereDate('start_date', '>=', $request->input('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->whereDate('end_date', '<=', $request->input('end_date'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('ptw_classification', 'like', "%{$search}%")
                  ->orWhere('sikka', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('start_date', 'desc')->get();
        $fileName = 'Laporan_Daily_Work_' . date('Ymd_His') . '.pdf';

        $filterPeriod = ($request->filled('start_date') || $request->filled('end_date'))
            ? ($request->input('start_date', 'Awal') . ' s/d ' . $request->input('end_date', 'Sekarang'))
            : ($dateMode === 'today' ? 'Hari Ini (' . date('d/m/Y') . ')' : 'Seluruh Periode');

        $filterScope = ($request->input('company') ?? $request->input('unit', 'Semua Perusahaan')) . ' / ' . $request->input('ptw_classification', 'Semua PTW');

        $pdf = Pdf::loadView('exports.daily-work-pdf', [
            'title' => 'Laporan Daily Work - HSSE Manajemen',
            'reportTitle' => 'LAPORAN REKAPITULASI DAILY WORK',
            'moduleName' => 'Daily Work (Pekerjaan Harian)',
            'filterPeriod' => $filterPeriod,
            'filterScope' => $filterScope,
            'totalRecords' => $records->count(),
            'records' => $records,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    // =========================================================================
    // FITUR CRUD MASTER DATA (PERUSAHAAN, PTW CLASSIFICATION, SIKKA)
    // =========================================================================

    public function getMasters(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'companies' => DailyWorkCompany::orderBy('name')->get(),
            'ptw_classifications' => PtwClassification::orderBy('name')->get(),
            'sikkas' => Sikka::orderBy('name')->get(),
        ]);
    }

    public function storeCompany(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name'));
        if (strtoupper($name) === 'SOKKO') {
            return response()->json(['success' => false, 'message' => 'Perusahaan SOKKO sudah tidak diizinkan.'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:daily_work_companies,name',
        ]);

        $company = DailyWorkCompany::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Perusahaan '{$company->name}' berhasil ditambahkan.",
            'data' => $company,
            'companies' => DailyWorkCompany::orderBy('name')->get(),
        ]);
    }

    public function updateCompany(Request $request, $id): JsonResponse
    {
        $company = DailyWorkCompany::findOrFail($id);
        $name = trim((string) $request->input('name'));
        if (strtoupper($name) === 'SOKKO') {
            return response()->json(['success' => false, 'message' => 'Perusahaan SOKKO sudah tidak diizinkan.'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:daily_work_companies,name,' . $id,
        ]);

        $oldName = $company->name;
        $company->update(['name' => $validated['name']]);

        // Sinkronkan pada data daily_works yang pernah menggunakan nama perusahaan lama
        DailyWork::where('unit', $oldName)->update(['unit' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => "Perusahaan berhasil diperbarui.",
            'data' => $company,
            'companies' => DailyWorkCompany::orderBy('name')->get(),
        ]);
    }

    public function destroyCompany($id): JsonResponse
    {
        $company = DailyWorkCompany::findOrFail($id);
        $usageCount = DailyWork::where('unit', $company->name)->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Perusahaan '{$company->name}' tidak dapat dihapus karena masih digunakan pada {$usageCount} data pekerjaan harian. Anda dapat menonaktifkannya.",
            ], 422);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => "Perusahaan '{$company->name}' berhasil dihapus.",
            'companies' => DailyWorkCompany::orderBy('name')->get(),
        ]);
    }

    public function toggleCompany($id): JsonResponse
    {
        $company = DailyWorkCompany::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        return response()->json([
            'success' => true,
            'message' => "Status keaktifan perusahaan '{$company->name}' berhasil diubah.",
            'is_active' => $company->is_active,
            'companies' => DailyWorkCompany::orderBy('name')->get(),
        ]);
    }

    public function storePtw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:ptw_classifications,name',
        ]);

        $ptw = PtwClassification::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "PTW Classification '{$ptw->name}' berhasil ditambahkan.",
            'data' => $ptw,
            'ptw_classifications' => PtwClassification::orderBy('name')->get(),
        ]);
    }

    public function updatePtw(Request $request, $id): JsonResponse
    {
        $ptw = PtwClassification::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:ptw_classifications,name,' . $id,
        ]);

        $oldName = $ptw->name;
        $ptw->update(['name' => $validated['name']]);
        DailyWork::where('ptw_classification', $oldName)->update(['ptw_classification' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => "PTW Classification berhasil diperbarui.",
            'data' => $ptw,
            'ptw_classifications' => PtwClassification::orderBy('name')->get(),
        ]);
    }

    public function destroyPtw($id): JsonResponse
    {
        $ptw = PtwClassification::findOrFail($id);
        $usageCount = DailyWork::where('ptw_classification', $ptw->name)->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "PTW Classification '{$ptw->name}' tidak dapat dihapus karena masih digunakan pada {$usageCount} data pekerjaan.",
            ], 422);
        }

        $ptw->delete();

        return response()->json([
            'success' => true,
            'message' => "PTW Classification '{$ptw->name}' berhasil dihapus.",
            'ptw_classifications' => PtwClassification::orderBy('name')->get(),
        ]);
    }

    public function storeSikka(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:sikkas,name',
        ]);

        $sikka = Sikka::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "SIKKA '{$sikka->name}' berhasil ditambahkan.",
            'data' => $sikka,
            'sikkas' => Sikka::orderBy('name')->get(),
        ]);
    }

    public function updateSikka(Request $request, $id): JsonResponse
    {
        $sikka = Sikka::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:sikkas,name,' . $id,
        ]);

        $oldName = $sikka->name;
        $sikka->update(['name' => $validated['name']]);
        DailyWork::where('sikka', $oldName)->update(['sikka' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => "SIKKA berhasil diperbarui.",
            'data' => $sikka,
            'sikkas' => Sikka::orderBy('name')->get(),
        ]);
    }

    public function destroySikka($id): JsonResponse
    {
        $sikka = Sikka::findOrFail($id);
        $usageCount = DailyWork::where('sikka', $sikka->name)->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "SIKKA '{$sikka->name}' tidak dapat dihapus karena masih digunakan pada {$usageCount} data pekerjaan.",
            ], 422);
        }

        $sikka->delete();

        return response()->json([
            'success' => true,
            'message' => "SIKKA '{$sikka->name}' berhasil dihapus.",
            'sikkas' => Sikka::orderBy('name')->get(),
        ]);
    }
}

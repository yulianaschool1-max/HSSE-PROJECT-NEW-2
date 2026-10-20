<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueFollowUpRequest;
use App\Http\Requests\UpdateIssueFollowUpRequest;
use App\Models\ActivityLog;
use App\Models\IssueFollowUp;
use App\Services\IssueFollowUpService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssueFollowUpController extends Controller
{
    public function __construct(
        protected IssueFollowUpService $service
    ) {}

    /**
     * Tampilkan halaman utama Issue & Follow Up beserta statistik, grafik, highlight terbaru, dan tabel.
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeDivision = trim((string) $request->input('hsse_division', $request->input('division', '')));

        $query = $this->service->buildFilteredQuery($request);
        $allFiltered = (clone $query)->get();

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 10;
        $issues = $query->paginate($perPage)->withQueryString();

        // Statistik dari data sesuai filter (atau seluruh data jika tanpa filter)
        $stats = $this->service->calculateStats($allFiltered);

        // Ringkasan per Divisi H, S, E (dari seluruh data tanpa filter divisi agar badge tab selalu menampilkan jumlah tiap divisi)
        $reqWithoutDiv = Request::create('/issue-follow-up', 'GET', collect($request->query())->except(['hsse_division', 'division'])->all());
        $baseWithoutDiv = $this->service->buildFilteredQuery($reqWithoutDiv)->get();
        $divisionSummary = $this->service->calculateDivisionSummary($baseWithoutDiv);

        // Data Grafik dari database
        $charts = $this->service->buildChartData($allFiltered);
        $trendChart = $charts['trendChart'];
        $statusChart = $charts['statusChart'];
        $categoryChart = $charts['categoryChart'];
        $divisionChart = $charts['divisionChart'];

        // Highlight Temuan Terbaru (Temuan Aktif):
        // Hanya mengambil temuan berstatus Open & In Progress (status Closed otomatis hilang dari highlight),
        // difilter sesuai divisi yang dipilih (jika ada), diurutkan berdasarkan reported_date DESC, created_at DESC, id DESC.
        $latestHighlights = $this->service->getActiveHighlights(6, $activeDivision ?: null);

        // Pilihan dropdown dinamis dari database + standar project
        $dbCategories = IssueFollowUp::select('category')->distinct()->pluck('category')->filter()->values()->all();
        $categories = array_values(array_unique(array_merge(IssueFollowUp::CATEGORIES, $dbCategories)));

        $dbUnits = IssueFollowUp::select('unit')->distinct()->pluck('unit')->filter()->values()->all();
        $units = array_values(array_unique(array_merge(IssueFollowUp::UNITS, $dbUnits)));

        $pics = IssueFollowUp::select('pic')->distinct()->orderBy('pic')->pluck('pic')->filter()->values()->all();
        $priorities = IssueFollowUp::PRIORITIES;
        $statuses = IssueFollowUp::STATUSES;
        $divisions = IssueFollowUp::HSSE_DIVISIONS;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'active_division' => $activeDivision,
                'issues' => $issues->items(),
                'all_filtered' => $allFiltered->values(),
                'latest_highlights' => $latestHighlights->values(),
                'pagination' => [
                    'current_page' => $issues->currentPage(),
                    'last_page' => $issues->lastPage(),
                    'per_page' => $issues->perPage(),
                    'total' => $issues->total(),
                ],
                'stats' => $stats,
                'divisionSummary' => $divisionSummary,
                'trendChart' => $trendChart,
                'statusChart' => $statusChart,
                'categoryChart' => $categoryChart,
                'divisionChart' => $divisionChart,
            ]);
        }

        return view('data.issue-follow-up.index', compact(
            'issues',
            'allFiltered',
            'latestHighlights',
            'stats',
            'divisionSummary',
            'activeDivision',
            'divisions',
            'trendChart',
            'statusChart',
            'categoryChart',
            'divisionChart',
            'categories',
            'units',
            'pics',
            'priorities',
            'statuses'
        ));
    }

    /**
     * Halaman form tambah temuan baru.
     */
    public function create(): View
    {
        $divisions = IssueFollowUp::HSSE_DIVISIONS;
        $categories = IssueFollowUp::CATEGORIES;
        $units = IssueFollowUp::UNITS;
        $priorities = IssueFollowUp::PRIORITIES;
        $statuses = IssueFollowUp::STATUSES;

        return view('data.issue-follow-up.create', compact(
            'divisions',
            'categories',
            'units',
            'priorities',
            'statuses'
        ));
    }

    /**
     * Simpan temuan baru ke database MySQL.
     */
    public function store(StoreIssueFollowUpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $issue = $this->service->createIssue(
                $request->validated(),
                $request->file('attachment'),
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Temuan HSSE baru berhasil disimpan ke database.',
                    'issue' => $issue,
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ], 201);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('success', "Temuan [{$issue->issue_code}] \"{$issue->title}\" berhasil ditambahkan.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan temuan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail temuan beserta riwayat tindak lanjut secara kronologis.
     */
    public function show(Request $request, $id): View|JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::with(['logs', 'chronologicalLogs'])->find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        $activities = ActivityLog::where('module', 'Issue & Follow Up')
            ->where('record_id', $issue->id)
            ->latest('id')
            ->take(20)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'issue' => $issue,
                'logs' => $issue->logs,
                'chronological_logs' => $issue->chronologicalLogs,
                'activities' => $activities,
            ]);
        }

        return view('data.issue-follow-up.show', compact('issue', 'activities'));
    }

    /**
     * Halaman form edit temuan.
     */
    public function edit($id): View|RedirectResponse
    {
        $issue = IssueFollowUp::with('logs')->find($id);

        if (!$issue) {
            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        $divisions = IssueFollowUp::HSSE_DIVISIONS;
        $categories = array_values(array_unique(array_merge(IssueFollowUp::CATEGORIES, [$issue->category])));
        $units = array_values(array_unique(array_merge(IssueFollowUp::UNITS, [$issue->unit])));
        $priorities = IssueFollowUp::PRIORITIES;
        $statuses = IssueFollowUp::STATUSES;

        return view('data.issue-follow-up.edit', compact(
            'issue',
            'divisions',
            'categories',
            'units',
            'priorities',
            'statuses'
        ));
    }

    /**
     * Perbarui data temuan di MySQL.
     */
    public function update(UpdateIssueFollowUpRequest $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        try {
            $updated = $this->service->updateIssue(
                $issue,
                $request->validated(),
                $request->file('attachment'),
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data temuan berhasil diperbarui.',
                    'issue' => $updated,
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return redirect()->route('issue-follow-up.show', $updated->id)
                ->with('success', "Data temuan [{$updated->issue_code}] berhasil diperbarui.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui temuan: ' . $e->getMessage());
        }
    }

    /**
     * Tambahkan catatan tindak lanjut (Follow Up) tanpa menimpa riwayat sebelumnya.
     */
    public function addFollowUp(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string'],
            'status' => ['nullable', 'string', Rule::in(['Open', 'In Progress', 'Closed', 'Close'])],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:5120'],
        ], [
            'note.required' => 'Catatan tindak lanjut wajib diisi.',
        ]);

        try {
            $log = $this->service->addFollowUp(
                $issue,
                $validated,
                $request->file('attachment'),
                $request
            );

            $freshIssue = $issue->fresh(['logs', 'chronologicalLogs']);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tindak lanjut berhasil ditambahkan dan status temuan telah diperbarui.',
                    'log' => $log,
                    'issue' => $freshIssue,
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ], 201);
            }

            return back()->with('success', 'Catatan tindak lanjut berhasil disimpan.');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan tindak lanjut: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan tindak lanjut: ' . $e->getMessage());
        }
    }

    /**
     * Tutup temuan (tandai Closed) setelah penyelesaian dicatat.
     */
    public function close(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string'],
        ]);

        try {
            $updated = $this->service->changeStatus(
                $issue,
                'Closed',
                $validated['note'] ?? 'Temuan telah diselesaikan dan ditutup (Closed).',
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Temuan berhasil ditutup (Closed).',
                    'issue' => $updated,
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return back()->with('success', "Temuan [{$updated->issue_code}] berhasil ditutup (Closed).");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menutup temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menutup temuan: ' . $e->getMessage());
        }
    }

    /**
     * Buka kembali temuan yang sudah Closed melalui aksi khusus yang tervalidasi.
     */
    public function reopen(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string'],
            'status' => ['nullable', 'string', Rule::in(['Open', 'In Progress'])],
        ], [
            'note.required' => 'Alasan membuka kembali temuan wajib diisi.',
        ]);

        try {
            $targetStatus = $validated['status'] ?? 'In Progress';
            $updated = $this->service->changeStatus(
                $issue,
                $targetStatus,
                $validated['note'],
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Temuan berhasil dibuka kembali dengan status {$updated->status}.",
                    'issue' => $updated,
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return back()->with('success', "Temuan [{$updated->issue_code}] berhasil dibuka kembali.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka kembali temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal membuka kembali temuan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus temuan beserta riwayatnya dari database.
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        try {
            $code = $issue->issue_code;
            $this->service->deleteIssue($issue, $request);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data temuan [{$code}] berhasil dihapus.",
                    'latest_highlights' => $this->service->getActiveHighlights(6)->values(),
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('success', "Data temuan [{$code}] berhasil dihapus.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus temuan: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor daftar temuan ke format PDF sesuai filter aktif.
     */
    public function export(Request $request): Response
    {
        $records = $this->service->buildFilteredQuery($request)->get();

        $division = $request->input('hsse_division', $request->input('division'));
        $divisionLabel = 'Semua Divisi';
        if ($division && isset(IssueFollowUp::HSSE_DIVISIONS[$division])) {
            $val = IssueFollowUp::HSSE_DIVISIONS[$division];
            $divisionLabel = is_array($val) ? ($val['label'] ?? $division) : $val;
        }

        $pdf = Pdf::loadView('exports.issue-follow-up-pdf', [
            'reportTitle' => 'LAPORAN TEMUAN & TINDAK LANJUT (ISSUE & FOLLOW UP)',
            'moduleName' => 'Issue & Follow Up',
            'filterInfo' => [
                'Divisi HSSE' => $divisionLabel,
                'Status' => $request->input('status', 'Semua'),
                'Prioritas' => $request->input('priority', 'Semua'),
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => auth()->user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Issue-FollowUp-' . now()->format('Ymd-His') . '.pdf');
    }
}


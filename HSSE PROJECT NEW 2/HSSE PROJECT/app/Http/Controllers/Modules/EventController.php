<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Event;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    /**
     * Display a listing of events with metrics and charts data.
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Base query for filtering
        $query = Event::query()->withCount('documents');

        // Filter: Date Range
        $startDate = null;
        $endDate = null;

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->input('date_range'));
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                } catch (\Exception) {
                    try {
                        $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                        $endDate = Carbon::parse(trim($dates[1]))->endOfDay();
                    } catch (\Exception) {
                        // ignore invalid date
                    }
                }
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            } catch (\Exception) {}
        }

        if ($startDate && $endDate) {
            $query->whereBetween('event_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        // Filter: Category
        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        // Filter: Unit / Component
        if ($request->filled('unit') && $request->input('unit') !== 'Semua') {
            $query->where('unit', $request->input('unit'));
        }

        // Filter: Location
        if ($request->filled('location') && $request->input('location') !== 'Semua') {
            $query->where('location', $request->input('location'));
        }

        // Filter: Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('pic', 'like', "%{$search}%");
            });
        }

        // Filter: Status (optional additional filter)
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        // Clone query for metrics calculations before pagination
        // Hanya kegiatan berstatus 'Selesai' yang dihitung sebagai kegiatan selesai/terlaksana
        $statsQuery = clone $query;
        $totalKegiatan = (clone $statsQuery)->count();
        $selesai = (clone $statsQuery)->where('status', Event::STATUS_SELESAI)->count();
        $berlangsung = (clone $statsQuery)->where('status', 'Berlangsung')->count();
        $direncanakan = (clone $statsQuery)->where('status', 'Direncanakan')->count();
        $dibatalkan = (clone $statsQuery)->where('status', 'Dibatalkan')->count();
        $selesaiParticipants = (int) (clone $statsQuery)->where('status', Event::STATUS_SELESAI)->sum('participants');

        // 2. Monthly aggregation for Monthly Chart
        // Group by month (1 to 12) and category
        $monthlyQuery = clone $statsQuery;
        $monthlyRows = $monthlyQuery
            ->select([
                DB::raw('MONTH(event_date) as month'),
                'category',
                DB::raw('COUNT(*) as total')
            ])
            ->groupBy(DB::raw('MONTH(event_date)'), 'category')
            ->orderBy(DB::raw('MONTH(event_date)'))
            ->get();

        // Standard categories
        $categoriesList = ['Pelatihan', 'Safety Talk', 'Inspeksi', 'Kampanye', 'Simulasi/Drill', 'Sosialisasi', 'Lainnya'];

        // Prepare monthly dataset
        $monthsLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyDatasets = [];
        $categoryPalette = [
            'Pelatihan' => '#3b82f6',     // Blue
            'Safety Talk' => '#06b6d4',   // Cyan
            'Inspeksi' => '#f97316',      // Orange
            'Kampanye' => '#f43f5e',      // Salmon / Rose
            'Simulasi/Drill' => '#a855f7', // Purple
            'Sosialisasi' => '#10b981',   // Teal / Mint
            'Lainnya' => '#ec4899',       // Pink
        ];

        foreach ($categoriesList as $cat) {
            $monthlyDatasets[$cat] = array_fill(0, 12, 0);
        }

        foreach ($monthlyRows as $row) {
            $monthIdx = (int)$row->month - 1;
            if ($monthIdx >= 0 && $monthIdx < 12) {
                $catName = in_array($row->category, $categoriesList) ? $row->category : 'Lainnya';
                $monthlyDatasets[$catName][$monthIdx] += (int)$row->total;
            }
        }

        // 3. Category distribution for Donut Chart
        $categoryStats = (clone $statsQuery)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $categoryChartData = [];
        foreach ($categoryStats as $cStat) {
            $percentage = $totalKegiatan > 0 ? round(($cStat->total / $totalKegiatan) * 100) : 0;
            $color = $categoryPalette[$cStat->category] ?? '#94a3b8';
            $categoryChartData[] = [
                'name' => $cStat->category,
                'count' => (int)$cStat->total,
                'percentage' => $percentage,
                'color' => $color,
            ];
        }

        // 4. Unit distribution for Horizontal Bar Chart
        $unitStats = (clone $statsQuery)
            ->select('unit', DB::raw('COUNT(*) as total'))
            ->groupBy('unit')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $unitColors = ['#3b82f6', '#f97316', '#10b981', '#a855f7', '#6366f1', '#eab308'];
        $unitChartData = [];
        foreach ($unitStats as $idx => $uStat) {
            $unitChartData[] = [
                'unit' => $uStat->unit,
                'count' => (int)$uStat->total,
                'color' => $unitColors[$idx % count($unitColors)],
            ];
        }

        // 5. Pagination for Events Table — Urutkan dari tanggal terbaru ke terlama (event_date DESC, created_at DESC, id DESC)
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $events = $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // 6. Filter dropdown sources from DB
        $allCategories = Event::query()->select('category')->distinct()->pluck('category')->sort()->values();
        $allUnits = Event::query()->select('unit')->distinct()->pluck('unit')->sort()->values();
        $allLocations = Event::query()->select('location')->distinct()->pluck('location')->sort()->values();

        // If AJAX request asking for JSON (e.g. dynamic refresh without page reload)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'stats' => [
                    'total' => $totalKegiatan,
                    'selesai' => $selesai,
                    'berlangsung' => $berlangsung,
                    'direncanakan' => $direncanakan,
                    'dibatalkan' => $dibatalkan,
                    'selesai_participants' => $selesaiParticipants,
                ],
                'monthly' => [
                    'labels' => $monthsLabels,
                    'datasets' => $monthlyDatasets,
                ],
                'categories' => $categoryChartData,
                'units' => $unitChartData,
                'events' => $events,
            ]);
        }

        return view('events.index', compact(
            'events',
            'totalKegiatan',
            'selesai',
            'berlangsung',
            'direncanakan',
            'dibatalkan',
            'selesaiParticipants',
            'monthsLabels',
            'monthlyDatasets',
            'categoryChartData',
            'unitChartData',
            'allCategories',
            'allUnits',
            'allLocations',
            'perPage'
        ));
    }

    /**
     * Display the specified event with documentation and activity logs.
     */
    public function show($id): JsonResponse|View
    {
        $event = Event::with(['documents', 'activityLogs'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event,
                'documents' => $event->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_name' => $doc->file_name,
                        'file_path' => $doc->file_path,
                        'file_url' => $doc->file_url,
                        'mime_type' => $doc->mime_type,
                        'file_size' => $doc->file_size,
                        'created_at' => $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : null,
                    ];
                }),
                'logs' => $event->activityLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user_name' => $log->user_name,
                        'action' => $log->action,
                        'description' => $log->description,
                        'created_at' => $log->created_at ? $log->created_at->format('d/m/Y H:i') : null,
                    ];
                }),
            ]);
        }

        return view('events.show', compact('event'));
    }

    /**
     * Check for potential duplicate event records.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $title = trim((string) $request->input('title'));
        $eventDate = $request->input('event_date');
        $location = trim((string) $request->input('location'));
        $ignoreId = $request->input('ignore_id');

        if (empty($title)) {
            return response()->json(['duplicate' => false]);
        }

        $query = Event::query();

        if ($eventDate) {
            $query->whereDate('event_date', $eventDate);
        }

        $query->where(function ($q) use ($title, $location) {
            $q->where('title', $title)
              ->orWhere('title', 'like', "%{$title}%");
        });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $duplicates = $query->latest('id')->take(3)->get();

        if ($duplicates->isNotEmpty()) {
            $first = $duplicates->first();
            $creatorLog = ActivityLog::where('module', 'events')
                ->where('action', 'TAMBAH')
                ->where('record_id', $first->id)
                ->latest('id')
                ->first();

            return response()->json([
                'duplicate' => true,
                'message' => "Ditemukan kegiatan serupa '{$first->title}' pada tanggal yang sama (" . ($first->event_date ? $first->event_date->format('d/m/Y') : '-') . ").",
                'data' => $first,
                'created_by' => $creatorLog ? $creatorLog->user_name : ($first->created_by ?? 'Tim HSSE'),
                'created_at' => $first->created_at ? $first->created_at->format('d/m/Y H:i') : '-',
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $userName = Auth::user()->name ?? 'Budi Santoso';

        DB::beginTransaction();
        try {
            $event = Event::create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'location' => $validated['location'],
                'participants' => (int)($validated['participants'] ?? 0),
                'pic' => $validated['pic'] ?? $userName,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'created_by' => $userName,
                'updated_by' => $userName,
            ]);

            // Handle file uploads
            if ($request->hasFile('documentation_files')) {
                foreach ($request->file('documentation_files') as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $storedPath = $file->store('events/documentation', 'public');
                        $mime = $file->getMimeType();
                        $size = $file->getSize();

                        $document = Document::create([
                            'title' => pathinfo($originalName, PATHINFO_FILENAME),
                            'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                            'module' => 'events',
                            'record_id' => $event->id,
                            'file_name' => $originalName,
                            'file_path' => $storedPath,
                            'mime_type' => $mime,
                            'file_size' => $size,
                            'uploaded_by' => $userName,
                            'description' => 'Dokumentasi kegiatan ' . $event->title,
                        ]);

                        if (empty($event->documentation)) {
                            $event->update(['documentation' => $storedPath]);
                        }
                    }
                }
            }

            // Create Activity Log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'TAMBAH',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Menambahkan event baru: {$event->title} ({$event->category})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil ditambahkan!',
                    'data' => $event->load('documents'),
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, $id): RedirectResponse|JsonResponse
    {
        $event = Event::findOrFail($id);
        $validated = $request->validated();
        $userName = Auth::user()->name ?? 'Budi Santoso';

        DB::beginTransaction();
        try {
            $event->update([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'] ?? $event->start_time,
                'end_time' => $validated['end_time'] ?? $event->end_time,
                'location' => $validated['location'],
                'participants' => (int)($validated['participants'] ?? $event->participants),
                'pic' => $validated['pic'] ?? $event->pic,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? $event->description,
                'updated_by' => $userName,
            ]);

            // Handle file uploads
            if ($request->hasFile('documentation_files')) {
                foreach ($request->file('documentation_files') as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $storedPath = $file->store('events/documentation', 'public');
                        $mime = $file->getMimeType();
                        $size = $file->getSize();

                        Document::create([
                            'title' => pathinfo($originalName, PATHINFO_FILENAME),
                            'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                            'module' => 'events',
                            'record_id' => $event->id,
                            'file_name' => $originalName,
                            'file_path' => $storedPath,
                            'mime_type' => $mime,
                            'file_size' => $size,
                            'uploaded_by' => $userName,
                            'description' => 'Dokumentasi kegiatan ' . $event->title,
                        ]);

                        if (empty($event->documentation)) {
                            $event->update(['documentation' => $storedPath]);
                        }
                    }
                }
            }

            // Create Activity Log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'UBAH',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Memperbarui event: {$event->title}",
                'changes' => [
                    'after' => $event->getChanges(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil diperbarui!',
                    'data' => $event->load('documents'),
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Upload additional documentation directly to an event.
     */
    public function uploadDocumentation(Request $request, $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $request->validate([
            'files.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
        ]);

        $userName = Auth::user()->name ?? 'Budi Santoso';
        $uploaded = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $mime = $file->getMimeType();
                    $size = $file->getSize();

                    if (str_starts_with($mime, 'image/')) {
                        $imageService = app(\App\Services\ImageUploadService::class);
                        $media = $imageService->uploadImage($file, 'events/documentation', $event, 'events', 'Dokumentasi ' . $event->title);
                        $storedPath = $media->file_path;
                        $size = $media->compressed_size;
                    } else {
                        $storedPath = $file->store('events/documentation', 'public');
                    }

                    $doc = Document::create([
                        'title' => pathinfo($originalName, PATHINFO_FILENAME),
                        'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                        'module' => 'events',
                        'record_id' => $event->id,
                        'file_name' => $originalName,
                        'file_path' => $storedPath,
                        'mime_type' => $mime,
                        'file_size' => $size,
                        'uploaded_by' => $userName,
                        'description' => 'Upload tambahan untuk ' . $event->title,
                    ]);

                    $uploaded[] = [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_name' => $doc->file_name,
                        'file_url' => $doc->file_url,
                        'mime_type' => $doc->mime_type,
                    ];
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'UPLOAD',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => 'Mengunggah ' . count($uploaded) . " file dokumentasi baru untuk {$event->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi berhasil diunggah!',
            'files' => $uploaded,
        ]);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Request $request, $id): RedirectResponse|JsonResponse
    {
        $event = Event::findOrFail($id);
        $userName = Auth::user()->name ?? 'Budi Santoso';
        $title = $event->title;

        DB::beginTransaction();
        try {
            // Delete associated documents
            $documents = Document::where('module', 'events')->where('record_id', $event->id)->get();
            foreach ($documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'HAPUS',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Menghapus kegiatan: {$title}",
                'changes' => [
                    'before' => ['title' => $title, 'category' => $event->category, 'unit' => $event->unit],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $event->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil dihapus!',
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Export filtered events to PDF format.
     */
    public function export(Request $request): Response
    {
        $query = Event::query()->withCount('documents');

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('unit') && $request->input('unit') !== 'Semua') {
            $query->where('unit', $request->input('unit'));
        }
        if ($request->filled('location') && $request->input('location') !== 'Semua') {
            $query->where('location', $request->input('location'));
        }
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.events-pdf', [
            'reportTitle' => 'LAPORAN DAFTAR KEGIATAN & EVENTS HSSE',
            'moduleName' => 'Events',
            'filterInfo' => [
                'Kategori' => $request->input('category', 'Semua'),
                'Unit' => $request->input('unit', 'Semua'),
                'Status' => $request->input('status', 'Semua'),
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Events-HSSE-' . now()->format('Ymd-His') . '.pdf');
    }
}


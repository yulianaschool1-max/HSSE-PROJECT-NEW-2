<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Incident;
use App\Services\ImageUploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Tampilkan halaman Incident sesuai referensi Apps/incident/index.blade.php
     * DENGAN IDENTITAS WARNA MERAH KHAS INCIDENT TETAP DIPERTAHANKAN.
     */
    public function index(Request $request): View|JsonResponse
    {
        $baseQuery = Incident::query();

        // 1. Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nomor_insiden', 'like', "%{$search}%")
                  ->orWhere('incident_code', 'like', "%{$search}%")
                  ->orWhere('lokasi_kejadian', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('reported_by', 'like', "%{$search}%")
                  ->orWhere('deskripsi_kejadian', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Filter Klasifikasi Area
        if ($request->filled('klasifikasi_area')) {
            $baseQuery->where('klasifikasi_area', $request->input('klasifikasi_area'));
        }

        // 3. Filter Kategori Umum (JSON tag check)
        if ($request->filled('kategori_umum')) {
            $tagUmum = $request->input('kategori_umum');
            $baseQuery->where(function ($q) use ($tagUmum) {
                $q->whereJsonContains('kategori_umum', $tagUmum)
                  ->orWhere('kategori_umum', 'like', "%\"{$tagUmum}\"%");
            });
        }

        // 4. Filter Kategori EBTKE (JSON tag check)
        if ($request->filled('kategori_ebtke')) {
            $tagEbtke = $request->input('kategori_ebtke');
            $baseQuery->where(function ($q) use ($tagEbtke) {
                $q->whereJsonContains('kategori_ebtke', $tagEbtke)
                  ->orWhere('kategori_ebtke', 'like', "%\"{$tagEbtke}\"%");
            });
        }

        // 5. Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $baseQuery->whereBetween('tanggal_waktu', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay(),
            ]);
        } elseif ($request->filled('start_date')) {
            $baseQuery->where('tanggal_waktu', '>=', Carbon::parse($request->input('start_date'))->startOfDay());
        } elseif ($request->filled('end_date')) {
            $baseQuery->where('tanggal_waktu', '<=', Carbon::parse($request->input('end_date'))->endOfDay());
        }

        // A. Data Akumulasi Penuh untuk 3 Donut Charts & Rekapitulasi (Bebas dari limit/offset pagination)
        $chartIncidents = (clone $baseQuery)->orderBy('tanggal_waktu', 'desc')->get();

        // B. Data Tabel Insiden dengan Pagination 10 baris per halaman
        $incidents = (clone $baseQuery)->orderBy('tanggal_waktu', 'desc')->paginate(10)->withQueryString();

        // 1. Chart Area (Panas Bumi vs Non-Panas Bumi)
        $panasBumiCount = $chartIncidents->filter(function ($i) {
            $val = (string) ($i->klasifikasi_area ?? $i->work_area ?? '');
            return !str_contains($val, 'Non') && str_contains($val, 'Panas');
        })->count();

        $nonPanasBumiCount = $chartIncidents->filter(function ($i) {
            $val = (string) ($i->klasifikasi_area ?? $i->work_area ?? '');
            return str_contains($val, 'Non');
        })->count();

        // 1. Chart Klasifikasi Area: Oranye Terang (Geothermal) & Biru Langit (Non-Geothermal)
        $chartArea = [
            'labels' => ['Kecelakaan Panas Bumi', 'Kecelakaan Non-Panas Bumi'],
            'data' => [$panasBumiCount, $nonPanasBumiCount],
            'colors' => ['#ea580c', '#0284c7'], // Oranye Terang Geothermal & Biru Langit Non-Panas
            'total' => $chartIncidents->count(),
        ];

        // 2. Chart Kategori Umum Tags - Palet Warna Modern Unik & Sangat Kontras per Tag
        $kategoriUmumPalette = [
            'Fatality' => '#b91c1c',                 // Merah Tua Gelap (Crimson Red)
            'LTI (Lost Time Injury)' => '#ea580c',   // Oranye Terang (Vivid Orange)
            'Medical Treatment Case' => '#0d9488',   // Pirus / Dark Teal
            'First Aid Case' => '#10b981',           // Hijau Zamrud (Emerald Green)
            'Property Damage' => '#2563eb',          // Biru Safir Terang (Sapphire Blue)
            'Near Miss (Hampir Celaka)' => '#d97706',// Kuning Amber (Golden Amber)
            'Environmental Incident' => '#65a30d',   // Hijau Limau Segar (Lime Green)
            'Vehicle Accident' => '#7c3aed',         // Ungu Lembayung (Deep Violet)
            'Fire Incident' => '#f43f5e',            // Merah Koral Terang (Coral Rose)
            'Hazard Report' => '#4338ca',            // Biru Indigo (Midnight Indigo)
        ];

        $extraUmumPalette = [
            '#06b6d4', // Cyan Pirus Muda
            '#ec4899', // Pink Fusia
            '#8b5cf6', // Lavender Purple
            '#0284c7', // Ocean Blue
            '#64748b', // Slate Elegan
        ];

        $umumFreq = [];
        $umumColors = [];
        $extraIdx = 0;
        foreach (Incident::KATEGORI_UMUM_LIST as $k) {
            $umumFreq[$k] = 0;
        }
        foreach ($chartIncidents as $inc) {
            $tags = is_array($inc->kategori_umum) ? $inc->kategori_umum : (json_decode($inc->kategori_umum, true) ?: []);
            foreach ($tags as $t) {
                if (isset($umumFreq[$t])) {
                    $umumFreq[$t]++;
                } else {
                    $umumFreq[$t] = 1;
                }
            }
        }
        foreach (array_keys($umumFreq) as $lbl) {
            if (isset($kategoriUmumPalette[$lbl])) {
                $umumColors[] = $kategoriUmumPalette[$lbl];
            } else {
                $umumColors[] = $extraUmumPalette[$extraIdx % count($extraUmumPalette)];
                $extraIdx++;
            }
        }
        $chartUmum = [
            'labels' => array_keys($umumFreq),
            'data' => array_values($umumFreq),
            'colors' => $umumColors,
            'color_map' => $kategoriUmumPalette,
            'total' => array_sum($umumFreq),
        ];

        // 3. Chart Kategori EBTKE Tags - 5 Tingkat Keparahan ESDM Berwarna Unik & Kontras
        $kategoriEbtkePalette = [
            'Kecelakaan Mati' => '#7f1d1d',          // Merah Marun Gelap (Deep Maroon)
            'Kecelakaan Berat' => '#dc2626',         // Merah Scarlet Terang (Bright Scarlet)
            'Kecelakaan Ringan' => '#f59e0b',        // Kuning Emas Amber (Amber Gold)
            'Kejadian Berbahaya' => '#1d4ed8',       // Biru Tua Royal (Royal Deep Blue)
            'Penyakit Akibat Kerja' => '#059669',    // Hijau Emerald (Forest Emerald)
        ];

        $ebtkeFreq = [];
        $ebtkeColors = [];
        $extraEbtkeIdx = 0;
        foreach (Incident::KATEGORI_EBTKE_LIST as $k) {
            $ebtkeFreq[$k] = 0;
        }
        foreach ($chartIncidents as $inc) {
            $tags = is_array($inc->kategori_ebtke) ? $inc->kategori_ebtke : (json_decode($inc->kategori_ebtke, true) ?: []);
            foreach ($tags as $t) {
                if (isset($ebtkeFreq[$t])) {
                    $ebtkeFreq[$t]++;
                } else {
                    $ebtkeFreq[$t] = 1;
                }
            }
        }
        foreach (array_keys($ebtkeFreq) as $lbl) {
            if (isset($kategoriEbtkePalette[$lbl])) {
                $ebtkeColors[] = $kategoriEbtkePalette[$lbl];
            } else {
                $ebtkeColors[] = $extraUmumPalette[$extraEbtkeIdx % count($extraUmumPalette)];
                $extraEbtkeIdx++;
            }
        }
        $chartEbtke = [
            'labels' => array_keys($ebtkeFreq),
            'data' => array_values($ebtkeFreq),
            'colors' => $ebtkeColors,
            'color_map' => $kategoriEbtkePalette,
            'total' => array_sum($ebtkeFreq),
        ];

        // 4. Perhitungan Metrik & Distribusi Hari Kerja Hilang (LWD)
        $totalInsiden = $chartIncidents->count();
        $totalHariHilang = (int) $chartIncidents->sum(function ($i) {
            return (int) ($i->jumlah_hari_hilang ?? $i->lost_workdays ?? 0);
        });
        $kasusHariHilangCount = $chartIncidents->filter(function ($i) {
            return ((int) ($i->jumlah_hari_hilang ?? $i->lost_workdays ?? 0)) > 0;
        })->count();

        // Distribusi Hari Hilang per Bulan (Kronologis)
        $lwdByMonth = [];
        $sortedChartIncidents = $chartIncidents->sortBy('tanggal_waktu');
        foreach ($sortedChartIncidents as $inc) {
            $tgl = $inc->tanggal_waktu ? Carbon::parse($inc->tanggal_waktu) : ($inc->incident_date ? Carbon::parse($inc->incident_date) : null);
            $key = $tgl ? $tgl->isoFormat('MMM YYYY') : 'Lainnya';
            $days = (int) ($inc->jumlah_hari_hilang ?? $inc->lost_workdays ?? 0);
            if (!isset($lwdByMonth[$key])) {
                $lwdByMonth[$key] = [
                    'hari' => 0,
                    'kasus' => 0,
                ];
            }
            $lwdByMonth[$key]['hari'] += $days;
            $lwdByMonth[$key]['kasus']++;
        }

        $chartHariHilang = [
            'labels' => array_keys($lwdByMonth),
            'days' => array_column($lwdByMonth, 'hari'),
            'cases' => array_column($lwdByMonth, 'kasus'),
            'total_days' => $totalHariHilang,
            'total_cases_affected' => $kasusHariHilangCount,
        ];

        $nextIncidentNumber = Incident::generateNextNumber();
        $areaList = Incident::KLASIFIKASI_AREAS;
        $kategoriUmumList = Incident::KATEGORI_UMUM_LIST;
        $kategoriEbtkeList = Incident::KATEGORI_EBTKE_LIST;

        $chartDetailIncidents = $chartIncidents->map(function ($inc) {
            $kategoriUmum = is_array($inc->kategori_umum) ? $inc->kategori_umum : (json_decode($inc->kategori_umum, true) ?: []);
            $kategoriEbtke = is_array($inc->kategori_ebtke) ? $inc->kategori_ebtke : (json_decode($inc->kategori_ebtke, true) ?: []);

            return [
                'id' => $inc->id,
                'nomor_insiden' => $inc->nomor_insiden ?: ('INC-' . str_pad($inc->id, 5, '0', STR_PAD_LEFT)),
                'tanggal_waktu' => $inc->tanggal_waktu ? (\Carbon\Carbon::parse($inc->tanggal_waktu)->format('d/m/Y H:i')) : ($inc->incident_date ? \Carbon\Carbon::parse($inc->incident_date)->format('d/m/Y') : '-'),
                'klasifikasi_area' => $inc->klasifikasi_area ?: ($inc->work_area ?? 'Kecelakaan Panas Bumi (Wilayah Kerja Kuasa Pertambangan Panas Bumi)'),
                'kategori_umum' => $kategoriUmum,
                'kategori_ebtke' => $kategoriEbtke,
                'lokasi_kejadian' => $inc->lokasi_kejadian ?: ($inc->location ?? '-'),
                'komponen_terkait' => $inc->komponen_terkait ?: ($inc->category ?? '-'),
                'jumlah_hari_hilang' => (int) ($inc->jumlah_hari_hilang ?? $inc->lost_workdays ?? 0),
                'status_laporan' => $inc->status_laporan ?: ($inc->status === 'Closed' ? 'Final' : 'Preliminary'),
                'penanggung_jawab' => $inc->penanggung_jawab ?: ($inc->pic ?? '-'),
                'deskripsi_kejadian' => $inc->deskripsi_kejadian ?: ($inc->incident_details ?? '-'),
            ];
        })->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'incidents' => $incidents,
                'totalInsiden' => $totalInsiden,
                'totalHariHilang' => $totalHariHilang,
                'kasusHariHilangCount' => $kasusHariHilangCount,
                'panasBumiCount' => $panasBumiCount,
                'nonPanasBumiCount' => $nonPanasBumiCount,
                'chartArea' => $chartArea,
                'chartUmum' => $chartUmum,
                'chartEbtke' => $chartEbtke,
                'chartHariHilang' => $chartHariHilang,
                'chartDetailIncidents' => $chartDetailIncidents,
            ]);
        }

        return view('data.incident.index', compact(
            'incidents',
            'totalInsiden',
            'totalHariHilang',
            'kasusHariHilangCount',
            'panasBumiCount',
            'nonPanasBumiCount',
            'chartArea',
            'chartUmum',
            'chartEbtke',
            'chartHariHilang',
            'chartDetailIncidents',
            'nextIncidentNumber',
            'areaList',
            'kategoriUmumList',
            'kategoriEbtkeList'
        ));
    }

    /**
     * Simpan Insiden Baru (Mendukung upload gambar investigasi via ImageUploadService)
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'nomor_insiden' => 'nullable|string|max:100',
            'tanggal_waktu' => 'required|date',
            'klasifikasi_area' => 'required|string|max:100',
            'kategori_umum' => 'nullable|array',
            'kategori_ebtke' => 'nullable|array',
            'lokasi_kejadian' => 'required|string|max:255',
            'komponen_terkait' => 'required|string|max:100',
            'deskripsi_kejadian' => 'required|string',
            'tindakan_perbaikan' => 'nullable|string',
            'penanggung_jawab' => 'nullable|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'status_laporan' => 'required|string|max:50',
            'jumlah_hari_hilang' => 'nullable|integer|min:0',
            'dokumen_investigasi' => 'nullable|file|max:10240',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $num = $validated['nomor_insiden'] ?? Incident::generateNextNumber();

            $docPath = null;
            if ($request->hasFile('dokumen_investigasi')) {
                $file = $request->file('dokumen_investigasi');
                $imgService = app(ImageUploadService::class);
                if ($imgService->isValidImage($file)) {
                    $media = $imgService->uploadImage($file, 'incidents/evidence', null, 'incidents', 'Foto Bukti Insiden ' . $num);
                    $docPath = $media->file_path;
                } else {
                    $docPath = $file->store('incidents/evidence', 'public');
                }
            }

            $incident = Incident::create([
                'nomor_insiden' => $num,
                'incident_code' => $num,
                'title' => $num . ' - ' . $validated['lokasi_kejadian'],
                'tanggal_waktu' => $validated['tanggal_waktu'],
                'incident_date' => $validated['tanggal_waktu'],
                'klasifikasi_area' => $validated['klasifikasi_area'],
                'incident_type' => $validated['klasifikasi_area'],
                'kategori_umum' => $validated['kategori_umum'] ?? [],
                'kategori_ebtke' => $validated['kategori_ebtke'] ?? [],
                'lokasi_kejadian' => $validated['lokasi_kejadian'],
                'location' => $validated['lokasi_kejadian'],
                'komponen_terkait' => $validated['komponen_terkait'],
                'unit' => $validated['komponen_terkait'],
                'deskripsi_kejadian' => $validated['deskripsi_kejadian'],
                'description' => $validated['deskripsi_kejadian'],
                'tindakan_perbaikan' => $validated['tindakan_perbaikan'] ?? null,
                'corrective_action' => $validated['tindakan_perbaikan'] ?? null,
                'penanggung_jawab' => $validated['penanggung_jawab'] ?? (Auth::user()?->name ?? 'Admin HSSE'),
                'reported_by' => $validated['penanggung_jawab'] ?? (Auth::user()?->name ?? 'Admin HSSE'),
                'target_penyelesaian' => $validated['target_penyelesaian'] ?? null,
                'status_laporan' => $validated['status_laporan'],
                'status' => $validated['status_laporan'] === 'Final' ? 'Selesai' : 'Investigasi',
                'jumlah_hari_hilang' => (int) ($validated['jumlah_hari_hilang'] ?? 0),
                'dokumen_investigasi' => $docPath,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Admin HSSE',
                'module' => 'Incident',
                'action' => 'TAMBAH',
                'record_type' => Incident::class,
                'record_id' => $incident->id,
                'description' => "Melaporkan insiden baru: [{$incident->nomor_insiden}] di {$incident->lokasi_kejadian}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laporan insiden berhasil disimpan!',
                    'incident' => $incident,
                ], 201);
            }

            return redirect()->route('incident.index')->with('success', "Laporan insiden [{$incident->nomor_insiden}] berhasil disimpan!");
        });
    }

    /**
     * Perbarui Laporan Insiden
     */
    public function update(Request $request, $id): RedirectResponse|JsonResponse
    {
        $incident = Incident::findOrFail($id);

        $validated = $request->validate([
            'tanggal_waktu' => 'required|date',
            'klasifikasi_area' => 'required|string|max:100',
            'kategori_umum' => 'nullable|array',
            'kategori_ebtke' => 'nullable|array',
            'lokasi_kejadian' => 'required|string|max:255',
            'komponen_terkait' => 'required|string|max:100',
            'deskripsi_kejadian' => 'required|string',
            'tindakan_perbaikan' => 'nullable|string',
            'penanggung_jawab' => 'nullable|string|max:100',
            'target_penyelesaian' => 'nullable|date',
            'status_laporan' => 'required|string|max:50',
            'jumlah_hari_hilang' => 'nullable|integer|min:0',
            'dokumen_investigasi' => 'nullable|file|max:10240',
        ]);

        return DB::transaction(function () use ($validated, $request, $incident) {
            $docPath = $incident->dokumen_investigasi;
            if ($request->hasFile('dokumen_investigasi')) {
                $file = $request->file('dokumen_investigasi');
                $imgService = app(ImageUploadService::class);
                if ($imgService->isValidImage($file)) {
                    $media = $imgService->uploadImage($file, 'incidents/evidence', $incident, 'incidents', 'Foto Bukti Insiden ' . $incident->nomor_insiden);
                    $docPath = $media->file_path;
                } else {
                    $docPath = $file->store('incidents/evidence', 'public');
                }
            }

            $incident->update([
                'tanggal_waktu' => $validated['tanggal_waktu'],
                'incident_date' => $validated['tanggal_waktu'],
                'klasifikasi_area' => $validated['klasifikasi_area'],
                'incident_type' => $validated['klasifikasi_area'],
                'kategori_umum' => $validated['kategori_umum'] ?? [],
                'kategori_ebtke' => $validated['kategori_ebtke'] ?? [],
                'lokasi_kejadian' => $validated['lokasi_kejadian'],
                'location' => $validated['lokasi_kejadian'],
                'komponen_terkait' => $validated['komponen_terkait'],
                'unit' => $validated['komponen_terkait'],
                'deskripsi_kejadian' => $validated['deskripsi_kejadian'],
                'description' => $validated['deskripsi_kejadian'],
                'tindakan_perbaikan' => $validated['tindakan_perbaikan'] ?? null,
                'corrective_action' => $validated['tindakan_perbaikan'] ?? null,
                'penanggung_jawab' => $validated['penanggung_jawab'] ?? $incident->penanggung_jawab,
                'reported_by' => $validated['penanggung_jawab'] ?? $incident->reported_by,
                'target_penyelesaian' => $validated['target_penyelesaian'] ?? null,
                'status_laporan' => $validated['status_laporan'],
                'status' => $validated['status_laporan'] === 'Final' ? 'Selesai' : 'Investigasi',
                'jumlah_hari_hilang' => (int) ($validated['jumlah_hari_hilang'] ?? 0),
                'dokumen_investigasi' => $docPath,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Admin HSSE',
                'module' => 'Incident',
                'action' => 'UBAH',
                'record_type' => Incident::class,
                'record_id' => $incident->id,
                'description' => "Memperbarui laporan insiden [{$incident->nomor_insiden}]",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laporan insiden berhasil diperbarui!',
                    'incident' => $incident,
                ]);
            }

            return redirect()->route('incident.index')->with('success', "Laporan insiden [{$incident->nomor_insiden}] berhasil diperbarui!");
        });
    }

    /**
     * Hapus Laporan Insiden
     */
    public function destroy(Request $request, $id): RedirectResponse|JsonResponse
    {
        $incident = Incident::findOrFail($id);
        $num = $incident->nomor_insiden;

        $incident->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Admin HSSE',
            'module' => 'Incident',
            'action' => 'HAPUS',
            'record_type' => Incident::class,
            'record_id' => (int) $id,
            'description' => "Menghapus laporan insiden [{$num}]",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan insiden berhasil dihapus!',
            ]);
        }

        return redirect()->route('incident.index')->with('success', "Laporan insiden [{$num}] berhasil dihapus!");
    }

    /**
     * Export data Incident ke format PDF sesuai filter aktif.
     */
    public function export(Request $request): Response
    {
        $query = Incident::query();

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nomor_insiden', 'like', "%{$search}%")
                  ->orWhere('incident_code', 'like', "%{$search}%")
                  ->orWhere('lokasi_kejadian', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('reported_by', 'like', "%{$search}%")
                  ->orWhere('deskripsi_kejadian', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Klasifikasi Area
        if ($request->filled('klasifikasi_area') && $request->input('klasifikasi_area') !== 'Semua') {
            $query->where('klasifikasi_area', $request->input('klasifikasi_area'));
        }

        // Filter Kategori Umum
        if ($request->filled('kategori_umum') && $request->input('kategori_umum') !== 'Semua') {
            $tagUmum = $request->input('kategori_umum');
            $query->where(function ($q) use ($tagUmum) {
                $q->whereJsonContains('kategori_umum', $tagUmum)
                  ->orWhere('kategori_umum', 'like', "%\"{$tagUmum}\"%");
            });
        }

        // Filter Kategori EBTKE
        if ($request->filled('kategori_ebtke') && $request->input('kategori_ebtke') !== 'Semua') {
            $tagEbtke = $request->input('kategori_ebtke');
            $query->where(function ($q) use ($tagEbtke) {
                $q->whereJsonContains('kategori_ebtke', $tagEbtke)
                  ->orWhere('kategori_ebtke', 'like', "%\"{$tagEbtke}\"%");
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_waktu', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay(),
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal_waktu', '>=', Carbon::parse($request->input('start_date'))->startOfDay());
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal_waktu', '<=', Carbon::parse($request->input('end_date'))->endOfDay());
        }

        $records = $query->orderBy('tanggal_waktu', 'desc')->get();

        $pdf = Pdf::loadView('exports.incident-pdf', [
            'reportTitle' => 'LAPORAN REKAPITULASI INSIDEN & KEJADIAN HSSE',
            'moduleName' => 'Incident',
            'filterInfo' => [
                'Klasifikasi Area' => $request->input('klasifikasi_area', 'Semua'),
                'Kategori Umum' => $request->input('kategori_umum', 'Semua'),
                'Kategori EBTKE' => $request->input('kategori_ebtke', 'Semua'),
                'Periode' => ($request->filled('start_date') ? $request->input('start_date') : 'Awal') . ' s/d ' . ($request->filled('end_date') ? $request->input('end_date') : 'Sekarang'),
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Incident-HSSE-' . now()->format('Ymd-His') . '.pdf');
    }
}

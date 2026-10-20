<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Tampilkan halaman utama Dashboard dengan data aktual database.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'unit' => $request->input('unit', 'all'),
            'category' => $request->input('category', 'all'),
            'year' => (int) $request->input('year', date('Y')),
        ];

        $data = $this->dashboardService->getDashboardData($filters);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('dashboard', $data);
    }

    /**
     * Unduh Laporan Ringkasan Eksekutif Dashboard dalam format PDF.
     */
    public function export(Request $request): Response
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'unit' => $request->input('unit', 'all'),
            'category' => $request->input('category', 'all'),
            'year' => (int) $request->input('year', date('Y')),
        ];

        $data = $this->dashboardService->getDashboardData($filters);

        $pdf = Pdf::loadView('exports.dashboard-pdf', [
            'reportTitle' => 'LAPORAN EKSEKUTIF HSSE MANAJEMEN',
            'moduleName' => 'Dashboard Safe Guard Monitoring',
            'filterInfo' => [
                'Periode Laporan' => $data['filters']['date_range_display'] ?? 'Seluruh Riwayat',
                'Unit Filter' => $data['filters']['unit'] ?? 'Semua Unit',
                'Kategori Filter' => $data['filters']['category'] ?? 'Semua Kategori',
            ],
            'hsseMetrics' => $data['hsse_metrics'] ?? [],
            'coreCards' => $data['core_cards'] ?? [],
            'progressCards' => $data['progress_cards'] ?? [],
            'projectTimeline' => $data['project_timeline'] ?? [],
            'latestIssues' => $data['latest_issues'] ?? [],
            'userName' => auth()->user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Laporan-Eksekutif-Dashboard-HSSE-' . now()->format('Ymd-His') . '.pdf');
    }
}

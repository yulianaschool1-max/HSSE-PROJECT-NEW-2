<?php

namespace App\Services;

use App\Models\DailyWork;
use App\Models\Event;
use App\Models\HssePlan;
use App\Models\Incident;
use App\Models\IssueFollowUp;
use App\Models\ManHour;
use App\Models\OperationalUnit;
use App\Models\Workforce;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const MULTIPLIER = 1000000;

    /**
     * Dapatkan seluruh data dashboard terintegrasi sesuai filter aktual database.
     */
    public function getDashboardData(array $filters = []): array
    {
        // 1. Ekstraksi dan Normalisasi Filter
        $startDateRaw = $filters['start_date'] ?? null;
        $endDateRaw = $filters['end_date'] ?? null;
        $unit = trim((string) ($filters['unit'] ?? 'all'));
        $category = trim((string) ($filters['category'] ?? 'all'));
        $year = (int) ($filters['year'] ?? date('Y'));

        // Default rentang tanggal: 1 tahun kalender penuh jika tidak ditentukan
        if (!empty($startDateRaw) && !empty($endDateRaw)) {
            $startDate = Carbon::parse($startDateRaw)->startOfDay();
            $endDate = Carbon::parse($endDateRaw)->endOfDay();
        } elseif (!empty($startDateRaw)) {
            $startDate = Carbon::parse($startDateRaw)->startOfDay();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        }

        // Hitung Periode Pembanding (Previous Period) dengan durasi yang sama
        $diffDays = max(1, $startDate->diffInDays($endDate) + 1);
        $prevStartDate = $startDate->copy()->subDays($diffDays)->startOfDay();
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();

        // 2. Synchronize Man Hours jika ada record baru
        if (class_exists(ManHour::class) && method_exists(ManHour::class, 'syncAllFromWorkforce')) {
            ManHour::syncAllFromWorkforce();
        }

        // 3. Hitung Indikator HSSE & Data Insiden
        $hsseMetrics = $this->calculateHsseMetrics($startDate, $endDate, $prevStartDate, $prevEndDate, $unit);

        // 4. Hitung Kartu Ringkasan Progres Proyek
        $progressCards = $this->calculateProgressCards($startDate, $endDate, $prevStartDate, $prevEndDate, $unit, $category);

        // 5. Hitung Grafik Persentase Penyelesaian Proyek (12 Bulan)
        $projectCompletionChart = $this->buildProjectCompletionChart($year, $unit, $category);

        // 6. Hitung Grafik Man Hours dan Jumlah Pekerja (12 Bulan)
        $manHoursWorkersChart = $this->buildManHoursWorkersChart($year, $unit);

        // 7. Hitung Grafik Open vs Closed per Divisi HSSE (H, S_SOCIAL, S_SECURITY, E)
        $divisionIssueChart = $this->buildDivisionIssueChart($startDate, $endDate, $unit);

        // 7b. Hitung 3 Donut Charts (Legacy/Fallback)
        $donutCharts = $this->buildDonutCharts($startDate, $endDate, $unit);

        // 8. Ambil Data Isu Tindak Lanjut Terbaru (5 item)
        $latestIssues = $this->getLatestIssues($startDate, $endDate, $unit);

        // 9. Ambil Data Kegiatan Terbaru Events (4-5 item)
        $latestEvents = $this->getLatestEvents($startDate, $endDate, $unit);

        // 10. Daftar Pilihan Filter (Unit & Kategori)
        $availableUnits = $this->getAvailableUnits();
        $availableCategories = $this->getAvailableCategories();

        // 11. Hitung Progress Waktu Proyek Linier & Durasi Berjalan (29 Des 2020 - 28 Mar 2028)
        $projectTimeline = $this->calculateProjectTimeline();

        // 12. Hitung 2 Card Utama: Total Akumulatif Man Hours & Total Manpower Aktif Terbaru
        $coreCards = $this->getDashboardCoreCards();

        return [
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'date_range_display' => $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y'),
                'unit' => $unit,
                'category' => $category,
                'year' => $year,
            ],
            'project_timeline' => $projectTimeline,
            'core_cards' => $coreCards,
            'hsse_metrics' => $hsseMetrics,
            'hsse_indicators' => $hsseMetrics,
            'progress_cards' => $progressCards,
            'project_completion_chart' => $projectCompletionChart,
            'man_hours_workers_chart' => $manHoursWorkersChart,
            'division_issue_chart' => $divisionIssueChart,
            'donut_charts' => $donutCharts,
            'latest_issues' => $latestIssues,
            'latest_events' => $latestEvents,
            'recent_events' => $latestEvents,
            'available_units' => $availableUnits,
            'available_categories' => $availableCategories,
        ];
    }

    /**
     * Hitung 2 Card Utama Dashboard:
     * 1. Total Akumulatif Jam Kerja Orang (Man Hours) dari menu Man Hours
     * 2. Total Manpower / Jumlah Pekerja Aktif terbaru dari input terakhir menu Workforce
     */
    public function getDashboardCoreCards(): array
    {
        if (class_exists(ManHour::class) && method_exists(ManHour::class, 'syncAllFromWorkforce')) {
            ManHour::syncAllFromWorkforce();
        }

        // 1. Total Akumulatif Man Hours
        $totalManHours = (float) ManHour::sum('total_hours');
        $latestManHourDateRaw = ManHour::max('work_date');
        $latestManHourDate = $latestManHourDateRaw 
            ? Carbon::parse($latestManHourDateRaw)->locale('id')->isoFormat('D MMMM Y')
            : 'Belum ada data';

        // 2. Total Manpower Aktif dari Input Terakhir Workforce
        $latestWorkforceDateRaw = Workforce::max('record_date');
        if ($latestWorkforceDateRaw) {
            $latestWorkforceRecords = Workforce::whereDate('record_date', $latestWorkforceDateRaw)->get();
            $totalActiveWorkers = (int) $latestWorkforceRecords->sum('worker_count');
            $latestWorkforceDate = Carbon::parse($latestWorkforceDateRaw)->locale('id')->isoFormat('D MMMM Y');
            $recordedUnitsCount = $latestWorkforceRecords->where('worker_count', '>', 0)->pluck('unit')->unique()->count();
        } else {
            $totalActiveWorkers = 0;
            $latestWorkforceDate = 'Belum ada data';
            $recordedUnitsCount = 0;
        }

        return [
            'man_hours' => [
                'total' => $totalManHours,
                'total_hours' => $totalManHours,
                'total_formatted' => number_format($totalManHours, 0, ',', '.'),
                'total_decimal_formatted' => number_format($totalManHours, 2, ',', '.'),
                'latest_date' => $latestManHourDate,
                'latest_record_date_display' => $latestManHourDate,
            ],
            'workforce' => [
                'total_workers' => $totalActiveWorkers,
                'total_workers_formatted' => number_format($totalActiveWorkers, 0, ',', '.'),
                'latest_date' => $latestWorkforceDate,
                'latest_record_date_display' => $latestWorkforceDate,
                'units_count' => $recordedUnitsCount,
                'unit_count' => $recordedUnitsCount,
            ],
        ];
    }

    /**
     * Hitung Progress Waktu Proyek Linier dan Durasi Berjalan Otomatis
     * Tanggal Mulai: 29 Desember 2020
     * Tanggal Selesai: 28 Maret 2028
     */
    public function calculateProjectTimeline(): array
    {
        $startDate = Carbon::create(2020, 12, 29)->startOfDay();
        $endDate = Carbon::create(2028, 3, 28)->endOfDay();
        $now = Carbon::now();

        // Total hari proyek keseluruhan (29 Des 2020 - 28 Mar 2028)
        $totalDays = (int) round($startDate->diffInDays($endDate) + 1);

        if ($now->lt($startDate)) {
            $elapsedDays = 0;
            $percentage = 0.0;
        } elseif ($now->gt($endDate)) {
            $elapsedDays = $totalDays;
            $percentage = 100.0;
        } else {
            $elapsedDays = (int) floor($startDate->diffInDays($now));
            $percentage = min(100.0, max(0.0, ($elapsedDays / $totalDays) * 100));
        }

        $remainingDays = (int) max(0, ceil($now->diffInDays($endDate)));

        // 1. Total Jumlah Hari Proyek Berjalan: $elapsedDays
        // 2. Total Jumlah Minggu Proyek Berjalan
        $elapsedWeeks = (int) floor($elapsedDays / 7);

        // 3. Total Jumlah Tahun (dan Bulan) Proyek Berjalan
        $diffYears = $startDate->diff($now)->y;
        $diffMonths = $startDate->diff($now)->m;
        $diffDaysRemainder = $startDate->diff($now)->d;
        $totalMonths = (int) floor($startDate->diffInMonths($now));

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_date_display' => $startDate->locale('id')->isoFormat('D MMMM Y'), // 29 Desember 2020
            'end_date_display' => $endDate->locale('id')->isoFormat('D MMMM Y'),     // 28 Maret 2028
            'now_date_display' => $now->locale('id')->isoFormat('D MMMM Y'),
            'total_days' => $totalDays,
            'elapsed_days' => $elapsedDays,
            'remaining_days' => $remainingDays,
            'percentage' => round($percentage, 2),
            'percentage_display' => number_format($percentage, 2, ',', '.') . '%',
            'elapsed_weeks' => $elapsedWeeks,
            'elapsed_years' => $diffYears,
            'elapsed_months_remainder' => $diffMonths,
            'elapsed_days_remainder' => $diffDaysRemainder,
            'elapsed_total_months' => $totalMonths,
            'year_month_display' => $diffYears . ' Tahun' . ($diffMonths > 0 ? ' ' . $diffMonths . ' Bulan' : ''),
        ];
    }

    /**
     * Hitung Indikator TRIF, LTI TF, LTI-SF, Total Insiden, Total LTI, dan Hari Hilang.
     */
    public function calculateHsseMetrics(Carbon $start, Carbon $end, Carbon $prevStart, Carbon $prevEnd, string $unit = 'all'): array
    {
        // Base Query Insiden Periode Aktif
        $incQuery = Incident::query()->whereBetween('tanggal_waktu', [$start, $end]);
        if ($unit !== 'all' && $unit !== '') {
            $incQuery->where(function ($q) use ($unit) {
                $q->where('unit', $unit)
                  ->orWhere('komponen_terkait', $unit)
                  ->orWhere('lokasi_kejadian', 'like', "%{$unit}%");
            });
        }

        // Base Query Insiden Periode Sebelumnya (untuk tren)
        $prevIncQuery = Incident::query()->whereBetween('tanggal_waktu', [$prevStart, $prevEnd]);
        if ($unit !== 'all' && $unit !== '') {
            $prevIncQuery->where(function ($q) use ($unit) {
                $q->where('unit', $unit)
                  ->orWhere('komponen_terkait', $unit)
                  ->orWhere('lokasi_kejadian', 'like', "%{$unit}%");
            });
        }

        // Base Query Man Hours Periode Aktif
        $mhQuery = ManHour::query()->whereBetween('work_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $mhQuery->where(function ($q) use ($unit) {
                $q->where('unit', $unit)
                  ->orWhere('category', $unit)
                  ->orWhereHas('operationalUnit', fn ($u) => $u->where('name', $unit));
            });
        }

        // Perhitungan Komponen Pembilang & Penyebut
        $totalInsiden = (int) $incQuery->count();
        $prevTotalInsiden = (int) $prevIncQuery->count();

        // Kriteria LTI: Severity Fatality/Berat, jumlah hari hilang > 0, atau tag LTI / Kecelakaan Mati / Berat
        $ltiQuery = (clone $incQuery)->where(function ($q) {
            $q->whereIn('severity', ['Fatality', 'Berat'])
              ->orWhere('jumlah_hari_hilang', '>', 0)
              ->orWhereJsonContains('kategori_umum', 'LTI (Lost Time Injury)')
              ->orWhereJsonContains('kategori_umum', 'Fatality')
              ->orWhereJsonContains('kategori_ebtke', 'Kecelakaan Mati')
              ->orWhereJsonContains('kategori_ebtke', 'Kecelakaan Berat');
        });
        $totalLti = (int) $ltiQuery->count();

        $prevLtiQuery = (clone $prevIncQuery)->where(function ($q) {
            $q->whereIn('severity', ['Fatality', 'Berat'])
              ->orWhere('jumlah_hari_hilang', '>', 0)
              ->orWhereJsonContains('kategori_umum', 'LTI (Lost Time Injury)')
              ->orWhereJsonContains('kategori_umum', 'Fatality')
              ->orWhereJsonContains('kategori_ebtke', 'Kecelakaan Mati')
              ->orWhereJsonContains('kategori_ebtke', 'Kecelakaan Berat');
        });
        $prevTotalLti = (int) $prevLtiQuery->count();

        $totalHariHilang = (int) $incQuery->sum('jumlah_hari_hilang');
        $totalManHours = round((float) $mhQuery->sum('total_hours'), 2);

        // Rumus Indikator HSSE Wajib (Faktor Pengali 1.000.000)
        // Menghindari Division by Zero: jika Total Man Hours = 0, kembalikan null
        $trif = ($totalManHours > 0) ? round(($totalInsiden / $totalManHours) * self::MULTIPLIER, 2) : null;
        $ltiTf = ($totalManHours > 0) ? round(($totalLti / $totalManHours) * self::MULTIPLIER, 2) : null;
        $ltiSf = ($totalManHours > 0) ? round(($totalHariHilang / $totalManHours) * self::MULTIPLIER, 2) : null;

        // Perhitungan Tren Perubahan (%)
        $trendInsiden = $this->calculateTrendPercentage($totalInsiden, $prevTotalInsiden);
        $trendLti = $this->calculateTrendPercentage($totalLti, $prevTotalLti);

        return [
            'total_insiden' => $totalInsiden,
            'prev_total_insiden' => $prevTotalInsiden,
            'trend_insiden' => $trendInsiden,

            'total_lti' => $totalLti,
            'prev_total_lti' => $prevTotalLti,
            'trend_lti' => $trendLti,

            'total_hari_hilang' => $totalHariHilang,
            'total_man_hours' => $totalManHours,
            'has_man_hours' => ($totalManHours > 0),

            'trif' => $trif,
            'trif_display' => ($trif !== null) ? number_format($trif, 2, '.', ',') : '—',

            'lti_tf' => $ltiTf,
            'lti_tf_display' => ($ltiTf !== null) ? number_format($ltiTf, 2, '.', ',') : '—',

            'lti_sf' => $ltiSf,
            'lti_sf_display' => ($ltiSf !== null) ? number_format($ltiSf, 2, '.', ',') : '—',

            'multiplier' => self::MULTIPLIER,
            'multiplier_label' => '1.000.000',
        ];
    }

    /**
     * Hitung Kartu Ringkasan Progres: Daily Work, Mingguan, dan Tahunan.
     */
    protected function calculateProgressCards(Carbon $start, Carbon $end, Carbon $prevStart, Carbon $prevEnd, string $unit, string $category): array
    {
        // 1. Progress Daily Work
        $dwQuery = DailyWork::query()->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $dwQuery->where('unit', $unit);
        }
        if ($category !== 'all' && $category !== '') {
            $dwQuery->where('category', $category);
        }
        $totalDailyWork = (int) $dwQuery->count();

        $prevDwQuery = DailyWork::query()->whereBetween('start_date', [$prevStart->toDateString(), $prevEnd->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $prevDwQuery->where('unit', $unit);
        }
        if ($category !== 'all' && $category !== '') {
            $prevDwQuery->where('category', $category);
        }
        $prevTotalDailyWork = (int) $prevDwQuery->count();
        $trendDailyWork = $this->calculateTrendPercentage($totalDailyWork, $prevTotalDailyWork);

        // 2. Progress Mingguan (Berdasarkan 7 hari terakhir atau rasio penyelesaian pekerjaan)
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $prevWeekStart = now()->subWeek()->startOfWeek();
        $prevWeekEnd = now()->subWeek()->endOfWeek();

        $dwWeekTotal = DailyWork::whereBetween('start_date', [$weekStart, $weekEnd])->count();
        $dwWeekDone = DailyWork::whereBetween('start_date', [$weekStart, $weekEnd])
            ->where('status', DailyWork::STATUS_COMPLETED)
            ->count();
        $progressMingguan = ($dwWeekTotal > 0) ? round(($dwWeekDone / $dwWeekTotal) * 100, 1) : 0;

        $dwPrevWeekTotal = DailyWork::whereBetween('start_date', [$prevWeekStart, $prevWeekEnd])->count();
        $dwPrevWeekDone = DailyWork::whereBetween('start_date', [$prevWeekStart, $prevWeekEnd])
            ->where('status', DailyWork::STATUS_COMPLETED)
            ->count();
        $prevProgressMingguan = ($dwPrevWeekTotal > 0) ? round(($dwPrevWeekDone / $dwPrevWeekTotal) * 100, 1) : 0;
        $trendMingguan = $this->calculateTrendPercentage($progressMingguan, $prevProgressMingguan);

        // 3. Progress Tahunan (Rasio kumulatif target & realisasi HSSE Plan tahun ini)
        $currentYear = $start->year;
        $planQuery = HssePlan::query()->where('year', $currentYear);
        if ($unit !== 'all' && $unit !== '') {
            $planQuery->where('unit', $unit);
        }
        if ($category !== 'all' && $category !== '') {
            $planQuery->where('category', $category);
        }

        $sumTarget = (float) $planQuery->sum('target');
        $sumRealization = (float) $planQuery->sum('realization');
        $progressTahunan = ($sumTarget > 0) ? round(($sumRealization / $sumTarget) * 100, 1) : 0;

        // Tahun Sebelumnya
        $prevPlanQuery = HssePlan::query()->where('year', $currentYear - 1);
        if ($unit !== 'all' && $unit !== '') {
            $prevPlanQuery->where('unit', $unit);
        }
        $prevSumTarget = (float) $prevPlanQuery->sum('target');
        $prevSumRealization = (float) $prevPlanQuery->sum('realization');
        $prevProgressTahunan = ($prevSumTarget > 0) ? round(($prevSumRealization / $prevSumTarget) * 100, 1) : 0;
        $trendTahunan = $this->calculateTrendPercentage($progressTahunan, $prevProgressTahunan);

        return [
            'daily_work' => [
                'total' => $totalDailyWork,
                'trend' => $trendDailyWork,
            ],
            'mingguan' => [
                'percentage' => $progressMingguan,
                'trend' => $trendMingguan,
            ],
            'tahunan' => [
                'percentage' => $progressTahunan,
                'trend' => $trendTahunan,
            ],
        ];
    }

    /**
     * Bangun Data Grafik Persentase Penyelesaian Proyek (12 Bulan).
     */
    protected function buildProjectCompletionChart(int $year, string $unit, string $category): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $targetData = [];
        $realisasiData = [];
        $progressData = [];

        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $mEnd = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            $query = HssePlan::query()->where('year', $year);
            if ($unit !== 'all' && $unit !== '') {
                $query->where('unit', $unit);
            }
            if ($category !== 'all' && $category !== '') {
                $query->where('category', $category);
            }

            // Agregasi bulanan
            $monthPlans = (clone $query)->where(function ($q) use ($mStart, $mEnd, $m) {
                $q->whereBetween('start_date', [$mStart, $mEnd])
                  ->orWhereMonth('start_date', $m);
            })->get();

            $target = round((float) $monthPlans->sum('target'), 1);
            $real = round((float) $monthPlans->sum('realization'), 1);
            $prog = ($target > 0) ? round(($real / $target) * 100, 1) : 0;

            // Jika belum ada plan spesifik bulan tersebut, ambil rasio daily work sebagai representasi
            if ($target == 0 && $real == 0) {
                $dwTotal = DailyWork::whereBetween('start_date', [$mStart, $mEnd])->count();
                $dwDone = DailyWork::whereBetween('start_date', [$mStart, $mEnd])->where('status', DailyWork::STATUS_COMPLETED)->count();
                $target = $dwTotal > 0 ? 100 : 0;
                $real = $dwTotal > 0 ? round(($dwDone / $dwTotal) * 100, 1) : 0;
                $prog = $real;
            }

            $targetData[] = $target;
            $realisasiData[] = $real;
            $progressData[] = min(100, $prog);
        }

        return [
            'labels' => $months,
            'months' => $months,
            'target' => $targetData,
            'realisasi' => $realisasiData,
            'progress' => $progressData,
        ];
    }

    /**
     * Bangun Data Grafik Jam Kerja (Man Hours) dan Jumlah Pekerja (Combo 12 Bulan).
     */
    protected function buildManHoursWorkersChart(int $year, string $unit): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $manHoursData = [];
        $workersData = [];

        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $mEnd = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            // Query Man Hours bulanan
            $mhQuery = ManHour::query()->whereBetween('work_date', [$mStart, $mEnd]);
            if ($unit !== 'all' && $unit !== '') {
                $mhQuery->where(function ($q) use ($unit) {
                    $q->where('unit', $unit)
                      ->orWhere('category', $unit)
                      ->orWhereHas('operationalUnit', fn ($u) => $u->where('name', $unit));
                });
            }
            $totalHours = round((float) $mhQuery->sum('total_hours'), 1);

            // Query Pekerja Unik / Rata-rata dari Workforce
            $wfQuery = Workforce::query()->whereBetween('record_date', [$mStart, $mEnd]);
            if ($unit !== 'all' && $unit !== '') {
                $wfQuery->where('unit', $unit);
            }
            // Mengambil sum worker_count pada record tanggal terbaru di bulan tersebut agar tidak dihitung ganda
            $latestDate = (clone $wfQuery)->max('record_date');
            if ($latestDate) {
                $workerCount = (int) (clone $wfQuery)->where('record_date', $latestDate)->sum('worker_count');
            } else {
                $workerCount = 0;
            }

            $manHoursData[] = $totalHours;
            $workersData[] = $workerCount;
        }

        return [
            'labels' => $months,
            'man_hours' => $manHoursData,
            'workers' => $workersData,
        ];
    }

    /**
     * Bangun Grafik Perbandingan Status Open dan Closed per Divisi HSSE:
     * - H — Health & Savety (kode: H)
     * - S — Sosial (kode: S_SOCIAL)
     * - S — Scurity (kode: S_SECURITY)
     * - E — Emvironment (kode: E)
     *
     * Ketentuan:
     * - Membandingkan Open dan Closed aktual per divisi
     * - Status In Progress tidak digabungkan diam-diam ke Open maupun Closed
     * - Dua divisi berawalan S tetap terpisah
     */
    public function buildDivisionIssueChart(Carbon $start, Carbon $end, string $unit = 'all'): array
    {
        $divisionDefs = [
            'H' => 'H — Health & Savety',
            'S_SOCIAL' => 'S — Sosial',
            'S_SECURITY' => 'S — Scurity',
            'E' => 'E — Emvironment',
        ];

        $labels = [];
        $openData = [];
        $closedData = [];
        $inProgressData = [];
        $summary = [];
        $totalOpen = 0;
        $totalClosed = 0;
        $totalInProgress = 0;

        foreach ($divisionDefs as $code => $label) {
            $labels[] = $label;

            $baseQuery = IssueFollowUp::query()
                ->where('hsse_division', $code)
                ->whereBetween('reported_date', [$start->toDateString(), $end->toDateString()]);

            if ($unit !== 'all' && $unit !== '') {
                $baseQuery->where('unit', $unit);
            }

            $open = (int) (clone $baseQuery)->where('status', 'Open')->count();
            $closed = (int) (clone $baseQuery)->whereIn('status', ['Closed', 'Close'])->count();
            $inProgress = (int) (clone $baseQuery)->where('status', 'In Progress')->count();

            $openData[] = $open;
            $closedData[] = $closed;
            $inProgressData[] = $inProgress;

            $totalOpen += $open;
            $totalClosed += $closed;
            $totalInProgress += $inProgress;

            $summary[$code] = [
                'code' => $code,
                'label' => $label,
                'open' => $open,
                'closed' => $closed,
                'in_progress' => $inProgress,
                'total' => $open + $closed + $inProgress,
            ];
        }

        $hasData = ($totalOpen + $totalClosed + $totalInProgress) > 0;

        return [
            'labels' => $labels,
            'open' => $openData,
            'closed' => $closedData,
            'in_progress' => $inProgressData,
            'total_open' => $totalOpen,
            'total_closed' => $totalClosed,
            'total_in_progress' => $totalInProgress,
            'total_all' => $totalOpen + $totalClosed + $totalInProgress,
            'summary' => $summary,
            'has_data' => $hasData,
        ];
    }

    /**
     * Bangun 3 Donut Charts: Status Pekerjaan, Distribusi Issue (4 Divisi), dan Status Issue.
     */
    protected function buildDonutCharts(Carbon $start, Carbon $end, string $unit): array
    {
        // 1. Status Pekerjaan (Daily Work)
        $dwQuery = DailyWork::query()->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $dwQuery->where('unit', $unit);
        }
        $totalDw = (int) $dwQuery->count();

        $dwSelesai = (int) (clone $dwQuery)->where('status', DailyWork::STATUS_COMPLETED)->count();
        $dwBerlangsung = (int) (clone $dwQuery)->where('status', DailyWork::STATUS_ONGOING)->count();
        $dwDitunda = (int) (clone $dwQuery)->where('status', DailyWork::STATUS_PAUSED)->count();
        $dwBelumMulai = (int) (clone $dwQuery)->where('status', DailyWork::STATUS_NOT_STARTED)->count();

        $dwChart = [
            'total' => $totalDw,
            'labels' => ['Selesai', 'Berlangsung', 'Ditunda', 'Belum Mulai'],
            'data' => [$dwSelesai, $dwBerlangsung, $dwDitunda, $dwBelumMulai],
            'percentages' => [
                $totalDw > 0 ? round(($dwSelesai / $totalDw) * 100) : 0,
                $totalDw > 0 ? round(($dwBerlangsung / $totalDw) * 100) : 0,
                $totalDw > 0 ? round(($dwDitunda / $totalDw) * 100) : 0,
                $totalDw > 0 ? round(($dwBelumMulai / $totalDw) * 100) : 0,
            ],
            'colors' => ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
        ];

        // 2. Distribusi Issue (4 Divisi HSSE: H, S_SOCIAL, S_SECURITY, E)
        $issueQuery = IssueFollowUp::query()->whereBetween('reported_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $issueQuery->where('unit', $unit);
        }
        $totalIssue = (int) $issueQuery->count();

        $issueH = (int) (clone $issueQuery)->where('hsse_division', 'H')->count();
        $issueSocial = (int) (clone $issueQuery)->where('hsse_division', 'S_SOCIAL')->count();
        $issueSecurity = (int) (clone $issueQuery)->where('hsse_division', 'S_SECURITY')->count();
        $issueEnv = (int) (clone $issueQuery)->where('hsse_division', 'E')->count();

        $distribusiChart = [
            'total' => $totalIssue,
            'labels' => ['H&S', 'Sosial', 'Security', 'Environment'],
            'data' => [$issueH, $issueSocial, $issueSecurity, $issueEnv],
            'percentages' => [
                $totalIssue > 0 ? round(($issueH / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueSocial / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueSecurity / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueEnv / $totalIssue) * 100) : 0,
            ],
            'colors' => ['#2563eb', '#10b981', '#f97316', '#8b5cf6'],
        ];

        // 3. Status Issue (Open, In Progress, Overdue, Closed)
        $issueOpen = (int) (clone $issueQuery)->where('status', 'Open')->where(function ($q) {
            $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
        })->count();

        $issueInProgress = (int) (clone $issueQuery)->where('status', 'In Progress')->where(function ($q) {
            $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
        })->count();

        $issueOverdue = (int) (clone $issueQuery)->overdue()->count();
        $issueClosed = (int) (clone $issueQuery)->whereIn('status', ['Closed', 'Close'])->count();

        $statusChart = [
            'total' => $totalIssue,
            'labels' => ['Open', 'In Progress', 'Overdue', 'Closed'],
            'data' => [$issueOpen, $issueInProgress, $issueOverdue, $issueClosed],
            'percentages' => [
                $totalIssue > 0 ? round(($issueOpen / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueInProgress / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueOverdue / $totalIssue) * 100) : 0,
                $totalIssue > 0 ? round(($issueClosed / $totalIssue) * 100) : 0,
            ],
            'colors' => ['#3b82f6', '#eab308', '#ef4444', '#059669'],
        ];

        return [
            'status_pekerjaan' => $dwChart,
            'distribusi_issue' => $distribusiChart,
            'status_issue' => $statusChart,
        ];
    }

    /**
     * Ambil 5 Rekaman Isu Tindak Lanjut Terbaru.
     */
    protected function getLatestIssues(Carbon $start, Carbon $end, string $unit)
    {
        $query = IssueFollowUp::query()->whereBetween('reported_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $query->where('unit', $unit);
        }

        return $query->orderBy('reported_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Ambil 5 Rekaman Kegiatan Terbaru Events.
     */
    protected function getLatestEvents(Carbon $start, Carbon $end, string $unit)
    {
        $query = Event::query()->whereBetween('event_date', [$start->toDateString(), $end->toDateString()]);
        if ($unit !== 'all' && $unit !== '') {
            $query->where('unit', $unit);
        }

        return $query->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Daftar Unit Operasional dari Database.
     */
    protected function getAvailableUnits(): array
    {
        $dbUnits = [];
        if (class_exists(OperationalUnit::class)) {
            $dbUnits = OperationalUnit::orderBy('name')->pluck('name')->toArray();
        }

        $fallbackUnits = ['PMU', 'Dieng Unit 1', 'Dieng Unit 2', 'Utility', 'JOTRE', 'BISMO'];

        return array_values(array_unique(array_merge($dbUnits, $fallbackUnits)));
    }

    /**
     * Daftar Kategori Tersedia.
     */
    protected function getAvailableCategories(): array
    {
        return [
            'Operasional & Pemeliharaan',
            'Konstruksi & Proyek',
            'Pengeboran (Drilling)',
            'Eksplorasi & Uji Sumur',
            'K3 & Lingkungan (HSSE)',
        ];
    }

    /**
     * Hitung Persentase Perubahan Tren.
     */
    protected function calculateTrendPercentage(float|int $current, float|int $previous): array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return ['direction' => 'up', 'value' => 100, 'label' => '↑ 100%'];
            }
            return ['direction' => 'neutral', 'value' => 0, 'label' => '0%'];
        }

        $diff = (($current - $previous) / $previous) * 100;
        $rounded = round(abs($diff));

        if ($diff > 0) {
            return ['direction' => 'up', 'value' => $rounded, 'label' => "↑ {$rounded}%"];
        } elseif ($diff < 0) {
            return ['direction' => 'down', 'value' => $rounded, 'label' => "↓ {$rounded}%"];
        }

        return ['direction' => 'neutral', 'value' => 0, 'label' => '0%'];
    }
}

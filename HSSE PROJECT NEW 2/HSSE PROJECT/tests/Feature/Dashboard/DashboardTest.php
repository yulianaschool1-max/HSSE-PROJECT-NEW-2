<?php

namespace Tests\Feature\Dashboard;

use App\Models\DailyWork;
use App\Models\HssePlan;
use App\Models\Incident;
use App\Models\IssueFollowUp;
use App\Models\ManHour;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_dash_test@geodipa.co.id'],
            [
                'name' => 'Admin Dashboard Tester',
                'username' => 'admindashtest',
                'password' => bcrypt('Admin123!'),
                'role' => 'Admin',
                'status' => 'Aktif',
            ]
        );
    }

    /**
     * Uji 1: Verifikasi Branding Global & Halaman Login.
     */
    public function test_global_branding_and_login_page_identity(): void
    {
        // 1. Halaman Login
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Safe Guard Monitoring System');
        $loginRes->assertSee('Project Manajemen Geodipa Unit Dieng 2');

        // 2. Halaman Dashboard (Setelah Login)
        $dashRes = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Hsse Manajemen');
        $dashRes->assertSee('Project Manajemen Geodipa');
        $dashRes->assertSee('Unit Dieng 2');

        // Logo resmi Geodipa di sidebar
        $dashRes->assertSee('geo-dipa.png', false);

        // Progress Bar Linier Proyek Memanjang & 3 Durasi Waktu
        $dashRes->assertSee('Progress Waktu Proyek Dieng 2', false);
        $dashRes->assertSee('29 Desember 2020', false);
        $dashRes->assertSee('28 Maret 2028', false);
        $dashRes->assertSee('Hari Proyek Berjalan', false);
        $dashRes->assertSee('Minggu Proyek Berjalan', false);
        $dashRes->assertSee('Durasi Waktu Berjalan', false);

        // Elemen filter lama yang dihapus tidak boleh muncul
        $dashRes->assertDontSee('Pilih rentang tanggal...', false);
    }

    /**
     * Uji 2: Verifikasi Matematis Manual Rumus Indikator HSSE (Pengali 1.000.000).
     * Skenario Uji:
     * - Total Insiden = 2
     * - Total LTI = 1
     * - Total Hari Hilang = 10
     * - Total Man Hours = 100.000
     * Ekspektasi Formula Matematis:
     * - TRIF = (2 / 100.000) * 1.000.000 = 20.00
     * - LTI TF = (1 / 100.000) * 1.000.000 = 10.00
     * - LTI-SF = (10 / 100.000) * 1.000.000 = 100.00
     */
    public function test_hsse_indicator_mathematical_formulas_with_one_million_multiplier(): void
    {
        $testDate = '2099-12-31';
        $testDateTime = Carbon::parse($testDate)->startOfDay();

        // Bersihkan data tanggal test
        ManHour::where('work_date', $testDate)->delete();
        Incident::whereIn('incident_code', ['INC-TEST-001', 'INC-TEST-002'])->delete();
        Incident::whereDate('tanggal_waktu', $testDate)->delete();

        // 1. Buat Man Hour = 100.000 (10.000 pekerja x 10 jam)
        ManHour::create([
            'work_date' => $testDate,
            'unit' => 'Pabrik Dieng 1',
            'category' => 'Operasional',
            'number_of_workers' => 10000,
            'hours_per_worker' => 10,
            'notes' => 'Test manhours 100.000',
        ]);

        // 2. Buat Insiden 1: Non-LTI (First Aid, 0 hari hilang)
        Incident::create([
            'incident_code' => 'INC-TEST-001',
            'title' => 'Jari Tergores Obeng',
            'tanggal_waktu' => $testDateTime,
            'incident_date' => $testDateTime,
            'unit' => 'Pabrik Dieng 1',
            'location' => 'Bengkel Mekanikal',
            'kategori_umum' => ['First Aid Case'],
            'kategori_ebtke' => ['Kecelakaan Ringan'],
            'severity' => 'Ringan',
            'jumlah_hari_hilang' => 0,
            'status' => 'Investigasi Selesai',
        ]);

        // 3. Buat Insiden 2: LTI (Severity Berat, 10 hari hilang)
        Incident::create([
            'incident_code' => 'INC-TEST-002',
            'title' => 'Kaki Terkilir Tangga Tangki',
            'tanggal_waktu' => $testDateTime->copy()->addHours(2),
            'incident_date' => $testDateTime->copy()->addHours(2),
            'unit' => 'Pabrik Dieng 1',
            'location' => 'Area Tangki',
            'kategori_umum' => ['LTI (Lost Time Injury)'],
            'kategori_ebtke' => ['Kecelakaan Berat'],
            'severity' => 'Berat',
            'jumlah_hari_hilang' => 10,
            'status' => 'Tindakan Selesai',
        ]);

        $service = app(DashboardService::class);
        $data = $service->getDashboardData([
            'start_date' => $testDate,
            'end_date' => $testDate,
            'unit' => 'all',
        ]);

        $indicators = $data['hsse_indicators'];

        // Assert data pembilang dan penyebut
        $this->assertEquals(2, $indicators['total_insiden'], 'Total Insiden harus 2');
        $this->assertEquals(1, $indicators['total_lti'], 'Total LTI harus 1 (dibedakan dari total insiden)');
        $this->assertEquals(10, $indicators['total_hari_hilang'], 'Total Hari Hilang harus 10');
        $this->assertEquals(100000, $indicators['total_man_hours'], 'Total Man Hours harus 100.000');
        $this->assertEquals(1000000, $indicators['multiplier'], 'Faktor pengali harus 1.000.000');

        // Assert Rumus Matematis
        $this->assertEquals(20.0, $indicators['trif'], 'TRIF = (2 / 100.000) * 1.000.000 = 20.0');
        $this->assertEquals(10.0, $indicators['lti_tf'], 'LTI TF = (1 / 100.000) * 1.000.000 = 10.0');
        $this->assertEquals(100.0, $indicators['lti_sf'], 'LTI-SF = (10 / 100.000) * 1.000.000 = 100.0');

        $this->assertEquals('20.00', $indicators['trif_display']);
        $this->assertEquals('10.00', $indicators['lti_tf_display']);
        $this->assertEquals('100.00', $indicators['lti_sf_display']);

        // Bersihkan kembali data test
        ManHour::where('work_date', $testDate)->delete();
        Incident::whereIn('incident_code', ['INC-TEST-001', 'INC-TEST-002'])->delete();
    }

    /**
     * Uji 3: Penanganan Total Man Hours = 0 (Tanpa Error Division by Zero / NaN).
     */
    public function test_handles_zero_man_hours_gracefully_without_division_by_zero(): void
    {
        $pastDate = '2005-01-01';

        $service = app(DashboardService::class);
        $data = $service->getDashboardData([
            'start_date' => $pastDate,
            'end_date' => $pastDate,
            'unit' => 'all',
        ]);

        $indicators = $data['hsse_indicators'];

        $this->assertEquals(0, $indicators['total_man_hours']);
        $this->assertNull($indicators['trif']);
        $this->assertNull($indicators['lti_tf']);
        $this->assertNull($indicators['lti_sf']);

        // Tampilan format di view harus tanda strip em-dash '—'
        $this->assertEquals('—', $indicators['trif_display']);
        $this->assertEquals('—', $indicators['lti_tf_display']);
        $this->assertEquals('—', $indicators['lti_sf_display']);

        // Render view web juga tidak boleh melempar exception
        $response = $this->actingAs($this->adminUser)->get(route('dashboard', [
            'start_date' => $pastDate,
            'end_date' => $pastDate,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Data Man Hours belum tersedia');
    }

    /**
     * Uji 4: Filter Dashboard (Rentang Tanggal, Unit, AJAX JSON).
     */
    public function test_dashboard_filters_by_unit_and_date_range_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->json('GET', route('dashboard'), [
                'start_date' => Carbon::today()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->toDateString(),
                'unit' => 'Dieng Unit 1',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'hsse_indicators' => ['trif', 'lti_tf', 'lti_sf', 'multiplier'],
                'progress_cards' => ['daily_work', 'mingguan', 'tahunan'],
                'project_completion_chart' => ['months', 'target', 'realisasi', 'progress'],
                'man_hours_workers_chart' => ['labels', 'man_hours', 'workers'],
                'donut_charts' => ['status_pekerjaan', 'distribusi_issue', 'status_issue'],
                'latest_issues',
                'recent_events',
            ],
        ]);
    }

    /**
     * Uji 5: Ekspor Laporan Dashboard Ringkasan Eksekutif (CSV / Excel).
     */
    public function test_dashboard_export_endpoint_returns_valid_csv_stream(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.export', [
            'start_date' => Carbon::today()->subDays(7)->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Laporan_Eksekutif_Dashboard_HSSE_', $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();

        // Periksa UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Periksa konten header eksekutif
        $this->assertStringContainsString('RINGKASAN EKSEKUTIF HSSE MANAJEMEN', $content);
        $this->assertStringContainsString('Project Manajemen Geodipa Unit Dieng 2', $content);
        $this->assertStringContainsString('TRIF (Total Recordable Incident Frequency)', $content);
        $this->assertStringContainsString('LTI TF (Lost Time Injury Frequency', $content);
        $this->assertStringContainsString('LTI-SF (Lost Time Injury Severity Rate)', $content);
        $this->assertStringContainsString('1.000.000', $content);
    }
}

<?php

namespace Tests\Feature\GasDetector;

use App\Models\ActivityLog;
use App\Models\GasDetector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GasDetectorManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Cari atau buat Admin Dieng
        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test@geodipa.co.id'],
            [
                'name' => 'Admin Test Dieng',
                'password' => bcrypt('password123'),
                'role' => 'Admin',
                'status' => 'Aktif',
            ]
        );

        // Cari atau buat Regular User
        $this->regularUser = User::firstOrCreate(
            ['email' => 'user.test@geodipa.co.id'],
            [
                'name' => 'User Test Dieng',
                'password' => bcrypt('password123'),
                'role' => 'User',
                'status' => 'Aktif',
            ]
        );
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('gas-detector.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_access_gas_detector_index_in_read_only(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('gas-detector.index'));

        $response->assertStatus(200);
        $response->assertSee('Gasdetektor');
        $response->assertSee('Manajemen &amp; Pemantauan Perangkat', false);
        $response->assertSee('Total Perangkat');
        $response->assertDontSee('id="btnTambahPerangkat"', false); // Tombol tambah disembunyikan untuk User biasa
    }

    public function test_admin_sees_create_button_on_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('gas-detector.index'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Perangkat');
    }

    public function test_gas_detector_index_ajax_json_response(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson(route('gas-detector.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'devices',
            'stats' => ['total', 'available', 'in_use', 'in_maintenance', 'due_calibration', 'maintenance_or_calibration'],
            'unitChart' => ['labels', 'data'],
            'statusChart' => ['labels', 'data', 'colors'],
            'gasTypeChart' => ['labels', 'data'],
        ]);
    }

    public function test_regular_user_cannot_create_device(): void
    {
        $payload = [
            'device_code' => 'GD-TEST-USER',
            'model_name' => 'Honeywell BW Clip',
            'unit' => 'PMU',
            'status' => 'Tersedia',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson(route('gas-detector.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_update_device(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-TEST-RO',
            'model_name' => 'Drager X-am 2500',
            'unit' => 'Dieng Unit 1',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->putJson(route('gas-detector.update', $device->id), [
                'device_code' => 'GD-TEST-RO',
                'model_name' => 'Drager Modified',
                'unit' => 'Dieng Unit 1',
                'status' => 'Sedang Digunakan',
            ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_delete_device(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-TEST-DEL',
            'model_name' => 'MSA Altair 4XR',
            'unit' => 'Dieng Unit 2',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson(route('gas-detector.destroy', $device->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_device_and_logs_activity(): void
    {
        $payload = [
            'device_code' => 'GD-TEST-001',
            'model_name' => 'Honeywell BW Clip4 Multi-Gas',
            'serial_number' => 'SN-99881122',
            'gas_type' => 'Multi-Gas (H2S, CO, O2, LEL)',
            'unit' => 'Dieng Unit 1',
            'location' => 'Pos HSSE Unit 1',
            'status' => 'Tersedia',
            'last_calibration_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'next_calibration_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
            'user_in_charge' => 'Safety Inspector Dieng',
            'notes' => 'Unit baru dikalibrasi berkala',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('gas-detector.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Perangkat Gasdetektor berhasil ditambahkan.',
        ]);

        $this->assertDatabaseHas('gas_detectors', [
            'device_code' => 'GD-TEST-001',
            'model_name' => 'Honeywell BW Clip4 Multi-Gas',
        ]);

        // Pastikan ActivityLog tercatat
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Gasdetektor',
            'action' => 'TAMBAH',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_create_validates_duplicate_device_code(): void
    {
        GasDetector::create([
            'device_code' => 'GD-DUP-01',
            'model_name' => 'Industrial Scientific Ventis',
            'unit' => 'PMU',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('gas-detector.store'), [
                'device_code' => 'GD-DUP-01',
                'model_name' => 'Another Device',
                'unit' => 'Dieng Unit 1',
                'status' => 'Tersedia',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['device_code']);
    }

    public function test_check_duplicate_endpoint(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-EXIST-99',
            'model_name' => 'BW Solo H2S',
            'unit' => 'Dieng Unit 2',
            'status' => 'Tersedia',
        ]);

        // Cek duplikasi kode yang sudah ada
        $res1 = $this->actingAs($this->admin)
            ->getJson(route('gas-detector.check-duplicate', ['device_code' => 'GD-EXIST-99']));
        $res1->assertStatus(200);
        $res1->assertJson(['duplicate' => true]);

        // Abaikan ID sendiri (saat mode edit)
        $res2 = $this->actingAs($this->admin)
            ->getJson(route('gas-detector.check-duplicate', ['device_code' => 'GD-EXIST-99', 'ignore_id' => $device->id]));
        $res2->assertStatus(200);
        $res2->assertJson(['duplicate' => false]);

        // Cek kode unik baru
        $res3 = $this->actingAs($this->admin)
            ->getJson(route('gas-detector.check-duplicate', ['device_code' => 'GD-BRAND-NEW']));
        $res3->assertStatus(200);
        $res3->assertJson(['duplicate' => false]);
    }

    public function test_admin_can_view_device_detail_json(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-SHOW-01',
            'model_name' => 'Honeywell BW GasAlertMicro 5',
            'unit' => 'Power Plant',
            'status' => 'Sedang Digunakan',
            'next_calibration_date' => Carbon::now()->subDays(5), // sudah overdue
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('gas-detector.show', $device->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_calibration_due' => true,
        ]);
        $response->assertJsonPath('device.device_code', 'GD-SHOW-01');
    }

    public function test_admin_can_update_device_with_diff_logging(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-UPDATE-01',
            'model_name' => 'Riken Keiki GX-3R',
            'unit' => 'Dieng Unit 1',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson(route('gas-detector.update', $device->id), [
                'device_code' => 'GD-UPDATE-01',
                'model_name' => 'Riken Keiki GX-3R Pro',
                'unit' => 'Dieng Unit 2',
                'status' => 'Dalam Perawatan',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Data perangkat Gasdetektor berhasil diperbarui.',
        ]);

        $this->assertDatabaseHas('gas_detectors', [
            'id' => $device->id,
            'unit' => 'Dieng Unit 2',
            'status' => 'Dalam Perawatan',
        ]);

        // Pastikan ActivityLog mencatat aksi UBAH dengan diff
        $log = ActivityLog::where('module', 'Gasdetektor')
            ->where('action', 'UBAH')
            ->where('record_id', $device->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertIsArray($log->changes);
        $this->assertEquals('Dieng Unit 1', $log->changes['before']['unit'] ?? null);
        $this->assertEquals('Dieng Unit 2', $log->changes['after']['unit'] ?? null);
    }

    public function test_admin_can_delete_device_and_logs_deletion(): void
    {
        $device = GasDetector::create([
            'device_code' => 'GD-DELETE-01',
            'model_name' => 'Old Gas Detector',
            'unit' => 'PMU',
            'status' => 'Dalam Perawatan',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('gas-detector.destroy', $device->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Perangkat Gasdetektor berhasil dihapus.',
        ]);

        $this->assertDatabaseMissing('gas_detectors', ['id' => $device->id]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Gasdetektor',
            'action' => 'HAPUS',
            'record_id' => $device->id,
        ]);
    }

    public function test_gas_detector_export_csv(): void
    {
        GasDetector::create([
            'device_code' => 'GD-EXP-01',
            'model_name' => 'Drager Pac 6500',
            'unit' => 'Wellpad',
            'status' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->get(route('gas-detector.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_alias_route_gasdetektor_redirects_to_gas_detector_index(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/gasdetektor');
        $response->assertRedirect(route('gas-detector.index'));
    }

    public function test_gas_detector_activity_log_is_queryable_in_user_activities(): void
    {
        // Buat log aktivitas Gasdetektor
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'module' => 'Gasdetektor',
            'action' => 'TAMBAH',
            'description' => 'Test log gas detector query integration',
        ]);

        // Query melalui User Management all activities dengan filter module=gas-detector
        $response = $this->actingAs($this->admin)
            ->getJson(route('users.activities.all', ['module' => 'gas-detector']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $activities = $response->json('logs.data');
        $this->assertNotEmpty($activities);
        $this->assertEquals('Gasdetektor', $activities[0]['module']);
    }

    public function test_sidebar_places_gas_detector_inside_data_submenu_and_expands_when_active(): void
    {
        $response = $this->actingAs($this->admin)->get(route('gas-detector.index'));
        $response->assertStatus(200);

        $html = $response->getContent();

        // 1. Pastikan Gasdetektor hanya muncul satu kali di menu navigasi sidebar
        preg_match('/<nav class="nav-menu">(.*?)<\/nav>/s', $html, $navMatch);
        $this->assertNotEmpty($navMatch);
        $navHtml = $navMatch[1];
        $this->assertEquals(1, substr_count($navHtml, route('gas-detector.index')));

        // 2. Pastikan berada di dalam #dataSubmenu sejajar dengan Man Hours, Workforce, Incident, Issue & Follow Up
        preg_match('/<div class="collapse[^"]*submenu" id="dataSubmenu">(.*?)<\/div>/s', $navHtml, $subMatch);
        $this->assertNotEmpty($subMatch);
        $submenuHtml = $subMatch[1];

        $this->assertStringContainsString('Man Hours', $submenuHtml);
        $this->assertStringContainsString('Workforce', $submenuHtml);
        $this->assertStringContainsString('Incident', $submenuHtml);
        $this->assertStringContainsString('Issue &amp; Follow Up', $submenuHtml);
        $this->assertStringContainsString('Gasdetektor', $submenuHtml);

        // 3. Pastikan dropdown Data otomatis terbuka (aria-expanded="true" & class "show") dan Gasdetektor ditandai aktif
        $this->assertStringContainsString('href="#dataSubmenu" role="button" aria-expanded="true"', $navHtml);
        $this->assertStringContainsString('class="collapse show submenu" id="dataSubmenu"', $navHtml);
        $this->assertMatchesRegularExpression('/class="nav-link-custom active"[^>]*href="' . preg_quote(route('gas-detector.index'), '/') . '"/', $submenuHtml);
    }

    public function test_gas_detector_breadcrumb_displays_dashboard_data_gasdetektor(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('gas-detector.index'));
        $response->assertStatus(200);

        $html = $response->getContent();
        preg_match('/<div class="breadcrumb-nav">(.*?)<\/div>/s', $html, $bcMatch);
        $this->assertNotEmpty($bcMatch);
        $bcHtml = $bcMatch[1];

        $this->assertStringContainsString('Dashboard', $bcHtml);
        $this->assertStringContainsString('Data', $bcHtml);
        $this->assertStringContainsString('Gasdetektor', $bcHtml);
    }

    public function test_all_other_data_submenus_work_properly(): void
    {
        $submenus = [
            '/man-hours' => 'Man Hours',
            '/workforce' => 'Workforce',
            '/incident' => 'Incident',
            '/issue-follow-up' => 'Issue & Follow Up',
        ];

        foreach ($submenus as $uri => $title) {
            $response = $this->actingAs($this->regularUser)->get($uri);
            $response->assertStatus(200);
            $response->assertSee($title);
            $response->assertSee('class="collapse show submenu" id="dataSubmenu"', false);
        }
    }
}


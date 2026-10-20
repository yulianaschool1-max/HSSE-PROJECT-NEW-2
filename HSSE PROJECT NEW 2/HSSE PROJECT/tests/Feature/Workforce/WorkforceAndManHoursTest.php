<?php

namespace Tests\Feature\Workforce;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\ManHour;
use App\Models\OperationalUnit;
use App\Models\User;
use App\Models\Workforce;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkforceAndManHoursTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin Workforce',
            'email' => 'admin.wf.test@geodipa.co.id',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
            'unit' => 'Dieng',
            'is_active' => true,
        ]);

        $this->regularUser = User::create([
            'name' => 'Test User Workforce',
            'email' => 'user.wf.test@geodipa.co.id',
            'password' => Hash::make('password123'),
            'role' => 'User',
            'unit' => 'Dieng',
            'is_active' => true,
        ]);
    }

    public function test_workforce_and_man_hours_pages_render_and_expand_data_submenu(): void
    {
        $wfResponse = $this->actingAs($this->admin)->get(route('workforce.index'));
        $wfResponse->assertStatus(200);
        $wfResponse->assertSee('Workforce');
        $wfResponse->assertSee('Dashboard');
        $wfResponse->assertSee('Data');
        $wfResponse->assertSee('class="collapse show submenu" id="dataSubmenu"', false);

        $mhResponse = $this->actingAs($this->admin)->get(route('man-hours.index'));
        $mhResponse->assertStatus(200);
        $mhResponse->assertSee('Man Hours');
        $mhResponse->assertSee('Dashboard');
        $mhResponse->assertSee('Data');
        $mhResponse->assertSee('class="collapse show submenu" id="dataSubmenu"', false);
    }

    public function test_sidebar_orders_workforce_first_and_man_hours_second_under_data_submenu(): void
    {
        $html = $this->actingAs($this->admin)->get(route('workforce.index'))->assertOk()->getContent();

        $submenuPos = strpos($html, 'id="dataSubmenu"');
        $this->assertNotFalse($submenuPos);

        $submenuHtml = substr($html, $submenuPos, 2500);
        $wfPos = strpos($submenuHtml, route('workforce.index'));
        $mhPos = strpos($submenuHtml, route('man-hours.index'));
        $incPos = strpos($submenuHtml, route('incident.index'));
        $ifuPos = strpos($submenuHtml, route('issue-follow-up.index'));
        $gdPos = strpos($submenuHtml, route('gas-detector.index'));

        $this->assertNotFalse($wfPos);
        $this->assertNotFalse($mhPos);
        $this->assertLessThan($mhPos, $wfPos, 'Menu Workforce harus berada di urutan pertama (sebelum Man Hours) di dalam dropdown Data.');
        $this->assertLessThan($incPos, $mhPos);
        $this->assertLessThan($ifuPos, $incPos);
        $this->assertLessThan($gdPos, $ifuPos);
    }

    public function test_events_module_orders_newest_first_and_tracks_completed_events_accurately(): void
    {
        // Tambah event lama yang sudah selesai (10 Oktober 2026)
        $olderCompleted = Event::create([
            'title' => 'Safety Drill Oktober Awal',
            'category' => 'Emergency Drill',
            'unit' => 'PMU',
            'event_date' => '2026-10-10',
            'participants' => 45,
            'status' => 'Selesai',
            'location' => 'Wellpad 7',
            'pic' => 'Tim HSSE',
        ]);

        // Tambah event yang masih direncanakan (tidak boleh dihitung sebagai Selesai)
        $plannedEvent = Event::create([
            'title' => 'Rencana Townhall Akhir Tahun',
            'category' => 'Kampanye Budaya K3',
            'unit' => 'PMU',
            'event_date' => '2026-10-11',
            'participants' => 100,
            'status' => 'Direncanakan',
            'location' => 'Aula Utama',
            'pic' => 'Tim HSSE',
        ]);

        // Tambah event baru yang sudah terlaksana (13 Oktober 2026) via controller
        $storeRes = $this->actingAs($this->admin)->postJson(route('events.store'), [
            'title' => 'Inspeksi Gabungan HSSE Terbaru',
            'category' => 'Safety Inspection',
            'unit' => 'Dieng Unit 1',
            'event_date' => '2026-10-13',
            'participants' => 30,
            'location' => 'Power Plant Unit 1',
            'pic' => 'Koordinator HSSE',
            // status tidak dikirim -> default ke Selesai (Kegiatan Terlaksana)
        ]);

        $storeRes->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Selesai');

        $newestEventId = $storeRes->json('data.id');

        // Ambil daftar Events dan pastikan urutan terbaru di paling atas (newestEventId di atas olderCompleted)
        $indexJson = $this->actingAs($this->admin)->getJson(route('events.index', ['per_page' => 100]))->assertOk();
        $orderedIds = collect($indexJson->json('events.data') ?? $indexJson->json('events'))->pluck('id')->all();

        $posNewest = array_search($newestEventId, $orderedIds, true);
        $posOlder = array_search($olderCompleted->id, $orderedIds, true);
        $this->assertNotFalse($posNewest);
        $this->assertNotFalse($posOlder);
        $this->assertLessThan($posOlder, $posNewest, 'Event terbaru harus tampil di paling atas dan menggeser event yang lebih lama ke bawah.');

        // Pastikan event Direncanakan tetap berstatus Direncanakan dan tidak terhitung di completed_events
        $this->assertEquals('Direncanakan', $plannedEvent->fresh()->status);

        // Filter hanya kegiatan yang sudah selesai (status=Selesai)
        $completedOnlyJson = $this->actingAs($this->admin)
            ->getJson(route('events.index', ['status' => 'Selesai', 'per_page' => 100]))
            ->assertOk();
        $completedIds = collect($completedOnlyJson->json('events.data') ?? $completedOnlyJson->json('events'))->pluck('id')->all();
        $this->assertContains($newestEventId, $completedIds);
        $this->assertContains($olderCompleted->id, $completedIds);
        $this->assertNotContains($plannedEvent->id, $completedIds);
    }

    public function test_empty_state_shows_zero_stats_and_no_dummy_data(): void
    {
        // Filter pada tanggal masa depan yang belum memiliki data
        $wfJson = $this->actingAs($this->admin)
            ->getJson(route('workforce.index', ['start_date' => '2099-01-01', 'end_date' => '2099-01-31']));

        $wfJson->assertStatus(200)
            ->assertJsonPath('stats.total_workers', 0)
            ->assertJsonPath('stats.total_man_hours', 0)
            ->assertJsonPath('stats.total_records', 0);

        $mhJson = $this->actingAs($this->admin)
            ->getJson(route('man-hours.index', ['start_date' => '2099-01-01', 'end_date' => '2099-01-31']));

        $mhJson->assertStatus(200)
            ->assertJsonPath('stats.total_man_hours', 0)
            ->assertJsonPath('stats.total_workers', 0)
            ->assertJsonPath('stats.daily_man_hours', 0)
            ->assertJsonPath('stats.monthly_man_hours', 0);
    }

    public function test_admin_can_create_workforce_and_automatically_calculates_man_hours(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-15',
            'category' => 'GDE',
            'unit' => 'PMU',
            'department' => 'Divisi HSSE',
            'worker_count' => 25,
            // hours_per_worker tidak dikirim -> otomatis memakai default 8 jam
            'notes' => 'Shift Pagi Normal',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('workforce.category', 'GDE')
            ->assertJsonPath('workforce.worker_count', 25)
            ->assertJsonPath('man_hour.number_of_workers', 25);

        $wfId = $response->json('workforce.id');
        $this->assertDatabaseHas('workforces', [
            'id' => $wfId,
            'category' => 'GDE',
            'worker_count' => 25,
        ]);

        // Pastikan ManHour otomatis terbuat (25 pekerja x 8 jam = 200 jam)
        $manHour = ManHour::where('workforce_id', $wfId)->first();
        $this->assertNotNull($manHour);
        $this->assertEquals(25, (int) $manHour->number_of_workers);
        $this->assertEquals(8.0, (float) $manHour->hours_per_worker);
        $this->assertEquals(200.0, (float) $manHour->total_hours);

        // Pastikan tercatat di ActivityLog
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Workforce',
            'action' => 'TAMBAH',
            'record_id' => $wfId,
        ]);
    }

    public function test_multiple_categories_aggregate_correctly_and_reload_does_not_double_count(): void
    {
        $categoriesData = [
            ['category' => 'GDE', 'worker_count' => 30, 'hours_per_worker' => 8],
            ['category' => 'Kodipa', 'worker_count' => 20, 'hours_per_worker' => 8],
            ['category' => 'Utility', 'worker_count' => 15, 'hours_per_worker' => 8],
            ['category' => 'Security', 'worker_count' => 12, 'hours_per_worker' => 12],
            ['category' => 'Contractor & Sub-Contractor', 'worker_count' => 40, 'hours_per_worker' => 8],
            ['category' => 'Kerja Praktik', 'worker_count' => 5, 'hours_per_worker' => 8],
        ];

        $expectedWorkers = 0;
        $expectedManHours = 0;

        foreach ($categoriesData as $row) {
            $this->actingAs($this->admin)->postJson(route('workforce.store'), [
                'record_date' => '2026-11-20',
                'category' => $row['category'],
                'unit' => $row['category'],
                'worker_count' => $row['worker_count'],
                'hours_per_worker' => $row['hours_per_worker'],
            ])->assertStatus(201);

            $expectedWorkers += $row['worker_count'];
            $expectedManHours += ($row['worker_count'] * $row['hours_per_worker']);
        }

        // Panggil halaman Man Hours beberapa kali untuk memastikan tidak ada penggandaan perhitungan (idempoten)
        $this->actingAs($this->admin)->get(route('man-hours.index', ['start_date' => '2026-11-20', 'end_date' => '2026-11-20']))->assertStatus(200);
        $mhResponse = $this->actingAs($this->admin)->getJson(route('man-hours.index', ['start_date' => '2026-11-20', 'end_date' => '2026-11-20']));

        $mhResponse->assertStatus(200)
            ->assertJsonPath('stats.total_workers', $expectedWorkers)
            ->assertJsonPath('stats.total_records', 6);
        $this->assertEquals($expectedManHours, (float) $mhResponse->json('stats.total_man_hours'));
        $this->assertEquals($expectedManHours, (float) $mhResponse->json('stats.daily_man_hours'));
    }

    public function test_master_operational_units_batch_workforce_latest_date_highlight_and_man_hours_expansion(): void
    {
        // 1. Pastikan 6 unit standar awal sudah aktif
        $initialActiveCount = OperationalUnit::where('is_active', true)->count();
        $this->assertGreaterThanOrEqual(6, $initialActiveCount);

        // 2. Input batch Workforce pada tanggal 2026-12-13 untuk 6 unit awal
        $batchDate1 = '2026-12-13';
        $batchRes1 = $this->actingAs($this->admin)->postJson(route('workforce.store-batch'), [
            'record_date' => $batchDate1,
            'entries' => [
                ['unit_name' => 'GDE', 'worker_count' => 20, 'hours_per_worker' => 8, 'notes' => 'Normal'],
                ['unit_name' => 'Kodipa', 'worker_count' => 15, 'hours_per_worker' => 8, 'notes' => 'Normal'],
                ['unit_name' => 'Utility', 'worker_count' => 10, 'hours_per_worker' => 8, 'notes' => 'Normal'],
                ['unit_name' => 'Security', 'worker_count' => 12, 'hours_per_worker' => 8, 'notes' => 'Normal'],
                ['unit_name' => 'Contractor & Sub-Contractor', 'worker_count' => 30, 'hours_per_worker' => 8, 'notes' => 'Normal'],
                ['unit_name' => 'Kerja Praktik', 'worker_count' => 5, 'hours_per_worker' => 8, 'notes' => 'Normal'],
            ],
        ]);

        // Total pekerja tanggal 2026-12-13 = 92 orang, Total Man Hours = 92 * 8 = 736 jam
        $batchRes1->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonPath('latest_highlight.record_date', $batchDate1)
            ->assertJsonPath('latest_highlight.total_workers', 92);
        $this->assertEquals(736.0, (float) $batchRes1->json('latest_highlight.total_man_hours'));

        // 3. Tambahkan 2 Unit / Area Operasi baru (dari 6 unit menjadi 8 unit aktif)
        $unit7Res = $this->actingAs($this->admin)->postJson(route('workforce.units.store'), [
            'name' => 'Wellpad Rig Drilling',
            'description' => 'Unit Pengeboran Sumur Baru',
        ]);
        $unit7Res->assertStatus(201)->assertJsonPath('unit.name', 'Wellpad Rig Drilling');
        $unit7Id = $unit7Res->json('unit.id');

        $unit8Res = $this->actingAs($this->admin)->postJson(route('workforce.units.store'), [
            'name' => 'Civil & Geohazard Team',
            'description' => 'Unit Sipil dan Mitigasi Lereng',
        ]);
        $unit8Res->assertStatus(201)->assertJsonPath('unit.name', 'Civil & Geohazard Team');
        $unit8Id = $unit8Res->json('unit.id');

        // Cegah duplikasi nama unit
        $this->actingAs($this->admin)->postJson(route('workforce.units.store'), [
            'name' => 'Wellpad Rig Drilling',
        ])->assertStatus(422);

        // 4. Input batch Workforce pada tanggal berikutnya (2026-12-14) dengan 8 unit aktif
        $batchDate2 = '2026-12-14';
        $batchRes2 = $this->actingAs($this->admin)->postJson(route('workforce.store-batch'), [
            'record_date' => $batchDate2,
            'entries' => [
                ['unit_name' => 'GDE', 'worker_count' => 20, 'hours_per_worker' => 8],
                ['unit_name' => 'Kodipa', 'worker_count' => 15, 'hours_per_worker' => 8],
                ['unit_name' => 'Utility', 'worker_count' => 10, 'hours_per_worker' => 8],
                ['unit_name' => 'Security', 'worker_count' => 12, 'hours_per_worker' => 8],
                ['unit_name' => 'Contractor & Sub-Contractor', 'worker_count' => 30, 'hours_per_worker' => 8],
                ['unit_name' => 'Kerja Praktik', 'worker_count' => 5, 'hours_per_worker' => 8],
                ['unit_name' => 'Wellpad Rig Drilling', 'worker_count' => 18, 'hours_per_worker' => 8],
                ['unit_name' => 'Civil & Geohazard Team', 'worker_count' => 10, 'hours_per_worker' => 8],
            ],
        ]);

        // Total pekerja tanggal 2026-12-14 = 120 orang, Total Man Hours = 120 * 8 = 960 jam
        // Pastikan Highlight Tanggal Terbaru otomatis berpindah dari 2026-12-13 ke 2026-12-14
        $batchRes2->assertSuccessful()
            ->assertJsonPath('latest_highlight.record_date', $batchDate2)
            ->assertJsonPath('latest_highlight.total_workers', 120);
        $this->assertEquals(960.0, (float) $batchRes2->json('latest_highlight.total_man_hours'));

        // Pastikan tanggal 2026-12-13 tetap tersimpan di riwayat harian (tidak hilang)
        $wfFilterJson = $this->actingAs($this->admin)
            ->getJson(route('workforce.index', ['start_date' => '2026-12-13', 'end_date' => '2026-12-14']))
            ->assertOk();

        $dailyDates = collect($wfFilterJson->json('daily_history'))->pluck('record_date')->all();
        $this->assertContains('2026-12-14', $dailyDates);
        $this->assertContains('2026-12-13', $dailyDates);

        // 5. Periksa perhitungan Man Hours untuk periode 2026-12-13 s/d 2026-12-14:
        // Total Man Hours Harian (tanggal terbaru 2026-12-14) = 960 jam
        // Total Akumulasi Man Hours (2026-12-13 + 2026-12-14) = 736 + 960 = 1696 jam
        $mhPeriodeJson = $this->actingAs($this->admin)
            ->getJson(route('man-hours.index', ['start_date' => '2026-12-13', 'end_date' => '2026-12-14']))
            ->assertOk();

        $this->assertEquals(1696.0, (float) $mhPeriodeJson->json('stats.total_man_hours'));
        $this->assertEquals(960.0, (float) $mhPeriodeJson->json('stats.daily_man_hours'));
        $this->assertTrue((bool) $mhPeriodeJson->json('unit_expansion.has_new_units'));
        $this->assertGreaterThanOrEqual(2, (int) $mhPeriodeJson->json('unit_expansion.new_active_count'));

        // Pastikan keterangan ekspansi unit muncul pada halaman HTML Man Hours
        $mhHtml = $this->actingAs($this->admin)
            ->get(route('man-hours.index', ['start_date' => '2026-12-13', 'end_date' => '2026-12-14']))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('id="unitExpansionBanner"', $mhHtml);
        $this->assertStringContainsString('Wellpad Rig Drilling', $mhHtml);
        $this->assertStringContainsString('Civil &amp; Geohazard Team', $mhHtml);

        // 6. Uji edit dan nonaktifkan unit baru tanpa merusak data historis
        $this->actingAs($this->admin)->putJson(route('workforce.units.update', $unit7Id), [
            'name' => 'Wellpad Rig Drilling Updated',
            'description' => 'Deskripsi Diperbarui',
        ])->assertOk()->assertJsonPath('unit.name', 'Wellpad Rig Drilling Updated');

        $this->actingAs($this->admin)->patchJson(route('workforce.units.toggle', $unit8Id))
            ->assertOk()
            ->assertJsonPath('unit.is_active', false);

        // Data historis Man Hours tetap utuh (1696 jam) walaupun unit dinonaktifkan
        $mhAfterToggle = $this->actingAs($this->admin)
            ->getJson(route('man-hours.index', ['start_date' => '2026-12-13', 'end_date' => '2026-12-14']))
            ->assertOk();
        $this->assertEquals(1696.0, (float) $mhAfterToggle->json('stats.total_man_hours'));
    }

    public function test_updating_and_deleting_workforce_synchronizes_man_hours_immediately(): void
    {
        $storeRes = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-18',
            'category' => 'Kodipa',
            'unit' => 'PMU',
            'worker_count' => 10,
            'hours_per_worker' => 8,
        ]);

        $wfId = $storeRes->json('workforce.id');
        $this->assertEquals(80.0, (float) ManHour::where('workforce_id', $wfId)->value('total_hours'));

        // Update jumlah pekerja dari 10 menjadi 18
        $updateRes = $this->actingAs($this->admin)->putJson(route('workforce.update', $wfId), [
            'record_date' => '2026-10-18',
            'category' => 'Kodipa',
            'unit' => 'PMU',
            'worker_count' => 18,
            'hours_per_worker' => 8,
        ]);

        $updateRes->assertStatus(200)->assertJsonPath('success', true);
        $manHour = ManHour::where('workforce_id', $wfId)->first();
        $this->assertEquals(18, (int) $manHour->number_of_workers);
        $this->assertEquals(144.0, (float) $manHour->total_hours);

        // Hapus data Workforce -> data Man Hours terkait harus ikut terhapus otomatis
        $deleteRes = $this->actingAs($this->admin)->deleteJson(route('workforce.destroy', $wfId));
        $deleteRes->assertStatus(200)->assertJsonPath('success', true);

        $this->assertDatabaseMissing('workforces', ['id' => $wfId]);
        $this->assertDatabaseMissing('man_hours', ['workforce_id' => $wfId]);
    }

    public function test_admin_can_adjust_hours_per_worker_in_man_hours_and_recalculate(): void
    {
        $wf = Workforce::create([
            'record_date' => '2026-10-19',
            'category' => 'Contractor & Sub-Contractor',
            'unit' => 'Dieng Unit 2',
            'worker_count' => 30,
            'hours_per_worker' => 8,
        ]);

        $manHour = ManHour::where('workforce_id', $wf->id)->firstOrFail();
        $this->assertEquals(240.0, (float) $manHour->total_hours);

        // Sesuaikan jam kerja per orang menjadi 10 jam
        $res = $this->actingAs($this->admin)->putJson(route('man-hours.update', $manHour->id), [
            'hours_per_worker' => 10,
            'notes' => 'Lembur proyek overhaul 2 jam',
        ]);

        $res->assertStatus(200)->assertJsonPath('success', true);

        $manHour->refresh();
        $wf->refresh();

        $this->assertEquals(30, (int) $manHour->number_of_workers);
        $this->assertEquals(10.0, (float) $manHour->hours_per_worker);
        $this->assertEquals(300.0, (float) $manHour->total_hours);
        $this->assertEquals(10.0, (float) $wf->hours_per_worker);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Man Hours',
            'action' => 'UBAH',
            'record_id' => $manHour->id,
        ]);
    }

    public function test_workforce_validation_rejects_negative_worker_count_and_prevents_duplicate_date_category(): void
    {
        // Nilai negatif harus ditolak
        $negRes = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-20',
            'category' => 'Utility',
            'unit' => 'Utility',
            'worker_count' => -5,
        ]);
        $negRes->assertStatus(422)->assertJsonValidationErrors(['worker_count']);

        // Unit/kategori yang tidak terdaftar di Master Unit harus ditolak
        $invalidCat = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-20',
            'category' => 'Kategori Sembarangan',
            'unit' => 'Kategori Sembarangan',
            'worker_count' => 10,
        ]);
        $invalidCat->assertStatus(422);

        // Buat 1 data valid
        $storeRes = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-20',
            'category' => 'Utility',
            'unit' => 'Utility',
            'worker_count' => 14,
            'hours_per_worker' => 8,
        ]);
        $storeRes->assertStatus(201);
        $wfId = $storeRes->json('workforce.id');

        // Cek endpoint duplikasi
        $dupRes = $this->actingAs($this->admin)->getJson(route('workforce.check-duplicate', [
            'record_date' => '2026-10-20',
            'category' => 'Utility',
        ]));

        $dupRes->assertStatus(200)
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('exists', true)
            ->assertJsonPath('record.id', $wfId);

        // Coba simpan lagi dengan kombinasi tanggal + unit yang sama -> harus dicegah (422 duplicate: true)
        $dupStore = $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-20',
            'category' => 'Utility',
            'unit' => 'Utility',
            'worker_count' => 25,
        ]);
        $dupStore->assertStatus(422)
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('existing.id', $wfId);
    }

    public function test_authenticated_users_can_see_add_button_and_input_workforce_data(): void
    {
        // Pastikan baik Admin maupun User yang login dapat melihat tombol "+ Input Workforce" dan modal input
        $pageRes = $this->actingAs($this->regularUser)->get(route('workforce.index'));
        $pageRes->assertStatus(200);
        $pageRes->assertSee('id="btnTambahWorkforce"', false);
        $pageRes->assertSee('Input Workforce');
        $pageRes->assertSee('id="workforceFormModal"', false);
        $pageRes->assertSee('id="workforceBatchModal"', false);

        // User dapat menyimpan data Workforce dan otomatis terhubung ke Man Hours
        $createRes = $this->actingAs($this->regularUser)->postJson(route('workforce.store'), [
            'record_date' => '2026-10-21',
            'category' => 'Security',
            'unit' => 'Security',
            'worker_count' => 16,
            'hours_per_worker' => 8,
            'notes' => 'Shift Pengamanan Area',
        ]);
        $createRes->assertStatus(201)->assertJsonPath('success', true);
        $wfId = $createRes->json('workforce.id');

        $mh = ManHour::where('workforce_id', $wfId)->firstOrFail();
        $this->assertEquals(128.0, (float) $mh->total_hours);

        // User dapat melihat detail, mengedit, dan menghapus
        $this->actingAs($this->regularUser)->getJson(route('workforce.show', $wfId))->assertStatus(200);
        $this->actingAs($this->regularUser)->putJson(route('workforce.update', $wfId), [
            'record_date' => '2026-10-21',
            'category' => 'Security',
            'unit' => 'Security',
            'worker_count' => 20,
            'hours_per_worker' => 8,
        ])->assertStatus(200);

        $this->assertEquals(160.0, (float) $mh->fresh()->total_hours);

        $this->actingAs($this->regularUser)->deleteJson(route('workforce.destroy', $wfId))->assertStatus(200);
        $this->assertDatabaseMissing('workforces', ['id' => $wfId]);
        $this->assertDatabaseMissing('man_hours', ['workforce_id' => $wfId]);
    }

    public function test_export_endpoints_work_for_workforce_and_man_hours(): void
    {
        Workforce::create([
            'record_date' => '2026-10-22',
            'category' => 'Kerja Praktik',
            'unit' => 'Kerja Praktik',
            'worker_count' => 6,
            'hours_per_worker' => 8,
        ]);

        $wfExport = $this->actingAs($this->admin)->get(route('workforce.export'));
        $wfExport->assertStatus(200);
        $this->assertStringContainsString('text/csv', $wfExport->headers->get('Content-Type'));

        $mhExport = $this->actingAs($this->admin)->get(route('man-hours.export'));
        $mhExport->assertStatus(200);
        $this->assertStringContainsString('text/csv', $mhExport->headers->get('Content-Type'));
    }

    public function test_edit_unit_accumulated_man_hours_manual_correction_and_persistence(): void
    {
        OperationalUnit::ensureDefaultsSeeded();
        $testUnit = OperationalUnit::create([
            'name' => 'Unit Khusus Uji Akumulasi',
            'code' => 'UNIT-UJA',
            'default_hours' => 8.0,
            'is_active' => true,
            'is_initial' => false,
            'sort_order' => 99,
        ]);

        // 1. Catat Workforce awal untuk testUnit pada tanggal 2026-11-01: 25 pekerja x 8 jam = 200 Man Hours
        $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-11-01',
            'category' => 'GDE',
            'unit' => $testUnit->name,
            'operational_unit_id' => $testUnit->id,
            'worker_count' => 25,
            'hours_per_worker' => 8,
        ])->assertCreated();

        // Cek data awal sebelum diedit
        $mhIndexBefore = $this->actingAs($this->admin)->getJson(route('man-hours.index', ['unit' => $testUnit->name]))->assertOk();
        $unitRowBefore = collect($mhIndexBefore->json('unitRecap.rows'))->firstWhere('unit_name', $testUnit->name);
        $this->assertEquals(200.0, (float) $unitRowBefore['period_man_hours']);
        $this->assertEquals(200.0, (float) $mhIndexBefore->json('stats.daily_man_hours'));
        $this->assertFalse((bool) $unitRowBefore['has_manual_adjustment']);

        // 2. Koreksi manual Total Akumulasi Man Hours unit dari 200 menjadi 250 Jam
        $editRes = $this->actingAs($this->admin)->putJson(route('man-hours.units.accumulated-hours', $testUnit->id), [
            'accumulated_hours' => 250.0,
            'notes' => 'Penyesuaian manual sesuai audit lapangan',
        ])->assertOk();

        $editRes->assertJsonPath('success', true);
        $editRes->assertJsonPath('unit.accumulated_hours', 250);
        $editRes->assertJsonPath('unit.adjustment_delta', 50);

        // Pastikan tersimpan di database MySQL (tabel operational_units, unit_man_hour_adjustments, dan activity_logs)
        $this->assertDatabaseHas('operational_units', [
            'id' => $testUnit->id,
            'manual_accumulated_hours' => 250.00,
            'accumulated_hours_adjustment' => 50.00,
        ]);

        $this->assertDatabaseHas('unit_man_hour_adjustments', [
            'operational_unit_id' => $testUnit->id,
            'new_accumulated_hours' => 250.00,
            'adjustment_delta' => 50.00,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Man Hours',
            'action' => 'UBAH',
            'record_id' => $testUnit->id,
        ]);

        // 3. Muat ulang halaman Man Hours: nilai hasil edit tetap tersimpan (250 Jam)
        $mhIndexAfter = $this->actingAs($this->admin)->getJson(route('man-hours.index', ['unit' => $testUnit->name]))->assertOk();
        $unitRowAfter = collect($mhIndexAfter->json('unitRecap.rows'))->firstWhere('unit_name', $testUnit->name);
        $this->assertEquals(250.0, (float) $unitRowAfter['period_man_hours']);
        $this->assertTrue((bool) $unitRowAfter['has_manual_adjustment']);
        $this->assertEquals(50.0, (float) $unitRowAfter['adjustment_delta']);

        // 4. Pastikan Man Hours Harian TIDAK berubah akibat edit akumulasi (tetap 200 Jam)
        $this->assertEquals(200.0, (float) $mhIndexAfter->json('stats.daily_man_hours'));
        $this->assertEquals(200.0, (float) $unitRowAfter['latest_man_hours']);

        // Cek HTML render: tombol edit, badge koreksi manual, dan input inline tersedia
        $html = $this->actingAs($this->admin)->get(route('man-hours.index', ['unit' => $testUnit->name]))->assertOk()->getContent();
        $this->assertStringContainsString('id="unitAccumDisplay-' . $testUnit->id . '"', $html);
        $this->assertStringContainsString('id="unitAccumForm-' . $testUnit->id . '"', $html);
        $this->assertStringContainsString('Koreksi Manual', $html);

        // 5. Tambahkan data Workforce pada tanggal berikutnya (2026-11-02): 10 pekerja x 8 jam = 80 Jam
        $this->actingAs($this->admin)->postJson(route('workforce.store'), [
            'record_date' => '2026-11-02',
            'category' => 'GDE',
            'unit' => $testUnit->name,
            'operational_unit_id' => $testUnit->id,
            'worker_count' => 10,
            'hours_per_worker' => 8,
        ])->assertCreated();

        // Perhitungan selanjutnya mengikuti aturan akumulasi:
        // Raw baru = 200 + 80 = 280 Jam
        // Akumulasi dengan koreksi = 280 + 50 = 330 Jam
        // Man Hours Harian tanggal terbaru (2026-11-02) = 80 Jam
        $mhIndexNextDate = $this->actingAs($this->admin)->getJson(route('man-hours.index', ['unit' => $testUnit->name]))->assertOk();
        $unitRowNext = collect($mhIndexNextDate->json('unitRecap.rows'))->firstWhere('unit_name', $testUnit->name);
        $this->assertEquals(330.0, (float) $unitRowNext['period_man_hours']);
        $this->assertEquals(80.0, (float) $mhIndexNextDate->json('stats.daily_man_hours'));

        // 6. Validasi: nilai negatif ditolak
        $this->actingAs($this->admin)->putJson(route('man-hours.units.accumulated-hours', $testUnit->id), [
            'accumulated_hours' => -50.0,
        ])->assertStatus(422)->assertJsonValidationErrors(['accumulated_hours']);

        // 7. Reset ke perhitungan otomatis sistem
        $resetRes = $this->actingAs($this->admin)->putJson(route('man-hours.units.accumulated-hours', $testUnit->id), [
            'reset' => true,
            'accumulated_hours' => 280.0,
        ])->assertOk();

        $this->assertDatabaseHas('operational_units', [
            'id' => $testUnit->id,
            'manual_accumulated_hours' => null,
            'accumulated_hours_adjustment' => 0.00,
        ]);

        $mhAfterReset = $this->actingAs($this->admin)->getJson(route('man-hours.index', ['unit' => $testUnit->name]))->assertOk();
        $unitRowReset = collect($mhAfterReset->json('unitRecap.rows'))->firstWhere('unit_name', $testUnit->name);
        $this->assertEquals(280.0, (float) $unitRowReset['period_man_hours']);
        $this->assertFalse((bool) $unitRowReset['has_manual_adjustment']);
    }
}

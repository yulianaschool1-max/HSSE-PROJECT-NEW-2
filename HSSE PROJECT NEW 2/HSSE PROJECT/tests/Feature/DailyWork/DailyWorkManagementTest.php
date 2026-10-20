<?php

namespace Tests\Feature\DailyWork;

use App\Models\DailyWork;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DailyWorkManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Admin Dieng',
                'email' => 'admin.dieng@example.com',
            ]);
        }
    }

    public function test_daily_work_index_renders_successfully(): void
    {
        $response = $this->get(route('daily-work.index'));
        $response->assertStatus(200);
        $response->assertSee('Daily Work');
        $response->assertSee('Daftar Pekerjaan Harian');
    }

    public function test_daily_work_index_ajax_json_response(): void
    {
        $response = $this->getJson(route('daily-work.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'works',
            'stats' => ['total', 'done', 'ongoing', 'paused', 'not_started'],
        ]);
    }

    public function test_daily_work_show_json_detail_with_real_database_data(): void
    {
        $work = DailyWork::create([
            'name' => 'Uji Coba Turbin Geothermal Unit 1',
            'unit' => 'PMU',
            'category' => 'Mechanical',
            'start_date' => '2025-05-10',
            'end_date' => '2025-05-12',
            'location' => 'Powerhouse Unit 1',
            'executor' => 'Tim Maintenance Turbin PT Geo Dipa Energi',
            'status' => 'Berlangsung',
            'description' => 'Pemeriksaan getaran dan efisiensi poros generator.',
        ]);

        $response = $this->getJson(route('daily-work.show', $work->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'work' => [
                'id' => $work->id,
                'name' => 'Uji Coba Turbin Geothermal Unit 1',
                'unit' => 'PMU',
                'category' => 'Mechanical',
                'status' => 'Berlangsung',
                'executor' => 'Tim Maintenance Turbin PT Geo Dipa Energi',
            ],
        ]);
    }

    public function test_daily_work_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson(route('daily-work.show', 99999999));
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data pekerjaan tidak ditemukan.',
        ]);
    }

    public function test_daily_work_show_html_renders_detail_page(): void
    {
        $work = DailyWork::create([
            'name' => 'Inspeksi Pipa Transmisi Uap Pad 28',
            'unit' => 'Utility',
            'category' => 'Piping',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-03',
            'location' => 'Pad 28 Dieng',
            'executor' => 'Tim Pipeline Dieng',
            'status' => 'Selesai',
            'description' => 'Ultrasonic thickness test pada pipa uap utama.',
        ]);

        $response = $this->get(route('daily-work.show', $work->id));
        $response->assertStatus(200);
        $response->assertSee('Detail Pekerjaan Harian');
        $response->assertSee('Inspeksi Pipa Transmisi Uap Pad 28');
        $response->assertSee('Tim Pipeline Dieng');
    }

    public function test_daily_work_store_creates_record(): void
    {
        $payload = [
            'name' => 'Pengujian Katup Tekanan Tinggi Pad 7',
            'unit' => 'BISMO',
            'category' => 'Instrumentation',
            'executor' => 'Tim Kontrol & Instrumentasi',
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-02',
            'location' => 'Wellhead Pad 7',
            'status' => 'Belum Mulai',
            'description' => 'Kalibrasi transmitter dan pengujian ESD valve.',
        ];

        $response = $this->postJson(route('daily-work.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Pekerjaan baru berhasil ditambahkan.',
            'work' => [
                'name' => 'Pengujian Katup Tekanan Tinggi Pad 7',
                'status' => 'Belum Mulai',
            ],
        ]);

        $this->assertDatabaseHas('daily_works', [
            'name' => 'Pengujian Katup Tekanan Tinggi Pad 7',
            'location' => 'Wellhead Pad 7',
        ]);
    }

    public function test_daily_work_update_modifies_record(): void
    {
        $work = DailyWork::create([
            'name' => 'Pekerjaan Awal',
            'unit' => 'JOTRE',
            'category' => 'Civil',
            'start_date' => '2025-08-01',
            'end_date' => '2025-08-05',
            'location' => 'Akses Jalan Pad 30',
            'executor' => 'Kontraktor Sipil',
            'status' => 'Berlangsung',
            'description' => 'Perbaikan rabat beton.',
        ]);

        $response = $this->putJson(route('daily-work.update', $work->id), [
            'name' => 'Pekerjaan Telah Diperbarui',
            'unit' => 'JOTRE',
            'category' => 'Civil',
            'start_date' => '2025-08-01',
            'end_date' => '2025-08-05',
            'location' => 'Akses Jalan Pad 30',
            'executor' => 'Kontraktor Sipil',
            'status' => 'Selesai',
            'description' => 'Perbaikan jalan selesai 100%.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Data pekerjaan berhasil diperbarui.',
            'work' => [
                'id' => $work->id,
                'name' => 'Pekerjaan Telah Diperbarui',
                'status' => 'Selesai',
            ],
        ]);

        $this->assertDatabaseHas('daily_works', [
            'id' => $work->id,
            'name' => 'Pekerjaan Telah Diperbarui',
            'status' => 'Selesai',
        ]);
    }

    public function test_daily_work_destroy_removes_record(): void
    {
        $work = DailyWork::create([
            'name' => 'Pekerjaan Yang Dihapus',
            'unit' => 'PMU',
            'category' => 'Electrical',
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-02',
            'location' => 'Switchyard Dieng',
            'executor' => 'Tim Elektrikal',
            'status' => 'Belum Mulai',
            'description' => 'Akan dibatalkan.',
        ]);

        $response = $this->deleteJson(route('daily-work.destroy', $work->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pekerjaan berhasil dihapus.',
        ]);

        $this->assertDatabaseMissing('daily_works', [
            'id' => $work->id,
        ]);
    }

    public function test_daily_work_export_csv(): void
    {
        $response = $this->get(route('daily-work.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_daily_work_index_renders_ongoing_highlight_directly(): void
    {
        $ongoing = DailyWork::create([
            'name' => 'Pekerjaan Berlangsung Uji Highlight ' . uniqid(),
            'unit' => 'PMU',
            'category' => 'Mechanical',
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-10',
            'location' => 'Wellpad 28',
            'executor' => 'PJ Khusus Highlight',
            'status' => 'Berlangsung',
            'description' => 'Kegiatan sedang berjalan di lapangan.',
        ]);

        $completed = DailyWork::create([
            'name' => 'Pekerjaan Selesai Tidak Boleh Tampil ' . uniqid(),
            'unit' => 'PMU',
            'category' => 'Mechanical',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-05',
            'location' => 'Wellpad 28',
            'executor' => 'PJ Selesai',
            'status' => 'Selesai',
        ]);

        $response = $this->get(route('daily-work.index'));
        $response->assertStatus(200);
        $response->assertSee('Pekerjaan Sedang Berlangsung');
        $response->assertSee($ongoing->name);
        $response->assertSee($ongoing->executor);
    }

    public function test_daily_work_check_duplicate_detects_similar_work(): void
    {
        $existing = DailyWork::create([
            'name' => 'Pembersihan Filter Udara Kompresor',
            'unit' => 'Utility',
            'category' => 'Maintenance',
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
            'location' => 'Ruang Kompresor',
            'executor' => 'Tim Utility',
            'status' => 'Belum Mulai',
        ]);

        $response = $this->getJson(route('daily-work.check-duplicate', [
            'name' => 'Pembersihan Filter Udara Kompresor',
            'unit' => 'Utility',
            'start_date' => '2025-03-01',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'duplicate' => true,
        ]);
    }

    public function test_daily_work_index_renders_harmonized_layout_and_elements(): void
    {
        $response = $this->get(route('daily-work.index'));
        $response->assertStatus(200);

        // Filter elements
        $response->assertSee('filter-grid-daily');
        $response->assertSee('unitFilter');
        $response->assertSee('categoryFilter');
        $response->assertSee('statusFilter');
        $response->assertSee('startDate');
        $response->assertSee('endDate');
        $response->assertSee('searchInput');

        // 4 Stat Cards
        $response->assertSee('Total Pekerjaan');
        $response->assertSee('Pekerjaan Selesai');
        $response->assertSee('Sedang Berlangsung');
        $response->assertSee('Ditunda');

        // Donut Chart Layout & Legend
        $response->assertSee('donut-layout-daily');
        $response->assertSee('legendDone');
        $response->assertSee('legendOngoing');
        $response->assertSee('legendPaused');
        $response->assertSee('legendNotStarted');
    }

    public function test_ongoing_highlight_lifecycle_in_progress_to_completed_and_cancelled(): void
    {
        // 1. Buat pekerjaan aktif (Berlangsung / In Progress)
        $storeRes = $this->postJson(route('daily-work.store'), [
            'name' => 'Inspeksi Katup Uap Utama Highlight Test',
            'unit' => 'PMU',
            'category' => 'Maintenance',
            'executor' => 'Tim Mekanikal Dieng',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'location' => 'Wellpad 9',
            'status' => 'In Progress', // dinormalisasi ke 'Berlangsung'
            'description' => 'Sedang dikerjakan.',
        ]);

        $storeRes->assertStatus(201)
            ->assertJsonPath('work.status', 'Berlangsung');

        $workId = $storeRes->json('work.id');

        // Pastikan muncul di query highlight ongoing_works (JSON & HTML)
        $ongoingIdsAfterCreate = collect($storeRes->json('ongoing_works'))->pluck('id')->all();
        $this->assertContains($workId, $ongoingIdsAfterCreate);

        $indexHtml1 = $this->get(route('daily-work.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-work-id="' . $workId . '"', $indexHtml1);

        // 2. Ubah status pekerjaan menjadi Completed ('Selesai')
        $updateRes = $this->putJson(route('daily-work.update', $workId), [
            'status' => 'Completed', // dinormalisasi ke 'Selesai'
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('work.status', 'Selesai');

        // 3. Pekerjaan langsung hilang dari highlight (baik di response update, GET JSON, maupun HTML highlight card)
        $ongoingIdsAfterComplete = collect($updateRes->json('ongoing_works'))->pluck('id')->all();
        $this->assertNotContains($workId, $ongoingIdsAfterComplete);

        $indexJson = $this->getJson(route('daily-work.index'))->assertOk();
        $this->assertNotContains($workId, collect($indexJson->json('ongoing_works'))->pluck('id')->all());

        $indexHtml2 = $this->get(route('daily-work.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('data-work-id="' . $workId . '"', $indexHtml2);

        // 4. Pekerjaan tetap tersimpan di database dan tersedia di tabel riwayat (works)
        $this->assertDatabaseHas('daily_works', [
            'id' => $workId,
            'status' => 'Selesai',
        ]);
        $this->assertContains($workId, collect($indexJson->json('works'))->pluck('id')->all());

        // 5. Jika pekerjaan dibatalkan / ditunda ('Cancelled' -> 'Ditunda'), juga tidak boleh tampil di highlight
        $cancelRes = $this->putJson(route('daily-work.update', $workId), [
            'status' => 'Cancelled',
        ]);
        $cancelRes->assertStatus(200)
            ->assertJsonPath('work.status', 'Ditunda');
        $this->assertNotContains($workId, collect($cancelRes->json('ongoing_works'))->pluck('id')->all());

        // 6. Detail pekerjaan tetap dapat dibuka (JSON & HTML)
        $this->getJson(route('daily-work.show', $workId))
            ->assertOk()
            ->assertJsonPath('work.id', $workId)
            ->assertJsonPath('work.name', 'Inspeksi Katup Uap Utama Highlight Test');

        $this->get(route('daily-work.show', $workId))
            ->assertOk()
            ->assertSee('Inspeksi Katup Uap Utama Highlight Test');
    }
}


<?php

namespace Tests\Feature\HSSEPlan;

use App\Models\HssePlan;
use App\Models\HssePlanNote;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HssePlanManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure at least one test user exists
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Admin Dieng',
                'email' => 'admin.dieng@example.com',
            ]);
        }
    }

    public function test_hsse_plan_index_renders_successfully(): void
    {
        $response = $this->get(route('hsse-plan.index'));
        $response->assertStatus(200);
        $response->assertSee('HSSE Plan');
        $response->assertSee('KPI Kontraktor');
    }

    public function test_hsse_plan_index_ajax_json_response(): void
    {
        $response = $this->getJson(route('hsse-plan.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'kpis',
            'stats' => ['total', 'achieved', 'progress', 'not_achieved', 'leading', 'lagging'],
            'statusChart',
            'typeChart',
            'monthlyChart',
        ]);
    }

    public function test_store_hsse_plan_with_form_payload(): void
    {
        $payload = [
            'title' => 'Inspeksi K3 Triwulan IV',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'target' => 100,
            'unit_measure' => '%',
            'realization' => 75,
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'Dalam Progress',
            'description' => 'Target inspeksi berkala di seluruh area kerja Unit Dieng.',
        ];

        $response = $this->postJson(route('hsse-plan.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'KPI berhasil ditambahkan.',
            'kpi' => [
                'title' => 'Inspeksi K3 Triwulan IV',
                'category' => 'Leading',
                'status' => 'Berlangsung',
            ],
        ]);

        $this->assertDatabaseHas('hsse_plans', [
            'title' => 'Inspeksi K3 Triwulan IV',
            'status' => 'Berlangsung',
        ]);
    }

    public function test_update_hsse_plan_successfully(): void
    {
        $plan = HssePlan::create([
            'title' => 'Pengujian Gas Emisi',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 50,
            'realization' => 20,
            'unit_measure' => 'titik',
            'status' => 'Berlangsung',
        ]);

        $response = $this->putJson(route('hsse-plan.update', $plan->id), [
            'title' => 'Pengujian Gas Emisi Terpadu',
            'realization' => 50,
            'status' => 'Tercapai',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kpi' => [
                'title' => 'Pengujian Gas Emisi Terpadu',
                'status' => 'Tercapai',
            ],
        ]);

        $this->assertDatabaseHas('hsse_plans', [
            'id' => $plan->id,
            'title' => 'Pengujian Gas Emisi Terpadu',
            'status' => 'Tercapai',
        ]);
    }

    public function test_hsse_plan_show_json_detail_with_real_database_data(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Pemantauan Lingkungan',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 100,
            'realization' => 90,
            'unit_measure' => '%',
            'status' => 'Berlangsung',
            'description' => 'Monitoring berkala emisi dan gas H2S.',
        ]);

        $note = $plan->notes()->create([
            'note' => 'Catatan verifikasi sensor H2S di wellpad 28.',
            'author' => 'Budi Santoso',
        ]);

        $response = $this->getJson(route('hsse-plan.show', $plan->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'kpi' => [
                'id' => $plan->id,
                'title' => 'Test KPI Pemantauan Lingkungan',
                'category' => 'Leading',
                'status' => 'Berlangsung',
            ],
        ]);
        $response->assertJsonFragment([
            'note' => 'Catatan verifikasi sensor H2S di wellpad 28.',
            'author' => 'Budi Santoso',
        ]);
    }

    public function test_hsse_plan_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson(route('hsse-plan.show', 99999999));
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data KPI tidak ditemukan.',
        ]);
    }

    public function test_hsse_plan_show_html_renders_detail_page(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Standalone View',
            'category' => 'Lagging',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 0,
            'realization' => 0,
            'unit_measure' => 'index',
            'status' => 'Tercapai',
        ]);

        $response = $this->get(route('hsse-plan.show', $plan->id));
        $response->assertStatus(200);
        $response->assertSee('Detail KPI Kontraktor');
        $response->assertSee('Test KPI Standalone View');
    }

    public function test_create_note_for_kpi(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Note Creation',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 10,
            'realization' => 5,
            'status' => 'Berlangsung',
        ]);

        $response = $this->postJson(route('hsse-plan.notes.store', $plan->id), [
            'note' => 'Ini adalah catatan tindak lanjut evaluasi keselamatan kerja.',
            'author' => 'Siti Rahma',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Catatan berhasil ditambahkan.',
            'note' => [
                'hsse_plan_id' => $plan->id,
                'note' => 'Ini adalah catatan tindak lanjut evaluasi keselamatan kerja.',
                'author' => 'Siti Rahma',
            ],
        ]);

        $this->assertDatabaseHas('hsse_plan_notes', [
            'hsse_plan_id' => $plan->id,
            'note' => 'Ini adalah catatan tindak lanjut evaluasi keselamatan kerja.',
            'author' => 'Siti Rahma',
        ]);
    }

    public function test_create_note_validates_required_fields(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Note Validation',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
        ]);

        $response = $this->postJson(route('hsse-plan.notes.store', $plan->id), [
            'note' => '', // empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['note']);
    }

    public function test_update_note_successfully(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Note Update',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
        ]);

        $note = $plan->notes()->create([
            'note' => 'Catatan lama sebelum revisi.',
            'author' => 'Admin HSSE',
        ]);

        $response = $this->putJson(route('hsse-plan.notes.update', [$plan->id, $note->id]), [
            'note' => 'Catatan telah diperbarui dengan data evaluasi terbaru.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Catatan berhasil diperbarui.',
            'note' => [
                'id' => $note->id,
                'note' => 'Catatan telah diperbarui dengan data evaluasi terbaru.',
            ],
        ]);

        $this->assertDatabaseHas('hsse_plan_notes', [
            'id' => $note->id,
            'note' => 'Catatan telah diperbarui dengan data evaluasi terbaru.',
        ]);
    }

    public function test_delete_note_successfully(): void
    {
        $plan = HssePlan::firstOrCreate([
            'title' => 'Test KPI Note Deletion',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
        ]);

        $note = $plan->notes()->create([
            'note' => 'Catatan yang akan dihapus segera.',
            'author' => 'Admin HSSE',
        ]);

        $response = $this->deleteJson(route('hsse-plan.notes.destroy', [$plan->id, $note->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Catatan berhasil dihapus.',
        ]);

        $this->assertDatabaseMissing('hsse_plan_notes', [
            'id' => $note->id,
        ]);
    }

    public function test_delete_note_on_different_kpi_returns_404(): void
    {
        $plan1 = HssePlan::firstOrCreate([
            'title' => 'KPI 1',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
        ]);

        $plan2 = HssePlan::firstOrCreate([
            'title' => 'KPI 2',
            'category' => 'Lagging',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
        ]);

        $note = $plan1->notes()->create([
            'note' => 'Catatan milik KPI 1',
            'author' => 'Admin',
        ]);

        // Attempt to delete note of plan1 through plan2
        $response = $this->deleteJson(route('hsse-plan.notes.destroy', [$plan2->id, $note->id]));

        $response->assertStatus(404);
        $this->assertDatabaseHas('hsse_plan_notes', [
            'id' => $note->id,
        ]);
    }

    public function test_export_hsse_plan_csv(): void
    {
        $response = $this->get(route('hsse-plan.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_hsse_plan_achieved_page_renders_only_tercapai_records(): void
    {
        $achievedKpi = HssePlan::create([
            'title' => 'KPI Khusus Sudah Tercapai 100%',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 50,
            'realization' => 50,
            'unit_measure' => 'Kegiatan',
            'status' => 'Tercapai',
            'description' => 'Target tercapai sepenuhnya.',
        ]);

        $ongoingKpi = HssePlan::create([
            'title' => 'KPI Sedang Berjalan Belum Selesai',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 1',
            'year' => 2025,
            'target' => 50,
            'realization' => 25,
            'unit_measure' => 'Kegiatan',
            'status' => 'Berlangsung',
            'description' => 'Target belum tercapai.',
        ]);

        $response = $this->get(route('hsse-plan.achieved'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat KPI Tercapai');
        $response->assertSee('KPI Khusus Sudah Tercapai 100%');
        $response->assertDontSee('KPI Sedang Berjalan Belum Selesai');
    }

    public function test_hsse_plan_achieved_excludes_non_tercapai_plans(): void
    {
        $unachievedKpi = HssePlan::create([
            'title' => 'KPI Status Tidak Tercapai Eksklusif',
            'category' => 'Lagging',
            'unit' => 'Dieng Unit 2',
            'year' => 2025,
            'target' => 10,
            'realization' => 2,
            'unit_measure' => 'Kasus',
            'status' => 'Tidak Tercapai',
        ]);

        $notStartedKpi = HssePlan::create([
            'title' => 'KPI Status Belum Mulai Eksklusif',
            'category' => 'Leading',
            'unit' => 'Dieng Unit 2',
            'year' => 2025,
            'target' => 10,
            'realization' => 0,
            'unit_measure' => 'Inspeksi',
            'status' => 'Belum Mulai',
        ]);

        $response = $this->get(route('hsse-plan.achieved'));

        $response->assertStatus(200);
        $response->assertDontSee('KPI Status Tidak Tercapai Eksklusif');
        $response->assertDontSee('KPI Status Belum Mulai Eksklusif');
    }

    public function test_hsse_plan_achieved_json_api_returns_only_tercapai_with_stats(): void
    {
        $response = $this->getJson(route('hsse-plan.achieved'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'kpis',
            'stats' => ['total', 'achieved', 'leading', 'lagging', 'average_percentage'],
            'typeChart' => ['labels', 'data'],
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);

        // Assert all returned KPIs have status 'Tercapai'
        foreach ($data['kpis'] as $kpi) {
            $this->assertEquals('Tercapai', $kpi['status']);
        }
    }

    public function test_hsse_plan_achieved_alias_route_works(): void
    {
        $response = $this->get(route('hsse-plan.riwayat-tercapai'));
        $response->assertStatus(200);
        $response->assertSee('Riwayat KPI Tercapai');
    }
}


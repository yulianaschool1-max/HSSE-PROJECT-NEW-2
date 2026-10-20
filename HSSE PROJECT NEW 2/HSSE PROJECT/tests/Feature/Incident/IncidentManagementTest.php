<?php

namespace Tests\Feature\Incident;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IncidentManagementTest extends TestCase
{
    use DatabaseTransactions;
    protected function getAdmin(): User
    {
        return User::first() ?: User::factory()->create([
            'role' => 'Admin',
            'status' => 'Aktif',
        ]);
    }

    public function test_incident_index_renders_successfully(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/incident');
        $response->assertStatus(200);
        $response->assertSee('Pencatatan Insiden & Kecelakaan Kerja Lapangan', false);
        $response->assertSee('1. Kategori Insiden', false);
        $response->assertSee('2. Kategori Umum', false);
        $response->assertSee('3. Kategori EBTKE', false);
        $response->assertSee('chartArea', false);
        $response->assertSee('chartUmum', false);
        $response->assertSee('chartEbtke', false);
    }

    public function test_incident_red_identity_strictly_maintained(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/incident');
        $response->assertStatus(200);
        // Red color identity is mandatory
        $response->assertSee('--red-incident: #dc2626;', false);
        $response->assertSee('btn-danger', false);
        $response->assertSee('text-danger', false);
    }

    public function test_incident_ajax_json_endpoint(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->getJson('/incident');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
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
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_incident_displays_three_interactive_pie_charts(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/incident');
        $response->assertStatus(200);

        // 3 Pie chart utama harus tampil
        $response->assertSee('1. Kategori Insiden', false);
        $response->assertSee('2. Kategori Umum (Tags)', false);
        $response->assertSee('3. Kategori EBTKE (Tags)', false);

        // Kartu statistik ringkasan dan grafik LWD sudah dihapus
        $response->assertDontSee('Total Hari Hilang', false);
        $response->assertDontSee('4. Visualisasi & Distribusi Total Hari Kerja Hilang', false);
        $response->assertDontSee('Klik segmen untuk rincian insiden', false);
    }

    public function test_can_create_incident_with_binary_classification_and_tags(): void
    {
        $admin = $this->getAdmin();
        $nomor = Incident::generateNextNumber();

        $response = $this->actingAs($admin)->post(route('incident.store'), [
            'nomor_insiden' => $nomor,
            'tanggal_waktu' => '2026-10-13 10:00:00',
            'klasifikasi_area' => 'Kecelakaan Panas Bumi (Wilayah Kerja Kuasa Pertambangan Panas Bumi)',
            'kategori_umum' => ['Property Damage / Kerusakan Aset', 'Near Miss / Hampir Celaka'],
            'kategori_ebtke' => ['Ringan (Perawatan Medis/P3K)'],
            'lokasi_kejadian' => 'Wellpad 28 Dieng',
            'komponen_terkait' => 'Pipa Geothermal Unit 1',
            'deskripsi_kejadian' => 'Terjadi rembesan uap saat inspeksi rutin, tidak ada korban cedera.',
            'tindakan_perbaikan' => 'Isolasi area dan penggantian gasket pipa.',
            'penanggung_jawab' => 'Budi Santoso (Safety Officer)',
            'status_laporan' => 'Preliminary',
            'jumlah_hari_hilang' => 0,
        ]);

        $response->assertRedirect(route('incident.index'));
        $this->assertDatabaseHas('incidents', [
            'nomor_insiden' => $nomor,
            'lokasi_kejadian' => 'Wellpad 28 Dieng',
        ]);
    }

    public function test_can_update_incident_and_log_diff(): void
    {
        $admin = $this->getAdmin();
        $incident = Incident::create([
            'nomor_insiden' => 'INC-TEST-UPD-' . time(),
            'tanggal_waktu' => '2026-10-13 09:00:00',
            'klasifikasi_area' => 'Kecelakaan Non-Panas Bumi (Di Luar Wilayah Kuasa Pertambangan)',
            'lokasi_kejadian' => 'Kantor Administrasi Dieng',
            'komponen_terkait' => 'Gedung Utama',
            'deskripsi_kejadian' => 'Lantai licin di koridor.',
            'status_laporan' => 'Preliminary',
            'jumlah_hari_hilang' => 0,
        ]);

        $response = $this->actingAs($admin)->put(route('incident.update', $incident->id), [
            'tanggal_waktu' => '2026-10-13 09:00:00',
            'klasifikasi_area' => 'Kecelakaan Non-Panas Bumi (Di Luar Wilayah Kuasa Pertambangan)',
            'lokasi_kejadian' => 'Kantor Administrasi Dieng Lt. 2',
            'komponen_terkait' => 'Gedung Utama',
            'deskripsi_kejadian' => 'Lantai licin di koridor telah dipasang rambu peringatan.',
            'tindakan_perbaikan' => 'Pembersihan dan pemasangan anti slip mat.',
            'status_laporan' => 'Final',
            'jumlah_hari_hilang' => 0,
        ]);

        $response->assertRedirect(route('incident.index'));
        $incident->refresh();
        $this->assertEquals('Kantor Administrasi Dieng Lt. 2', $incident->lokasi_kejadian);
        $this->assertEquals('Final', $incident->status_laporan);
    }

    public function test_can_delete_incident(): void
    {
        $admin = $this->getAdmin();
        $incident = Incident::create([
            'nomor_insiden' => 'INC-TEST-DEL-' . time(),
            'tanggal_waktu' => '2026-10-13 08:00:00',
            'klasifikasi_area' => 'Kecelakaan Panas Bumi (Wilayah Kerja Kuasa Pertambangan Panas Bumi)',
            'lokasi_kejadian' => 'Area Turbin',
            'komponen_terkait' => 'Turbin Unit 1',
            'deskripsi_kejadian' => 'Insiden uji coba untuk dihapus.',
            'status_laporan' => 'Preliminary',
            'jumlah_hari_hilang' => 0,
        ]);

        $response = $this->actingAs($admin)->delete(route('incident.destroy', $incident->id));
        $response->assertRedirect(route('incident.index'));
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_incident_charts_reflect_active_filters(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->getJson('/incident?' . http_build_query([
            'klasifikasi_area' => 'Kecelakaan Panas Bumi (Wilayah Kerja Kuasa Pertambangan Panas Bumi)',
        ]));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('chartArea', $data);
        $this->assertArrayHasKey('colors', $data['chartArea']);
        $this->assertCount(2, $data['chartArea']['colors']);
        $this->assertArrayHasKey('chartUmum', $data);
        $this->assertArrayHasKey('colors', $data['chartUmum']);
        $this->assertArrayHasKey('chartEbtke', $data);
        $this->assertArrayHasKey('colors', $data['chartEbtke']);
    }

    public function test_incident_charts_remain_full_accumulation_across_pagination_pages(): void
    {
        $admin = $this->getAdmin();

        // Buat 15 record insiden khusus uji paginasi
        for ($i = 1; $i <= 15; $i++) {
            Incident::create([
                'incident_code' => 'INC-PAGINASI-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_insiden' => 'INC-PAGINASI-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'title' => 'Insiden Paginasi ' . $i,
                'incident_type' => 'Property Damage',
                'unit' => 'Dieng',
                'incident_date' => now()->subDays($i),
                'tanggal_waktu' => now()->subDays($i),
                'location' => 'Lokasi ' . $i,
                'lokasi_kejadian' => 'Lokasi ' . $i,
                'severity' => 'Rendah',
                'description' => 'Deskripsi Paginasi ' . $i,
                'deskripsi_kejadian' => 'Deskripsi Paginasi ' . $i,
                'reported_by' => 'Tester',
                'penanggung_jawab' => 'Tester',
                'status' => 'Dilaporkan',
                'status_laporan' => 'Preliminary',
                'klasifikasi_area' => 'Kecelakaan Panas Bumi',
                'kategori_umum' => ['Property Damage'],
                'kategori_ebtke' => ['Kecelakaan Ringan'],
            ]);
        }

        // 1. Cek Halaman 1: Tabel harus berisi 10 record, tapi chartArea['total'] harus 15
        $page1Response = $this->actingAs($admin)->getJson('/incident?search=INC-PAGINASI&page=1');
        $page1Response->assertStatus(200);
        $page1Data = $page1Response->json();
        $this->assertCount(10, $page1Data['incidents']['data']);
        $this->assertEquals(15, $page1Data['chartArea']['total']);
        $this->assertEquals(15, $page1Data['chartArea']['data'][0]); // 15 Panas Bumi

        // 2. Cek Halaman 2: Tabel berisi 5 record sisa, tapi chartArea['total'] TETAP 15 (TIDAK terpotong ke 5)
        $page2Response = $this->actingAs($admin)->getJson('/incident?search=INC-PAGINASI&page=2');
        $page2Response->assertStatus(200);
        $page2Data = $page2Response->json();
        $this->assertCount(5, $page2Data['incidents']['data']);
        $this->assertEquals(15, $page2Data['chartArea']['total'], 'Chart total tidak boleh terpotong saat pagination berpindah ke halaman 2');
        $this->assertEquals(15, $page2Data['chartArea']['data'][0]);
    }

    public function test_incident_export_csv_with_filters(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('incident.export', [
            'search' => 'INC',
            'klasifikasi_area' => 'Kecelakaan Panas Bumi',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Laporan_Incident_HSSE', (string) $response->headers->get('content-disposition'));
    }
}


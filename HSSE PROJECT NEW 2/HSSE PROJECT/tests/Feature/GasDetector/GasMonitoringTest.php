<?php

namespace Tests\Feature\GasDetector;

use App\Models\ActivityLog;
use App\Models\GasMonitoring;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GasMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin.gas@geodipa.co.id'],
            [
                'name' => 'Admin Gas Dieng',
                'password' => bcrypt('password123'),
                'role' => 'Admin',
                'status' => 'Aktif',
            ]
        );
    }

    public function test_gas_monitoring_index_renders_with_nab_cards_and_measurements(): void
    {
        $response = $this->actingAs($this->user)->get(route('gas-detector.index'));

        $response->assertStatus(200);
        $response->assertSee('Gas Monitoring');
        $response->assertSee('NAB');
        $response->assertSee('Nilai CO Tertinggi');
        $response->assertSee('Nilai H₂S Tertinggi');
        $response->assertSee('Pressure Tertinggi');
        $response->assertSee('Total Data');
        $response->assertSee('Well Pad 7');
        $response->assertSee('Well Pad 28');
        $response->assertSee('Rekapitulasi per Well Pad');
    }

    public function test_can_create_update_and_delete_measurement(): void
    {
        // 1. Create measurement (CO 15 <= 25 => Sesuai, H2S 12 > 10 => Tidak Sesuai, Pressure 18.5 <= 20 => Sesuai)
        $payload = [
            'measurement_date' => Carbon::today()->toDateString(),
            'well_pad' => 'Well Pad 7',
            'co_level' => 15.0,
            'h2s_level' => 12.0,
            'pressure' => 18.5,
            'notes' => 'Uji coba operasional well pad 7',
        ];

        $createResponse = $this->actingAs($this->user)
            ->post(route('gas-monitoring.store'), $payload);

        $createResponse->assertRedirect();

        $measurement = GasMonitoring::where('well_pad', 'Well Pad 7')
            ->whereDate('measurement_date', Carbon::today()->toDateString())
            ->latest('id')
            ->first();

        $this->assertNotNull($measurement);
        $this->assertEquals('Sesuai', $measurement->co_status);
        $this->assertEquals('Tidak Sesuai', $measurement->h2s_status);
        $this->assertEquals('Sesuai', $measurement->pressure_status);

        // 2. Update measurement
        $updatePayload = [
            'measurement_date' => Carbon::today()->toDateString(),
            'well_pad' => 'Well Pad 7',
            'co_level' => 8.0,
            'h2s_level' => 5.0,
            'pressure' => 16.0,
            'notes' => 'Telah dinormalisasi dan ventilasi optimal',
        ];

        $updateResponse = $this->actingAs($this->user)
            ->put(route('gas-monitoring.update', $measurement->id), $updatePayload);

        $updateResponse->assertRedirect();

        $measurement->refresh();
        $this->assertEquals(8.0, $measurement->co_level);
        $this->assertEquals('Sesuai', $measurement->h2s_status); // Sekarang <= 10 ppm

        // 3. Delete measurement
        $deleteResponse = $this->actingAs($this->user)
            ->delete(route('gas-monitoring.destroy', $measurement->id));

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('gas_monitorings', ['id' => $measurement->id]);
    }

    public function test_gas_monitoring_export_csv(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('gas-monitoring.export', [
                'month' => date('n'),
                'year' => date('Y'),
                'well_pad' => 'all',
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}

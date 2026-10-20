<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_events_page_can_be_rendered_successfully(): void
    {
        $response = $this->get('/events?per_page=100');

        $response->assertStatus(200);
        $response->assertSee('Events');
        $response->assertSee('Daftar Kegiatan');
        $response->assertSee('Safety Talk Pekerjaan Panas');
        $response->assertSee('Pelatihan APAR');
    }

    public function test_events_filtering_by_category(): void
    {
        $response = $this->get('/events?category=Pelatihan&per_page=100');

        $response->assertStatus(200);
        $response->assertSee('Pelatihan APAR');
        $response->assertDontSee('Safety Talk Pekerjaan Panas');
    }

    public function test_events_ajax_json_response(): void
    {
        $response = $this->getJson('/events');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => ['total', 'selesai', 'berlangsung', 'dibatalkan'],
            'monthly' => ['labels', 'datasets'],
            'categories',
            'units',
            'events',
        ]);
    }

    public function test_events_show_details(): void
    {
        $event = Event::first();
        $this->assertNotNull($event);

        $response = $this->getJson("/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
            ],
        ]);
    }

    public function test_events_store_creates_event_and_logs(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('dokumentasi-test.jpg');

        $data = [
            'title' => 'Simulasi Kebakaran Lapangan Baru',
            'category' => 'Simulasi/Drill',
            'unit' => 'PMU',
            'event_date' => '2025-10-15',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location' => 'Area Turbin',
            'participants' => 35,
            'pic' => 'Budi Santoso',
            'status' => 'Selesai',
            'description' => 'Simulasi tanggap darurat dan uji coba fire alarm.',
            'documentation_files' => [$file],
        ];

        $response = $this->post('/events', $data);

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Simulasi Kebakaran Lapangan Baru',
            'category' => 'Simulasi/Drill',
            'unit' => 'PMU',
            'participants' => 35,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'events',
            'action' => 'TAMBAH',
        ]);
    }

    public function test_events_update(): void
    {
        $event = Event::where('title', 'Simulasi Kebakaran Lapangan Baru')->first() ?? Event::first();

        $response = $this->put("/events/{$event->id}", [
            'title' => 'Judul Kegiatan Terupdate',
            'category' => $event->category,
            'unit' => $event->unit,
            'event_date' => $event->event_date->format('Y-m-d'),
            'location' => 'Area Turbin Dieng',
            'participants' => 45,
            'status' => 'Berlangsung',
            'description' => 'Deskripsi update.',
        ]);

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Judul Kegiatan Terupdate',
            'participants' => 45,
            'status' => 'Berlangsung',
        ]);
    }

    public function test_events_destroy(): void
    {
        $event = Event::create([
            'title' => 'Event Hapus Temporary',
            'category' => 'Lainnya',
            'unit' => 'PMU',
            'event_date' => '2025-12-31',
            'location' => 'Temporary Area',
            'participants' => 5,
            'status' => 'Direncanakan',
            'pic' => 'Tester',
        ]);

        $response = $this->delete("/events/{$event->id}");

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }

    public function test_events_export_excel_csv(): void
    {
        $response = $this->get('/events/export');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }
}


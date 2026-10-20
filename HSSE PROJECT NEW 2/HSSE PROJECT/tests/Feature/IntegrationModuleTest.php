<?php

namespace Tests\Feature;

use Tests\TestCase;

class IntegrationModuleTest extends TestCase
{
    public function test_daily_work_page_renders_successfully(): void
    {
        $response = $this->get('/daily-work');

        $response->assertStatus(200);
        $response->assertSee('Daily Work');
        $response->assertSee('Daftar Pekerjaan Harian');
        $response->assertSee('HSSE PROJECT');
        $response->assertSee('PMU');
        $response->assertSee('Utility');
    }

    public function test_hsse_plan_page_renders_successfully(): void
    {
        $response = $this->get('/hsse-plan');

        $response->assertStatus(200);
        $response->assertSee('HSSE Plan');
        $response->assertSee('KPI Kontraktor');
        $response->assertSee('Daftar KPI Kontraktor');
        $response->assertSee('HSSE PROJECT');
        $response->assertSee('Leading');
        $response->assertSee('Lagging');
    }

    public function test_events_page_renders_successfully(): void
    {
        $response = $this->get('/events');

        $response->assertStatus(200);
        $response->assertSee('Events');
        $response->assertSee('Daftar Kegiatan');
        $response->assertSee('HSSE PROJECT');
    }

    public function test_shared_sidebar_footer_and_gradient_across_all_pages(): void
    {
        $user = \App\Models\User::first() ?: \App\Models\User::factory()->create([
            'role' => 'Admin',
            'status' => 'Aktif',
        ]);

        $pages = [
            '/dashboard',
            '/daily-work',
            '/hsse-plan',
            '/events',
            '/inventory',
            '/man-hours',
            '/workforce',
            '/incident',
            '/issue-follow-up',
            '/gas-detector',
            '/users',
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($user)->get($page);
            $response->assertStatus(200);
            $response->assertSee('class="sidebar" id="sidebar"', false);
            $response->assertSee('class="sidebar-footer"', false);
            $response->assertSee('class="sidebar-footer-text"', false);
            $response->assertSee('Safety Today');
            $response->assertSee('Sustainability Tomorrow');
            $response->assertSee('/css/hsse-theme.css', false);
        }

        // Pastikan CSS tema memiliki aturan gradient overlay ::before dan border-radius halus
        $cssContent = file_get_contents(public_path('css/hsse-theme.css'));
        $this->assertStringContainsString('.sidebar-footer::before', $cssContent);
        $this->assertStringContainsString('/images/backgrounds/login-dieng.webp', $cssContent);
        $this->assertStringContainsString('linear-gradient', $cssContent);
        $this->assertStringContainsString('var(--navy-dark)', $cssContent);
        $this->assertStringContainsString('flex-shrink: 0', $cssContent);
    }
}



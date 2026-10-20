<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve existing admin in db_hsse or ensure one exists
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@geodipa.co.id'],
            [
                'name' => 'Administrator Dieng',
                'password' => 'Admin123!',
                'role' => 'Admin',
                'status' => 'Aktif',
            ]
        );
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('HSSE PROJECT');
    }

    public function test_authenticated_admin_visiting_login_page_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->post(route('login.process'), [
            'email' => 'admin@geodipa.co.id',
            'password' => 'Admin123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        // Follow redirect to dashboard to verify access
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard');
    }

    public function test_admin_login_with_whitespace_in_email_is_trimmed_and_succeeds(): void
    {
        $response = $this->post(route('login.process'), [
            'email' => '  admin@geodipa.co.id  ',
            'password' => 'Admin123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_login_fails_with_incorrect_password(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'email' => 'admin@geodipa.co.id',
            'password' => 'SalahPassword999!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        // Deactivate admin account
        $this->admin->status = 'Nonaktif';
        $this->admin->save();

        $response = $this->from(route('login'))->post(route('login.process'), [
            'email' => 'admin@geodipa.co.id',
            'password' => 'Admin123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $response = $this->actingAs($this->admin)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_access_dashboard_and_user_management(): void
    {
        $dashboardResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);

        $usersResponse = $this->actingAs($this->admin)->get(route('users.index'));
        $usersResponse->assertStatus(200);
        $usersResponse->assertSee('Manajemen Pengguna');
    }
}


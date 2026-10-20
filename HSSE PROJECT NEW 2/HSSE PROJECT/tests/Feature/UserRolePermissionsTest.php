<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserRolePermissionsTest extends TestCase
{
    protected ?User $userRole = null;
    protected ?User $adminInventoryRole = null;
    protected ?User $adminManagementRole = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = User::firstOrCreate(
            ['email' => 'user_test@geodipa.co.id'],
            [
                'name' => 'User Tester',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_USER,
                'status' => 'aktif',
            ]
        );
        $this->userRole->update(['role' => User::ROLE_USER, 'status' => 'aktif']);

        $this->adminInventoryRole = User::firstOrCreate(
            ['email' => 'inventory_test@geodipa.co.id'],
            [
                'name' => 'Inventory Tester',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_ADMIN_INVENTORY,
                'status' => 'aktif',
            ]
        );
        $this->adminInventoryRole->update(['role' => User::ROLE_ADMIN_INVENTORY, 'status' => 'aktif']);

        $this->adminManagementRole = User::firstOrCreate(
            ['email' => 'admin_test@geodipa.co.id'],
            [
                'name' => 'Admin Tester',
                'password' => bcrypt('password123'),
                'role' => User::ROLE_ADMIN_MANAGEMENT,
                'status' => 'aktif',
            ]
        );
        $this->adminManagementRole->update(['role' => User::ROLE_ADMIN_MANAGEMENT, 'status' => 'aktif']);
    }

    public function test_user_can_access_all_menus_and_views(): void
    {
        $this->actingAs($this->userRole);

        $routes = [
            route('dashboard'),
            route('daily-work.index'),
            route('hsse-plan.index'),
            route('events.index'),
            route('inventory.index'),
            route('inventory.transactions'),
            route('workforce.index'),
            route('man-hours.index'),
            route('incident.index'),
            route('issue-follow-up.index'),
            route('gas-detector.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_user_can_access_pdf_exports(): void
    {
        $this->actingAs($this->userRole);

        $exportRoutes = [
            route('daily-work.export'),
            route('hsse-plan.export'),
            route('events.export'),
            route('inventory.export'),
            route('workforce.export'),
            route('man-hours.export'),
            route('incident.export'),
            route('issue-follow-up.export'),
            route('gas-detector.export'),
            route('gas-monitoring.export'),
        ];

        foreach ($exportRoutes as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(403, $response->getStatusCode(), "Export route {$url} should not return 403 for user role.");
            $this->assertEquals(200, $response->getStatusCode(), "Export route {$url} should return 200 for user role.");
        }
    }

    public function test_user_is_forbidden_from_all_mutations(): void
    {
        $this->actingAs($this->userRole);

        // Daily Work
        $res = $this->post(route('daily-work.store'), ['work_title' => 'Test']);
        $res->assertStatus(403);

        // HSSE Plan
        $res = $this->post(route('hsse-plan.store'), ['indicator' => 'Test']);
        $res->assertStatus(403);

        // Events
        $res = $this->post(route('events.store'), ['title' => 'Test']);
        $res->assertStatus(403);

        // Inventory writes
        $res = $this->post(route('inventory.store'), ['item_name' => 'Test']);
        $res->assertStatus(403);

        $res = $this->post(route('inventory.transactions.store'), ['item_id' => 1]);
        $res->assertStatus(403);

        $res = $this->post(route('inventory.borrowings.store'), ['item_id' => 1]);
        $res->assertStatus(403);

        // Workforce
        $res = $this->post(route('workforce.store'), ['category' => 'Test']);
        $res->assertStatus(403);

        // Incident
        $res = $this->post(route('incident.store'), ['title' => 'Test']);
        $res->assertStatus(403);

        // Issue Follow Up
        $res = $this->post(route('issue-follow-up.store'), ['title' => 'Test']);
        $res->assertStatus(403);

        // User Management
        $res = $this->get(route('users.index'));
        $res->assertStatus(403);
    }

    public function test_admin_inventory_access_rules(): void
    {
        $this->actingAs($this->adminInventoryRole);

        // Can access views
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('inventory.index'))->assertStatus(200);
        $this->get(route('daily-work.index'))->assertStatus(200);

        // Forbidden from non-inventory writes
        $this->post(route('daily-work.store'), ['work_title' => 'Test'])->assertStatus(403);
        $this->post(route('events.store'), ['title' => 'Test'])->assertStatus(403);
        $this->get(route('users.index'))->assertStatus(403);

        // Forbidden from PDF exports according to RBAC matrix
        $this->get(route('daily-work.export'))->assertStatus(403);
        $this->get(route('inventory.export'))->assertStatus(403);
    }

    public function test_admin_management_has_full_access(): void
    {
        $this->actingAs($this->adminManagementRole);

        // Can access all views
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('daily-work.index'))->assertStatus(200);
        $this->get(route('users.index'))->assertStatus(200);

        // Can access PDF exports
        $this->get(route('daily-work.export'))->assertStatus(200);
        $this->get(route('inventory.export'))->assertStatus(200);
    }

    protected function tearDown(): void
    {
        User::whereIn('email', [
            'user_test@geodipa.co.id',
            'inventory_test@geodipa.co.id',
            'admin_test@geodipa.co.id',
        ])->delete();

        parent::tearDown();
    }
}


<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an active Admin
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Penguji',
            'email' => 'admin.test.' . uniqid() . '@geodipa.co.id',
            'password' => Hash::make('AdminSecret123!'),
            'role' => User::ROLE_ADMIN_MANAGEMENT,
            'status' => 'Aktif',
        ]);

        // Create a regular User
        $this->regularUser = User::factory()->create([
            'name' => 'Staff Lapangan',
            'email' => 'staff.test.' . uniqid() . '@geodipa.co.id',
            'password' => Hash::make('UserSecret123!'),
            'role' => User::ROLE_USER,
            'status' => 'Aktif',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_users(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_json_request_receives_401(): void
    {
        $response = $this->getJson(route('users.index'));
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_regular_user_cannot_access_users_index(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_fetch_users_via_ajax_json(): void
    {
        $response = $this->actingAs($this->regularUser)->getJson(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_access_create_form(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('users.create'));
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_store_user(): void
    {
        $payload = [
            'name' => 'Hacker User',
            'email' => 'hack.' . uniqid() . '@geodipa.co.id',
            'role' => 'User',
            'status' => 'Aktif',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->actingAs($this->regularUser)->postJson(route('users.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_access_edit_form(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('users.edit', $this->adminUser->id));
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_update_user(): void
    {
        $payload = [
            'name' => 'Attempted Edit',
            'email' => $this->adminUser->email,
            'role' => 'Admin',
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($this->regularUser)->putJson(route('users.update', $this->adminUser->id), $payload);
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_delete_user(): void
    {
        $target = User::factory()->create([
            'role' => 'User',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->regularUser)->deleteJson(route('users.destroy', $target->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_inactive_user_is_blocked_and_logged_out(): void
    {
        $inactiveAdmin = User::factory()->create([
            'name' => 'Admin Nonaktif',
            'email' => 'inactive.admin.' . uniqid() . '@geodipa.co.id',
            'password' => Hash::make('Secret123!'),
            'role' => 'Admin',
            'status' => 'Nonaktif',
        ]);

        $response = $this->actingAs($inactiveAdmin)->get(route('users.index'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_access_users_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Daftar Akun Pengguna');
        $response->assertSee('Tambah Pengguna');
        $response->assertSee('#modalTambahPengguna');
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->regularUser->name);
    }

    public function test_admin_can_get_users_via_ajax_json(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('users.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'users' => [
                'data',
                'current_page',
                'total',
            ],
            'stats' => ['total', 'admin', 'user', 'active', 'inactive'],
        ]);
    }

    public function test_admin_can_search_users(): void
    {
        $uniqueName = 'Zulfa Geothermal ' . uniqid();
        $targetUser = User::factory()->create([
            'name' => $uniqueName,
            'email' => 'zulfa.' . uniqid() . '@geodipa.co.id',
            'role' => 'User',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('users.index', ['search' => $uniqueName]));

        $response->assertStatus(200);
        $response->assertSee($uniqueName);
    }

    public function test_admin_can_filter_users_by_role_and_status(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('users.index', [
            'role' => 'Admin',
            'status' => 'Aktif',
        ]));

        $response->assertStatus(200);
        $data = $response->json('users.data');
        foreach ($data as $u) {
            $this->assertTrue(in_array($u['role'], [User::ROLE_ADMIN_MANAGEMENT, 'Admin', 'admin_management']));
            $this->assertEquals('Aktif', $u['status']);
        }
    }

    public function test_admin_can_create_new_user_with_hashed_password(): void
    {
        $newEmail = 'new.operator.' . uniqid() . '@geodipa.co.id';
        $payload = [
            'name' => 'Operator Turbin Dieng',
            'email' => $newEmail,
            'role' => 'User',
            'status' => 'Aktif',
            'password' => 'TurbinPassword123!',
            'password_confirmation' => 'TurbinPassword123!',
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('users.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Pengguna baru berhasil ditambahkan.',
            'user' => [
                'name' => 'Operator Turbin Dieng',
                'email' => $newEmail,
                'role' => User::ROLE_USER,
                'status' => 'Aktif',
            ],
        ]);

        $created = User::where('email', $newEmail)->first();
        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('TurbinPassword123!', $created->password));
    }

    public function test_create_user_validates_duplicate_email(): void
    {
        $payload = [
            'name' => 'Duplikat Admin',
            'email' => $this->adminUser->email, // duplicate
            'role' => 'Admin',
            'status' => 'Aktif',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('users.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_validates_password_confirmation_and_min_length(): void
    {
        $payload = [
            'name' => 'Invalid Password User',
            'email' => 'invalid.pass.' . uniqid() . '@geodipa.co.id',
            'role' => 'User',
            'status' => 'Aktif',
            'password' => 'short', // less than 8
            'password_confirmation' => 'different',
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('users.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_admin_can_view_user_detail(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson(route('users.show', $this->regularUser->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $this->regularUser->id,
                'name' => $this->regularUser->name,
                'email' => $this->regularUser->email,
                'role' => User::ROLE_USER,
            ],
        ]);
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $originalPasswordHash = $this->regularUser->password;
        $updatedName = 'Staff Senior Geothermal';

        $payload = [
            'name' => $updatedName,
            'email' => $this->regularUser->email,
            'role' => 'User',
            'status' => 'Aktif',
            'password' => '', // blank
            'password_confirmation' => '',
        ];

        $response = $this->actingAs($this->adminUser)->putJson(route('users.update', $this->regularUser->id), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
            'user' => [
                'id' => $this->regularUser->id,
                'name' => $updatedName,
            ],
        ]);

        $this->regularUser->refresh();
        $this->assertEquals($updatedName, $this->regularUser->name);
        $this->assertEquals($originalPasswordHash, $this->regularUser->password);
    }

    public function test_admin_can_update_user_password(): void
    {
        $newPlainPassword = 'BrandNewPassword999!';

        $payload = [
            'name' => $this->regularUser->name,
            'email' => $this->regularUser->email,
            'role' => 'User',
            'status' => 'Aktif',
            'password' => $newPlainPassword,
            'password_confirmation' => $newPlainPassword,
        ];

        $response = $this->actingAs($this->adminUser)->putJson(route('users.update', $this->regularUser->id), $payload);

        $response->assertStatus(200);

        $this->regularUser->refresh();
        $this->assertTrue(Hash::check($newPlainPassword, $this->regularUser->password));
    }

    public function test_prevent_demoting_or_deactivating_the_last_active_admin(): void
    {
        // Demote all other admins to user so this adminUser is the ONLY active admin
        User::whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin', 'admin_management'])->where('id', '!=', $this->adminUser->id)->update(['role' => User::ROLE_USER]);

        $payload = [
            'name' => $this->adminUser->name,
            'email' => $this->adminUser->email,
            'role' => User::ROLE_USER, // Demotion attempt
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($this->adminUser)->putJson(route('users.update', $this->adminUser->id), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak dapat mengubah role atau menonaktifkan satu-satunya Admin Management aktif pada sistem.',
        ]);

        $this->adminUser->refresh();
        $this->assertTrue($this->adminUser->isAdminManagement());
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->adminUser)->deleteJson(route('users.destroy', $this->adminUser->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
        ]);

        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    public function test_prevent_deleting_the_last_admin_account(): void
    {
        $secondAdmin = User::factory()->create([
            'name' => 'Admin Kedua',
            'email' => 'admin2.' . uniqid() . '@geodipa.co.id',
            'role' => User::ROLE_ADMIN_MANAGEMENT,
            'status' => 'Aktif',
        ]);

        // Remove/demote all other admins except secondAdmin
        User::whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin', 'admin_management'])->where('id', '!=', $secondAdmin->id)->update(['role' => User::ROLE_USER]);

        // Now secondAdmin is the only admin in system
        // Another user attempts to delete secondAdmin
        $response = $this->actingAs($this->adminUser)->deleteJson(route('users.destroy', $secondAdmin->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak dapat menghapus akun Admin Management terakhir pada sistem.',
        ]);

        $this->assertDatabaseHas('users', ['id' => $secondAdmin->id]);
    }

    public function test_admin_can_delete_regular_user(): void
    {
        $userToDelete = User::factory()->create([
            'name' => 'User Yang Dihapus',
            'email' => 'delete.me.' . uniqid() . '@geodipa.co.id',
            'role' => 'User',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->adminUser)->deleteJson(route('users.destroy', $userToDelete->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.',
        ]);

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $loginPassword = 'ValidPassword123!';
        $user = User::factory()->create([
            'email' => 'login.test.' . uniqid() . '@geodipa.co.id',
            'password' => Hash::make($loginPassword),
            'role' => 'User',
            'status' => 'Aktif',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => $user->email,
            'password' => $loginPassword,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $loginPassword = 'ValidPassword123!';
        $inactiveUser = User::factory()->create([
            'email' => 'inactive.login.' . uniqid() . '@geodipa.co.id',
            'password' => Hash::make($loginPassword),
            'role' => 'User',
            'status' => 'Nonaktif',
        ]);

        $response = $this->post(route('login.process'), [
            'email' => $inactiveUser->email,
            'password' => $loginPassword,
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_can_view_activity_logs(): void
    {
        // Log an activity for regularUser
        \App\Models\ActivityLog::create([
            'user_id' => $this->regularUser->id,
            'user_name' => $this->regularUser->name,
            'module' => 'Daily Work',
            'action' => 'TAMBAH',
            'record_type' => 'App\Models\DailyWork',
            'record_id' => 99,
            'description' => 'Menambahkan pekerjaan uji aktivitas',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('users.activities', $this->regularUser->id));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $this->regularUser->id,
                'name' => $this->regularUser->name,
            ],
        ]);
        $response->assertJsonFragment([
            'action' => 'TAMBAH',
            'module' => 'Daily Work',
            'description' => 'Menambahkan pekerjaan uji aktivitas',
        ]);
    }

    public function test_regular_user_can_view_their_own_or_others_activity_in_read_only_mode(): void
    {
        \App\Models\ActivityLog::create([
            'user_id' => $this->adminUser->id,
            'user_name' => $this->adminUser->name,
            'module' => 'User Management',
            'action' => 'Create User',
            'record_type' => 'User',
            'record_id' => 10,
            'description' => 'Audit log admin untuk pengujian',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->regularUser)->getJson(route('users.activities', $this->adminUser->id));
        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_view_all_activities_page(): void
    {
        $userViewResponse = $this->actingAs($this->regularUser)->get(route('users.activities.all'));
        $userViewResponse->assertStatus(403);
    }

    public function test_admin_can_view_all_activities_page_and_json(): void
    {
        \App\Models\ActivityLog::create([
            'user_id' => $this->adminUser->id,
            'user_name' => $this->adminUser->name,
            'module' => 'Daily Work',
            'action' => 'UBAH',
            'record_type' => 'App\Models\DailyWork',
            'record_id' => 101,
            'description' => 'Mengubah status pekerjaan #101 menjadi Selesai',
            'changes' => [
                'before' => ['status' => 'Berlangsung'],
                'after' => ['status' => 'Selesai'],
            ],
            'ip_address' => '127.0.0.1',
        ]);

        // 1. Admin view HTML page
        $adminViewResponse = $this->actingAs($this->adminUser)->get(route('users.activities.all'));
        $adminViewResponse->assertStatus(200);
        $adminViewResponse->assertSee('Riwayat Aktivitas Seluruh Modul');
        $adminViewResponse->assertSee('Mengubah status pekerjaan #101 menjadi Selesai');

        // 2. Admin JSON API
        $adminJsonResponse = $this->actingAs($this->adminUser)->getJson(route('users.activities.all'));
        $adminJsonResponse->assertStatus(200);
        $adminJsonResponse->assertJsonStructure([
            'success',
            'logs' => ['data'],
            'stats' => ['total', 'today', 'modules_count', 'users_count'],
            'available_modules',
            'available_actions',
            'available_users',
        ]);
        $adminJsonResponse->assertJsonFragment([
            'action' => 'UBAH',
            'module' => 'Daily Work',
            'description' => 'Mengubah status pekerjaan #101 menjadi Selesai',
        ]);
    }

    public function test_activity_log_diff_changes_are_recorded(): void
    {
        $log = \App\Models\ActivityLog::create([
            'user_id' => $this->adminUser->id,
            'user_name' => $this->adminUser->name,
            'module' => 'Events',
            'action' => 'UBAH',
            'record_type' => 'App\Models\Event',
            'record_id' => 50,
            'description' => 'Memperbarui lokasi kegiatan #50',
            'changes' => [
                'before' => ['location' => 'Gedung A'],
                'after' => ['location' => 'Gedung B'],
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'id' => $log->id,
            'module' => 'Events',
            'action' => 'UBAH',
        ]);

        $retrieved = \App\Models\ActivityLog::find($log->id);
        $this->assertIsArray($retrieved->changes);
        $this->assertEquals('Gedung A', $retrieved->changes['before']['location']);
        $this->assertEquals('Gedung B', $retrieved->changes['after']['location']);
    }
}


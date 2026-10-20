<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds to create or update the default Admin Management and Admin Inventory accounts safely.
     */
    public function run(): void
    {
        // 1. Admin Management utama
        User::updateOrCreate(
            ['email' => 'admin@geodipa.co.id'],
            [
                'name' => 'Administrator Dieng',
                'password' => Hash::make('Admin123'),
                'role' => User::ROLE_ADMIN_MANAGEMENT,
                'status' => 'Aktif',
            ]
        );

        // 2. Admin Inventory default
        User::updateOrCreate(
            ['email' => 'inventory@geodipa.co.id'],
            [
                'name' => 'Admin Inventory',
                'password' => Hash::make('Inventory123'),
                'role' => User::ROLE_ADMIN_INVENTORY,
                'status' => 'Aktif',
            ]
        );
    }
}

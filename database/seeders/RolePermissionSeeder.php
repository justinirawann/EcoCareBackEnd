<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ROLES =====
        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator']
        );

        $user = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User Biasa']
        );

        $petugas = Role::firstOrCreate(
            ['slug' => 'petugas'],
            ['name' => 'Petugas Lapangan']
        );

        // ===== PERMISSIONS =====
        $createReport = Permission::firstOrCreate(
            ['slug' => 'create_report'],
            ['name' => 'Buat Laporan Sampah']
        );

        $verifyReport = Permission::firstOrCreate(
            ['slug' => 'verify_report'],
            ['name' => 'Verifikasi Laporan Sampah']
        );

        $pickupTask = Permission::firstOrCreate(
            ['slug' => 'pickup_task'],
            ['name' => 'Tugas Penjemputan Sampah']
        );

        $adminDashboard = Permission::firstOrCreate(
            ['slug' => 'dashboard_admin'],
            ['name' => 'Akses Dashboard Admin']
        );

        $userDashboard = Permission::firstOrCreate(
            ['slug' => 'dashboard_user'],
            ['name' => 'Akses Dashboard User']
        );

        $petugasDashboard = Permission::firstOrCreate(
            ['slug' => 'dashboard_petugas'],
            ['name' => 'Akses Dashboard Petugas']
        );

        // ===== ATTACH PERMISSIONS (SAFE) =====
        $admin->permissions()->syncWithoutDetaching([
            $createReport->id,
            $verifyReport->id,
            $pickupTask->id,
            $adminDashboard->id,
        ]);

        $user->permissions()->syncWithoutDetaching([
            $createReport->id,
            $userDashboard->id,
        ]);

        $petugas->permissions()->syncWithoutDetaching([
            $pickupTask->id,
            $petugasDashboard->id,
        ]);
    }
}

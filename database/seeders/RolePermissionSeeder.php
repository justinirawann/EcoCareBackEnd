<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        
        $admin = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin'
        ]);

        $user = Role::create([
            'name' => 'User Biasa',
            'slug' => 'user'
        ]);

        $petugas = Role::create([
            'name' => 'Petugas Lapangan',
            'slug' => 'petugas'
        ]);

        $createReport = Permission::create([
            'name' => 'Buat Laporan Sampah',
            'slug' => 'create_report'
        ]);

        $verifyReport = Permission::create([
            'name' => 'Verifikasi Laporan Sampah',
            'slug' => 'verify_report'
        ]);

        $pickupTask = Permission::create([
            'name' => 'Tugas Penjemputan Sampah',
            'slug' => 'pickup_task'
        ]);

        $adminDashboard = Permission::create([
            'name' => 'Akses Dashboard Admin',
            'slug' => 'dashboard_admin'
        ]);

        $userDashboard = Permission::create([
            'name' => 'Akses Dashboard User',
            'slug' => 'dashboard_user'
        ]);

        $petugasDashboard = Permission::create([
            'name' => 'Akses Dashboard Petugas',
            'slug' => 'dashboard_petugas'
        ]);

        $admin->permissions()->attach([
            $createReport->id,
            $verifyReport->id,
            $pickupTask->id,
            $adminDashboard->id,
        ]);

        $user->permissions()->attach([
            $createReport->id,
            $userDashboard->id,
        ]);

        $petugas->permissions()->attach([
            $pickupTask->id,
            $petugasDashboard->id,
        ]);
    }
}

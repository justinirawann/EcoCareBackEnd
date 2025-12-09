<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        
        $user = User::create([
            'name' => 'Admin EcoCare',
            'email' => 'admin@ecocare.com',
            'password' => Hash::make('admin123')
        ]);

        $user->roles()->attach(1);



    }
}

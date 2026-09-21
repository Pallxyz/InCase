<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Password awal HANYA untuk development. Ganti sebelum hosting.
        User::updateOrCreate(
            ['email' => 'admin@incase.test'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'class_id' => null,
                'school_name' => 'SMKN 1 Cirebon',
            ]
        );
    }
}
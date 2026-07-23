<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'arya.milito@incase.test',
            ],
            [
                'name' => 'Arya Milito',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'class_id' => null,
                'phone' => '081298765432',
                'school_name' => 'SMKN 1 Cirebon',
            ]
        );
    }
}

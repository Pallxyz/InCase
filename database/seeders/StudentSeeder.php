<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $class = SchoolClass::first();

        User::create([
            'name' => 'Novvalino',
            'email' => 'novvalino@incase.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'class_id' => $class?->id,
            'phone' => '081234567890',
            'student_id' => '20260001',
            'school_name' => 'SMKN 1 Cirebon',
        ]);
    }
}

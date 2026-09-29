<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        // Kelas XII tidak dipakai (sedang PKL), jadi hanya kelas X dan XI.
        $classes = [
            ['name' => 'X RPL 1', 'major' => 'PPLG', 'grade' => 'X'],
            ['name' => 'X RPL 2', 'major' => 'PPLG', 'grade' => 'X'],
            ['name' => 'XI RPL 1', 'major' => 'PPLG', 'grade' => 'XI'],
            ['name' => 'XI RPL 2', 'major' => 'PPLG', 'grade' => 'XI'],
        ];

        $schoolName = 'SMKN 1 Cirebon';

        foreach ($classes as $class) {
            SchoolClass::updateOrCreate(
                ['name' => $class['name'], 'school_name' => $schoolName],
                ['grade' => $class['grade'], 'major' => $class['major']]
            );
        }
    }
}
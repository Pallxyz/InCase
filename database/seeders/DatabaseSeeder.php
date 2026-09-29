<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting:
        //  - AcademicYearSeeder harus sebelum SubjectSeeder (jadwal butuh tahun ajaran aktif).
        //  - SchoolClassSeeder & TeacherSeeder harus sebelum SubjectSeeder.
        //
        // RplDemoSeeder / RplScheduleSeeder SENGAJA tidak dipanggil di sini:
        // keduanya membuat guru demo (guru.matematika@...) yang akan dobel dengan
        // TeacherSeeder. Keduanya dipakai khusus oleh automated test.
        $this->call([
            AdminSeeder::class,
            AcademicYearSeeder::class,
            SchoolClassSeeder::class,
            TeacherSeeder::class,
            SubjectSeeder::class,
        ]);
    }
}
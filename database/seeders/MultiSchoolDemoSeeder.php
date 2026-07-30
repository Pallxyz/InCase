<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiSchoolDemoSeeder extends Seeder
{
    /**
     * Bikin 2 sekolah tambahan (SMA & SMP) buat testing multi-sekolah,
     * lengkap sama variasi kelasnya. Semua 5 hari (Senin-Jumat).
     * SMKN 1 Cirebon (guru Arya, murid Novvalino) udah ada dari seeder lain.
     */
    public function run(): void
    {
        $sma = School::firstOrCreate(
            ['name' => 'SMA Negeri 1 Testing'],
            ['type' => 'SMA', 'days_per_week' => 5]
        );

        $smaClasses = [];
        foreach (['X', 'XI', 'XII'] as $grade) {
            foreach (['MIPA', 'IPS'] as $track) {
                $sectionsCount = $track === 'MIPA' ? 3 : 2;
                for ($i = 1; $i <= $sectionsCount; $i++) {
                    $smaClasses[] = SchoolClass::firstOrCreate([
                        'name' => "{$grade} {$track} {$i}",
                        'school_name' => $sma->name,
                    ], [
                        'grade' => $grade,
                        'major' => $track,
                    ]);
                }
            }
        }

        $smp = School::firstOrCreate(
            ['name' => 'SMP Negeri 2 Testing'],
            ['type' => 'SMP', 'days_per_week' => 5]
        );

        // Sengaja beda-beda jumlah section per tingkat — 7 sampe F doang,
        // 8 sampe D doang, 9 sampe J — biar keliatan emang variatif,
        // gak semua sekolah/tingkat nyampe K.
        $smpSectionCounts = ['VII' => 6, 'VIII' => 4, 'IX' => 11];
        $smpClasses = [];
        foreach ($smpSectionCounts as $grade => $sectionsCount) {
            for ($i = 0; $i < $sectionsCount; $i++) {
                $letter = chr(ord('A') + $i);
                $smpClasses[] = SchoolClass::firstOrCreate([
                    'name' => "{$grade} {$letter}",
                    'school_name' => $smp->name,
                ], [
                    'grade' => $grade,
                    'major' => $letter,
                ]);
            }
        }

        User::updateOrCreate(
            ['email' => 'budi.santoso@incase.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'class_id' => null,
                'school_name' => $sma->name,
            ]
        );

        User::updateOrCreate(
            ['email' => 'siti.aminah@incase.test'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password'),
                'role' => 'student',
                'class_id' => $smaClasses[0]->id,
                'student_id' => '20260002',
                'school_name' => $sma->name,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dewi.lestari@incase.test'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'class_id' => null,
                'school_name' => $smp->name,
            ]
        );

        User::updateOrCreate(
            ['email' => 'ahmad.fauzi@incase.test'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'role' => 'student',
                'class_id' => $smpClasses[0]->id,
                'student_id' => '20260003',
                'school_name' => $smp->name,
            ]
        );
    }
}

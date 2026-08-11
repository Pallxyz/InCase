<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiSchoolDemoSeeder extends Seeder
{
    /**
     * Setup demo lengkap: 1 sekolah per jenjang (SMK/SMA/SMP) di Cirebon,
     * masing-masing 1 guru + 1 murid + 2 jadwal pelajaran BEDA MAPEL,
     * dengan barang wajib spesifik per mapel (bukan generik "Buku Paket").
     *
     * Pelajaran pertama tiap sekolah dijadiin "jadwal aktif sepanjang hari"
     * biar bisa didemo scan RFID kapan pun, gak tergantung jam beneran.
     */
    public function run(): void
    {
        $this->renameSchoolEverywhere('SMA Negeri 1 Testing', 'SMAN 1 Cirebon');
        $this->renameSchoolEverywhere('SMP Negeri 2 Testing', 'SMPN 1 Cirebon');

        $this->setupSchool(
            schoolName: 'SMKN 1 Cirebon',
            type: 'SMK',
            className: 'XI RPL 2',
            grade: 'XI',
            major: 'PPLG',
            teacherEmail: 'arya.milito@incase.test',
            teacherName: 'Arya Milito',
            studentEmail: 'novvalino@incase.test',
            studentName: 'Novvalino',
            studentId: '20260001',
            subjects: [
                [
                    'name' => 'Matematika',
                    'isLiveDemo' => true, // aktif sepanjang hari, buat demo scan fisik
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'requiredItems' => [
                        // kartu biru Wokwi
                        'Laptop' => '01020304',
                        // kartu ijo Wokwi
                        'Buku Paket Matematika' => '11223344',
                        // sengaja gak ada kartu fisiknya -> bakal ketauan "kurang" pas didemo
                        'Buku Tulis Matematika' => null,
                    ],
                ],
                [
                    'name' => 'Bahasa Indonesia',
                    'isLiveDemo' => false,
                    'start_time' => '08:30',
                    'end_time' => '10:00',
                    'requiredItems' => [
                        'Buku Paket Bahasa Indonesia' => null,
                        'Buku Tulis Bahasa Indonesia' => null,
                    ],
                ],
            ]
        );

        $this->setupSchool(
            schoolName: 'SMAN 1 Cirebon',
            type: 'SMA',
            className: 'X MIPA 1',
            grade: 'X',
            major: 'MIPA',
            teacherEmail: 'budi.santoso@incase.test',
            teacherName: 'Budi Santoso',
            studentEmail: 'siti.aminah@incase.test',
            studentName: 'Siti Aminah',
            studentId: '20260002',
            subjects: [
                [
                    'name' => 'Matematika',
                    'isLiveDemo' => true,
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'requiredItems' => [
                        'Laptop' => null,
                        'Buku Paket Matematika' => null,
                        'Buku Tulis Matematika' => null,
                    ],
                ],
                [
                    'name' => 'Bahasa Indonesia',
                    'isLiveDemo' => false,
                    'start_time' => '08:30',
                    'end_time' => '10:00',
                    'requiredItems' => [
                        'Buku Paket Bahasa Indonesia' => null,
                        'Buku Tulis Bahasa Indonesia' => null,
                    ],
                ],
            ]
        );

        $this->setupSchool(
            schoolName: 'SMPN 1 Cirebon',
            type: 'SMP',
            className: 'VII A',
            grade: 'VII',
            major: 'A',
            teacherEmail: 'dewi.lestari@incase.test',
            teacherName: 'Dewi Lestari',
            studentEmail: 'ahmad.fauzi@incase.test',
            studentName: 'Ahmad Fauzi',
            studentId: '20260003',
            subjects: [
                [
                    'name' => 'Matematika',
                    'isLiveDemo' => true,
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'requiredItems' => [
                        'Laptop' => null,
                        'Buku Paket Matematika' => null,
                        'Buku Tulis Matematika' => null,
                        'Buku LKS Matematika' => null,
                    ],
                ],
                [
                    'name' => 'Bahasa Indonesia',
                    'isLiveDemo' => false,
                    'start_time' => '08:30',
                    'end_time' => '10:00',
                    'requiredItems' => [
                        'Buku Paket Bahasa Indonesia' => null,
                        'Buku Tulis Bahasa Indonesia' => null,
                        'Buku LKS Bahasa Indonesia' => null,
                    ],
                ],
            ]
        );

        $this->command->info('Demo 3 sekolah siap, masing-masing 2 mapel dengan barang wajib spesifik.');
    }

    private function setupSchool(
        string $schoolName,
        string $type,
        string $className,
        string $grade,
        string $major,
        string $teacherEmail,
        string $teacherName,
        string $studentEmail,
        string $studentName,
        string $studentId,
        array $subjects,
    ): void {
        $school = School::firstOrCreate(
            ['name' => $schoolName],
            ['type' => $type, 'days_per_week' => 5]
        );

        $class = SchoolClass::firstOrCreate(
            ['name' => $className, 'school_name' => $school->name],
            ['grade' => $grade, 'major' => $major]
        );

        $teacher = User::updateOrCreate(
            ['email' => $teacherEmail],
            [
                'name' => $teacherName,
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'class_id' => null,
                'school_name' => $school->name,
            ]
        );

        $student = User::updateOrCreate(
            ['email' => $studentEmail],
            [
                'name' => $studentName,
                'password' => Hash::make('password'),
                'role' => 'student',
                'class_id' => $class->id,
                'student_id' => $studentId,
                'school_name' => $school->name,
            ]
        );

        // Bersihin barang & jadwal demo lama, biar gak numpuk kalau seeder diulang.
        Item::where('user_id', $student->id)->delete();
        Subject::where('teacher_id', $teacher->id)
            ->where('class_id', $class->id)
            ->delete();

        // Kumpulin semua nama barang unik dari semua mapel, biar gak dobel dibikin.
        $allItemsNeeded = [];
        foreach ($subjects as $subjectData) {
            foreach ($subjectData['requiredItems'] as $itemName => $rfid) {
                $allItemsNeeded[$itemName] = $rfid ?? ($allItemsNeeded[$itemName] ?? null);
            }
        }

        foreach ($allItemsNeeded as $itemName => $rfid) {
            Item::create([
                'user_id' => $student->id,
                'name' => $itemName,
                'category' => 'Electronics',
                'rfid_uid' => $rfid ?? ('DEMO-' . Str::upper(Str::random(8))),
            ]);
        }

        foreach ($subjects as $subjectData) {
            $subject = Subject::create([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'name' => $subjectData['name'],
                'location' => $subjectData['isLiveDemo'] ? 'Ruang Demo' : 'R1',
                'day' => now()->englishDayOfWeek,
                'start_time' => $subjectData['start_time'],
                'end_time' => $subjectData['end_time'],
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ]);

            foreach (array_keys($subjectData['requiredItems']) as $itemName) {
                $subject->requiredItems()->create(['name' => $itemName]);
            }
        }
    }

    private function renameSchoolEverywhere(string $oldName, string $newName): void
    {
        if (! School::where('name', $oldName)->exists()) {
            return;
        }

        $targetExists = School::where('name', $newName)->exists();

        if ($targetExists) {
            DB::table('school_classes')->where('school_name', $oldName)->update(['school_name' => $newName]);
            DB::table('users')->where('school_name', $oldName)->update(['school_name' => $newName]);

            if (DB::getSchemaBuilder()->hasTable('holidays')) {
                DB::table('holidays')->where('school_name', $oldName)->update(['school_name' => $newName]);
            }

            School::where('name', $oldName)->delete();
            return;
        }

        School::where('name', $oldName)->update(['name' => $newName]);
        DB::table('school_classes')->where('school_name', $oldName)->update(['school_name' => $newName]);
        DB::table('users')->where('school_name', $oldName)->update(['school_name' => $newName]);

        if (DB::getSchemaBuilder()->hasTable('holidays')) {
            DB::table('holidays')->where('school_name', $oldName)->update(['school_name' => $newName]);
        }
    }
}
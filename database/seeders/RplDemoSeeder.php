<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Item;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Seeder\RplTimetable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo lengkap untuk SMKN 1 Cirebon, jurusan RPL:
 * 1 tahun ajaran aktif (otomatis mengikuti tahun berjalan), 6 kelas, 14 guru,
 * jadwal lengkap Senin-Jumat (3 jam pelajaran per hari, tanpa bentrok),
 * barang wajib, 18 siswa + barangnya.
 *
 * Aman dijalankan berulang kali (tidak menggandakan data).
 * Akun demo (password semuanya: "password"):
 *   guru : guru.pendidikan-agama-islam@incase.test, guru.matematika@incase.test, dst.
 *   siswa: siswa01@incase.test ... siswa18@incase.test  (siswa01 = X RPL 1)
 */
class RplDemoSeeder extends Seeder
{
    private const SCHOOL = 'SMKN 1 Cirebon';

    private const STUDENT_NAMES = [
        'Muhammad Rizky Ramadhan', 'Siti Nur Azizah', 'Fajar Nugroho',
        'Ayu Lestari', 'Bagas Setiawan', 'Intan Permatasari',
        'Rendy Firmansyah', 'Salsabila Putri', 'Dimas Aditya',
        'Nadia Ramadhani', 'Reza Maulana', 'Wulan Sari',
        'Taufik Hidayat', 'Yulia Anggraini', 'Gilang Saputra',
        'Devi Oktaviani', 'Hafizh Al Farizi', 'Cindy Amelia',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $year = $this->academicYear();
            $classes = $this->classes();
            $teachers = $this->teachers();

            // Tanpa event: supaya seeder tidak memicu notifikasi PR ke siswa.
            Subject::withoutEvents(fn () => $this->subjects($year, $classes, $teachers));

            $this->students($classes);
        });
    }

    private function academicYear(): AcademicYear
    {
        $start = now()->month >= 7 ? now()->year : now()->year - 1;

        AcademicYear::query()->update(['is_active' => false]);

        return AcademicYear::updateOrCreate(
            [
                'year_start' => $start,
                'year_end' => $start + 1,
                'semester' => now()->month >= 7 ? 'ganjil' : 'genap',
            ],
            ['is_active' => true],
        );
    }

    /** @return list<SchoolClass> indeks sama dengan RplTimetable::CLASSES */
    private function classes(): array
    {
        return array_map(fn (array $c) => SchoolClass::updateOrCreate(
            ['name' => $c[0]],
            ['major' => 'PPLG', 'grade' => $c[1], 'school_name' => self::SCHOOL],
        ), RplTimetable::CLASSES);
    }

    /** @return list<User> indeks sama dengan RplTimetable::SUBJECTS */
    private function teachers(): array
    {
        return array_map(function (array $subject) {
            $slug = \Illuminate\Support\Str::slug($subject[0]);

            return User::updateOrCreate(
                ['email' => "guru.{$slug}@incase.test"],
                [
                    'name' => $subject[1],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'class_id' => null,
                    'school_name' => self::SCHOOL,
                ],
            );
        }, RplTimetable::SUBJECTS);
    }

    private function subjects(AcademicYear $year, array $classes, array $teachers): void
    {
        foreach (RplTimetable::entries() as $e) {
            [$subjectName, , $requiredItems] = RplTimetable::SUBJECTS[$e['subject']];

            $subject = Subject::updateOrCreate(
                [
                    'academic_year_id' => $year->id,
                    'class_id' => $classes[$e['class']]->id,
                    'day' => $e['day'],
                    'start_time' => $e['start'],
                ],
                [
                    'teacher_id' => $teachers[$e['subject']]->id,
                    'name' => $subjectName,
                    'location' => RplTimetable::CLASSES[$e['class']][2],
                    'end_time' => $e['end'],
                    'homework' => null,
                    'has_exam' => false,
                    'is_active' => true,
                ],
            );

            $subject->requiredItems()->delete();

            foreach ($requiredItems as $name) {
                $subject->requiredItems()->create(['name' => $name]);
            }
        }
    }

    private function students(array $classes): void
    {
        foreach (self::STUDENT_NAMES as $i => $name) {
            $number = $i + 1;

            $student = User::updateOrCreate(
                ['email' => sprintf('siswa%02d@incase.test', $number)],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'class_id' => $classes[intdiv($i, 3)]->id,   // 3 siswa per kelas
                    'school_name' => self::SCHOOL,
                    'student_id' => sprintf('2026%04d', $number),
                    'phone' => sprintf('0812000%04d', $number),
                ],
            );

            // Setiap siswa punya semua barang yang pernah diwajibkan, lengkap dengan tag RFID unik.
            foreach (RplTimetable::allItemNames() as $j => $itemName) {
                Item::updateOrCreate(
                    ['rfid_uid' => sprintf('RPL-%02d-%02d', $number, $j + 1)],
                    [
                        'user_id' => $student->id,
                        'name' => $itemName,
                        'category' => RplTimetable::ITEM_CATEGORY[$itemName],
                        // 'quantity' => 1,
                        'status' => 'active',
                    ],
                );
            }
        }
    }
}
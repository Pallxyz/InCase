<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Jadwal CONTOH (Senin-Jumat, 3 jam pelajaran/hari) untuk 6 kelas RPL.
 * Dipakai KHUSUS untuk automated test (atau kalau sewaktu-waktu butuh demo
 * cepat) -- BUKAN alur normal aplikasi. Di alur normal, jadwal dibuat
 * manual oleh admin lewat halaman jadwal.
 *
 * Butuh RplDemoSeeder sudah dijalankan lebih dulu (kelas, guru, dan tahun
 * ajaran aktif harus sudah ada), kalau belum bakal error "not found".
 *
 * Cara pakai di test:
 *   $this->seed(AdminSeeder::class);
 *   $this->seed(RplDemoSeeder::class);
 *   $this->seed(RplScheduleSeeder::class);
 */
class RplScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->firstOrFail();

        $classes = array_map(
            fn (array $c) => SchoolClass::where('name', $c[0])->firstOrFail(),
            RplTimetable::CLASSES,
        );

        $teachers = array_map(function (array $subject) {
            $slug = \Illuminate\Support\Str::slug($subject[0]);

            return User::where('email', "guru.{$slug}@incase.test")->firstOrFail();
        }, RplTimetable::SUBJECTS);

        Subject::withoutEvents(function () use ($year, $classes, $teachers) {
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
        });
    }
}
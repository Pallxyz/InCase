<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->get();

        $classes = SchoolClass::whereIn('name', [
            'X RPL 1',
            'X RPL 2',
            'XI RPL 1',
            'XI RPL 2',
            'XII RPL 1',
            'XII RPL 2',
        ])->get();

        $activeYear = AcademicYear::where('is_active', true)->first();

        if ($teachers->isEmpty() || $classes->isEmpty() || !$activeYear) {
            return;
        }

        $subjectTemplates = [

            /*
            |--------------------------------------------------------------------------
            | SENIN
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Matematika',
                'location' => 'R11',
                'day' => 'Monday',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'homework' => 'Kerjakan latihan halaman 42',
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket Matematika',
                    'Buku Tulis Matematika',
                    'Kalkulator',
                ],
            ],
            [
                'name' => 'Bahasa Indonesia',
                'location' => 'R11',
                'day' => 'Monday',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket Bahasa Indonesia',
                    'Buku Tulis Bahasa Indonesia',
                ],
            ],
            [
                'name' => 'Desain Grafis',
                'location' => 'Lab RPL',
                'day' => 'Monday',
                'start_time' => '10:15',
                'end_time' => '12:15',
                'homework' => 'Buat desain poster',
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Laptop',
                    'Charger Laptop',
                    'Mouse',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | SELASA
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Bahasa Inggris',
                'location' => 'R11',
                'day' => 'Tuesday',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket Bahasa Inggris',
                    'Buku Tulis Bahasa Inggris',
                ],
            ],
            [
                'name' => 'Sejarah',
                'location' => 'R11',
                'day' => 'Tuesday',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'homework' => 'Rangkuman Bab 3',
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket Sejarah',
                    'Buku Tulis Sejarah',
                ],
            ],
            [
                'name' => 'MKK',
                'location' => 'Lab RPL',
                'day' => 'Tuesday',
                'start_time' => '10:15',
                'end_time' => '12:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Laptop',
                    'Charger Laptop',
                    'Buku Catatan',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | RABU
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'PKK',
                'location' => 'Lab RPL',
                'day' => 'Wednesday',
                'start_time' => '07:00',
                'end_time' => '09:30',
                'homework' => 'Proposal usaha',
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Laptop',
                    'Charger Laptop',
                    'Buku Catatan',
                ],
            ],
            [
                'name' => 'PAI',
                'location' => 'R11',
                'day' => 'Wednesday',
                'start_time' => '09:45',
                'end_time' => '11:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket PAI',
                    'Buku Tulis PAI',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | KAMIS
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'MPP',
                'location' => 'Lab RPL',
                'day' => 'Thursday',
                'start_time' => '07:00',
                'end_time' => '09:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Laptop',
                    'Charger Laptop',
                ],
            ],
            [
                'name' => 'BK',
                'location' => 'R11',
                'day' => 'Thursday',
                'start_time' => '09:15',
                'end_time' => '10:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Buku Catatan',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | JUMAT
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'PJOK',
                'location' => 'Lapangan',
                'day' => 'Friday',
                'start_time' => '07:00',
                'end_time' => '09:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
                'required_items' => [
                    'Baju Olahraga',
                    'Sepatu Olahraga',
                    'Handuk',
                ],
            ],
            [
                'name' => 'PPKN',
                'location' => 'R11',
                'day' => 'Friday',
                'start_time' => '09:15',
                'end_time' => '10:45',
                'homework' => 'Pelajari UUD 1945',
                'has_exam' => true,
                'is_active' => true,
                'required_items' => [
                    'Buku Paket PPKN',
                    'Buku Tulis PPKN',
                ],
            ],
        ];

        $teacherCount = $teachers->count();
        $teacherIndex = 0;

        foreach ($classes as $class) {
            foreach ($subjectTemplates as $template) {
                $teacher = $teachers[$teacherIndex % $teacherCount];
                $teacherIndex++;

                $requiredItems = $template['required_items'] ?? [];
                $subjectData = $template;
                unset($subjectData['required_items']);

                $subjectData['class_id'] = $class->id;
                $subjectData['teacher_id'] = $teacher->id;
                $subjectData['academic_year_id'] = $activeYear->id;

                $created = Subject::create($subjectData);

                foreach ($requiredItems as $itemName) {
                    $created->requiredItems()->create([
                        'name' => $itemName,
                    ]);
                }
            }
        }
    }
}
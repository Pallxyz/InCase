<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', 'teacher')->first();

        $class = SchoolClass::where('name', 'XI RPL 2')->first();

        if (!$teacher || !$class) {
            return;
        }

        $subjects = [

            /*
            |--------------------------------------------------------------------------
            | SENIN
            |--------------------------------------------------------------------------
            */

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'Matematika',
                'location'
                 => 'R11',
                'day' => 'Monday',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'homework' => 'Kerjakan latihan halaman 42',
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'Bahasa Indonesia',
                'location'
                 => 'R11',
                'day' => 'Monday',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'Desain Grafis',
                'location'
                 => 'Lab RPL',
                'day' => 'Monday',
                'start_time' => '10:15',
                'end_time' => '12:15',
                'homework' => 'Buat desain poster',
                'has_exam' => false,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | SELASA
            |--------------------------------------------------------------------------
            */

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'Bahasa Inggris',
                'location'
                 => 'R11',
                'day' => 'Tuesday',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'Sejarah',
                'location'
                 => 'R11',
                'day' => 'Tuesday',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'homework' => 'Rangkuman Bab 3',
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'MKK',
                'location'
                 => 'Lab RPL',
                'day' => 'Tuesday',
                'start_time' => '10:15',
                'end_time' => '12:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | RABU
            |--------------------------------------------------------------------------
            */

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'PKK',
                'location'
                 => 'Lab RPL',
                'day' => 'Wednesday',
                'start_time' => '07:00',
                'end_time' => '09:30',
                'homework' => 'Proposal usaha',
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'PAI',
                'location'
                 => 'R11',
                'day' => 'Wednesday',
                'start_time' => '09:45',
                'end_time' => '11:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | KAMIS
            |--------------------------------------------------------------------------
            */

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'MPP',
                'location'
                 => 'Lab RPL',
                'day' => 'Thursday',
                'start_time' => '07:00',
                'end_time' => '09:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'BK',
                'location'
                 => 'R11',
                'day' => 'Thursday',
                'start_time' => '09:15',
                'end_time' => '10:15',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | JUMAT
            |--------------------------------------------------------------------------
            */

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'PJOK',
                'location'
                 => 'Lapangan',
                'day' => 'Friday',
                'start_time' => '07:00',
                'end_time' => '09:00',
                'homework' => null,
                'has_exam' => false,
                'is_active' => true,
            ],

            [
                'class_id' => $class->id,
                'teacher_id' => $teacher->id,
                'name' => 'PPKN',
                'location'
                 => 'R11',
                'day' => 'Friday',
                'start_time' => '09:15',
                'end_time' => '10:45',
                'homework' => 'Pelajari UUD 1945',
                'has_exam' => true,
                'is_active' => true,
            ],

        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
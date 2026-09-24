<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $schoolName = 'SMKN 1 Cirebon';

        $teachers = [
            'guru2@incase.test'  => 'Eri Rumsari',
            'guru3@incase.test'  => 'Dudung Zulkipli',
            'guru4@incase.test'  => 'Vihantika Rachma Fitri',
            'guru5@incase.test'  => 'Afika Awwaliyah Rozzaq',
            'guru6@incase.test'  => 'Zaenal Abidin',
            'guru7@incase.test'  => 'Dedi Supriyadi',
            'guru8@incase.test'  => 'Rizal Murtiyono',
            'guru9@incase.test'  => 'Insulinde Yuliyati',
            'guru10@incase.test' => 'Pipit Komariah',
            'guru11@incase.test' => 'Rudi Hermanto',
            'guru12@incase.test' => 'Sri Prihantoro',
            'guru13@incase.test' => 'Syahrul Ronny',
            'guru14@incase.test' => 'Bambang Tri Setiadi',
        ];

        foreach ($teachers as $email => $name) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'        => $name,
                    'password'    => Hash::make('password'),
                    'role'        => 'teacher',
                    'class_id'    => null,
                    'school_name' => $schoolName,
                ]
            );
        }
    }
}
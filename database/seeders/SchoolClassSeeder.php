<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [

            /*
            |--------------------------------------------------------------------------
            | Kelas X
            |--------------------------------------------------------------------------
            */

            ['name' => 'X DPIB 1', 'major' => 'DPIB', 'grade' => 'X'],
            ['name' => 'X DPIB 2', 'major' => 'DPIB', 'grade' => 'X'],

            ['name' => 'X Teknik Mesin 1', 'major' => 'Teknik Mesin', 'grade' => 'X'],
            ['name' => 'X Teknik Mesin 2', 'major' => 'Teknik Mesin', 'grade' => 'X'],

            ['name' => 'X Teknik Otomotif 1', 'major' => 'Teknik Otomotif', 'grade' => 'X'],
            ['name' => 'X Teknik Otomotif 2', 'major' => 'Teknik Otomotif', 'grade' => 'X'],

            ['name' => 'X Teknik Elektronika 1', 'major' => 'Teknik Elektronika', 'grade' => 'X'],
            ['name' => 'X Teknik Elektronika 2', 'major' => 'Teknik Elektronika', 'grade' => 'X'],

            ['name' => 'X Teknik Listrik 1', 'major' => 'Teknik Listrik', 'grade' => 'X'],
            ['name' => 'X Teknik Listrik 2', 'major' => 'Teknik Listrik', 'grade' => 'X'],

            ['name' => 'X TKJ 1', 'major' => 'TJKT', 'grade' => 'X'],
            ['name' => 'X TKJ 2', 'major' => 'TJKT', 'grade' => 'X'],

            ['name' => 'X RPL 1', 'major' => 'PPLG', 'grade' => 'X'],
            ['name' => 'X RPL 2', 'major' => 'PPLG', 'grade' => 'X'],

            /*
            |--------------------------------------------------------------------------
            | Kelas XI
            |--------------------------------------------------------------------------
            */

            ['name' => 'XI DPIB 1', 'major' => 'DPIB', 'grade' => 'XI'],
            ['name' => 'XI DPIB 2', 'major' => 'DPIB', 'grade' => 'XI'],

            ['name' => 'XI Teknik Pemesinan 1', 'major' => 'Teknik Pemesinan', 'grade' => 'XI'],
            ['name' => 'XI Teknik Pemesinan 2', 'major' => 'Teknik Pemesinan', 'grade' => 'XI'],

            ['name' => 'XI Teknik Kendaraan Ringan 1', 'major' => 'TKR', 'grade' => 'XI'],
            ['name' => 'XI Teknik Kendaraan Ringan 2', 'major' => 'TKR', 'grade' => 'XI'],

            ['name' => 'XI Teknik Bodi Kendaraan 1', 'major' => 'TBK', 'grade' => 'XI'],

            ['name' => 'XI Teknik Elektronika Industri 1', 'major' => 'TEI', 'grade' => 'XI'],

            ['name' => 'XI Teknik Otomasi Industri 1', 'major' => 'TOI', 'grade' => 'XI'],

            ['name' => 'XI Instalasi Tenaga Listrik 1', 'major' => 'ITL', 'grade' => 'XI'],

            ['name' => 'XI Teknik Pendinginan Tata Udara 1', 'major' => 'TPTU', 'grade' => 'XI'],

            ['name' => 'XI TKJ 1', 'major' => 'TJKT', 'grade' => 'XI'],
            ['name' => 'XI TKJ 2', 'major' => 'TJKT', 'grade' => 'XI'],

            ['name' => 'XI RPL 1', 'major' => 'PPLG', 'grade' => 'XI'],
            ['name' => 'XI RPL 2', 'major' => 'PPLG', 'grade' => 'XI'],

            /*
            |--------------------------------------------------------------------------
            | Kelas XII
            |--------------------------------------------------------------------------
            */

            ['name' => 'XII DPIB 1', 'major' => 'DPIB', 'grade' => 'XII'],
            ['name' => 'XII DPIB 2', 'major' => 'DPIB', 'grade' => 'XII'],

            ['name' => 'XII Teknik Pemesinan 1', 'major' => 'Teknik Pemesinan', 'grade' => 'XII'],
            ['name' => 'XII Teknik Pemesinan 2', 'major' => 'Teknik Pemesinan', 'grade' => 'XII'],

            ['name' => 'XII Teknik Kendaraan Ringan 1', 'major' => 'TKR', 'grade' => 'XII'],
            ['name' => 'XII Teknik Kendaraan Ringan 2', 'major' => 'TKR', 'grade' => 'XII'],

            ['name' => 'XII Teknik Bodi Kendaraan 1', 'major' => 'TBK', 'grade' => 'XII'],

            ['name' => 'XII Teknik Elektronika Industri 1', 'major' => 'TEI', 'grade' => 'XII'],

            ['name' => 'XII Teknik Otomasi Industri 1', 'major' => 'TOI', 'grade' => 'XII'],

            ['name' => 'XII Instalasi Tenaga Listrik 1', 'major' => 'ITL', 'grade' => 'XII'],

            ['name' => 'XII Teknik Pendinginan Tata Udara 1', 'major' => 'TPTU', 'grade' => 'XII'],

            ['name' => 'XII TKJ 1', 'major' => 'TJKT', 'grade' => 'XII'],
            ['name' => 'XII TKJ 2', 'major' => 'TJKT', 'grade' => 'XII'],

            ['name' => 'XII RPL 1', 'major' => 'PPLG', 'grade' => 'XII'],
            ['name' => 'XII RPL 2', 'major' => 'PPLG', 'grade' => 'XII'],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }
    }
}
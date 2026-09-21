<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
            AdminSeeder::class,
            RplDemoSeeder::class,   // 1 SMK, jurusan RPL, 6 kelas + jadwal + siswa + barang
        ]);
    }
}
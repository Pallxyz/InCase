<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::updateOrCreate(
            [
                'name' => 'SMKN 1 Cirebon',
            ],
            [
                'type' => 'SMK',
                'days_per_week' => 5,
            ]
        );
    }
}
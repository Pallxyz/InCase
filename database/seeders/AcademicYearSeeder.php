<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

/**
 * Membuat tahun ajaran aktif yang mengikuti tanggal hari ini
 * (Juli-Desember = ganjil, Januari-Juni = genap).
 *
 * Wajib dijalankan SEBELUM SubjectSeeder: SubjectSeeder berhenti diam-diam
 * kalau belum ada tahun ajaran aktif, sehingga jadwal tidak terbentuk.
 * Aman dijalankan berulang kali.
 */
class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $start = now()->month >= 7 ? now()->year : now()->year - 1;

        AcademicYear::query()->update(['is_active' => false]);

        AcademicYear::updateOrCreate(
            [
                'year_start' => $start,
                'year_end' => $start + 1,
                'semester' => now()->month >= 7 ? 'ganjil' : 'genap',
            ],
            ['is_active' => true],
        );
    }
}
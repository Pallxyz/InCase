<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jadwal lama yang dibuat sebelum ada fitur tahun ajaran punya academic_year_id = NULL.
 * Setelah filter tahun ajaran diterapkan, jadwal seperti itu tidak akan tampil di mana pun.
 * Migration ini memindahkannya ke tahun ajaran aktif (atau yang terbaru).
 * Kalau belum ada tahun ajaran sama sekali, dibuatkan satu dari tanggal hari ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('subjects')->whereNull('academic_year_id')->exists()) {
            return;
        }

        $yearId = DB::table('academic_years')->where('is_active', true)->value('id')
            ?? DB::table('academic_years')->orderByDesc('year_start')->orderByDesc('semester')->value('id');

        if (! $yearId) {
            $now = now();
            // Juli-Desember = ganjil, Januari-Juni = genap
            $start = $now->month >= 7 ? $now->year : $now->year - 1;

            $yearId = DB::table('academic_years')->insertGetId([
                'year_start' => $start,
                'year_end' => $start + 1,
                'semester' => $now->month >= 7 ? 'ganjil' : 'genap',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('subjects')
            ->whereNull('academic_year_id')
            ->update(['academic_year_id' => $yearId]);
    }

    public function down(): void
    {
        // Tidak bisa dibalikkan: data lama sudah tidak dibedakan dari data baru.
    }
};
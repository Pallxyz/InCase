<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Bikin barang milik tiap siswa berdasarkan barang wajib di jadwal kelasnya
 * (SubjectRequiredItem). Nama barang dipakai PERSIS seperti di jadwal, jadi
 * pencocokan barang wajib di dashboard/ScanService langsung nyambung tanpa
 * perlu siswa ketik ulang manual.
 *
 * PENTING: jalankan ini SETELAH ada siswa dengan class_id terisi (lewat
 * registrasi, atau seeder siswa lain). Siswa tanpa class_id dilewati.
 * Jalankan SETELAH SubjectSeeder, kalau belum ada jadwal maka tidak ada
 * barang yang dibuat.
 *
 * Aman dijalankan berulang kali: barang yang sudah ada (nama sama, siswa
 * sama) tidak dibuat dobel.
 *
 * Cara pakai: php artisan db:seed --class=StudentItemSeeder
 */
class StudentItemSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')
            ->whereNotNull('class_id')
            ->get();

        if ($students->isEmpty()) {
            $this->command?->warn('Belum ada siswa dengan class_id terisi. Tidak ada barang yang dibuat.');

            return;
        }

        $created = 0;

        foreach ($students as $student) {
            $names = Subject::where('class_id', $student->class_id)
                ->where('is_active', true)
                ->with('requiredItems')
                ->get()
                ->flatMap(fn ($subject) => $subject->requiredItems->pluck('name'))
                ->map(fn ($name) => trim($name))
                ->filter()
                ->unique(fn ($name) => mb_strtolower($name))
                ->values();

            foreach ($names as $name) {
                $lower = mb_strtolower($name);

                // Baju olahraga sengaja tidak dikasih RFID: stikernya rawan lepas/luntur
                // kena keringat atau kecuci, jadi tidak realistis dipantau lewat scan.
                $rfid = str_contains($lower, 'baju olahraga')
                    ? null
                    : sprintf(
                        'INC-%04d-%s',
                        $student->id,
                        strtoupper(substr(md5($lower), 0, 6))
                    );

                $item = Item::firstOrCreate(
                    ['user_id' => $student->id, 'name' => $name],
                    [
                        'category' => Item::categoryFor($name),
                        'rfid_uid' => $rfid,
                        'quantity' => 1,
                        'status'   => 'active',
                    ],
                );

                if ($item->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $this->command?->info("{$created} barang dibuat untuk {$students->count()} siswa.");
    }
}
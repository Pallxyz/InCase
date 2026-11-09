<?php

namespace App\Services;

use App\Models\User;

/**
 * Menyimpan sementara mode pindai yang dipilih siswa di halaman Pindai
 * ("packing" = Scan Berangkat, "return" = Scan Pulang).
 *
 * Alat ESP32 cuma mengirim UID, jadi pilihan mode disimpan di cache per siswa
 * dan dibaca ScanService saat scan masuk. Mode kedaluwarsa sendiri setelah
 * TTL_SECONDS, dan diperpanjang tiap ada scan yang diterima.
 */
class ScanModeService
{
    public const TTL_SECONDS = 120;

    private function key(User $student): string
    {
        return "scan_mode:{$student->id}";
    }

    public function set(User $student, string $mode): void
    {
        $until = now()->addSeconds(self::TTL_SECONDS);

        cache()->put($this->key($student), [
            'mode' => $mode,
            'until' => $until->timestamp,
        ], $until);
    }

    public function current(User $student): ?string
    {
        return cache()->get($this->key($student))['mode'] ?? null;
    }

    /** Sisa detik mode aktif (0 kalau tidak ada). */
    public function remaining(User $student): int
    {
        $value = cache()->get($this->key($student));

        return $value ? max(0, $value['until'] - now()->timestamp) : 0;
    }

    /** Perpanjang mode yang sedang aktif (dipanggil tiap scan diterima). */
    public function touch(User $student): void
    {
        if ($mode = $this->current($student)) {
            $this->set($student, $mode);
        }
    }

    public function clear(User $student): void
    {
        cache()->forget($this->key($student));
    }
}
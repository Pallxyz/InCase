<?php

namespace App\Services;

use App\Models\ScanLog;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Menghitung barang wajib yang BELUM discan (fase persiapan/packing)
 * untuk satu siswa pada satu hari sekolah. Dipakai oleh ScanService
 * (waktu scan) dan reminder (sebelum sempat scan sama sekali).
 */
class PackingChecklistService
{
    /** Nama barang (huruf kecil) yang sudah discan siswa untuk hari sekolah itu. */
    public function scannedNames(User $student, Carbon $date): Collection
    {
        return ScanLog::where('scan_logs.user_id', $student->id)
            ->where('scan_logs.status', 'success')
            ->where('scan_logs.phase', 'packing')
            ->whereDate('scan_logs.for_date', $date)
            ->join('items', 'items.id', '=', 'scan_logs.item_id')
            ->pluck('items.name')
            ->map(fn (string $name) => Str::lower(trim($name)));
    }

    /** Semua nama barang wajib (gabungan tiap pelajaran hari itu, tanpa duplikat nama). */
    public function requiredNames(Collection $subjects): Collection
    {
        return $subjects
            ->flatMap(fn (Subject $s) => $s->requiredItems->pluck('name'))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => Str::lower($name))
            ->values();
    }

    /** Barang wajib yang belum discan siswa hari itu. */
    public function missingItems(User $student, Carbon $date, Collection $subjects): Collection
    {
        if ($subjects->isEmpty()) {
            return collect();
        }

        $scanned = $this->scannedNames($student, $date);

        return $this->requiredNames($subjects)
            ->reject(fn (string $name) => $scanned->contains(Str::lower($name)))
            ->values();
    }
}
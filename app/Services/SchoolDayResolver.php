<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Menentukan sebuah scan itu masuk fase apa dan untuk hari sekolah yang mana.
 *
 *  'packing' : sebelum pelajaran terakhir selesai (siapkan barang hari itu),
 *              ATAU mulai jam 18:00 (siapkan barang hari sekolah berikutnya)
 *  'return'  : setelah pelajaran terakhir selesai sampai jam 18:00 (cek sebelum pulang)
 *  'idle'    : hari libur / tanpa jadwal sebelum jam 18:00 (scan cuma dicatat)
 */
class SchoolDayResolver
{
    /** Mulai jam ini scan dianggap persiapan untuk hari sekolah berikutnya. */
    public const EVENING_HOUR = 18;

    /** Jadwal aktif siswa pada tanggal itu. Kosong kalau libur atau tidak ada jadwal. */
    public function subjectsOn(User $student, Carbon $date): Collection
    {
        if (Holiday::findFor($student->school_name, $student->class_id, $date)) {
            return collect();
        }

        return Subject::with('requiredItems')
            ->inActiveYear()
            ->where('class_id', $student->class_id)
            ->where('day', $date->englishDayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    /** @return array{phase:string, date:Carbon, subjects:Collection} */
    public function resolve(User $student, Carbon $now): array
    {
        $today = $now->copy()->startOfDay();
        $evening = $today->copy()->setTime(self::EVENING_HOUR, 0);
        $subjects = $this->subjectsOn($student, $today);

        if ($subjects->isNotEmpty()) {
            $lastEnd = $today->copy()->setTimeFromTimeString($subjects->max('end_time'));

            if ($now->lt($lastEnd)) {
                return $this->result('packing', $today, $subjects);
            }

            if ($now->lt($evening)) {
                return $this->result('return', $today, $subjects);
            }
        } elseif ($now->lt($evening)) {
            return $this->result('idle', $today, $subjects);
        }

        // Malam hari (atau sudah lewat jam pelajaran terakhir): siapkan hari sekolah berikutnya.
        for ($i = 1; $i <= 14; $i++) {
            $date = $today->copy()->addDays($i);
            $next = $this->subjectsOn($student, $date);

            if ($next->isNotEmpty()) {
                return $this->result('packing', $date, $next);
            }
        }

        return $this->result('idle', $today, collect());
    }

    /**
     * Pengecekan pulang hari ini sudah dibuka?
     * (hari ini ada pelajaran dan pelajaran terakhir sudah selesai)
     */
    public function returnCheckOpen(User $student, Carbon $now): bool
    {
        $today = $now->copy()->startOfDay();
        $subjects = $this->subjectsOn($student, $today);

        return $subjects->isNotEmpty()
            && $now->gte($today->copy()->setTimeFromTimeString($subjects->max('end_time')));
    }

    private function result(string $phase, Carbon $date, Collection $subjects): array
    {
        return ['phase' => $phase, 'date' => $date, 'subjects' => $subjects];
    }
}
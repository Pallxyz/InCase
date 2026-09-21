<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\SubjectRoomChange;
use Carbon\Carbon;

/**
 * Cek apakah sebuah jadwal bentrok dengan jadwal lain
 * (tahun ajaran yang sama, hari yang sama, jam saling tumpang tindih).
 *
 * Ada 3 jenis bentrok:
 *  - KELAS  : kelas yang sama punya dua pelajaran di jam yang sama
 *  - GURU   : guru yang sama mengajar di dua tempat di jam yang sama
 *  - RUANG  : ruang yang sama dipakai dua kelas di jam yang sama
 *
 * Jam yang saling menempel TIDAK dianggap bentrok
 * (07:00-08:30 dan 08:30-10:00 boleh).
 */
class ScheduleConflictChecker
{
    /**
     * @param  array{class_id:mixed, day:string, start_time:string, end_time:string, location?:?string}  $data
     * @return array<string, list<string>>  key = nama field form, value = daftar pesan
     */
    public function check(
        array $data,
        ?int $academicYearId,
        ?int $teacherId,
        ?int $ignoreSubjectId = null,
    ): array {
        $errors = [];

        // Satu query saja, lalu disaring di memori untuk tiap jenis bentrok.
        $overlaps = $this->overlapping($data, $academicYearId, $ignoreSubjectId);

        // KELAS bentrok -> ditampilkan di field "kelas"
        foreach ($overlaps->where('class_id', (int) $data['class_id']) as $other) {
            $errors['class_id'][] = sprintf(
                'Kelas %s sudah punya jadwal %s pukul %s (guru: %s).',
                $other->schoolClass->name ?? '-',
                $other->name,
                $this->range($other),
                $other->teacher->name ?? '-',
            );
        }

        // GURU bentrok -> ditampilkan di field "jam mulai"
        if ($teacherId) {
            foreach ($overlaps->where('teacher_id', $teacherId) as $other) {
                $errors['start_time'][] = sprintf(
                    'Guru sudah mengajar %s di kelas %s pukul %s.',
                    $other->name,
                    $other->schoolClass->name ?? '-',
                    $this->range($other),
                );
            }
        }

        // RUANG bentrok -> ditampilkan di field "ruang"
        $room = $this->normalize($data['location'] ?? null);

        if ($room !== '') {
            foreach ($overlaps->filter(fn (Subject $s) => $this->normalize($s->location) === $room) as $other) {
                $errors['location'][] = sprintf(
                    'Ruang %s sudah dipakai kelas %s (%s) pukul %s.',
                    trim($other->location),
                    $other->schoolClass->name ?? '-',
                    $other->name,
                    $this->range($other),
                );
            }
        }

        return $errors;
    }

    /**
     * Cek apakah ruang pengganti untuk SATU TANGGAL sudah dipakai kelas lain
     * di jam yang bertumpuk. Ruang kelas lain dihitung dengan memperhitungkan
     * perpindahan ruang mereka pada tanggal itu juga.
     *
     * @return list<string>
     */
    public function roomConflictsOn(Subject $subject, string $location, Carbon $date): array
    {
        $room = $this->normalize($location);

        if ($room === '') {
            return [];
        }

        $others = $this->overlapping([
            'day' => $subject->day,
            'start_time' => $subject->start_time,
            'end_time' => $subject->end_time,
        ], $subject->academic_year_id, $subject->id);

        if ($others->isEmpty()) {
            return [];
        }

        $moved = SubjectRoomChange::whereIn('subject_id', $others->pluck('id'))
            ->whereDate('date', $date)
            ->pluck('location', 'subject_id');

        $errors = [];

        foreach ($others as $other) {
            $effective = $moved[$other->id] ?? $other->location;

            if ($this->normalize($effective) === $room) {
                $errors[] = sprintf(
                    'Ruang %s pada %s sudah dipakai kelas %s (%s) pukul %s.',
                    trim((string) $effective),
                    $date->format('d/m/Y'),
                    $other->schoolClass->name ?? '-',
                    $other->name,
                    $this->range($other),
                );
            }
        }

        return $errors;
    }

    /**
     * Semua jadwal aktif di hari & tahun ajaran yang sama yang jamnya
     * bertumpuk dengan jam yang diminta.
     * Dua rentang bertumpuk kalau: mulai_A < selesai_B  DAN  selesai_A > mulai_B
     */
        private function overlapping(array $data, ?int $academicYearId, ?int $ignoreSubjectId): \Illuminate\Support\Collection
    {
        // Samakan format jam ke H:i:s: "12:00" vs "12:00:00" bisa dianggap beda
        // (dan jam menempel jadi dianggap bentrok) di sebagian database.
        $start = Carbon::parse($data['start_time'])->format('H:i:s');
        $end = Carbon::parse($data['end_time'])->format('H:i:s');

        return Subject::with(['schoolClass', 'teacher'])
            ->where('is_active', true)
            ->where('academic_year_id', $academicYearId)
            ->where('day', $data['day'])
            ->whereTime('start_time', '<', $end)
            ->whereTime('end_time', '>', $start)
            ->when($ignoreSubjectId, fn ($q) => $q->where('id', '!=', $ignoreSubjectId))
            ->get();
    }

    private function normalize(?string $room): string
    {
        return mb_strtolower(trim((string) $room));
    }

    private function range(Subject $subject): string
    {
        return Carbon::parse($subject->start_time)->format('H:i')
            . '–'
            . Carbon::parse($subject->end_time)->format('H:i');
    }
}
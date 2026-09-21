<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Subject;
use Carbon\Carbon;

/**
 * Ekspor jadwal (beserta barang wajib & PR) satu tahun ajaran ke CSV.
 *
 * Format CSV dibuat supaya langsung kebuka rapi di Excel Indonesia:
 *  - pemisah titik koma (;)
 *  - diawali BOM UTF-8 supaya huruf/karakter khusus tidak rusak
 */
class AcademicYearExporter
{
    public const HEADERS = [
        'Tahun Ajaran',
        'Semester',
        'Kelas',
        'Hari',
        'Jam Mulai',
        'Jam Selesai',
        'Mata Pelajaran',
        'Guru',
        'Ruang',
        'Barang Wajib',
        'PR',
        'Ujian',
        'Status',
    ];

    private const DAY_ORDER = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];

    private const DAY_LABEL = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];

    /** Nama file, contoh: jadwal-2024-2025-ganjil.csv */
    public function filename(AcademicYear $year): string
    {
        return "jadwal-{$year->year_start}-{$year->year_end}-{$year->semester}.csv";
    }

    /** Tulis seluruh CSV ke $out (resource, mis. php://output). */
    public function write(AcademicYear $year, $out): void
    {
        fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
        fputcsv($out, self::HEADERS, ';');

        foreach ($this->rows($year) as $row) {
            fputcsv($out, $row, ';');
        }
    }

    /** @return list<list<string>> */
    public function rows(AcademicYear $year): array
    {
        $subjects = Subject::with(['schoolClass', 'teacher', 'requiredItems'])
            ->where('academic_year_id', $year->id)
            ->get();

        return $this->buildRows($year, $subjects);
    }

    /**
     * Dipisah dari rows() supaya bisa dites tanpa database.
     *
     * @param  iterable<Subject>  $subjects
     * @return list<list<string>>
     */
    public function buildRows(AcademicYear $year, iterable $subjects): array
    {
        $sorted = collect($subjects)->sort(function ($a, $b) {
            return [$a->schoolClass->name ?? '', self::DAY_ORDER[$a->day] ?? 9, (string) $a->start_time]
                <=> [$b->schoolClass->name ?? '', self::DAY_ORDER[$b->day] ?? 9, (string) $b->start_time];
        });

        $rows = [];

        foreach ($sorted as $s) {
            $rows[] = array_map([$this, 'clean'], [
                "{$year->year_start}/{$year->year_end}",
                $year->semester === 'ganjil' ? 'Ganjil' : 'Genap',
                $s->schoolClass->name ?? '-',
                self::DAY_LABEL[$s->day] ?? $s->day,
                Carbon::parse($s->start_time)->format('H:i'),
                Carbon::parse($s->end_time)->format('H:i'),
                $s->name,
                $s->teacher->name ?? '-',
                $s->location ?? '',
                collect($s->requiredItems)->pluck('name')->implode(', '),
                $s->homework ?? '',
                $s->has_exam ? 'Ya' : 'Tidak',
                $s->is_active ? 'Aktif' : 'Nonaktif',
            ]);
        }

        return $rows;
    }

    /**
     * Cegah "CSV injection": sel yang diawali = + - @ dianggap rumus oleh Excel.
     * Karena mapel/PR diisi bebas oleh guru, awalnya diberi tanda petik.
     */
    private function clean(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
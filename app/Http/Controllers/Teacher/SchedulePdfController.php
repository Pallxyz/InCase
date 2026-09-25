<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class SchedulePdfController extends Controller
{
    private const DAY_LABELS = [
        'Monday'    => 'SENIN',
        'Tuesday'   => 'SELASA',
        'Wednesday' => 'RABU',
        'Thursday'  => 'KAMIS',
        'Friday'    => 'JUMAT',
        'Saturday'  => 'SABTU',
    ];

    public function export(SchoolClass $schoolClass)
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        // Hanya admin yang boleh mencetak jadwal
        abort_unless($user->role === 'admin', 403);

        // Pastikan kelas berasal dari sekolah admin
        abort_unless(
            $schoolClass->school_name === $user->school_name,
            404
        );

        // Tahun ajaran aktif
        $academicYear = AcademicYear::active()->first();
        // Ambil jadwal khusus kelas yang dipilih
        $subjects = Subject::with('teacher')
            ->where('class_id', $schoolClass->id)
            ->where('is_active', true)
            ->inActiveYear()
            ->orderByRaw("
    FIELD(
        day,
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday'
    )
")
            ->orderBy('start_time')
            ->get();

        // Susun jadwal berdasarkan hari
        $schedule = [];

        foreach (self::DAY_LABELS as $day => $label) {
            $daySubjects = $subjects
                ->where('day', $day)
                ->sortBy('start_time')
                ->values();

            $schedule[$day] = [];

            foreach ($daySubjects as $subject) {
                $start = substr((string) $subject->start_time, 0, 5);
                $end = substr((string) $subject->end_time, 0, 5);

                $schedule[$day][] = [
                    'subject' => $subject,
                    'start'   => $start,
                    'end'     => $end,
                ];
            }
        }

        // Nama sekolah
        $schoolName = $user->school_name;

        // Buat PDF
        $pdf = Pdf::loadView('schedules.pdf', [
            'schoolName'   => $schoolName,
            'schoolClass'  => $schoolClass,
            'academicYear' => $academicYear,
            'schedule'     => $schedule,
            'dayLabels'    => self::DAY_LABELS,
            'printedAt'    => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');

        // Nama file PDF
        $filename = 'jadwal-' .
            str_replace(
                [' ', '/', '\\'],
                '-',
                strtolower($schoolClass->name)
            ) .
            '.pdf';

        return $pdf->stream($filename);
    }
}

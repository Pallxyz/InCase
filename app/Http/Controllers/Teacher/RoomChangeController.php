<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Holiday;
use App\Models\Subject;
use App\Models\SubjectRoomChange;
use App\Models\User;
use App\Notifications\RoomChangedNotification;
use App\Services\ScheduleConflictChecker;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Guru memindahkan ruang belajar untuk SATU TANGGAL tertentu
 * (mis. hari ini belajar di masjid). Jadwal utama tidak berubah.
 */
class RoomChangeController extends Controller
{
    private const DAY_LABEL = [
        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
    ];

    public function store(Request $request, Subject $subject, ScheduleConflictChecker $checker): RedirectResponse
    {
        $this->authorizeTeacher($subject);

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();
        $location = trim($data['location']);
        $teacher = User::findOrFail(Auth::id());

        // Tanggalnya harus jatuh di hari yang sama dengan jadwal (mis. jadwal Senin -> tanggal harus hari Senin).
        if ($date->englishDayOfWeek !== $subject->day) {
            $hari = self::DAY_LABEL[$subject->day] ?? $subject->day;

            return back()->withErrors(['date' => "Jadwal ini hari {$hari}, pilih tanggal yang jatuh di hari {$hari}."])->withInput();
        }

        if ($subject->academic_year_id !== AcademicYear::active()?->id) {
            return back()->withErrors(['date' => 'Jadwal ini bukan milik tahun ajaran yang sedang aktif.'])->withInput();
        }

        if ($holiday = Holiday::findFor($teacher->school_name, $subject->class_id, $date)) {
            return back()->withErrors(['date' => "Tanggal itu libur ({$holiday->name})."])->withInput();
        }

        if (mb_strtolower($location) === mb_strtolower(trim((string) $subject->location))) {
            return back()->withErrors(['location' => 'Itu sudah ruang biasa untuk pelajaran ini.'])->withInput();
        }

        $conflicts = $checker->roomConflictsOn($subject, $location, $date);

        if ($conflicts !== []) {
            return back()->withErrors(['location' => $conflicts])->withInput();
        }

        SubjectRoomChange::updateOrCreate(
            ['subject_id' => $subject->id, 'date' => $date->toDateString()],
            ['location' => $location, 'changed_by' => $teacher->id],
        );

        $this->notifyClass($subject, new RoomChangedNotification($subject, $teacher->name, $location, $date));

        return back()->with('success', "Ruang {$subject->name} dipindah ke {$location} pada {$date->format('d/m/Y')}. Siswa sudah diberi tahu.");
    }

    /** Batalkan perpindahan ruang: kembali ke ruang biasa. */
    public function destroy(Subject $subject, SubjectRoomChange $roomChange): RedirectResponse
    {
        $this->authorizeTeacher($subject);
        abort_if($roomChange->subject_id !== $subject->id, 404);

        $date = Carbon::parse($roomChange->date)->startOfDay();
        $roomChange->delete();

        // Kalau tanggalnya belum lewat, siswa perlu tahu ruangnya balik ke semula.
        if ($date->gte(today()) && filled($subject->location)) {
            $teacher = User::findOrFail(Auth::id());

            $this->notifyClass($subject, new RoomChangedNotification(
                $subject, $teacher->name, $subject->location, $date, restored: true
            ));
        }

        return back()->with('success', 'Perpindahan ruang dibatalkan.');
    }

    private function notifyClass(Subject $subject, RoomChangedNotification $notification): void
    {
        User::where('role', 'student')
            ->where('class_id', $subject->class_id)
            ->get()
            ->each(fn (User $student) => $student->notify($notification));
    }

    private function authorizeTeacher(Subject $subject): void
    {
        abort_if($subject->teacher_id !== Auth::id(), 403);
    }
}
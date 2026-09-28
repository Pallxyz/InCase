<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $subjects = Subject::with(['teacher', 'schoolClass', 'requiredItems'])
            ->inActiveYear()
            ->where('class_id', $user->class_id)
            ->where('is_active', true)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Ruang pengganti hari ini (mis. pindah ke masjid) menggantikan ruang biasa,
        // supaya jadwal siswa konsisten dengan yang tampil di dashboard.
        Subject::applyRoomChanges($subjects, today());

        $classes = SchoolClass::where('school_name', $user->school_name)
            ->orderBy('grade')
            ->orderBy('major')
            ->get();

        // View schedules.index juga dipakai oleh guru/admin (Teacher\SubjectController),
        // dan menampilkan dropdown $teachers di form tambah/edit jadwal. Siswa tidak
        // boleh menambah/edit ($canAddSchedule dan $canEdit otomatis false untuk role
        // student), tapi $teachers tetap dikirim kosong-tidak-crash karena view sudah
        // mem-fallback ke collect() -- dikirim eksplisit di sini biar konsisten, bukan
        // sekadar mengandalkan fallback.
        $teachers = User::where('role', 'teacher')
            ->where('school_name', $user->school_name)
            ->orderBy('name')
            ->get();

        $school = School::where('name', $user->school_name)->first();
        $schoolDayNames = $school?->dayNames() ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('schedules.index', compact('subjects', 'classes', 'schoolDayNames', 'teachers'));
    }
}
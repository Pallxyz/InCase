<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\School;
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
            ->where('class_id', $user->class_id)
            ->where('is_active', true)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $classes = SchoolClass::where('school_name', $user->school_name)
            ->orderBy('grade')
            ->orderBy('major')
            ->get();

        $school = School::where('name', $user->school_name)->first();
        $schoolDayNames = $school?->dayNames() ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('schedules.index', compact('subjects', 'classes', 'schoolDayNames'));
    }
}

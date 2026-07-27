<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolClass; // <-- tambah ini
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['teacher', 'items'])
            ->where('class_id', Auth::user()->class_id)
            ->where('is_active', true)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $classes = SchoolClass::orderBy('grade')->orderBy('major')->get(); // <-- tambah ini

        return view('schedules.index', compact('subjects', 'classes')); // <-- tambah 'classes'
    }
}
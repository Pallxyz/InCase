<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
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

        return view('schedules.index', compact('subjects'));
    }
}

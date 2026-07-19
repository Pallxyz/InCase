<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ScanLog;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on authenticated user role.
     */
    public function index(): View
    {
        $user = Auth::user();

        $today = now()->englishDayOfWeek;

        /*
        |--------------------------------------------------------------------------
        | STUDENT DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'student') {

            $todaySubjects = Subject::with([
                    'teacher',
                    'schoolClass',
                    'items',
                ])
                ->where('class_id', $user->class_id)
                ->where('day', $today)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get();

            $items = Item::where('user_id', $user->id)
                ->orderBy('name')
                ->get();

            $todayScans = ScanLog::with('item')
                ->where('user_id', $user->id)
                ->whereDate('scanned_at', today())
                ->latest('scanned_at')
                ->get();

            $packedItemIds = $todayScans
                ->pluck('item_id')
                ->unique();

            $packedCount = $packedItemIds->count();

            $totalItems = $items->count();

            $progress = $totalItems > 0
                ? round(($packedCount / $totalItems) * 100)
                : 0;

            return view('dashboard.index', [

                'role' => 'student',

                'user' => $user,

                'todaySubjects' => $todaySubjects,

                'items' => $items,

                'todayScans' => $todayScans,

                'packedCount' => $packedCount,

                'totalItems' => $totalItems,

                'progress' => $progress,

                // Teacher only
                'subjectCount' => 0,
                'studentCount' => 0,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TEACHER DASHBOARD
        |--------------------------------------------------------------------------
        */

        $todaySubjects = Subject::with([
                'schoolClass',
                'items',
            ])
            ->where('teacher_id', $user->id)
            ->where('day', $today)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $subjectCount = Subject::where(
            'teacher_id',
            $user->id
        )->count();

        $studentCount = $todaySubjects
            ->pluck('class_id')
            ->filter()
            ->unique()
            ->pipe(function ($classes) {

                return \App\Models\User::where('role', 'student')
                    ->whereIn('class_id', $classes)
                    ->count();

            });

        return view('dashboard.index', [

            'role' => 'teacher',

            'user' => $user,

            'todaySubjects' => $todaySubjects,

            'subjectCount' => $subjectCount,

            'studentCount' => $studentCount,

            // Student only
            'items' => collect(),
            'todayScans' => collect(),
            'packedCount' => 0,
            'totalItems' => 0,
            'progress' => 0,

        ]);
    }
}
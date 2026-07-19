<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ScanLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ScanHistoryController extends Controller
{
    /**
     * Display the authenticated student's scan history.
     */
    public function index(): View
    {
        $scanLogs = ScanLog::with([
                'item',
            ])
            ->where('user_id', Auth::id())
            ->latest('scanned_at')
            ->paginate(15);

        return view('scan-history.index', compact(
            'scanLogs'
        ));
    }
}
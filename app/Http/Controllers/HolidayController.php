<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $holidays = Holiday::where('school_name', $user->school_name)
            ->with('schoolClass')
            ->orderBy('date')
            ->get();

        $classes = SchoolClass::where('school_name', $user->school_name)
            ->orderBy('grade')
            ->orderBy('major')
            ->get();

        return view('holidays.index', compact('holidays', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
        ]);

        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        Holiday::create([
            'school_name' => $user->school_name,
            'class_id' => $validated['class_id'] ?? null,
            'date' => $validated['date'],
            'name' => $validated['name'],
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        abort_if($holiday->school_name !== $user->school_name, 403);

        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display teacher schedules.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $subjects = Subject::with([
            'teacher',
            'schoolClass',
            'requiredItems',
        ])
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->orderByRaw("
                FIELD(day,
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday'
                )
            ")
            ->orderBy('start_time')
            ->get();

        $school = \App\Models\School::where('name', $user->school_name)->first();
        $schoolDayNames = $school?->dayNames() ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $classes = SchoolClass::where('school_name', $user->school_name)
            ->orderBy('grade')
            ->orderBy('major')
            ->get();

        return view('schedules.index', compact(
            'subjects',
            'classes',
            'schoolDayNames'
        ));
    }

    /**
     * Not used because application uses modal.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Store new subject.
     */
    public function store(
        StoreSubjectRequest $request
    ): RedirectResponse {

        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $data = $request->safe()->except('required_items');

        $data['teacher_id'] = $user->id;

        $subject = Subject::create($data);

        $this->syncRequiredItems($subject, $request->input('required_items'));

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Display one schedule.
     */
    public function show(
        Subject $subject
    ): JsonResponse {

        $this->authorizeTeacher($subject);

        return response()->json(
            $subject->load([
                'teacher',
                'schoolClass',
                'requiredItems',
            ])
        );
    }

    /**
     * Edit modal data.
     */
    public function edit(
        Subject $subject
    ): JsonResponse {

        $this->authorizeTeacher($subject);

        return response()->json(
            $subject->load([
                'schoolClass',
                'requiredItems',
            ])
        );
    }

    /**
     * Update subject.
     */
    public function update(
        UpdateSubjectRequest $request,
        Subject $subject
    ): RedirectResponse {

        $this->authorizeTeacher($subject);

        $subject->update(
            $request->safe()->except('required_items')
        );

        $this->syncRequiredItems($subject, $request->input('required_items'));

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Delete subject.
     */
    public function destroy(
        Subject $subject
    ): RedirectResponse {

        $this->authorizeTeacher($subject);

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Replace required items from a comma-separated string.
     */
    private function syncRequiredItems(Subject $subject, ?string $rawInput): void
    {
        $subject->requiredItems()->delete();

        if (blank($rawInput)) {
            return;
        }

        $names = collect(explode(',', $rawInput))
            ->map(fn(string $name) => trim($name))
            ->filter()
            ->unique();

        foreach ($names as $name) {
            $subject->requiredItems()->create(['name' => $name]);
        }
    }

    /**
     * Verify ownership.
     */
    private function authorizeTeacher(
        Subject $subject
    ): void {

        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        abort_if(
            $subject->teacher_id !== $user->id,
            403
        );
    }
}

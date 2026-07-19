<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Item;
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
                'items',
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

        $classes = SchoolClass::orderBy('grade')
            ->orderBy('major')
            ->get();

        $items = Item::orderBy('name')->get();

        return view('schedules.index', compact(
            'subjects',
            'classes',
            'items'
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

        $data = $request->validated();

        $data['teacher_id'] = $user->id;

        $subject = Subject::create($data);

        $subject->items()->sync(
            $request->input('items', [])
        );

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
                'items',
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
                'items',
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
            $request->validated()
        );

        $subject->items()->sync(
            $request->input('items', [])
        );

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
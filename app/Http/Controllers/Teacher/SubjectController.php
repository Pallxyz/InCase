<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Jadwal (subjects) bisa dikelola oleh:
 *  - GURU: hanya jadwal miliknya sendiri
 *  - ADMIN: semua jadwal, semua kelas, semua guru
 */
class SubjectController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        $subjects = Subject::with(['teacher', 'schoolClass', 'requiredItems'])
            ->when($user->role === 'teacher', fn ($q) => $q->where('teacher_id', $user->id))
            ->where('is_active', true)
            ->inActiveYear()
            ->orderByRaw("
                FIELD(day,
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
                )
            ")
            ->orderBy('start_time')
            ->get();

        $school = School::where('name', $user->school_name)->first();
        $schoolDayNames = $school?->dayNames() ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $classes = SchoolClass::where('school_name', $user->school_name)
            ->orderBy('grade')
            ->orderBy('major')
            ->get();

        // Admin butuh daftar guru untuk memilih siapa yang mengajar.
        $teachers = $user->role === 'admin'
            ? User::where('role', 'teacher')->where('school_name', $user->school_name)->orderBy('name')->get()
            : collect();

        return view('schedules.index', compact('subjects', 'classes', 'schoolDayNames', 'teachers'));
    }

    /**
     * Not used because application uses modal.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('subjects.index');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $activeYear = AcademicYear::active();

        abort_if(! $activeYear, 422, 'Belum ada tahun ajaran aktif. Hubungi admin sekolah.');

        $data = $request->safe()->except(['required_items', 'teacher_id']);

        $data['teacher_id'] = $request->targetTeacherId();
        $data['academic_year_id'] = $activeYear->id;

        $subject = Subject::create($data);

        $this->syncRequiredItems($subject, $request->input('required_items'));

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Jadwal berhasil dibuat.');
    }

    public function show(Subject $subject): JsonResponse
    {
        $this->authorizeManage($subject);

        return response()->json($subject->load(['teacher', 'schoolClass', 'requiredItems']));
    }

    public function edit(Subject $subject): JsonResponse
    {
        $this->authorizeManage($subject);

        return response()->json($subject->load(['schoolClass', 'requiredItems']));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorizeManage($subject);

        $data = $request->safe()->except(['required_items', 'teacher_id']);
        $data['teacher_id'] = $request->targetTeacherId();

        $subject->update($data);

        $this->syncRequiredItems($subject, $request->input('required_items'));

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorizeManage($subject);

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Jadwal berhasil dihapus.');
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
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique();

        foreach ($names as $name) {
            $subject->requiredItems()->create(['name' => $name]);
        }
    }

    /**
     * Admin boleh mengelola jadwal siapa pun. Guru hanya jadwal miliknya sendiri.
     */
    private function authorizeManage(Subject $subject): void
    {
        /** @var User $user */
        $user = User::findOrFail(Auth::id());

        if ($user->role === 'admin') {
            return;
        }

        abort_if($subject->teacher_id !== $user->id, 403);
    }
}
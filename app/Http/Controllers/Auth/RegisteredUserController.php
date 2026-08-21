<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('grade')->orderBy('major')
            ->get(['id', 'name', 'grade', 'school_name']);

        $schools = School::all(['name', 'type']);

        return view('auth.register', compact('classes', 'schools'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:student,teacher'],
            'school_name' => ['required', 'string', 'max:255'],
            'days_per_week' => ['required', 'in:5,6'],
            'school_type' => ['nullable', 'in:SMK,SMA,SMP'],
            'class_id' => ['required_if:role,student', 'nullable', 'exists:school_classes,id'],
            'new_class_grade' => ['nullable', 'string', 'max:50'],
            'new_class_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Kalau sekolahnya belum pernah terdaftar, bikin baru pake pilihan hari
        // + jenis sekolah yang kedeteksi/dipilih di form ini. Kalau udah ada,
        // biarin apa adanya — gak boleh diubah diam-diam sama user baru.
        $school = School::firstOrCreate(
            ['name' => $request->school_name],
            [
                'type' => $request->school_type ?? 'SMK',
                'days_per_week' => (int) $request->days_per_week,
            ]
        );

        $classId = null;

        // Guru gak butuh class_id sama sekali. Cuma murid yang perlu kelas.
        if ($request->role === 'student') {
            if ($request->filled('class_id')) {
                $classId = $request->integer('class_id');
            } else {
                $class = SchoolClass::firstOrCreate(
                    ['name' => $request->new_class_name, 'school_name' => $school->name],
                    ['grade' => $request->new_class_grade, 'major' => $request->new_class_name]
                );

                $classId = $class->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'school_name' => $school->name,
            'class_id' => $classId,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
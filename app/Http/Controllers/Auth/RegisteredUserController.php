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

/**
 * Registrasi publik HANYA untuk siswa, dan HANYA ke kelas yang sudah ada.
 * Siswa tidak bisa membuat sekolah/kelas baru sendiri lewat form ini.
 *
 * Akun guru dibuatkan oleh admin (lihat Admin\TeacherController), bukan
 * lewat halaman registrasi publik ini.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
<<<<<<< HEAD
        $classes = SchoolClass::orderBy('grade')->orderBy('name')
=======
        $classes = SchoolClass::where('major', 'PPLG')
            ->orderBy('grade')
            ->orderBy('name')
>>>>>>> 41d2fe1772c31e0c6db946d7a657f9b13d873853
            ->get(['id', 'name', 'grade', 'school_name']);

        $schools = School::all(['name', 'type']);

        return view('auth.register', compact('classes', 'schools'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
<<<<<<< HEAD
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // Publik hanya boleh daftar sebagai siswa. Guru dibuatkan admin.
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
=======
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role' => ['required', 'in:student,teacher'],
            'school_name' => ['required', 'string', 'max:255'],
            'days_per_week' => ['required', 'in:5,6'],
            'school_type' => ['nullable', 'in:SMK,SMA,SMP'],
            'class_id' => ['required_if:role,student', 'nullable', 'exists:school_classes,id'],
            'new_class_grade' => ['nullable', 'string', 'max:50'],
            'new_class_name' => ['nullable', 'in:RPL 1,RPL 2'],
>>>>>>> 41d2fe1772c31e0c6db946d7a657f9b13d873853
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'class_id.required' => 'Pilih kelasmu.',
            'class_id.exists' => 'Kelas tidak ditemukan. Kalau kelasmu belum terdaftar, hubungi admin sekolah.',
        ]);

        $class = SchoolClass::findOrFail($data['class_id']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'school_name' => $class->school_name,
            'class_id' => $class->id,
            'role' => 'student',
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

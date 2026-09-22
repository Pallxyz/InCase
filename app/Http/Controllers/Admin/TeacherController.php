<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin membuat akun guru. Guru TIDAK bisa registrasi sendiri
 * (lihat catatan di RegisteredUserController).
 */
class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        ]);

        $admin = User::findOrFail(Auth::id());

        // Password awal dibuat acak. Admin menyampaikan ke guru secara langsung
        // (ditampilkan sekali di halaman ini setelah dibuat) -- guru bisa
        // menggantinya lewat halaman profil setelah login pertama kali.
        $password = Str::password(10);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => 'teacher',
            'class_id' => null,
            'school_name' => $admin->school_name,
        ]);

        return back()->with('success', "Akun guru {$data['name']} dibuat.")
            ->with('generated_password', $password)
            ->with('generated_email', $data['email']);
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_if($teacher->role !== 'teacher', 404);

        $teacher->delete();

        return back()->with('success', 'Akun guru dihapus.');
    }
}